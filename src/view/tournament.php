<?php

require_once (getenv("SITE_ROOT_API_TOURNAMENT") . '/src/config/app_config.php');

use kahra\src\database\Object;
use kahra\src\database\Pairing;
use kahra\src\database\Player;
use kahra\src\database\Tournament;
use kahra\src\database\Round;
use kahra\src\database\Bye;
use kahra\src\database\Match;
use kahra\src\database\Seat;

use kahra\src\view\View;
use kahra\src\view\APIResponse;

class TournamentView extends View {
    static function show($tournaments=false) {
        if ($tournaments) {
            $tournament_ids = array();

            foreach ($tournaments as $tournament) {
                $tournament_ids[] = $tournament[Object::getPrefix() . "id"];
                $tournament["rounds"] = array();
                $tournament["players"] = array();
                $tournament["players"]["debug"] = "not null plz";
            }

            $rounds = Round::getByFields("tournament_id", $tournament_ids);
            $players = Player::getByTournamentIds($tournament_ids);

            if ($players) {
                foreach ($players as $player) {
                    // Do any adjustments.
                    // TODO: Calculate points below, or fetch via MySQL.
                    $players[$player["dci"]]["points"] = -1;
                }
            }

            if ($rounds) {
                $round_ids = array();

                foreach ($rounds as $round) {
                    $round_ids[] = $round[Object::getPrefix() . "id"];
                    $round["matches"] = array();
                    $round["byes"] = array();
                }
                // TODO: do this better. There's a getByTournamentIds function in Player.
                $matches = Match::getByFields("round_id", $round_ids);
                $byes = Bye::getByFields("round_id", $round_ids);

                if ($matches) {
                    $match_ids = array();

                    foreach ($matches as $match) {
                        $match_ids[] = $match[Object::getPrefix() . "id"];
                        $match["seats"] = array();
                    }

                    $seats = Seat::getByFields("match_id", $match_ids);

                    if ($seats) {
                        foreach($seats as $seat) {
                            $matches[$seat[Object::getPrefix() . "match_id"]]["seats"][] = $seat;

                            if ($players && !empty($players[$seat["player_id"]])) {
                                $player = $players[$seat["player_id"]];

                                $match = $matches[$seat["match_id"]];
                                $round = $rounds[$match["round_id"]];
                                $tournament_id = $round["tournament_id"];
                                $tournaments[$tournament_id]["players"][$seat["player_id"]] = $player;
                            }
                        }
                    }

                    foreach($matches as $match) {
                        $rounds[$match[Object::getPrefix() . "round_id"]]["matches"][] = $match;
                    }
                }

                foreach($rounds as $round) {
                    $tournaments[$round[Object::getPrefix() . "tournament_id"]]["rounds"][] = $round;
                }
            }

            //echo "<pre>" . View::formatSuccessResponse("Fetched tournaments.", $tournaments) . "</pre>";
            echo APIResponse::getSuccess("Fetched tournaments.", $tournaments);
        } else {
            echo APIResponse::getFailure(-1, "No tournaments found.");
        }
    }
}

// API OPTIONS

/*function getPrefix() {
    return (View::IS_ALIASED ? "tournament_" : "");
}*/

function getTournaments() {
    $tournaments = false;

    // Get the appropriate information based on the request.
    if (array_key_exists("tournament_id", $_GET)) {
        //$tournaments = View::parseResult(Tournament::getById($_GET["tournament_id"]));
        $tournaments = Tournament::getById($_GET["tournament_id"]);
    } elseif (array_key_exists("tournament_vanity_url", $_GET)) {
        $tournaments = Tournament::getByField("vanity_url", $_GET["tournament_vanity_url"]);
    } elseif (array_key_exists("tournament_name", $_GET)) {
        $tournaments = Tournament::getByField("name", $_GET["tournament_name"]);
    } else {
        //$tournaments = View::parseResult(Tournament::get());
        $tournaments = Tournament::get();
    }
    return $tournaments;
}

