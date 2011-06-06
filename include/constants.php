<?php

define('INVALID_REQUEST',1);
define('CREATE_ACCOUNT_FORBIDDEN',2);
define('PASSWORD_FORGOTTEN',4);
define('DB_ERROR',8);

if ($conf['ENCRYPTION_ENABLED']=='true')
{
  define('EMAIL_PARAM','crypt_email');
  define('PASSWORD_PARAM','crypt_password');
} 
else 
{
  define('EMAIL_PARAM','email');
  define('PASSWORD_PARAM','password');
}
$request_parameters = array('version','rand','type',EMAIL_PARAM,PASSWORD_PARAM);


