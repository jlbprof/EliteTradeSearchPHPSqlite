<?php

include_once "lib/Configuration.php";
include_once "lib/TradeSearchDistance.php";
include_once "lib/TradeSearchSQL.php";

function message_exit ($message)
{
?>
    <HTML>
    <BODY>
    <H1><?php echo $message ?></H1>
    </BODY>
    <HTML>
<?php
    exit (0);
}

if (!isset ($_GET["DO"]))
{
    message_exit ("Error: missing params");
}

if ($_GET["DO"] == "list_systems")
{
    header ("Content-Type: text/html");

    $db = TradeSearchSQL::getInstance ();
    $db->getListOfSystems ();
}

message_exit ("Error: Action not known");
?>
