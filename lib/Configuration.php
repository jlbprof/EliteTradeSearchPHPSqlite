<?php

include_once "config.inc.php";

/* Singleton Pattern */
class Configuration
{
    private static $instance = null;

    public $data_dir;

    public static function getInstance ()
    {
        if (self::$instance  == null)
        {
            self::$instance = new Configuration ();
        }

        return self::$instance;
    }

    private function __construct ()
    {
        global $base_dir;
        $this->data_dir = $base_dir . "/EliteTradeSearchPySqlite";
    }
}

?>
