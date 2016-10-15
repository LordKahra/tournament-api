<?php

namespace kahra\src\file;

//require_once $_SERVER['DOCUMENT_ROOT'] . (strpos($_SERVER['DOCUMENT_ROOT'], 'pairings') === false ? '/pairings' : '') . '/scripts/database_connection.php';

/*require_once (SITE_ROOT . "/src/model/Player.php");
require_once (SITE_ROOT . "/src/model/Pairing.php");
require_once (SITE_ROOT . "/src/model/Tournament.php");
require_once (SITE_ROOT . "/src/util/util.php");
require_once (SITE_ROOT . "/src/util/email.php");*/

use kahra\src\model\Object;
use kahra\src\model\Player;
use kahra\src\model\Pairing;
use kahra\src\model\Tournament;
use kahra\src\util\Debug;
use kahra\src\util\Email;

class WERParser {
    const TAG = "WERParser";

    const TAG_EVENT = 'event';

    const TAG_PARTICIPATION = 'participation';
    const TAG_MATCHES = 'matches';
    const TAG_SEATS = 'seats';
    const TAG_PODS = 'pods';
    const TAG_LOG = 'log';

    const TAG_ROUND = 'round';
    const TAG_MATCH = 'match';

    const ATTRIBUTE_ROUND_NUMBER ='number';
    const ATTRIBUTE_MATCH_PERSON ='person';
    const ATTRIBUTE_MATCH_OPPONENT ='opponent';
    const ATTRIBUTE_MATCH_WINS ='win';
    const ATTRIBUTE_MATCH_LOSSES ='loss';
    const ATTRIBUTE_MATCH_DRAWS ='draw';

    const UPLOAD_DIRECTORY = "res/upload/tournament";

    /*

    WER File Structure
        event
            participation
                person
                team
                    member
                role
                    ref
            matches
                round
                    match
            seats
                table
                    seat
            pods
                podround
                    round
            log
                entry

    WER Tag Structure
    event
    person
    team
    member
    role
    ref
    round
    match
    table
    seat
    entry

     */

    public static function updateTournaments($where=false, $forceUpdate=false) {
        Debug::log(self::TAG, "Entered.");
        $tournaments = new Tournament();
        $records = Tournament::get($where);

        // If results were found, iterate through and update.
        if ($records) {
            while ($record = mysqli_fetch_assoc($records)) {
                Debug::log(self::TAG, "Parsing record " . $record["tournament_id"] . ".\");</script>");
                $fileName = SITE_ROOT . "/" . self::UPLOAD_DIRECTORY . "/" . $record["tournament_id"] . ".wer";
                Debug::log(self::TAG, getcwd());
                if (file_exists($fileName)) {
                    $testDate = filemtime($fileName);
                    $updated = intval($record["tournament_last_updated"]);
                    Debug::log(self::TAG, "testDate: " . $testDate . " updated: " . $updated);
                    if ($updated != $testDate OR $forceUpdate) {
                        Debug::log(self::TAG, "WERParser.updateTournaments(): Updating tournament.");
                        self::updateTournament($record);
                    } else {
                        Debug::log(self::TAG, "WERParser.updateTournaments(): No need to update. last update: " . $updated);
                    }
                    Debug::log(self::TAG, "WERParser.updateTournaments(): Done parsing.");
                } else {
                    // TODO: Better support for issues with missing files.
                    Debug::log(self::TAG, "File missing for tournament " . $record["tournament_id"] . ".");
                    Debug::log(self::TAG, "File: " . $fileName);
                }
            }
        } else {
            Debug::log(self::TAG, "No records found.");
        }
    }

