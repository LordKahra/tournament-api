<?php

namespace kahra\src\database;

use kahra\src\database\Object;
use kahra\src\util\Set;

class Location extends Object {
    const TABLE_NAME = "locations";
    const NAME_SINGULAR = "location";
    const ALIAS = "location";

    const FIELDS_SELECT = "id,address_1,address_2,city,state,zip,country,latitude,longitude";
    const FIELDS_UPDATE = "address_1,address_2,city,state,zip,country";
    const FIELDS_INSERT = "";

    const GOOGLE_MAPS_GEOCODE_URL = "https://maps.googleapis.com/maps/api/geocode/json?address=";

    const WHERE_BY_STORE = "";

    /**
     * Geocodes addresses using Google's API.
     *
     * @link https://developers.google.com/maps/documentation/geocoding/start
     *
     * @param string $address_1
     * @param string $address_2
     * @param string $city
     * @param string $state
     * @param string $zip
     * @param string $country
     */
    static function geocode (
        string $address_1="", string $address_2="",
        string $city="", string $state="", string $zip="",
        string $country=""
    ) {
        $address = "$address_1 $address_2 $city, $state $zip";
        if ($country) $address .= ", $country";

        $url = static::GOOGLE_MAPS_GEOCODE_URL . urlencode($address) . "&key=" . getenv("API_KEY_GOOGLE_MAPS");

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // TODO: trustworthy certificate location with CURLOPT_CAINFO.
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $exec = curl_exec($curl);
        $response = json_decode($exec, true);

        /*
         *  results
         *      0
         *          geometry
         *              location
         *                  lat (float)
         *                  lng (float)
         * */

        if ($response["status"] != "OK") return array();

        $results = Set::getObject("results", $response);
        $result = Set::getObject(0, $results);

        if (!$result) return array();

        $geometry = Set::getObject("geometry", $result);
        $location = Set::getObject("location", $geometry);
        $latitude = Set::getObject("lat", $location);
        $longitude = Set::getObject("lng", $location);

        return array("latitude" => $latitude, "longitude" => $longitude);
    }

    static function createWhereClauseByStore(string $store_id) : string {
        return "id = (SELECT store.location_id FROM stores store WHERE store.id = $store_id)";
    }

    static function getByStoreId($store_id) {
        return static::get(static::createWhereClauseByStore($store_id));
    }


}