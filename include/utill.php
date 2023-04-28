<?php

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json');
    echo json_encode($response);
}

function verifyFields($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = $_REQUEST;

    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }

    foreach ($required_fields as $field) {
         if (!isset($request_params[$field])) {
            $error = true;
            $error_fields .= $field . ', ';
        } else if (is_array($request_params[$field]) && count($request_params[$field]) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
            
        } else if (!is_array($request_params[$field]) && strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        $app = \Slim\Slim::getInstance();
        $response["error_code"] = 1;
        $response['status'] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}

function isValidEmail($email) {

    $error = filter_var($email, FILTER_VALIDATE_EMAIL) == false;

    if ($error) {
        $app = \Slim\Slim::getInstance();
        $response["error_code"] = 1;
        $response['status'] = true;
        $response["message"] = 'Please Enter valid Email Address';
        echoResponse(400, $response);
        $app->stop();
    }
}

function cleanUsername($string) {
    $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    return preg_replace('/ +/', '', strtolower($string)); // Replaces multiple hyphens with single one.
}

function genRndDgt($length = 8, $specialCharacters = true) {
    $digits = '';
    $chars = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
    if ($specialCharacters === true)
        $chars .= "!?=/&+,.";
    for ($i = 0; $i < $length; $i++) {
        $x = mt_rand(0, strlen($chars) - 1);
        $digits .= $chars{$x};
    }
    return $digits;
}

function sendMessage($notification) {

    if (!isset($notification['player_ids']) && count($notification['player_ids']) <= 0) {
        return;
    }

    $content = array(
        "en" => $notification['message']
    );

    $fields = array(
        'app_id' => ONESIGNAL_APP_ID,
        'include_player_ids' => $notification['player_ids'],
        'data' => $notification['data'],
        'contents' => $content
    );

    $fields = json_encode($fields);
    // print("\nJSON sent:\n");
    // print($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
        'Authorization: Basic OWZjNzQxMTgtNDk5NC00ZGE0LWFiNjEtNTcwM2VjZTMyOTFj'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    
//$return = json_encode( $response);
//      print("\n\nJSON received:\n");
//	print($return);
//  print("\n");
//  die;
    
    return $response;
}


function sendStaffMessage($notification) {

    if (!isset($notification['player_ids']) && count($notification['player_ids']) <= 0) {
        return;
    }

    $content = array(
        "en" => $notification['message']
    );

    $fields = array(
        'app_id' => ONESIGNAL_APP_ID_FOR_STAFF,
        'include_player_ids' => $notification['player_ids'],
        'data' => $notification['data'],
        'contents' => $content
    );

    $fields = json_encode($fields);
    // print("\nJSON sent:\n");
    // print($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
        'Authorization: Basic OWZjNzQxMTgtNDk5NC00ZGE0LWFiNjEtNTcwM2VjZTMyOTFj'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    
//$return = json_encode( $response);
//      print("\n\nJSON received:\n");
//  print($return);
//  print("\n");
//  die;
    
    return $response;
}
function sendClientMessage($notification ) {

    if (!isset($notification['player_ids']) && count($notification['player_ids']) <= 0) {
        return;
    }

    $content = array(
        "en" => $notification['message']
    );

    $fields = array(
        'app_id' => ONESIGNAL_APP_ID_FOR_CLIENT,
        //'app_id' => 'e9167b23-3e26-4d99-9059-d1b213a8d81c',
        'include_player_ids' => $notification['player_ids'],
        'data' => $notification['data'],
        'contents' => $content
    );

    $fields = json_encode($fields);
     print("\nJSON sent:\n");
     print($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
        'Authorization: Basic OWZjNzQxMTgtNDk5NC00ZGE0LWFiNjEtNTcwM2VjZTMyOTFj'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);

// $return = json_encode( $response);
//       print("\n\nJSON received:\n");
//  print($return);
//   print("\n");
//   die;

    return $response;
}

function authenticattion($app) {

    verifyFields(array('user', 'password'));

    $user = $app->request->post('user');
    $password = $app->request->post('password');
  $rememberme = $app->request->post('rememberme');

    // $login = Admin::find_by_sql("SELECT * FROM tbl_admin where username = '$user' AND password = $password");
    $login = Admin::find(array('conditions' => "username = '$user' AND password = '$password'"));

    // $login->is_login = 1;
    //$login->save();
    if (count($login)>0) {
		
			if (isset($rememberme)) {
            setcookie('username', $login->username, time() + (86400 * 30), "/"); // 86400 = 1 day [ This is for 1 Month ]
            setcookie('password', $login->password, time() + (86400 * 30), "/"); // 86400 = 1 day [ This is for 1 Month ]
        }
		
		
        return $login->username;
    } else {
        return 'please login';
    }
}
function clientsdata($id) {

    $data = [];

    $appointment = Appointment::find('all', array('conditions' => "company_id = {$id} group by(client_id)"));
    $client = Client::find('all', array('conditions' => "company_id = {$id} "));
    if (count($appointment) > 0) {
        foreach ($appointment as $key => $value) {
            $flag = $value->client->company_id != NULL ? TRUE : FALSE;
            $data[] = array(
                'client_id' => $value->client->client_id,
                'firstname' => $value->client->firstname,
                'lastname' => $value->client->lastname,
                'emailid' => $value->client->emailid,
                'profile_image' => $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
                'contact_number' => $value->client->contact_number,
                'client_address' => $value->client->client_address,
                'client_notes' => $value->client->client_notes,
                'player_id' => $value->client->player_id,
                'isManualClient' => $flag,
            );
        }
    }
    if (count($client) > 0) {
        foreach ($client as $key => $value) {
            $data[] = array(
                'client_id' => $value->client_id,
                'firstname' => $value->firstname,
                'lastname' => $value->lastname,
                'emailid' => $value->emailid,
                'profile_image' => $value->profile_image != NULL ? USER_PIC_URL_PATH . $value->profile_image : USER_PIC_URL_PATH .'1507664260-aaaaaa.jpg',
                'contact_number' => $value->contact_number,
                'client_address' => $value->client_address,
                'client_notes' => $value->client_notes,
                'player_id' => $value->player_id,
                'isManualClient' => TRUE,
            );
        }
    }
	$clientUnique = [];
        foreach ($data as $key => $value) {
            if (!empty($clientUnique) && in_array($value['client_id'], $clientUnique)) {
                unset($data[$key]);
            }
            $clientUnique[] = $value['client_id'];
        }
    
    return $data;
}