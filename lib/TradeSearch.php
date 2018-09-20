<?php

include_once "lib/Configuration.php";
include_once "lib/TradeSearchDistance.php";
include_once "lib/TradeSearchSQL.php";

class TradeSearch
{
    private $distance;
    private $sql;

    public function __construct ()
    {
        $this->distance = TradeSearchDistance::getInstance ();
        $this->sql      = TradeSearchSQL::getInstance ();
    }

	private function getStations ($stations_in, $params_to_consider)
	{
		# params to consider
		# minimum demand/supply 'min_demand'
		# pad size S, M, L 'pad_size'
		# max_age  'max_age' in number of days
		# max_from_sun 'max_from_sun' max number of light seconds from sun

		$pad_size = $params_to_consider['pad_size'];
		$max_age = $params_to_consider['max_age'];
		$max_from_sun = $params_to_consider['max_from_sun'];

		# some of these could be whittled down in the query itself, but the numbers of stations
		# are small and not worth the effort

		$stations_out = array ();
		for ($i = 0; $i < count($stations_in); ++$i)
		{
			# pad size

			if ($stations_in[$i]['max_landing_pad_size'] == 'L')
			{
			}
			else if ($stations_in[$i]['max_landing_pad_size'] == 'M')
			{
				if ($pad_size == "L")
					continue;
			}
			else if ($stations_in[$i]['max_landing_pad_size'] == 'S')
			{
				if ($pad_size != "S")
					continue;
			}
			else if ($stations_in[$i]['max_landing_pad_size'] == "None")
			{
				continue;
			}

			# max_age

			$age_cutoff = time () - ($max_age * 86400);
			if ($stations_in[$i]['updated_at'] < $age_cutoff)
				continue;

			# max_from_sun
			if ($stations_in[$i]['distance_to_star'] > $max_from_sun)
				continue;

			$stations_out [] = $stations_in [$i];
		}

		return $stations_out;
	}

    public function findTradeBetweenTwoSystems ($system_id_1, $system_id_2, $params_to_consider)
    {
		$pre_stations_1 = $this->sql->getStationsFromSystemID ($system_id_1);
		$pre_stations_2 = $this->sql->getStationsFromSystemID ($system_id_2);

		$stations_1 = $this->getStations ($pre_stations_1, $params_to_consider);
		$stations_2 = $this->getStations ($pre_stations_2, $params_to_consider);

print "Stations 1\n";
print var_dump ($stations_1);

print "Stations 2\n";
print var_dump ($stations_2);

		# min_demand is on a buy/sell decision so we are not able to whittle that away
    }
}
?>

