<?php

include_once "lib/Configuration.php";

/* Singleton Pattern */

/* This could have been a straight function, but I would need
 * many private static vars, so might as well keep them in a
 * Singleton class.
 */

class TradeSearchDistance
{
    private static $instance = null;

    private $config;
    private $max_count;

    public static function getInstance ()
    {
        if (self::$instance  == null)
        {
            self::$instance = new TradeSearchDistance ();
        }

        return self::$instance;
    }

    private function __construct ()
    {
        $this->config = Configuration::getInstance ();

        $fh = fopen ($this->config->data_dir . "/max_count.txt", 'r');
        $line = fgets ($fh);
        fclose ($fh);

        $this->max_count = (int)$line;
    }

    public function getCount ()
    {
        return $this->max_count;
    }

    public function getPOS ($system_idx_1, $system_idx_2)
    {
        $pos = (($system_idx_1 * $this->max_count) + $system_idx_2) * 2;
        return $pos;
    }

    public function getDistance ($system_idx_1, $system_idx_2)
    {
        $pos = $this->getPOS ($system_idx_1, $system_idx_2);
        $fh = fopen ($this->config->data_dir . "/distance_matrix.bin", 'r');
        fseek ($fh, $pos, SEEK_SET);
        $data = fread ($fh, 2);
        fclose ($fh);
        $distance_array = unpack ("S", $data);
        return $distance_array [1];
    }

    public function getSystemsWithinDistance ($system_idx, $distance)
    {
        $pos = $this->getPOS ($system_idx, 0);
        $fh = fopen ($this->config->data_dir . "/distance_matrix.bin", 'r');
        fseek ($fh, $pos, SEEK_SET);

        $length = 2 * $this->max_count;
        $data = fread ($fh, $length);

        fclose ($fh);
        $distance_array = unpack ("S*", $data);

        $systems = array ();
        for ($i = 0; $i < $this->max_count; ++$i)
        {
            $i1 = $i + 1;
            $distance_val = $distance_array[$i1];
            if ($distance_val <= $distance)
            {
                $systems [] = $i;
            }
        }

        return $systems;
    }
}

?>
