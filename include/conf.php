<?php

$conf = array(
   'debug' => 'false',
   'enable_log' => 'false',
   'log_sql' => 'true',
//   'log_filename' => preg_replace('/(.+)\.([^\.]+)/i','$1.log',$_SERVER['SCRIPT_FILENAME']),
   'db_conf' => array(
     'type' => 'mysql',
     'host' => '',
     'user' => '',
     'pass' => '',
     'name' => ''
   ),
   'db_table' => array(
      'user' => 'siphon_users',
      'log' => 'siphon_log'
   ),
   'PASSWORD_HASH' => 'sha1',
   'ENCRYPTION_ENABLED' => 'false',
   'ACCOUNT_CREATION_ENABLED' => 'false'
);


