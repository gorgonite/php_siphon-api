<?php
require_once('include/conf.php');

require_once('include/constants.php');
require_once('include/functions.php');

notification_start($conf);

if (PASSWORD_PARAM=='password' && isset($_GET[PASSWORD_PARAM]))
{
   $_GET[PASSWORD_PARAM] = prepare_password($_GET[PASSWORD_PARAM],$conf);
}
$request_body = file_get_contents('php://input');

// TODO: add password preparation if version >= 0.9.5 in the http request body

log_start($conf);

$result = array ( 'retval' => 0 );
foreach ( $request_parameters as $entry )
{
   if (! array_key_exists($entry,$_GET))
   {
      $result['retval'] |= INVALID_REQUEST;
      $result['alert_message'] = "Invalid request, not found '$entry'";
      break;
   }
}

if ( $result['retval']==0 ):

if ( $_GET['version']=='0.9.0' ):

$db = db_connect($conf, $result);

if ($db):  

$params = new stdClass();
$params->password = $_GET[PASSWORD_PARAM];
$params->email = $_GET[EMAIL_PARAM];

switch ($_GET['type'])
{
   case 'signup':
      if ('ACCOUNT_CREATION_ENABLED'!='true')
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
      if ( empty($request_body) )
      {
         $result['status_message'] = 'No Update done';
         break;
         $result['retval'] |= INVALID_REQUEST;
         $result['alert_message'] = "Invalid request, not found 'addons'";
         break;
      }
      $params->jsondata = $request_body;
      if ( set_request($params,$conf,$result) )
      {
         $result['status_message'] = 'Update data succeeded';
      }
      break;
   default:
      $result['retval'] |= INVALID_REQUEST;
      $result['alert_message'] = "Invalid request, '".$_GET['type']."' is not valid value for 'type'";
      break;
}

db_close($conf);

endif; // db

else:

$result['alert_message'] = "version " . $_GET['version'] . " is not managed currently.";

endif; // version

endif; // retval

echo json_encode($result);

