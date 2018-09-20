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
        $statement = $this->sqlite3_db->prepare ("SELECT p.id, p.station_id, c.name, p.supply, p.demand, p.buy_price, p.sell_price, p.collected_at FROM prices AS p JOIN commodities AS C ON c.id = p.commodity_id WHERE station_id = :station_id;");
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
}

?>

