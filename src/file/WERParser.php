<?php

namespace kahra\src\file;

use kahra\src\database\Object;
use kahra\src\database\Player;
use kahra\src\database\Pairing;
use kahra\src\database\Tournament;
use kahra\src\database\Round;
use kahra\src\database\Bye;
use kahra\src\database\Match;
use kahra\src\database\Seat;
use kahra\src\database\Upload;
use kahra\src\exception\InsertFailureException;
use kahra\src\exception\SQLException;
use kahra\src\exception\SQLInsertException;
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

    /**
     * @param bool $where
     * @param bool $forceUpdate
     *
     * @deprecated
     */
    public static function updateTournaments($where=false, $forceUpdate=false) {
        Debug::log(static::TAG, "Entered.");
        $tournaments = new Tournament();
        $records = Tournament::get($where);
        $prefix = Tournament::getPrefix();

        // If results were found, iterate through and update.
        if ($records) {
            foreach ($records as $record) {
            //while ($record = mysqli_fetch_assoc($records)) {
                Debug::log(static::TAG, "Parsing record " . $record[$prefix . "id"] . ".\");</script>");
                $fileName = SITE_ROOT . "/" . static::UPLOAD_DIRECTORY . "/" . $record[$prefix . "id"] . ".wer";

                Debug::log(static::TAG, getcwd());
                if (file_exists($fileName)) {
                    $testDate = filemtime($fileName);
                    $updated = intval($record[$prefix . "last_updated"]);
                    Debug::log(static::TAG, "testDate: " . $testDate . " updated: " . $updated);
                    if ($updated != $testDate OR $forceUpdate) {
                        Debug::log(static::TAG, "WERParser.updateTournaments(): Updating tournament.");
                        // TODO: This method is now broken.
                        //static::updateTournament($record);
                    } else {
                        Debug::log(static::TAG, "WERParser.updateTournaments(): No need to update. last update: " . $updated);
                    }
                    Debug::log(static::TAG, "WERParser.updateTournaments(): Done parsing.");
                } else {
                    // TODO: Better support for issues with missing files.
                    Debug::log(static::TAG, "File missing for tournament " . $record[$prefix . "id"] . ".");
                    Debug::log(static::TAG, "File: " . $fileName);
                }
            }
        } else {
            Debug::log(static::TAG, "No records found.");
        }
    }

    /**
     * @param $upload_id
     *
     * @throws SQLException if the upload_id is invalid.
     * @throws SQLInsertException
     */
    public static function updateTournament($upload_id, $tournament_id, $upload_data) {
        // Fetch the upload.
        $uploads = Upload::getById($upload_id);
        $upload = false;
        foreach ($uploads as $currentUpload) $upload = $currentUpload;
        if (!$upload) {
            // The upload_id is wrong.
            throw new SQLException("Failed to find the file upload metadata.");
        }
        //$tournament_id = $upload["tournament_id"];

        $prefix = Tournament::getPrefix();

        // Delete the old tournament data.
        Tournament::resetData($tournament_id);

        $filename = TOURNAMENT_UPLOAD_DIRECTORY . "/" . $upload["id"] . ".wer";

        // Get the file data.
        //$body = file_get_contents($filename);
        $body = $upload_data;
        // TODO: More error checking.

        // Update the player data.
        static::updatePlayerData($body);

        // Update the tournament.
        //static::handleWERText($body, $tournament_id);
        Tournament::generateData(new WERDocument($body), $tournament_id);

        // Set the tournament as last updated now.
        $fields = array(
            "last_updated" => filemtime($filename)
        );
        Tournament::update($fields, "id = " . $tournament_id);

        // Notify the players.
        static::notifyPlayers($tournament_id);
    }

    public static function notifyPlayers($tournamentID) {
        return;
        // TODO: Needs major overhaul.
        Debug::log(static::TAG, "notifyPlayers(): Entered.");
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
                Debug::log(static::TAG, "player needs to be notified at " . $record["user_email"]);

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

    // Updates the tournament data.
    public static function handleWERText($body, $tournamentID) {
        Debug::log(static::TAG, "handleWERText(): Entered.");

        // Update the player data.
        static::updatePlayerData($body);

        $dom = new WERDocument($body);
        $participation  = ($dom->getElementsByTagName(WERParser::TAG_PARTICIPATION));
        $participation  = $participation->item(0);
        $matches        = $dom->getElementsByTagName(WERParser::TAG_MATCHES);
        $matches        = $matches->item(0);
        $pods           = $dom->getElementsByTagName(WERParser::TAG_PODS);
        $pods           = $pods->item(0);
        $seats          = $dom->getElementsByTagName(WERParser::TAG_SEATS);
        $seats          = $seats->item(0);

        //Debug::log(static::TAG, "handleWERText(): Elements created.");

        // HANDLING PARTICIPATION
        // FUCK IT WE HAVE A FUNCTION FOR THAT

        // HANDLING MATCHES
        $rounds = $matches->getElementsByTagName(WERParser::TAG_ROUND);
        $roundArray = array();
        $assortedMatches = array();

        //Debug::log(static::TAG, "handleWERText(): Iterating through rounds.");

        $roundInsertData = array();

        foreach ($rounds as $round) {
            // Generate insert data.
            $roundInsertData[] = array(
                "tournament_id" => $tournamentID,
                "index" => $round->getAttribute(WERParser::ATTRIBUTE_ROUND_NUMBER)
            );
        }

        // Insert and fetch the rounds.
        Round::bulkInsert($roundInsertData);
        $roundResult = Round::getByTournamentId($tournamentID);

        // Iterate through the round data, generating match and bye data.
        $matchInsertData = array();
        $seatInsertData = array();
        $byeInsertData = array();
        $roundIds = array();
        $roundMap = array();

        foreach ($rounds as $round) {
            // Get the ID.
            $roundId = false;
            foreach ($roundResult as $result) {
                if ($result["round_index"] == $round->getAttribute(WERParser::ATTRIBUTE_ROUND_NUMBER)) {
                    $roundId = $result["round_id"];
                    break;
                }
            }
            if (!$roundId) throw new InsertFailureException("Failed to insert the round.");

            $roundMap[$roundId] = $round;
            $roundIds[] = $roundId;

            $currentTable = 0;
            foreach ($round->getElementsByTagName(WERParser::TAG_MATCH) as $match) {
                $bye = !($match->hasAttribute(WERParser::ATTRIBUTE_MATCH_OPPONENT));
                $person = $match->getAttribute(WERParser::ATTRIBUTE_MATCH_PERSON);

                if ($bye) {
                    $byeInsertData[] = array(
                        "round_id" => $roundId,
                        "player_id" => $person
                    );
                } else {
                    $currentTable++;
                    $gameWins   = max(0, $match->getAttribute(WERParser::ATTRIBUTE_MATCH_WINS));
                    $gameLosses = max(0, $match->getAttribute(WERParser::ATTRIBUTE_MATCH_LOSSES));
                    $gameDraws  = max(0, $match->getAttribute(WERParser::ATTRIBUTE_MATCH_DRAWS));
                    $person2 = $match->getAttribute(WERParser::ATTRIBUTE_MATCH_OPPONENT);

                    $matchInsertData[] = array(
                        "round_id" => $roundId,
                        "table" => $currentTable,
                        "draws" => $gameDraws
                    );
                    $seatInsertData[$currentTable] = array(
                        array(
                            "player_id" => $person,
                            "wins" => $gameWins
                        ),
                        array (
                            "player_id" => $person2,
                            "wins" => $gameLosses
                        )
                    );
                }
            }
        }

        // Insert the matches and byes.
        Match::bulkInsert($matchInsertData);
        Bye::bulkInsert($byeInsertData);

        // Fetch the matches.
        $matchResult = Match::getByFields("round_id", $roundIds);

        // Map the matches.
        $matchTableToIdMap = array();
        foreach ($matchResult as $matchRecord) {
            $matchTableToIdMap[$matchRecord["match_table"]] = $matchRecord["match_id"];
        }

        // Update seat insert data.
        $finalSeatInsertData = array();
        foreach ($seatInsertData as $table => $seats) {
            foreach ($seats as $seat) {
                $seat["match_id"] = $matchTableToIdMap[$table];
                $finalSeatInsertData[] = $seat;
            }
        }

        // Insert the seats.
        Seat::bulkInsert($finalSeatInsertData);

        /*foreach($rounds as $round) {
            $currentRound = $round->getAttribute(WERParser::ATTRIBUTE_ROUND_NUMBER);

            // Insert the round.



            $matchArray = array();
            $seatArray = array();
            $byeArray = array();

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
                    "match" => array(
                        "tournament_id" => $tournamentID,
                        "table" => ($bye ? 0 : $currentMatch),
                    )
                );

                $matchObject = array(
                    "tournament_id" => $tournamentID,
                    "round" => $currentRound,
                    "table_id" => ($bye ? 0 : $currentMatch),
                    //"player_id" => $person,
                    "is_bye" => $bye ? 1 : 0,
                    "points" => (!$matchDone ? 0 : ($matchWin ? 3 : ($matchDraw ? 1 : 0)))
                );

                $seatObject = array(
                    "match_id" => ,
                    "player_id" => $person
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
        }*/

        Debug::log(static::TAG, "handleWERText(): Done iterating through rounds. Objects inserted successfully.");

        //Pairing::upsert($assortedMatches);

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