    public static function updateTournament($record) {
        $tournaments = new Tournament();
        $pairings = new Pairing();

        // Delete the old pairings.
        Debug::log(self::TAG, "WERParser.updateTournament(): Deleting records from tournament " . $record['tournament_id'] . ".");
        //echo "<script>console.log(\"WERParser.updateTournament(): Deleting records from tournament " . $record['tournament_id'] . ".\");</script>";
        Pairing::delete("tournament_id = " . $record["tournament_id"]);

        $where = "id = " . $record["tournament_id"];
        $filename = SITE_ROOT . "/" . self::UPLOAD_DIRECTORY . "/" . $record["tournament_id"] . ".wer";
        //$filename = self::UPLOAD_DIRECTORY . "/" . $record["tournament_id"] . ".wer";

        $body = file_get_contents($filename);
        self::handleWERText($body, $record["tournament_id"]);
        // Update with the new file date.
        $fields = array(
            "last_updated" => filemtime($filename)
        );
        Tournament::update($fields, $where);

        // Notify the players.
        self::notifyPlayers($record["tournament_id"]);
    }

    public static function notifyPlayers($tournamentID) {
        Debug::log(self::TAG, "notifyPlayers(): Entered.");
        // Get the pairing data.
        $pairings = new Pairing();
        $records = $pairings->getByTournament($tournamentID);

        // Create storage variables.
        $matchIDs = array(); // dci => matchID
        $matches = array(); // match => array(dci, dci, ...);
        $emails = array(); // email => dci
        $players = array(); // dci=>array( name=>???, dci=>???

        // Parse the pairing data.
        while ($record = mysqli_fetch_assoc($records)) {
            $player = array(
                "dci" => $record["player_dci"],
                "name" => $record["player_first_name"] . " " . $record["player_last_name"],
                "table" => $record["pairing_table_id"]
            );

            // Create an array for the match if it doesn't exist.
            if(!isset($matches[$player["table"]])) $matches[$player["table"]] = array();

            // Map the table to the player.
            $matches[$player["table"]][] = $player["dci"];
            $matchIDs[$player["dci"]] = $player["table"];

            // Determine if this player needs to be notified.
            if (!empty($record["user_email"])) {
                Debug::log(self::TAG, "player needs to be notified at " . $record["user_email"]);

                $player["email"] = $record["user_email"];

                // Map the email to the dci.
                // TODO: Allow the same email for multiple players.
                $emails[$player["email"]] = $player["dci"];
            }
            // Store the player data.
            $players[$player["dci"]] = $player;
        }

        foreach($emails as $email => $dci) {
            $opponents = "";
            $matchID = $matchIDs[$dci];
            $match = $matches[$matchID];
            foreach($match as $oppDci) {
                if ($dci != $oppDci) {
                    // Opponent found. Add their name.
                    $opponents .= (!$opponents ? "" : ", ") . $players[$oppDci]["name"];
                }
            }

            $to = $email;
            $subject = "Paired against " . $opponents;
            $body = "You have been paired against " . $opponents . " at table " . $matchID;

            Email::send($to, $subject, $body);
        }
    }

