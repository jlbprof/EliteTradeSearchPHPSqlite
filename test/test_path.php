<?php

include_once "lib/Configuration.php";
include_once "lib/TradeSearchDistance.php";
include_once "lib/TradeSearchSQL.php";

print "001\n";
$path = get_include_path ();
print "$path\n";

print "002\n";
$myConfig = Configuration::getInstance ();
print "003\n";

print "DataDir :" . $myConfig->data_dir . ":\n";
print "004\n";

$tradeSearchDistance = TradeSearchDistance::getInstance ();

print "MAX :" . $tradeSearchDistance->getCount () . ":\n";
print "POS :" . $tradeSearchDistance->getPOS (169, 12188) . ":\n";
print "Distance :" . $tradeSearchDistance->getDistance (169, 12188) . ":\n";

$distances = $tradeSearchDistance->getSystemsWithinDistance (169, 16);

print "Distances\n";
$count = count($distances);
for ($i = 0; $i < $count; ++$i)
{
    print "$i :" . $distances[$i] . ":\n";
}

$db = TradeSearchSQL::getInstance ();

?>