function show($tournaments=false) {
    if ($tournaments) {
        $tournament_ids = array();

        foreach ($tournaments as $tournament) {
            $tournament_ids[] = $tournament[Object::getPrefix() . "id"];
            $tournament["rounds"] = array();
        }

        $rounds = Round::getByFields("tournament_id", $tournament_ids);

        if ($rounds) {
            $round_ids = array();

            foreach ($rounds as $round) {
                $round_ids[] = $round[Object::getPrefix() . "id"];
                $round["matches"] = array();
                $round["byes"] = array();
            }

            $matches = Match::getByFields("round_id", $round_ids);
            $byes = Bye::getByFields("round_id", $round_ids);

            if ($matches) {
                $match_ids = array();

                foreach ($matches as $match) {
                    $match_ids[] = $match[Object::getPrefix() . "id"];
                    $match["seats"] = array();
                }

                $seats = Seat::getByFields("match_id", $match_ids);

                if ($seats) {
                    foreach($seats as $seat) {
                        $matches[$seat[Object::getPrefix() . "match_id"]]["seats"][] = $seat;
                    }
                }

                foreach($matches as $match) {
                    $rounds[$match[Object::getPrefix() . "round_id"]]["matches"][] = $match;
                }
            }

            foreach($rounds as $round) {
                $tournaments[$round[Object::getPrefix() . "tournament_id"]]["rounds"][] = $round;
            }
        }

        //echo "<pre>" . View::formatSuccessResponse("Fetched tournaments.", $tournaments) . "</pre>";
        echo APIResponse::getSuccess("Fetched tournaments.", $tournaments);
    } else {
        echo APIResponse::getFailure(-1, "No tournaments found.");
    }
}

/*$tournaments = false;
$errors = [];
$title = false;
$og_url = false;
if (array_key_exists("tournament_id", $_GET)) {
    $result = Tournament::getById($_GET["tournament_id"]);

    if ($result) {
        while ($tournament = mysqli_fetch_assoc($result)) {
            if (!$tournaments) $tournaments = array();
            $tournaments[] = $tournament;
            $title = $tournament["tournament_name"];
            $og_url = "tournament/" . $_GET["tournament_id"];
        }
        //var_dump($tournaments);
    } else {
        $errors[] = array("priority" => "low", "message" => "Tournament not found.");
    }
} elseif (array_key_exists("tournament_name", $_GET)) {
    $title = "Search Results for \"" . $_GET["tournament_name"] . "\"";
    $og_url = "tournament/" . $_GET["tournament_name"];
    $result = Tournament::getByField("name", $_GET["tournament_name"]);

    // TODO: Add on the results from other searches.

    if ($result) {
        while ($tournament = mysqli_fetch_assoc($result)) {
            if (!$tournaments) $tournaments = array();
            $tournaments[] = $tournament;
        }
    }
    else $errors[] = array("priority" => "low", "message" => "Tournament not found.");
} else {
        // Show all tournaments.
        $result = Tournament::get();

        if ($result) {
            while ($tournament = mysqli_fetch_assoc($result)) {
                if (!$tournaments) $tournaments = array();
                $tournaments[] = $tournament;
            }
        }
        $errors[] = array("priority" => "low", "message" => "There are " . count($tournaments) . " tournaments in the database.");
    //}
}

if ($tournaments) {
    $tournament_arrays = Tournament::parseRecords($tournaments);

    foreach ($tournament_arrays as $tournament) {
        $pairing_result = Pairing::getByTournament($tournament["id"]);
        $pairings = false;
        while ($pairing = mysqli_fetch_assoc($pairing_result)) {
            if (!$pairings) $pairings = array();
            $pairings[] = $pairing;
        }
        $tournament["pairings"] = $pairings ? Pairing::getMatches($pairings) : array();

        //TournamentView::printTournament($tournament);
    }

    echo View::formatSuccessResponse("Fetched tournaments.", $tournament_arrays);
    //json_encode($tournament_arrays);
} else {
    ?>No tournaments found.<?php
}*/

TournamentView::show(getTournaments());

//print_r($_GET);

// If request is empty, show the "tournament stats" page.

?>