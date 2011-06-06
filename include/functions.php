<?php

if ( ! function_exists('json_encode') )
{
   require_once('JSON.php');

   function json_decode($arg) {
      $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
      return $json->decode($arg);
   } 

   function json_encode($arg) {
      $json = new Services_JSON();
      return $json->encode($arg);
   }
}

function notification_start($conf)
{
   if ( $conf['debug']=='true' )
   {
      error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
   }
}

function log_content()
{
   global $request_body;

   $log = "GET : " . json_encode($_GET)
        . "\nBODY : \n"
        . $request_body
        . "\n\n";

   return $log;
}

function log_format($content,&$conf)
{
   $res = '';
   if ( $conf['log_sql']!='true' )
   {
      $res = "%%%%%%%%%%%%%%%%%%\n"
           . date("Y-m-d H:i")
           . "\n";
   }
   return $res.$content;
}

function log_start($conf)
{
   if ( $conf['enable_log']!='true' )
   { return; }

   $log_content = log_format(log_content(),$conf);

   if ( $conf['log_sql']!='true' )
   {
      $fp = fopen($conf['log_filename'], 'a');
      if ( $fp==FALSE && $conf['debug']=='true' )
      {
         echo "Unable to open the log file $log<br/>\n"; 
         echo "<pre>$log_content</pre>";
      }
      else
      {
         fwrite($fp,$log_content);
         fclose($fp);
      }
   }
   else
   {
       $res = array();
       $db = db_connect($conf,$res);
       if ( !$db && $conf['debug']=='true' )
       {
          echo "Unable to open the log file $log<br/>\n"; 
          echo "<pre>$log_content</pre>";
       }
       else
       {
          $req = "INSERT INTO " . $conf['db_table']['log'] 
               . "(date,content) VALUES ( NOW(), " 
               . "'" . mysql_escape_string($log_content) . "')";
          $res = mysql_query($req,$conf['db_link']); 
          db_close($conf);
       }
   }
}

function prepare_password($password,&$conf)
{
   switch ($conf['PASSWORD_HASH'])
   {
      case 'des':
         $res = crypt($password);
         break;
      case 'md5':
         $res = md5($password);
         break;
      case 'sha1':
      default:
         $res = sha1($password);
         break;
   }
   return $res;
}

function db_connect(&$conf, &$result)
{
   $conf['db_link'] = mysql_connect($conf['db_conf']['host'],$conf['db_conf']['user'],$conf['db_conf']['pass']);
   if ( ! $conf['db_link'] )
   {
      $result['retval'] |= DB_ERROR;
      $result['alert_message'] = 'Database connection error';
      return FALSE;
   }

   $database = mysql_select_db($conf['db_conf']['name'],$conf['db_link']);
   if ( ! $database )
   {
      $result['retval'] |= DB_ERROR;
      $result['alert_message'] = 'Database connection error';
      return FALSE;
   }

   return TRUE;
}

function db_close(&$conf)
{
   mysql_close($conf['db_link']);
}

function signup_request(&$params,&$conf,&$result)
{
   $req = "INSERT INTO " . $conf['db_table']['user'] . " VALUES ("
        . "'" . mysql_escape_string($params->email) . "',"
        . "'" . mysql_escape_string($params->password) . "',"
        . "'[]','' )" ;
   if ( ! mysql_query($req,$conf['db_link']) )
   {
      $result['retval'] |= DB_ERROR;
      $result['alert_message'] = 'Database insertion error';
      return FALSE;
   }
   return TRUE;
}

function get_request(&$params,&$conf,&$result)
{
   $req = "SELECT jsondata FROM " . $conf['db_table']['user'] . " WHERE "
        . "email='" . mysql_escape_string($params->email) . "' "
        . "AND password='" . mysql_escape_string($params->password) . "' "
        . "LIMIT 1";
   $req_result = mysql_query($req,$conf['db_link']); 
   if ( !$req_result || mysql_num_rows($req_result)==0 )
   {
      $result['retval'] |= DB_ERROR;
      $result['alert_message'] = 'Database get data error';
      return FALSE;
   }
   return json_decode(mysql_result($req_result,0));
}

function set_request(&$params,&$conf,&$result)
{
   $req = "UPDATE " . $conf['db_table']['user'] . " SET "
        . "jsondata='" . mysql_escape_string($params->jsondata) . "' "
        . "WHERE email='" . mysql_escape_string($params->email) . "' "
        . "AND password='" . mysql_escape_string($params->password) . "' "
        . "LIMIT 1";
   if ( ! mysql_query($req,$conf['db_link']) )
   {
      $result['retval'] |= DB_ERROR;
      $result['alert_message'] = 'Database update error';
      return FALSE;
   }
   return TRUE;
}


