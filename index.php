<?php
require_once('include/conf.php');

require_once('include/constants.php');
require_once('include/functions.php');

notification_start($conf);

$request_body = http_request_body();

$result = array ( 'retval' => 0 );

$params = new stdClass();
$params->version = $_GET['version'];

check_parameters_in_array(prepare_required_get_arguments($params),$_GET,$result);

if ( $result['retval']==0 ):

if ( strcmp($params->version,'0.9.5')>=0 )
{
   $json_body = json_decode($request_body, true);
   check_parameters_in_array(array('type',PASSWORD_PARAM,EMAIL_PARAM),$json_body,$result);
   if ( PASSWORD_PARAM=='password' && isset($json_body[PASSWORD_PARAM]) )
   {
      blur_password_before_log($json_body[PASSWORD_PARAM],$conf);
	  $request_body = json_encode($json_body);
   }
   $params->type = $json_body['type'];
   $params->password = $json_body[PASSWORD_PARAM];
   $params->email = $json_body[EMAIL_PARAM];
   $params->jsondata = json_encode($json_body['addons']);
}
else
{
   if ( PASSWORD_PARAM=='password' )
   {
      blur_password_before_log($_GET[PASSWORD_PARAM],$conf);
   }
   $params->type = $_GET['type'];
   $params->password = $_GET[PASSWORD_PARAM];
   $params->email = $_GET[EMAIL_PARAM];
   $params->jsondata = $request_body;
}

log_start($conf);

if ( $result['retval']==0 ):

$db = db_connect($conf, $result);

if ($db):  

switch ($params->type)
{
   case 'signup':
      if ($conf['ACCOUNT_CREATION_ENABLED']!='true')
      {
         $result['retval'] |= CREATE_ACCOUNT_FORBIDDEN;
         $result['alert_message'] = 'Unable to create new user accounts';
      }
      else
      {
         if ( signup_request($params,$conf,$result) )
         {
            $result['status_message'] = 'Sign up succeeded';
         }
      }
      break;
   case 'forgot':
      $result['retval'] |= PASSWORD_FORGOTTEN;
      $result['alert_message'] = 'Unable to reset your password. Please contact the administrators';
      break;
   case 'get':
      $res = get_request($params,$conf,$result);
      if ($result['retval']==0)
      {
         $result['status_message'] = 'Get data succeeded';
         $result['addons'] = $res;
      }
      break;
   case 'set':
      if ( empty($params->jsondata) )
      {
         $result['status_message'] = 'No Update done';
         break;
         $result['retval'] |= INVALID_REQUEST;
         $result['alert_message'] = "Invalid request, not found 'addons'";
         break;
      }
      if ( set_request($params,$conf,$result) )
      {
         $result['status_message'] = 'Update data succeeded';
      }
      break;
   default:
      $result['retval'] |= INVALID_REQUEST;
      $result['alert_message'] = "Invalid request, '".$params->type."' is not valid value for 'type'";
      break;
}

db_close($conf);

endif; // db

else:

$result['alert_message'] = "version " . $params->version . " is not managed currently.";

endif; // retval

endif; // retval

echo json_encode($result);

