<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('upload_max_filesize', '40M');
 ini_set('post_max_size', '40M');
error_reporting(E_ALL);

global $user_id, $sessionData;
require __DIR__ . '/../vendor/autoload.php';
require_once "../Slim/Slim.php";
require '../config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

\Slim\Slim::registerAutoloader();

// $app = new \Slim\Slim();
$app = new \Slim\Slim(array(
    'templates.path' => '../templates'
        ));



// After instantiation
$app->add(new \Slim\Middleware\SessionCookie(array(
    'expires' => '20 minutes',
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'httponly' => false,
    'name' => 'slim_session',
    'secret' => 'CHANGE_ME',
    'cipher' => MCRYPT_RIJNDAEL_256,
    'cipher_mode' => MCRYPT_MODE_CBC
)));

$view = $app->view();
$view->setTemplatesDirectory('../templates');

$user_id = 0;

//for active records
ActiveRecord\Config::initialize(function($cfg) use($app) {
    $cfg->set_model_directory('../models');
    $cfg->set_connections(array('development' => 'mysql://' . DB_USER . ':' . DB_PASSWORD . '@' . DB_HOST . '/' . DB_NAME . ';charset=utf8'));
    $cfg->set_connections(array('production' => 'mysql://' . DB_USER . ':' . DB_PASSWORD . '@' . DB_HOST . '/' . DB_NAME . ';charset=utf8'));
    date_default_timezone_set('GMT');
    $cfg->set_default_connection('production');
});

$app->get('/', function() use($app) {
    $app->response->setStatus(200);
    echo "Petcare API V1.0";
});


require "../include/db.php";
require "../include/database.php";
require "../include/utill.php";
require "../Classes/DB.php";
require "../Classes/PHPExcel.php";
//Models
require "../models/client.php";
require "../models/lead.php";
require "../models/contract.php";
require "../models/service.php";
require "../models/company.php";
require "../models/companyService.php";
require "../models/pet.php";
require "../models/appointment.php";
require "../models/staff.php";
require "../models/price.php";
require "../models/payment.php";
require "../models/admin.php";
require "../models/notification.php";
require "../models/Credits.php";
require "../models/log.php";
require "../models/appointment_cancel.php";
require "../models/contact_backup.php";
require "../models/Credits1.php";
require "../models/pricenew.php";
require "../models/transactionlog.php";

//Routes For Apis
require "../routes/client.php";
require "../routes/contract.php";
require "../routes/company.php";
require "../routes/services.php";
//require "../routes/appointment.php";
require "../routes/pet.php";
require "../routes/staff.php";
require "../routes/admin.php";



function sendMail($username, $emailid, $url) {

    // below credential is my actual mailtrap credential

    $server = $_SERVER['SERVER_NAME'] . $url;
    $mail = new PHPMailer(true);
    try {
        //Server settings
        //$mail->SMTPDebug = 2;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'pethub@walk-abouts.co.uk';                 // SMTP username
        $mail->Password = 'mypethub12345';                          // SMTP password
        $mail->SMTPSecure = 'tls';                          // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to
        //Recipients

        $mail->setFrom('pethub@walk-abouts.co.uk', 'Mypethub');
        $mail->addAddress($emailid, $username);     // Add a recipient
         //$mail->addAddress('ellen@example.com');               // Name is optional
        // $mail->addReplyTo('info@example.com', 'Information');
        // $mail->addCC('cc@example.com');
        // $mail->addBCC('bcc@example.com');
        //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        //Content
        $mail->SMTPDebug = false;
		$mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Instructions for resetting the password for your account with Petsacre';
        $mail->Body = "
        <p>Hi,</p>
        <p>            
         We have received a request for a password reset on the account associated with this email address.
        </p>
        <p>
        To confirm and reset your password, please click <a href=\"$server\">here</a>.  If you did not initiate this request,
        please disregard this message.
        </p>
          ";
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        //echo 'Message has been sent';
    } catch (Exception $e) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}



$app->run();
