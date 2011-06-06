<?php

require_once('../include/conf.php');
require_once('../include/constants.php');
require_once('../include/functions.php');

$res = array();
$db = db_connect($conf,$res);

if ($db):

switch ($conf['db_conf']['type'])
{
   case 'mysql':

$req = "

CREATE TABLE IF NOT EXISTS `".$conf['db_table']['user']."` (
  `email` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `jsondata` text NOT NULL,
  `pwkey` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`email`)
);

";
$res = mysql_query($req,$conf['db_link']);
if ( !$res )
{
   echo "<pre>Error Database " . mysql_errno() . "\n"
        . mysql_error() . "</pre>";
}

$req = "

CREATE TABLE IF NOT EXISTS `".$conf['db_table']['log']."` (
  `log_id` bigint NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`log_id`)
);

";
$res = mysql_query($req,$conf['db_link']);
if ( !$res )
{
   echo "<pre>Error Database " . mysql_errno() . "\n"
        . mysql_error() . "</pre>";
}

      break;

   default:
      echo "Only MySQL is managed";
      break;
}

endif; // db_connect

db_close($conf);

