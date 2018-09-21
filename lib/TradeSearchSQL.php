<?php

include_once "lib/Configuration.php";
include_once "lib/TradeSearchDistance.php";

/* Singleton Pattern */

/* There is no need or desire to have multiple connections open
 * to the DB, so we will have it as a singleton.
 *
 */

class TradeSearchSQL
{
    private static $instance = null;

    private $config;
    private $db_file;
    private $sqlite3_db;

    public static function getInstance ()
    {
        if (self::$instance  == null)
        {
            self::$instance = new TradeSearchSQL ();
        }

        return self::$instance;
    }

    private function __construct ()
    {
        $this->config = Configuration::getInstance ();

        $this->db_file = $this->config->data_dir . "/tradesearch.db";
        $this->sqlite3_db = new SQLite3 ($this->db_file);
    }

    public function getListOfSystems ()
    {
        $statement = $this->sqlite3_db->prepare ("SELECT name, id, idx FROM Systems;");
        $result = $statement->execute ();

        $output = array ();

        while ($res = $result->fetchArray (SQLITE3_ASSOC))
        {
            $var = $res;
            array_push ($output, $var);
        }

        return $output;
    }

    public function getSystemByID ($id)
    {
        $statement = $this->sqlite3_db->prepare ("SELECT * FROM Systems WHERE id = :id;");
        $statement->bindParam (':id', $id);
        $result = $statement->execute ();

        $output = false;
        if ($result->numColumns () > 0)
        {
            $res = $result->fetchArray (SQLITE3_ASSOC);
            if ($res)
            {
                $output = $res;
            }
        }

        return $output;
    }

    public function getSystemByIdx ($idx)
    {
        $statement = $this->sqlite3_db->prepare ("SELECT * FROM Systems WHERE idx = :idx;");
        $statement->bindParam (':idx', $idx);
        $result = $statement->execute ();

        $output = false;
        if ($result->numColumns () > 0)
        {
            $res = $result->fetchArray (SQLITE3_ASSOC);
            if ($res)
            {
                $output = $res;
            }
        }

        return $output;
    }

    public function getSystemByName ($name)
    {
        $statement = $this->sqlite3_db->prepare ("SELECT * FROM Systems WHERE name = :name;");
        $statement->bindParam (':name', $name);
        $result = $statement->execute ();

        $output = false;
        if ($result->numColumns () > 0)
        {
            $res = $result->fetchArray (SQLITE3_ASSOC);
            if ($res)
            {
                $output = $res;
            }
        }

        return $output;
    }

    public function getSystemsWithinDistance ($system_id, $distance)
    {
        $distance_obj = TradeSearchDistance::getInstance ();

        $master_system = $this->getSystemByID ($system_id);

        $systems_idx_within_distance = $distance_obj->getSystemsWithinDistance ($master_system['idx'], $distance);
        $systems_within_distance = array ();

        foreach ($systems_idx_within_distance as $systems_idx)
        {
            $system = $this->getSystemByIdx ($systems_idx);
            if ($system['id'] == $master_system['id'])
                continue;

            $systems_within_distance [] = $system;
        }

        return $systems_within_distance;
    }

    public function getStationsFromSystemID ($system_id)
    {
        $statement = $this->sqlite3_db->prepare ("SELECT * FROM stations WHERE system_id = :system_id;");
        $statement->bindParam (':system_id', $system_id);
        $result = $statement->execute ();

        $output = array ();

        while ($res = $result->fetchArray (SQLITE3_ASSOC))
        {
            $var = $res;
            array_push ($output, $var);
        }

        return $output;
    }

    public function getStationByID ($id)
    {
        $statement = $this->sqlite3_db->prepare ("SELECT * FROM stations WHERE id = :id;");
        $statement->bindParam (':id', $id);
        $result = $statement->execute ();

        $output = false;
        if ($result->numColumns () > 0)
        {
            $res = $result->fetchArray (SQLITE3_ASSOC);
            if ($res)
            {
                $output = $res;
            }
        }

        return $output;
    }

    public function getPricesByStationID ($station_id)
    {
        $statement = $this->sqlite3_db->prepare ("SELECT p.id, p.station_id, c.name AS comm_name, p.commodity_id, p.supply, p.demand, p.buy_price, p.sell_price, p.collected_at, s.name AS station_name FROM prices AS p JOIN commodities AS c ON c.id = p.commodity_id JOIN stations AS s ON p.station_id = s.id WHERE station_id = :station_id;");
        $statement->bindParam (':station_id', $station_id);
        $result = $statement->execute ();

        $output = array ();

        while ($res = $result->fetchArray (SQLITE3_ASSOC))
        {
            $var = $res;
            array_push ($output, $var);
        }

        return $output;
    }

    public function getPricesByStationIDReturnByCommID ($station_id)
    {
        $prices = $this->getPricesByStationID ($station_id);
        $output = array ();

        foreach ($prices as $price)
        {
            $output[$price['commodity_id']] = $price;
        }

        return $output;
    }

}

?>