    public static function handleWERText($body, $tournamentID) {
        Debug::log(self::TAG, "handleWERText(): Entered.");
        // Create database access variables.
        $pairings = new Pairing();

        // Update the player data.
        self::updatePlayerData($body);

        $dom = new \DOMDocument();
        $dom->loadXML($body);
        $participation  = ($dom->getElementsByTagName(WERParser::TAG_PARTICIPATION));
        $participation  = $participation->item(0);
        $matches        = $dom->getElementsByTagName(WERParser::TAG_MATCHES);
        $matches        = $matches->item(0);
        $pods           = $dom->getElementsByTagName(WERParser::TAG_PODS);
        $pods           = $pods->item(0);
        $seats          = $dom->getElementsByTagName(WERParser::TAG_SEATS);
        $seats          = $seats->item(0);

        Debug::log(self::TAG, "handleWERText(): Elements created.");

        // HANDLING PARTICIPATION
        // FUCK IT WE HAVE A FUNCTION FOR THAT

        // HANDLING MATCHES
        $currentRound = 0;
        $currentMatch = 0;
        $rounds = $matches->getElementsByTagName(WERParser::TAG_ROUND);
        $roundArray = array();
        $assortedMatches = array();

        Debug::log(self::TAG, "handleWERText(): Iterating through rounds.");

        foreach($rounds as $round) {
            $currentRound = $round->getAttribute(WERParser::ATTRIBUTE_ROUND_NUMBER);

            $matchArray = array();

            foreach ($round->getElementsByTagName(WERParser::TAG_MATCH) as $match) {
                $bye = !($match->hasAttribute(WERParser::ATTRIBUTE_MATCH_OPPONENT));
                if (!$bye) {
                    $currentMatch++;
                }


                $gameWins   = max(0, $match->getAttribute(WERParser::ATTRIBUTE_MATCH_WINS));
                $gameLosses = max(0, $match->getAttribute(WERParser::ATTRIBUTE_MATCH_LOSSES));
                $gameDraws  = max(0, $match->getAttribute(WERParser::ATTRIBUTE_MATCH_DRAWS));

                $matchDone  = ($gameWins + $gameLosses + $gameDraws) > 0;

                $matchWin = $bye || ($gameWins > $gameLosses);
                $matchDraw = $gameWins == $gameLosses;
                $matchLoss = !$matchWin && !$matchDraw;

                $person = $match->getAttribute(WERParser::ATTRIBUTE_MATCH_PERSON);
                $matchObject = array(
                    "tournament_id" => $tournamentID,
                    "round" => $currentRound,
                    "table_id" => ($bye ? 0 : $currentMatch),
                    "player_id" => $person,
                    "is_bye" => $bye ? 1 : 0,
                    "points" => (!$matchDone ? 0 : ($matchWin ? 3 : ($matchDraw ? 1 : 0)))
                );
                $matchArray[] = $matchObject;
                $assortedMatches[] = $matchObject;

                if (!$bye) {
                    $matchObject["player_id"] = $match->getAttribute(WERParser::ATTRIBUTE_MATCH_OPPONENT);
                    $matchObject["points"] = (!$matchDone ? 0 : ($matchWin ? 0 : ($matchDraw ? 1 : 3)));
                    $matchArray[] = $matchObject;
                    $assortedMatches[] = $matchObject;
                }
            }
            $currentMatch = 0;
            $roundArray[$currentRound] = $matchArray;
        }

        Debug::log(self::TAG, "handleWERText(): Done iterating through rounds. Upserting new pairings.");

        Pairing::upsert($assortedMatches);

        // Done! Return the pairing information.
        return $roundArray;
    }

    public static function updatePlayerData($body) {
        Player::upsert(Player::parseXML($body));
    }

    public static function parsePlayer($line) {
        //echo "Parsing player: " . $line;
        try {
            $player = array();
            // 0
            // <person id= 0"1 1206888225 1"2 first= 2"3 Carl 3"4 last= 4"5 Cantrell 5"6  middle= 6"7 W 7"8  country= 8"9 US 9"10 />
            $segments = explode("\"", $line);
            $player["dci"]       = $segments[1];
            $player["first_name"]    = $segments[3];
            $player["last_name"]     = $segments[5];
            $player["middle_initial"]      = $segments[7];
            $player["country"]  = $segments[9];
            return $player;
        } catch (Exception $e) {
            return array();
        }
    }

    public static function getTag($line) {
        $line = trim($line);
        $escaped = str_replace("<", "", str_replace(">", "", $line));
        $segments = preg_split('/\s+/', $escaped);
        if (array_key_exists(0, $segments) && !empty($segments[0])) {
            return $segments[0];
        }
        return "null";
    }

    public static function getPerson($line) {
        // TODO: Implement.

        $id = "1234";
        $first = "firstName";
        $last = "lastName";
        $middle = "mid";
        $country = "country";
        return array(
            "id" => $id,
            "first" => $first,
            "last" => $last,
            "middle" => $middle,
            "country" => $country
        );
    }

    public static function getMatch($line) {

    }

    public static function escapeLine($line) {
        $line = trim($line);
        $line = str_replace("<", "", str_replace(">", "", $line));
        return $line;
    }
}

?>