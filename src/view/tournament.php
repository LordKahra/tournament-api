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

use kahra\src\util\Set;
use kahra\src\view\View;
use kahra\src\view\APIResponse;

class TournamentView extends View {
    static function show($objects) {
        // TODO: Is this needed?
    }

    private static function parse($tournaments) : array {
        if ($tournaments) {
            $tournament_ids = array();

            foreach ($tournaments as $tournament) {
                $tournament_ids[] = $tournament[Object::getPrefix() . "id"];
                $tournament["rounds"] = array();
                $tournament["players"] = array();
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

            return $tournaments;

            //echo "<pre>" . View::formatSuccessResponse("Fetched tournaments.", $tournaments) . "</pre>";
            //echo APIResponse::getSuccess("Fetched tournaments.", $tournaments);
        } else {

            return array();
            //echo APIResponse::getFailure(-1, "No tournaments found.");
        }
    }

    private static function handleGetAction($action) : bool {
        $store_id = Set::get("store_id", $_GET);
        $store_vanity_url = Set::get("store_vanity_url", $_GET);
        $id = Set::get("tournament_id", $_GET);
        $vanity_url = Set::get("tournament_vanity_url", $_GET);
        $query = "";

        $tournaments = false;
        switch ($action) {
            case "mine":
                if (!isAuthenticated()) {
                    echo APIResponse::getUnauthorizedResponse("You must be logged in to see your stores.");
                    return true;
                }
                $tournaments = Tournament::getByUserId(getLoggedInUserId());
                $tournaments = static::parse($tournaments);

                if (!$tournaments) echo APIResponse::getEmptyDataResponse("User " . getLoggedInUserId() . " has no tournaments.");
                else echo APIResponse::getSuccess("You have " . count($tournaments) . " tournaments.", $tournaments);
                return true;
            case "get_by_store":
                if (!$store_id && !$store_vanity_url) {
                    return false;
                }

                $query = "tournament.store_id = ";

                if ($store_id) $query .= "'$store_id'";
                else $query .= "(SELECT store.id FROM stores store WHERE store.vanity_url = '$store_vanity_url')";

                if ($id) $query .= " AND tournament.id = '$id'";
                elseif ($vanity_url) $query .= " AND tournament.vanity_url = '$vanity_url'";

                $tournaments = Tournament::get($query);

                /*if ($store_id) {
                    $query = "tournament.store_id = '$store_id'";
                    if ($id) {
                        $query .= " AND tournament.id = '$id'";
                        //$tournaments = Tournament::get("tournament.store_id = '$store_id' AND tournament.id = '$id'");
                    } elseif ($vanity_url) {
                        $query .= " AND tournament.vanity_url = '$vanity_url'";
                        //$tournaments = Tournament::get("tournament.store_id = '$store_id' AND tournament.vanity_url = '$vanity_url'");
                    } else {
                        //$tournaments = Tournament::getByStoreId($store_id);
                    }
                    $tournaments = Tournament::get($query);
                } elseif ($store_vanity_url) {
                    $query = "tournament.store_id = (SELECT store.id FROM stores WHERE store.vanity_url = '$store_vanity_url')";
                    if ($id) {
                        $query .= " AND tournament.id = '$id'";
                        //$tournaments = Tournament::get("tournament.id = '$id' AND ");
                    } elseif ($vanity_url) {
                        $query .= " AND tournament.vanity_url = '$vanity_url'";
                        /*$tournaments = Tournament::get("tournament.vanity_url = '$vanity_url' AND tournament.store_id = (
                            SELECT store.id FROM stores WHERE store.vanity_url = '$store_vanity_url'
                        )");
                    } else {
                        /*$tournaments = Tournament::get("tournament.store_id = (
                            SELECT store.id FROM stores WHERE store.vanity_url = '$store_vanity_url'
                        )");
                    }
                    $tournaments = Tournament::get($query);
                } else {
                    // Nope. You have to provide a store.
                }*/

                break;
            case "get":
                // Get submitted data.
                $id = Set::get("tournament_id", $_GET);
                $store_id = Set::get("store_id", $_GET);
                $vanity_url = Set::get("tournament_vanity_url", $_GET);

                if ($id) $tournaments = Tournament::getById($id);
                    elseif($store_id && !$vanity_url) {

                    }
                elseif ($store_id || $vanity_url) {
                    $tournaments = Tournament::get("store_id = '$store_id' AND vanity_url = '$vanity_url'");
                }
                else $tournaments = Tournament::get();

                break;
            case "search":
                $query = Set::get("tournament_query", $_GET);

                if ($query) $tournaments = Tournament::get("");
                break;
        }

        $tournaments = static::parse($tournaments);
        if (!$tournaments) echo APIResponse::getEmptyDataResponse("No tournaments found. Query: $query");
        else echo APIResponse::getSuccess("There are " . count($tournaments) . " tournaments.", $tournaments);
        return true;
    }

    static function handleAction($action) : bool {
        switch ($action) {
            case "get_by_store":
            case "mine":
                return static::handleGetAction($action);
                /*if (!isAuthenticated()) {
                    echo APIResponse::getUnauthorizedResponse("You must be logged in to see your stores.");
                    return true;
                }
                $tournaments = Tournament::getByUserId(getLoggedInUserId());
                $tournaments = static::parse($tournaments);

                if (!$tournaments) echo APIResponse::getEmptyDataResponse("User " . getLoggedInUserId() . " has no tournaments.");
                else echo APIResponse::getSuccess("You have " . count($tournaments) . " tournaments.", $tournaments);
                return true;*/
            case "get":
                $tournaments = false;
                if (array_key_exists("tournament_id", $_GET)) $tournaments = Tournament::getById($_GET["tournament_id"]);
                //elseif (array_key_exists("tournament_name", $_GET)) $tournaments = Tournament::getByField("name", $_GET["tournament_name"]);
                else $tournaments = Tournament::get();
                $tournaments = static::parse($tournaments);
                if (!$tournaments) echo APIResponse::getEmptyDataResponse("No tournaments found.");
                else echo APIResponse::getSuccess("There are " . count($tournaments) . " tournaments.", $tournaments);
                return true;
        }
        return false;
    }
}

// API OPTIONS

/*function getPrefix() {
    return (View::IS_ALIASED ? "tournament_" : "");
}*/

/*function getTournaments() {
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
    return TournamentView::parse($tournaments);
}*/

/*function show($tournaments=false) {
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
}*/

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

//TournamentView::show(getTournaments());
TournamentView::handleRequest();
exit();

//print_r($_GET);

// If request is empty, show the "tournament stats" page.

?>