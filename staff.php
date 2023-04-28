<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 *  Staff add 
 */
$app->post('/staffinsert', function () use ($app) {

    verifyFields(array('company_id', 'firstname', 'lastname', 'emailid', 'contact_number', 'username', 'password', 'address'));
    $company_id = $app->request->post('company_id');
    $username = $app->request->post('username');
    $password = $app->request->post('password');
    $firstname = $app->request->post('firstname');
    $lastname = $app->request->post('lastname');
    $profile_image = $app->request->post('profile_image');
    $emailid = $app->request->post('emailid');
    $contact_number = $app->request->post('contact_number');
    $address = $app->request->post('address');
    isValidEmail($emailid);
    $profile_image = NULL;
    if ($app->request->file('profile_image')) {
        $path_parts = pathinfo($_FILES['profile_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($firstname . '' . $lastname);
        $profile_image = time() . '-' . $name . '.' . $ext;
    }
    $proof = NULL;
    if (isset($_FILES['proof'])) {
        $path_parts = pathinfo($_FILES['proof']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($firstname . '' . $lastname);
        $proof = time() . '-' . $name . '.' . $ext;
    }
    //  else {

    //     $error_fields = 'Required field(s) proof is missing or empty';
    //     $response["error_code"] = 1;
    //     $response['status'] = true;
    //     $response["message"] = 'Required field(s) proof is missing or empty';
    //     echoResponse(400, $response);
    //     $app->stop();
    // }

    $staff = Staff::find_by_emailid($emailid);

    if ($staff) {
        $response["error_code"] = 1;
        $response["status"] = false;
        $response["message"] = "Sorry, this email already exists!";
        echoResponse(200, $response);
    } else {
        Staff::transaction(function () use ($app, $company_id, $firstname, $lastname, $emailid, $profile_image, $contact_number, $username, $proof, $address, $password) {
            $staff = new Staff();
            $staff->company_id = $company_id;
            //$staff->staff_name = $firstname;
            $staff->username = $username;
            $staff->firstname = $firstname;
            $staff->lastname = $lastname;
            $staff->salt = genRndDgt(8, false);
            $staff->password = sha1(md5($password) . $staff->salt);
            $staff->emailid = $emailid;
            $staff->profile_image = $profile_image;
            $staff->proof = $proof;
            $staff->status = "active";
            $staff->contact_number = $contact_number;
            $staff->address = $address;
            $staff->save();
            $staff->staff_id = (int) $staff->staff_id;


            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Error! Something went wrong. please try again later.';
            $response['data'] = [];

            if ($staff->staff_id > 0) {
                if ($profile_image) {
                    move_uploaded_file($_FILES['profile_image']['tmp_name'], '../' . STAFF_PIC_URL . $profile_image);
                    $staff->profile_image = $profile_image;
                }
                if ($proof) {
                    move_uploaded_file($_FILES['proof']['tmp_name'], '../' . STAFF_PROOF_URL . $proof);
                    $staff->proof = $proof;
                }
                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Staff inserted Successfully';
                $response['data'] = array(
                    'staff_id' => $staff->staff_id,
                    'company_id' => $staff->company_id,
                    'username' => $staff->username,
                    'password' => $staff->password,
                    'firstname' => $staff->firstname,
                    'lastname' => $staff->lastname,
                    'emailid' => $staff->emailid,
                    'status' => $staff->status,
                    'profile_image' => $staff->profile_image != NULL ? STAFF_PIC_PATH . $staff->profile_image : NULL,
                    'proof' => $staff->proof != NULL ? STAFF_PROOF_PATH . $staff->proof : NULL,
                    'contact_number' => $staff->contact_number,
                    'address' => $staff->address,
                );

                //            $notification = array('message' => $client_name.' has booked an appointment on '.$date,
                //            'player_ids' => array($appointment->company->player_id),
                //             'notification_flag' => 'appointment_booking',
                //            'data' => $response['data'],
                //            );
                ////            print_r($notification);
                ////            die;
                //            sendMessage($notification);
            }

            echoResponse(200, $response);
        });
    }
});



/*
 *  Staff Login
 */
$app->post('/stafflogin', function () use ($app) {
    verifyFields(array('username', 'password', 'playerid'));

    $emailid = $app->request->post('username');
    $password = $app->request->post('password');
    $playerid = $app->request->post('playerid');


    $staff = Staff::find_by_username($emailid);
    $response['error_code'] = 1;
    $response['message'] = 'Invalid credentials';
    $response['status'] = false;
    try {
        if ($staff) {
            $staff->playerid = $playerid;
            $staff->save();
            $staff_id = $staff->staff_id;
            if (sha1(md5($password) . ($staff->salt)) == ($staff->password)) {
                /* if (isset($satff->profile_image)) {
                    $staff->profile_image = STAFF_PIC_PATH . $staff->profile_image;
                } else {
                    $staff->profile_image = null;
                }*/

                $company = Company::find(array("conditions" => "company_id = $staff->company_id"));
                $companyname = $company->company_name;
                $companybanner = $company->company_banner != NULL ? COMPANY_BANNER_PATH . $company->company_banner : NULL;

                $response['error_code'] = 0;
                $response['message'] = 'Login successfully';
                $response['status'] = true;
                //$response['data'] = $client->to_array(array('except' => array('salt', 'password', 'added_on', 'updated_on')));
                $response['data'] = $response['data'] = array(
                    'staff_id' => $staff->staff_id,
                    'company_id' => $staff->company_id,
                    'company_name' => $companyname,
                    'company_banner' => $companybanner,
                    'username' => $staff->username,
                    'password' => $staff->password,
                    'firstname' => $staff->firstname,
                    'lastname' => $staff->lastname,
                    'emailid' => $staff->emailid,
                    'status' => $staff->status,
                    'profile_image' => $staff->profile_image != NULL ? STAFF_PIC_PATH . $staff->profile_image : NULL,
                    'proof' => $staff->proof != NULL ? STAFF_PROOF_PATH . $staff->proof : NULL,
                    'contact_number' => $staff->contact_number,
                    'address' => $staff->address,
                    'playerid' => $staff->playerid
                );
            }
        }

        echoResponse(200, $response);
    } catch (RecordNotFound $e) {
    }
    $app->stop();
});

/*
 * Recover passsword page
 */

$app->get('/staffrecover-password/:id/:token', function ($id, $token) use ($app) {

    $staff = Staff::find('all', array('conditions' => "staff_id = {$id} AND token = '{$token}' AND DATE( DATE_SUB( NOW() , INTERVAL 1 DAY ) ) < DATE(token_time)"));
    // print_r($staff);
    // //$a=NOW();
    // echo $id;
    // echo $token;
    // die();
    if ($staff) {

        /* $url = 'http://'.$_SERVER['SERVER_NAME']."/api/";

          echo '<form method="post" action='.ROOT_URL_API.'clientrecoverpassword>
          <table>
          <tr>
          <td><label><b>Email Address</b></label></td>
          <td><input type="text" placeholder="Enter Username" name="emailid" required></td.
          </tr>
          <tr>
          <td><label><b>New Password</b></label></td>
          <td><input type="password" placeholder="Enter Password" name="password" required></td>
          </tr>

          <tr><td><button type="submit">Reset Password</button></td></tr>
          </table>
          </form>'; */
        $app->render('staffresetpassword.php', array('path' => "staffrecover-password/{$id}"));
    } else {
        $app->render('staffresetpassword.php', array('error' => 'You not able to reset password , your token expired please try again!'));
    }
})->name('staffrecover-password');

/*staff forgot-password*/
$app->post('/staff/forgotpassword', function () use ($app) {
    verifyFields(array('emailid'));
    $emailid = $app->request->post('emailid');

    // $server = $_SERVER['SERVER_NAME'];
    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;

    $staff = Staff::find_by_emailid($emailid);

    if (empty($staff)) {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response["message"] = "Sorry, this email does not exists!";
        echoResponse(200, $response);
        $app->stop();
    } else {
        $token = genRndDgt(8, false);
        $staff->token = $token;
        $staff->token_time = date('Y-m-d H:m:i');
        $staff->save();
        $url = $app->urlFor('staffrecover-password', array('id' => $staff->id, 'token' => $staff->token));
        //echo $url;
        //die;

        $username = $staff->firstname . ' ' . $staff->lastname;
        $emailid = $staff->emailid;

        sendMail($username, $emailid, $url);
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Password reset link sent to your mailid!';
        //$response['data'] = $client->to_array();
    }
    // die;
    echoResponse(200, $response);
});


// Recover staff password
$app->post('/staffrecover-password/:id', function ($id) use ($app) {

    //  verifyFields(array('client_id', 'current_password', 'new_password'));
    verifyFields(array('new_password', 'confirm_password'));


    //$emailid = $app->request->post('emailid');
    //$currentPassword = $app->request->post('current_password');
    $newPassword = $app->request->post('new_password');
    $cpassword = $app->request->post('confirm_password');
    //print($emailid);
    //die;
    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;

    try {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Please provide correct email id.';
        // $client = Client::find($client_id);
        $staff = Staff::find($id);
        if ($cpassword == $newPassword && $staff) {
            $staff->password = sha1(md5($newPassword) . $staff->salt);
            $staff->token = NULL;
            $staff->save();
            $staff->staff_id = (int) $staff->staff_id;
            if ($staff->staff_id > 0) {
                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Your password has been updated successfully!';
                echo '<span style="color:green;">Your password has been updated successfully!<span>';
            }
        } else {
            echo '<span style="color:red;">Password does not match!<span>';
        }
        // echoResponse(200, $response);
    } catch (RecordNotFound $e) {
        $response['error_code'] = 1;
        $response['message'] = $e->getMessage();
        $response['status'] = false;
        echo $e->getMessage();
        //  echoResponse(200, $response);
    }
});
/*
 *  Staff listing 
 */
$app->get('/:compnayid/:organization/staffs/:page', function ($companyid, $organization, $page) use ($app) {

    $response['error_code'] = 1;
    $response['message'] = 'No Staff found';
    $response['status'] = false;
    $limit = 10;
    if ($page == 1) {
        $newoffset = 0;
    } else {
        $newoffset = ($page - 1) * $limit;
    }
    // $staff = Staff::find('all', array('conditions' => "company_id = {$companyid}",'limit' => $limit , 'offset' => $newoffset));

    $staff = Company::find_by_sql("SELECT sff.* FROM `tbl_staffs` sff join tbl_companies com on sff.company_id = com.company_id AND com.organization = '{$organization}' ");
    //    print_r($staff);
    //    die;
    if (count($staff) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Staff list retrive successfully.';

        $staffData = [];

        foreach ($staff as $key => $value) {
            /* new code[22-11-2017]*/
            $appointment = Appointment::find(array('conditions' => "staff_id = {$value->staff_id}"));
            if ($appointment) {
                $flag = $appointment->status != 'accepted' ? false : true;
            } else {
                $flag = false;
            }
            /*new code end*/
            $staffData[] = array(
                'isAssigned' => $flag,                        /*new code*/
                'staff_id' => $value->staff_id,
                'company_id' => $value->company_id,
                'username' => $value->username,
                'password' => $value->password,
                'firstname' => $value->firstname,
                'lastname' => $value->lastname,
                'emailid' => $value->emailid,
                'status' => $value->status,
                'profile_image' => $value->profile_image != NULL ? STAFF_PIC_PATH . $value->profile_image : NULL,
                'proof' => $value->proof != NULL ? STAFF_PROOF_PATH . $value->proof : NULL,
                'contact_number' => $value->contact_number,
                'address' => $value->address,
            );
        }
        $response['data'] = $staffData;
    }
    echoResponse(200, $response);
});


/*
 * Appointment listing for staff
 */
$app->get('/staff/:id/appointmentlist', function ($id) use ($app) {
    //$app->get('/staff/:id/appointmentlist/:page', function($id,$page) use($app) {
    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;
    $date = date('Y-m-d');
    /*$limit = 10;
    if ($page == 1) {
        $newoffset = 0;
    } else {
        $newoffset = ($page - 1) * $limit;
    }
    $appointment = Appointment::find('all',array('conditions'=> "staff_id = {$id} AND status = 'accepted' AND date =  '$date'",'limit' => $limit , 'offset' => $newoffset));*/
    $appointment = Appointment::find('all', array('conditions' => "staff_id = {$id} AND status = 'accepted' AND date =  '$date'"));

    foreach ($appointment as $key => $value) {
        $pet1[] = $value->pet->pet_name;
    }
    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointments  list for staff retrive successfully.';

        $appointmentData = [];

        foreach ($appointment as $key => $value) {

            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $serviceName = empty($service) ? NULL : $service->service_name;
            $ownerDetail = array(
                'client_id' => $value->client->client_id,
                'firstname' => $value->client->firstname,
                'lastname' => $value->client->lastname,
                'emailid' => $value->client->emailid,
                'profile_image' => $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
                'contact_number' => $value->client->contact_number,
                'client_address' => $value->client->client_address,
                'client_notes' => $value->client->client_notes,
                'player_id' => $value->client->player_id,
            );

            $status = '';
            $maincoin = [];
            for ($i = 0; $i < count($pet1); $i++) {
                // echo $haha."</br>";

                // print_r($maincoin);


                if (strcmp($pet1[$i], $value->pet->pet_name) === 0) {
                    $maincoin[] = 'a';
                    $status = '';
                    // echo "=========================$pet_names[$i]        ".$value2->pet_name.'==========</br>';
                }
                if (count($maincoin) > 1) {
                    $status = 'abcd';
                    // echo "$pet_names[$i]        ".$value2->pet_name.'</br>';
                    break;
                }
            }


            if ($status == 'abcd') {
                $petfull = $value->pet->pet_name . " " . $value->client->lastname;
            } else {
                $petfull = $value->pet->pet_name;
            }


            $appointmentData[] = array(
                'appointment_id' => $value->appointment_id,
                'company_id' => $value->company_id,
                'owner_detail' => $ownerDetail,
                'service_id' => $value->service_id,
                'service_name' => $serviceName,
                'date' => $value->date,
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'status' => $value->status,
                'accepted' => $value->accepted,
                'pet_detail' => array(
                    'pet_id' => $value->pet->pet_id,
                    'pet_name' => $petfull, //$value->pet->pet_name,
                    'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                    'pet_age' => $value->pet->age,
                    'medical_detail' => $value->pet->medical_detail,
                    'pet_notes' => $value->pet->pet_notes,
                    'latitude' => $value->pet->latitude,
                    'longitude' => $value->pet->longitude,
                ),
                'message' => $value->message,
            );
        }

        $response['data'] = $appointmentData;
    }
    echoResponse(200, $response);
});

/*
 * Staff job completed Appointment listing for staff
 */
$app->post('/staff/:id/staffjobscompleted', function ($id) use ($app) {
    //$app->get('/staff/:id/appointmentlist/:page', function($id,$page) use($app) {
    verifyFields(array('date'));
    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;
    $date = date('Y-m-d', strtotime($app->request->post('date')));
    //$date = date('Y-m-d');
    /*$limit = 10;
    if ($page == 1) {
        $newoffset = 0;
    } else {
        $newoffset = ($page - 1) * $limit;
    }
    $appointment = Appointment::find('all',array('conditions'=> "staff_id = {$id} AND status = 'accepted' AND date =  '$date'",'limit' => $limit , 'offset' => $newoffset));*/
    $appointment = Appointment::find('all', array('conditions' => "staff_id = {$id} AND accepted = '1' AND date =  '$date'"));


    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointments  list for staff retrive successfully.';

        $appointmentData = [];

        foreach ($appointment as $key => $value) {

            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $serviceName = empty($service) ? NULL : $service->service_name;
            $ownerDetail = array(
                'client_id' => $value->client->client_id,
                'firstname' => $value->client->firstname,
                'lastname' => $value->client->lastname,
                'emailid' => $value->client->emailid,
                'profile_image' => $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
                'contact_number' => $value->client->contact_number,
                'client_address' => $value->client->client_address,
                'client_notes' => $value->client->client_notes,
                'player_id' => $value->client->player_id,
            );

            $appointmentData[] = array(
                'appointment_id' => $value->appointment_id,
                'company_id' => $value->company_id,
                'owner_detail' => $ownerDetail,
                'service_id' => $value->service_id,
                'service_name' => $serviceName,
                'date' => $value->date,
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'status' => $value->status,
                'accepted' => $value->accepted,
                'pet_detail' => array(
                    'pet_id' => $value->pet->pet_id,
                    'pet_name' => $value->pet->pet_name,
                    'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                    'pet_age' => $value->pet->age,
                    'medical_detail' => $value->pet->medical_detail,
                    'pet_notes' => $value->pet->pet_notes,
                    'latitude' => $value->pet->latitude,
                    'longitude' => $value->pet->longitude,
                ),
                'message' => $value->message,
            );
        }

        $response['data'] = $appointmentData;
    }
    echoResponse(200, $response);
});

/*
 * Staff job completed Appointment listing for staff
 */
$app->post('/staff/:id/staffAlljobs', function ($id) use ($app) {
    //$app->get('/staff/:id/appointmentlist/:page', function($id,$page) use($app) {
    verifyFields(array('date'));
    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;
    $date = date('Y-m-d', strtotime($app->request->post('date')));
    //$date = date('Y-m-d');
    /*$limit = 10;
    if ($page == 1) {
        $newoffset = 0;
    } else {
        $newoffset = ($page - 1) * $limit;
    }
    $appointment = Appointment::find('all',array('conditions'=> "staff_id = {$id} AND status = 'accepted' AND date =  '$date'",'limit' => $limit , 'offset' => $newoffset));*/
    $appointment = Appointment::find('all', array('conditions' => "staff_id = {$id} AND date =  '$date'"));


    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointments  list for staff retrive successfully.';

        $appointmentData = [];

        foreach ($appointment as $key => $value) {

            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $serviceName = empty($service) ? NULL : $service->service_name;
            $ownerDetail = array(
                'client_id' => $value->client->client_id,
                'firstname' => $value->client->firstname,
                'lastname' => $value->client->lastname,
                'emailid' => $value->client->emailid,
                'profile_image' => $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
                'contact_number' => $value->client->contact_number,
                'client_address' => $value->client->client_address,
                'client_notes' => $value->client->client_notes,
                'player_id' => $value->client->player_id,
            );

            $appointmentData[] = array(
                'appointment_id' => $value->appointment_id,
                'company_id' => $value->company_id,
                'owner_detail' => $ownerDetail,
                'service_id' => $value->service_id,
                'service_name' => $serviceName,
                'date' => $value->date,
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'status' => $value->status,
                'accepted' => $value->accepted,
                'pet_detail' => array(
                    'pet_id' => $value->pet->pet_id,
                    'pet_name' => $value->pet->pet_name,
                    'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                    'pet_age' => $value->pet->age,
                    'medical_detail' => $value->pet->medical_detail,
                    'pet_notes' => $value->pet->pet_notes,
                    'latitude' => $value->pet->latitude,
                    'longitude' => $value->pet->longitude,
                ),
                'message' => $value->message,
            );
        }

        $response['data'] = $appointmentData;
    }
    echoResponse(200, $response);
});

