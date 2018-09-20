<?php

include_once "lib/Configuration.php";
include_once "lib/TradeSearchDistance.php";
include_once "lib/TradeSearchSQL.php";
include_once "lib/TradeSearch.php";

$db = TradeSearchSQL::getInstance ();
$output = $db->getListOfSystems ();

print "RESULTS\n";
#print var_dump ($output) . "\n";

$system_id_1 = 197; # 40 Ceti
$system_id_2 = 12946; # Lumba
$station_id_1 = 17874; # Fujimori Orbital

#print "40 Ceti\n";
#$output = $db->getSystemByID ($system_id_1);
#print var_dump ($output);
#
#print "Lumba\n";
#$output = $db->getSystemByID ($system_id_2);
#print var_dump ($output);
#
#print "ByName: Lumba\n";
#$output = $db->getSystemByName ("Lumba");
#print var_dump ($output);
#
#print "Lumba Stations\n";
#$output = $db->getStationsFromSystemID ($system_id_2);
#print var_dump ($output);
#
#print "Fujimori Orbital\n";
#$output = $db->getStationByID ($station_id_1);
#print var_dump ($output);
#
#print "Fujimori Orbital Prices\n";
#$output = $db->getPricesByStationID ($station_id_1);
#print var_dump ($output);

# params to consider
# minimum demand/supply 'min_demand'
# pad size S, M, L 'pad_size'
# max_age  'max_age' in number of days
# max_from_sun 'max_from_sun' max number of light seconds from sun

$params = array ();
$params ['min_demand'] = 1000;
$params ['pad_size'] = 'L';
$params ['max_age'] = 5;
$params ['max_from_sun'] = 5000;

$ts = new TradeSearch ();
$ts->findTradeBetweenTwoSystems ($system_id_1, $system_id_2, $params);

?>
