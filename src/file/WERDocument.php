<?php

namespace kahra\src\file;

use kahra\src\util\Debug;

class WERDocument extends \DOMDocument {
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

    public $rounds;

    //public $participation = false;
    public $matches;
    public $byes;
    //public $pods = false;
    public $seats;

    // retrieved with file_get_contents
    public function __construct($body) {
        //$dom = new \DOMDocument();
        $this->loadXML($body);

        $this->createRoundData();
        $this->createByeData();

        $participation    = ($this->getElementsByTagName(static::TAG_PARTICIPATION));
        $participation    = $participation->item(0);
        $pods             = $this->getElementsByTagName(static::TAG_PODS);
        $pods             = $pods->item(0);
        $matches          = $this->getElementsByTagName(static::TAG_MATCHES);
        $matches          = $matches->item(0);
        $seats            = $this->getElementsByTagName(static::TAG_SEATS);
        $seats            = $seats->item(0);

        $rounds = $matches->getElementsByTagName(static::TAG_ROUND);

        Debug::log(static::TAG, "handleWERText(): Iterating through rounds.");

        $matchData = array();
        $seatData = array();

        $currentTable = 0;

        foreach ($rounds as $round) {
            $round_index = $round->getAttribute(static::ATTRIBUTE_ROUND_NUMBER);
            $matchData[$round_index] = array();

            foreach ($round->getElementsByTagName(static::TAG_MATCH) as $match) {
                $bye = !($match->hasAttribute(WERParser::ATTRIBUTE_MATCH_OPPONENT));
                $person = $match->getAttribute(WERParser::ATTRIBUTE_MATCH_PERSON);

                if (!$bye) {
                    $currentTable++;
                    $gameWins   = max(0, $match->getAttribute(static::ATTRIBUTE_MATCH_WINS));
                    $gameLosses = max(0, $match->getAttribute(static::ATTRIBUTE_MATCH_LOSSES));
                    $gameDraws  = max(0, $match->getAttribute(static::ATTRIBUTE_MATCH_DRAWS));
                    $person2 = $match->getAttribute(static::ATTRIBUTE_MATCH_OPPONENT);

                    $matchData[$round_index][$currentTable] = array(
                        "round_index" => $round_index,
                        "table_id" => $currentTable,
                        "draws" => $gameDraws
                    );

                    $seatData[$currentTable] = array(
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

        $this->matches = $matchData;
        $this->seats = $seatData;
        /*
        echo "<h2>Rounds</h2>";
        var_dump($this->rounds);
        echo "<h2>byes</h2>";
        var_dump($this->byes);
        echo "<h2>matches</h2>";
        var_dump($this->matches);
        echo "<h2>seats</h2>";
        var_dump($this->seats);
        */
    }

    private function createRoundData() {
        $matches          = $this->getElementsByTagName(static::TAG_MATCHES);
        $matches          = $matches->item(0);
        $rounds = $matches->getElementsByTagName(static::TAG_ROUND);

        $roundData = array();

        foreach ($rounds as $round) {
            // Generate insert data.
            $round_index = $round->getAttribute(static::ATTRIBUTE_ROUND_NUMBER);
            $roundData[] = array(
                "r_index" => $round_index
            );
        }

        $this->rounds = $roundData;
    }

    private function createByeData() {
        $matches          = $this->getElementsByTagName(static::TAG_MATCHES);
        $matches          = $matches->item(0);
        $rounds = $matches->getElementsByTagName(static::TAG_ROUND);

        $byeData = array();

        foreach ($rounds as $round) {
            $round_index = $round->getAttribute(static::ATTRIBUTE_ROUND_NUMBER);
            $byeData[$round_index] = array();

            foreach ($round->getElementsByTagName(static::TAG_MATCH) as $match) {
                $bye = !($match->hasAttribute(WERParser::ATTRIBUTE_MATCH_OPPONENT));
                $person = $match->getAttribute(WERParser::ATTRIBUTE_MATCH_PERSON);

                if ($bye) {
                    $byeData[$round_index][] = array(
                        "player_id" => $person,
                        "round_index" => $round_index
                    );
                }
            }
        }

        $this->byes = $byeData;
    }

    function update($tournament_id) {
        // Delete all the old data.

    }
}