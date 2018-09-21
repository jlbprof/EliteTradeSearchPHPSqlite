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

    public function findTradeBetweenTwoStations ($station_id_1, $station_id_2, $min_demand)
    {
        $prices_buy = $this->sql->getPricesByStationID ($station_id_1);
        $prices_sell = $this->sql->getPricesByStationIDReturnByCommID ($station_id_2);

        $output = array ();

        $profit_item = array ();
        $profit_item['profit'] = 0;
        $profit_item['station_buy'] = $station_id_1;
        $profit_item['station_sell'] = $station_id_2;

        if (count($prices_buy) == 0 ||
            count($prices_sell) == 0)
        {
            return $profit_item;
        }

        foreach ($prices_buy as $price_buy)
        {
            $commodity_id = $price_buy['commodity_id'];

            if (!array_key_exists($commodity_id, $prices_sell))
                continue;

            $price_sell = $prices_sell[$commodity_id];

            if ($price_buy['supply'] < $min_demand ||
                $price_sell['demand'] < $min_demand)
            {
                continue;
            }

            if ($price_buy['buy_price'] >= $price_sell['sell_price'])
            {
                continue;
            }

            $this_profit = $price_sell['sell_price'] - $price_buy['buy_price'];

            if ($this_profit > $profit_item['profit'])
            {
                $profit_item['profit'] = $this_profit;
                $profit_item['commodity_id'] = $price_buy['commodity_id'];
                $profit_item['comm_name'] = $price_buy['comm_name'];
                $profit_item['station_buy_name'] = $price_buy['station_name'];
                $profit_item['station_sell_name'] = $price_sell['station_name'];
                $profit_item['station_buy_id'] = $price_buy['id'];
                $profit_item['station_sell_id'] = $price_sell['id'];
            }
        }

        return $profit_item;
    }

    public function findTradeBetweenTwoSystems ($system_id_1, $system_id_2, $params_to_consider)
    {
        $system_1 = $this->sql->getSystemByID ($system_id_1);
        $system_2 = $this->sql->getSystemByID ($system_id_2);

		$pre_stations_1 = $this->sql->getStationsFromSystemID ($system_id_1);
		$pre_stations_2 = $this->sql->getStationsFromSystemID ($system_id_2);

		$stations_1 = $this->getStations ($pre_stations_1, $params_to_consider);
		$stations_2 = $this->getStations ($pre_stations_2, $params_to_consider);

		# min_demand is on a buy/sell decision so we are not able to whittle that away

        # first we want to compare stations between systems, in both directions

		$min_demand = $params_to_consider['min_demand'];

        $system_to_system_12 = array ();
        foreach ($stations_1 as $station_buy)
        {
            foreach ($stations_2 as $station_sell)
            {
                $opportunity = $this->findTradeBetweenTwoStations ($station_buy['id'], $station_sell['id'], $min_demand);
                if ($opportunity['profit'] > 0)
                {
                    array_push ($system_to_system_12, $opportunity);
                }
            }
        }

        if (count($system_to_system_12) == 0)
        {
            return 0;
        }

        $system_to_system_21 = array ();
        foreach ($stations_2 as $station_buy)
        {
            foreach ($stations_1 as $station_sell)
            {
                $opportunity = $this->findTradeBetweenTwoStations ($station_buy['id'], $station_sell['id'], $min_demand);
                if ($opportunity['profit'] > 0)
                {
                    array_push ($system_to_system_21, $opportunity);
                }
            }
        }

        if (count($system_to_system_21) == 0)
        {
            return 0;
        }

        # let's look at intersystem trade opportunities

        $station_to_station_11 = array ();
        foreach ($stations_1 as $station_buy)
        {
            foreach ($stations_1 as $station_sell)
            {
                $opportunity = $this->findTradeBetweenTwoStations ($station_buy['id'], $station_sell['id'], $min_demand);
                if ($opportunity['profit'] > 0)
                {
                    array_push ($station_to_station_11, $opportunity);
                }
            }
        }

        $station_to_station_22 = array ();
        foreach ($stations_2 as $station_buy)
        {
            foreach ($stations_2 as $station_sell)
            {
                $opportunity = $this->findTradeBetweenTwoStations ($station_buy['id'], $station_sell['id'], $min_demand);
                if ($opportunity['profit'] > 0)
                {
                    array_push ($station_to_station_22, $opportunity);
                }
            }
        }

        print "Possible trade options\n";
        print "    " . $system_1 ['name'] . " to " . $system_2 ['name'] .  "\n";
        foreach ($system_to_system_12 as $item)
        {
            $station_buy_name  = $item ['station_buy_name'];
            $station_sell_name = $item ['station_sell_name'];
            $comm_name         = $item ['comm_name'];
            $profit            = $item ['profit'];

            print "    Stations Buy :$station_buy_name: Sell :$station_sell_name: Commodity :$comm_name: Profit $profit\n";
        }

        print "    " . $system_2 ['name'] . " to " . $system_1 ['name'] .  "\n";
        foreach ($system_to_system_21 as $item)
        {
            $station_buy_name  = $item ['station_buy_name'];
            $station_sell_name = $item ['station_sell_name'];
            $comm_name         = $item ['comm_name'];
            $profit            = $item ['profit'];

            print "    Stations Buy :$station_buy_name: Sell :$station_sell_name: Commodity :$comm_name: Profit $profit\n";
        }

        if (count ($station_to_station_11) > 0)
        {
            print "    Inter System" . $system_1 ['name'] . " to " . $system_1 ['name'] .  "\n";
            foreach ($station_to_station_11 as $item)
            {
                $station_buy_name  = $item ['station_buy_name'];
                $station_sell_name = $item ['station_sell_name'];
                $comm_name         = $item ['comm_name'];
                $profit            = $item ['profit'];

                print "    Stations Buy :$station_buy_name: Sell :$station_sell_name: Commodity :$comm_name: Profit $profit\n";
            }
        }

        if (count ($station_to_station_22) > 0)
        {
            print "    Inter System" . $system_2 ['name'] . " to " . $system_2 ['name'] .  "\n";
            foreach ($station_to_station_22 as $item)
            {
                $station_buy_name  = $item ['station_buy_name'];
                $station_sell_name = $item ['station_sell_name'];
                $comm_name         = $item ['comm_name'];
                $profit            = $item ['profit'];

                print "    Stations Buy :$station_buy_name: Sell :$station_sell_name: Commodity :$comm_name: Profit $profit\n";
            }
        }

        return 1;
    }

    public function exploreTradeFromSystemID ($master_system_id, $distance_from_master, $distance_from_trade_candidate, $params_to_consider)
    {
        $master_system = $this->sql->getSystemByID ($master_system_id);

        $systems_within_distance_from_master = $this->sql->getSystemsWithinDistance ($master_system_id, $distance_from_master);
        $count_1 = count ($systems_within_distance_from_master);

#        print "System from Master :" . $master_system ['name'] . ": :" . $count_1 . ":\n";
        if ($count_1 < 1)
        {
            print "No candidate systems\n";
            return;
        }

        foreach ($systems_within_distance_from_master as $system_1)
        {
            $systems_within_trade_distance = $this->sql->getSystemsWithinDistance ($system_1['id'], $distance_from_trade_candidate);
            $count_2 = count ($systems_within_trade_distance);

#            print "System from Candidate :" . $system_1['name'] . ": :" . $count_2 . ":\n";
            if ($count_2 < 1)
            {
#                print "No candidate systems\n";
                return;
            }

            foreach ($systems_within_trade_distance as $system_2)
            {
                $this->findTradeBetweenTwoSystems (
                    $system_1['id'],
                    $system_2['id'],
                    $params_to_consider);
            }
        }
    }
}
?>

