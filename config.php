<?php
/**
 * Database configuration
 */
define('ROOT_URL',		'http://3.20.3.85/petscare/');
define('ROOT_URL_API',	'http://3.20.3.85/petscare/api/');

// MySql
# Master DB:
define('DB_HOST'    , 'localhost');
define('DB_PORT'    ,  3306      );
define('DB_USER'    , 'root' );
define('DB_PASSWORD', 'J+FCRQ^EsI&23+fk' );
define('DB_NAME'    , 'db_petscare' );


// Uploads
define('USER_PIC_URL',       '/uploads/user-profiles/');
define('USER_PIC_URL_PATH',       ROOT_URL.'/uploads/user-profiles/');
define('COMPANY_PIC_URL',       '/uploads/company-profiles/');
define('COMPANY_PIC_PATH',       ROOT_URL.'/uploads/company-profiles/');
define('COMPANY_BANNER_URL',     '/uploads/company-banners/');
define('COMPANY_BANNER_PATH',      ROOT_URL . '/uploads/company-banners/');

define('EXCEL_PIC_PATH',       ROOT_URL . '/files/excel/');

define('PET_PIC_URL', '/uploads/pets-image/');
define('PET_PIC_PATH', ROOT_URL . '/uploads/pets-image/');

define('STAFF_PIC_URL', '/uploads/staff-profiles/');
define('STAFF_PIC_PATH', ROOT_URL . '/uploads/staff-profiles/');

// staff proof
define('STAFF_PROOF_URL', '/uploads/staff-profiles/proof/');
define('STAFF_PROOF_PATH', ROOT_URL . '/uploads/staff-profiles/proof/');

//Extra configs
define('ONESIGNAL_APP_ID', '2e6616ff-ae34-4711-9e9b-67dfb08d6c84');
define('ONESIGNAL_APP_ID_FOR_STAFF', 'e9167b23-3e26-4d99-9059-d1b213a8d81c');
define('ONESIGNAL_APP_ID_FOR_CLIENT', '41f48d76-10f3-4555-9ba9-37d160c751d5');

define('APN_CERT','');
define('APN_CERT_PW','');


?>