/*
 *  Staff update 
 */

$app->post('/staffupdate/:id', function ($id) use ($app) {

    verifyFields(array('company_id'));
    // $company_id = $app->request->post('company_id');

    // $staff = Staff::find(array('staff_id' => $id, 'company_id' => $company_id));
    $staff = Staff::find(array('staff_id' => $id));

    // $currentPassword = $app->request->post('current_password');
    // $newPassword = $app->request->post('new_password');
    $firstname = empty($app->request->post('firstname')) ? $staff->firstname : $app->request->post('firstname');
    $username = empty($app->request->post('username')) ? $staff->username : $app->request->post('username');
    $password = empty($app->request->post('password')) ? $staff->password : $app->request->post('password');
    $lastname = empty($app->request->post('lastname')) ? $staff->lastname : $app->request->post('lastname');
    $emailid = empty($app->request->post('emailid')) ? $staff->emailid : $app->request->post('emailid');
    $profile_image = $app->request->post('profile_image') == NULL ? $staff->profile_image : $app->request->post('profile_image');
    // $proof = $app->request->post('proof') == NULL ? $staff->proof : $app->request->post('proof');
    $contact_number = empty($app->request->post('contact_number')) ? $staff->contact_number : $app->request->post('contact_number');
    $address = empty($app->request->post('address')) ? $staff->address : $app->request->post('address');

    //$staff_exist = Staff::find(array('staff_id' => $id, 'company_id' => $company_id,'emailid' => $emailid)); // chekcing for unique emial id
    $staff_exist = Staff::find_by_sql("SELECT * FROM tbl_staffs where staff_id != {$id} AND emailid = '{$emailid}' "); // chekcing for unique emial id

    if (count($staff) <= 0) {

        $response['error_code'] = 1;
        $response['message'] = 'No staff found';
        $response['status'] = false;
        $response['data'] = [];
        echoResponse(200, $response);
        $app->stop();
    } else if (count($staff_exist) > 0) {
        $response['error_code'] = 1;
        $response['status'] = true;
        $response['message'] = 'Email already exist.';
        $response['data'] = [];
        echoResponse(200, $response);

        $app->stop();
    }
    // if (!empty($currentPassword) && !empty($newPassword)) {
    //     if ($staff && $staff->password == sha1(md5($currentPassword) . $staff->salt)) {
    //         $staff->password = sha1(md5($newPassword) . $staff->salt);
    //     } else {
    //         $response['error_code'] = 1;
    //         $response['status'] = false;
    //         $response['message'] = 'Please provide correct current password.';
    //         //  $response['data'] = '';
    //         echoResponse(200, $response);
    //         $app->stop();
    //     }
    // } else if (empty($currentPassword) && !empty($newPassword)) {
    //     $response['error_code'] = 1;
    //     $response['status'] = false;
    //     $response['message'] = 'Please provide correct current password.';
    //     // $response['data'] = '';
    //     echoResponse(200, $response);
    //     $app->stop();
    // }

    if (isset($_FILES['profile_image'])) {
        $path_parts = pathinfo($_FILES['profile_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($firstname);
        $profile_image = time() . '-' . $name . '.' . $ext;

        if ($staff->profile_image != NULL) {
            $oldimage = '../' . STAFF_PIC_URL . $staff->profile_image;  // old image path
            \file_exists($oldimage) ? unlink($oldimage) : '';
        }
        if ($profile_image) {
            move_uploaded_file($_FILES['profile_image']['tmp_name'], '../' . STAFF_PIC_URL . $profile_image);
            $staff->profile_image = $profile_image;
        }
    }
    if (isset($_FILES['proof'])) {
        $path_parts = pathinfo($_FILES['proof']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($firstname);
        $proof = time() . '-' . $name . '.' . $ext;

        if ($staff->proof != NULL) {
            $oldimage = '../' . STAFF_PIC_URL . $staff->proof;  // old image path
            \file_exists($oldimage) ? unlink($oldimage) : '';
        }
        if ($proof) {
            move_uploaded_file($_FILES['proof']['tmp_name'], '../' . STAFF_PROOF_URL . $proof);
            $staff->proof = $proof;
        }
    }
    $staff->username = $username;
    //$staff->password = sha1(md5($password) . $staff->salt);
    $staff->firstname = $firstname;
    $staff->lastname = $lastname;
    $staff->emailid = $emailid;
    $staff->contact_number = $contact_number;
    $staff->status = "active";
    $staff->address = $address;
    $up = $staff->save();
    /* new code[29-11-2017]*/
    $appointment = Appointment::find(array('conditions' => "staff_id = {$staff->staff_id}"));
    if ($appointment) {
        $flag = $appointment->status != 'accepted' ? false : true;
    } else {
        $flag = false;
    }
    /*new code end*/
    if ($up) {
        $response['error_code'] = 0;
        $response['message'] = 'Successfully updated.';
        $response['status'] = true;
        $response['data'] = array(
            'isAssigned' => $flag,
            'staff_id' => $staff->staff_id,
            'company_id' => $staff->company_id,
            'username' => $staff->username,
            // 'password' => $staff->password,
            'firstname' => $staff->firstname,
            'lastname' => $staff->lastname,
            'emailid' => $staff->emailid,
            'status' => $staff->status,
            'profile_image' => $staff->profile_image != NULL ? STAFF_PIC_PATH . $staff->profile_image : NULL,
            'proof' => $staff->proof != NULL ? STAFF_PROOF_PATH . $staff->proof : NULL,
            'contact_number' => $staff->contact_number,
            'address' => $staff->address,
        );
    }
    echoResponse(200, $response);
});

/*
 *  Staff delete 
 */

$app->get('/staff/:id/:compnayid/delete', function ($id, $compnayid) use ($app) {
    $appointment = Appointment::find('all', array("conditions" => "staff_id = {$id} AND company_id = {$compnayid} AND completed != 1 AND accepted != 1"));
    // $staff_exist = Staff::exists(array('staff_id' => $id, 'company_id' => $compnayid));
    $staff_exist = Staff::exists(array('staff_id' => $id));

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'You are not able to delete staff.';

    if ($staff_exist && !$appointment) {
        $staff = Staff::find($id);
        $staff->delete();
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Staff deleted successfully';
    }
    if ($appointment) {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = ' Could not delete staff, it appears that staff is assigned some to appointment.';
    }
    echoResponse(200, $response);
});


/*
 *  Staff inactive 
 */

$app->get('/staff/:id/inactive', function ($id) use ($app) {

    $staff_exist = Staff::exists(array('staff_id' => $id));

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'You are not able to inactive staff.';

    if ($staff_exist) {
        $staff = Staff::find($id);
        $staff->status = "inactive";
        $staff->save();
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Staff inactive successfully';
    }

    echoResponse(200, $response);
});


/*
 *  Staff active 
 */

$app->get('/staff/:id/active', function ($id) use ($app) {

    $staff_exist = Staff::exists(array('staff_id' => $id));

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'You are not able to active staff.';

    if ($staff_exist) {
        $staff = Staff::find($id);
        $staff->status = "active";
        $staff->save();
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Staff active successfully';
    }

    echoResponse(200, $response);
});



/*
 * Staff Assign 
 */

$app->post('/staffassign', function () use ($app) {

    verifyFields(array('appointment_id'));

    $appointment_ids = $app->request->post('appointment_id');

    $staff_id = $app->request->post('staff_id');

    $appoint_array = explode(",", $appointment_ids);
    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];
    foreach ($appoint_array as $key => $appointment_id) {
        if ($appointment_id != "") {
            $appointment_exist = Appointment::exists($appointment_id);
            $aa = Appointment::find_by_sql("select * from tbl_appointments where appointment_id='{$appointment_id}'");
            foreach ($aa as $key => $value) {
                $date1 = date('Y/m/d', strtotime($value->date));
                $s_id = $value->staff_id;
            }
            $temp = $s_id;
            //    var_dump($appointment_exist);
            //    die;
            //    if($appointment_exist){
            //        
            //    $appointment = Appointment::find($appointment_id);
            //    $appointment->staff_id = $staff_id;
            //    $appointment->save();
            //    }
            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Error! Something went wrong. please try again later.';
            $response['data'] = [];

            if ($appointment_exist) {

                $appointment = Appointment::find($appointment_id);
                $appointment->staff_id = $staff_id;
                $appointment->status = isset($staff_id) ? 'accepted' : 'assign staff';
                $appointment->save();

                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Successfully staff assigned.';

                if (isset($staff_id)) {
                    $response['data'] = array(
                        'appoinment_id' => $appointment->appointment_id,
                        'company_id' => $appointment->company_id,
                        'client_id' => $appointment->client->client_id,
                        'firstname' => $appointment->client->firstname,
                        'lastname' => $appointment->client->lastname,
                        'company_name' => $appointment->company->company_name,
                        'status' => $appointment->status,
                        'staff' => array(
                            'staff_id' => $appointment->staff->staff_id,
                            'firstname' => $appointment->staff->firstname,
                            'lastname' => $appointment->staff->lastname,
                            'emailid' => $appointment->staff->emailid,
                            'proflie_image' => $appointment->staff->profile_image != NULL ? STAFF_PIC_PATH . $appointment->staff->profile_image : NULL,
                            'contact_number' => $appointment->staff->contact_number,
                        ),
                        'service_id' => $appointment->service_id,
                        'date' => $appointment->date,
                        'visits' => $appointment->visits,
                        'visit_hours' => $appointment->visit_hours,
                        'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                        'profile_image  ' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                        'notification_flag' => 'staff_appointment_list'
                    );
                    $dt = date('Y/m/d');
                    $dat = date('Y/m/d', strtotime($appointment->date));
                    if ($dat == $dt) {
                        $notification = array(
                            'message' => ' Your list has been updated please review.',
                            'player_ids' => array($appointment->staff->playerid),
                            'data' => $response['data'],
                        );
                        // print_r($notification);
                        //  die;
                        sendStaffMessage($notification);
                    }
                } else {

                    $x = new stdClass();
                    $response['error_code'] = 0;
                    $response['status'] = true;
                    $response['message'] = 'Successfully staff unassigned.';

                    $response['data'] = array(
                        'appoinment_id' => $appointment->appointment_id,
                        'company_id' => $appointment->company_id,
                        'client_id' => $appointment->client->client_id,
                        'firstname' => $appointment->client->firstname,
                        'lastname' => $appointment->client->lastname,
                        'company_name' => $appointment->company->company_name,
                        'status' => $appointment->status,
                        'staff' => $x,
                        'service_id' => $appointment->service_id,
                        'date' => $appointment->date,
                        'visits' => $appointment->visits,
                        'visit_hours' => $appointment->visit_hours,
                        'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                        'profile_image  ' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                        'notification_flag' => 'staff_appointment_list'
                    );


                    $ab = Staff::find_by_sql("select  playerid from tbl_staffs where staff_id='{$temp}'");

                    foreach ($ab as $value2) {
                        $pl_id = $value2->playerid;
                    }
                    $ff = $pl_id;
                    // print_r($pl_id);
                    // die;
                    $d = date('Y/m/d');

                    if ($date1 == $d) {
                        $notification = array(
                            'message' => ' Your list has been updated please review.',
                            'player_ids' => array($ff),
                            'data' => $response['data'],
                        );
                        sendStaffMessage($notification);
                    }
                }
                // die;


            }
        }
    }

    // $notification = array('message' => $appointment->company->company_name . ' accepted your appointment request',
    //     'player_ids' => array($appointment->client->player_id),
    //     'data' => $response['data'],
    // );
    // $notificationstate = Notification::find(array("conditions" => "id = {$appointment->client_id} AND type = 'client' AND state = 'accepted'"));

    // if ($notificationstate->is_active == 1 || count($notificationstate) != 0) {
    //     sendMessage($notification);
    // }

    echoResponse(200, $response);
});



/*
 * Staff Assign 
 *

$app->post('/staffassign', function() use($app) {

    verifyFields(array('appointment_id', 'staff_id'));

    $appointment_id = $app->request->post('appointment_id');
    $staff_id = $app->request->post('staff_id');

    $appointment_exist = Appointment::exists($appointment_id);
//    var_dump($appointment_exist);
//    die;
//    if($appointment_exist){
//        
//    $appointment = Appointment::find($appointment_id);
//    $appointment->staff_id = $staff_id;
//    $appointment->save();
//    }
    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];

    if ($appointment_exist) {

        $appointment = Appointment::find($appointment_id);
        $appointment->staff_id = $staff_id;
        $appointment->status = 'accepted';
        $appointment->save();

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Successfully staff assign.';

        $response['data'] = array(
            'appoinment_id' => $appointment->appointment_id,
            'company_id' => $appointment->company_id,
            'client_id' => $appointment->client->client_id,
            'firstname' => $appointment->client->firstname,
            'lastname' => $appointment->client->lastname,
            'company_name' => $appointment->company->company_name,
            'staff' => array(
                'staff_id' => $appointment->staff->staff_id,
                'firstname' => $appointment->staff->firstname,
                'lastname' => $appointment->staff->lastname,
                'emailid' => $appointment->staff->emailid,
                'proflie_image' => $appointment->staff->profile_image != NULL ? STAFF_PIC_PATH . $appointment->staff->profile_image : NULL,
                'contact_number' => $appointment->staff->contact_number,
            ),
            'service_id' => $appointment->service_id,
            'date' => $appointment->date,
            'visits' => $appointment->visits,
            'visit_hours' => $appointment->visit_hours,
            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
            'profile_image  ' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
        );
    }
    
    echoResponse(200, $response);
});
*/

/*
 * Staff accepted
 */
/*
 * Staff accepted/completd
 */
$app->post('/accept/:id/staff', function ($id) use ($app) {

    verifyFields(array('appointmentno', 'appointmentid'));

    $appointment_id = $app->request->post('appointmentid');
    $appoinmentno = $app->request->post('appoinmentno');
    // $flag = $app->request->post('flag');
    $staff = Staff::find($id);
    $playerid = [];

    /*
     * add count of accepted and completd in one api  */


    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];
    if ($id) {
        $appointment = Appointment::find('all', array("conditions" => array("appointment_id  in (?)", $appointment_id)));



        $response['error_code'] = 0;
        $response['status'] = true;


        foreach ($appointment as $key => $value) {

            //     $value->acknowledge = $flag;
            if ($value->accepted == false) {

                $value->accepted = true;
                // $value->completed = false;
            }
            // else if($value->accepted == true && $value->completed == false){
            //     $value->accepted = true;
            //     $value->completed = true;
            // }
            $value->save();
            $playerid[] = $value->company->player_id;
        }
        $accepted = Appointment::find('all', array("conditions" => array("appointment_id  in (?) AND accepted = 1", $appointment_id)));
        // $completed = Appointment::find('all', array("conditions" => array("appointment_id  in (?) AND completed = 1", $appointment_id)));
        $acceptedcount = count($accepted) > 0 ? count($accepted) : 0;
        // $completedcount = count($completed)>0?count($completed):0;
        $msg = $acceptedcount == 1 ? "$acceptedcount appointment confirmed" : "$acceptedcount appointments confirmed";

        $response['message'] = "Appoinment successfully $msg by staff.";
        $response['data'] = array(
            //            'appoinment_id' => $appointment->appointment_id,
            //            'company_id' => $appointment->company_id,
            //            'client_id' => $appointment->client->client_id,
            //            'firstname' => $appointment->client->firstname,
            //            'lastname' => $appointment->client->lastname,
            //            'company_name' => $appointment->company->company_name,
            //'staff' => array(
            //   "isAccepted" => true,
            'staff_id' => $staff->staff_id,
            'firstname' => $staff->firstname,
            'lastname' => $staff->lastname,
            'emailid' => $staff->emailid,
            'proflie_image' => $staff->profile_image != NULL ? STAFF_PIC_PATH . $staff->profile_image : NULL,
            'contact_number' => $staff->contact_number,
            // ),
            //            'service_id' => $appointment->service_id,
            //            'date' => $appointment->date,
            //            'visits' => $appointment->visits,
            //            'visit_hours' => $appointment->visit_hours,
            //            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
            //            'profile_image  ' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
        );
    }
    $staffname = $staff->firstname . ' ' . $staff->lastname;

    //$msg = $appoinmentno == 1 ? "$acceptedcount appointment confirmed " : "$acceptedcount appointments confirmed ";
    $notification = array(
        'message' => "$staffname  has $msg",
        'player_ids' => $playerid,
        'data' => $response['data'],
    );

    //$notificationstate = Notification::find(array("conditions" => "id = {$company_id} AND type = 'company' AND state = 'created'"));
    //if ($notificationstate->is_active == 1 || count($notificationstate) == 0) {
    sendMessage($notification);
    //}
    echoResponse(200, $response);
});


/*
 * Staff complete
 *//*
$app->post('/complete/:id/staff', function($id) use($app) {
    verifyFields(array('appoinmentno','appoinmentid','flag'));
    
    $appoinment_no = $app->request->post('appoinment_no');
    $appointment_id = $app->request->post('appoinmentid');
    $staff = Staff::find($id);
	 $flag = $app->request->post('flag');
    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];
    if ($id) {
        $appointment = Appointment::find($appointment_id);
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appoinment successfully completed by staff.';

        $response['data'] = array(
//            'appoinment_id' => $appointment->appointment_id,
//            'company_id' => $appointment->company_id,
//            'client_id' => $appointment->client->client_id,
//            'firstname' => $appointment->client->firstname,
//            'lastname' => $appointment->client->lastname,
//            'company_name' => $appointment->company->company_name,
            //'staff' => array(
            'staff_id' => $staff->staff_id,
            'firstname' => $staff->firstname,
            'lastname' => $staff->lastname,
            'emailid' => $staff->emailid,
            'proflie_image' => $staff->profile_image != NULL ? STAFF_PIC_PATH . $staff->profile_image : NULL,
            'contact_number' => $staff->contact_number,
                // ),
//            'service_id' => $appointment->service_id,
//            'date' => $appointment->date,
//            'visits' => $appointment->visits,
//            'visit_hours' => $appointment->visit_hours,
//            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
//            'profile_image  ' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
        );
		$appointment->acknowledge = $flag;
        $appointment->save();
    }
      $staffname = $staff->firstname.' '.$staff->lastname;
    $msg = $appoinment_no == 1 ? "$appoinment_no appointment" : "$appoinment_no appointments.";

    $notification = array('message' => "$staffname  has completed $msg",
        'player_ids' => array($appointment->company->player_id),
        'data' => $response['data'],
    );
//            print_r($notification);
//            die;
    //$notificationstate = Notification::find(array("conditions" => "id = {$company_id} AND type = 'company' AND state = 'created'"));
    //if ($notificationstate->is_active == 1 || count($notificationstate) == 0) {
    sendMessage($notification);
    //}
    echoResponse(200, $response);
});*/
