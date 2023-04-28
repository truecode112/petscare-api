<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
// header('Access-Control-Allow-Headers: token, Content-Type');
// header('Access-Control-Max-Age: 1728000');
// header('Content-Length: 0');
// header('Content-Type: text/plain');

error_reporting(E_ALL);

use ActiveRecord\RecordNotFound;

$app->post('/clientlogin', function () use ($app) {
    verifyFields(array('emailid', 'password', 'playerid'));

    $emailid = $app->request->post('emailid');
    $password = $app->request->post('password');
    $playerid = $app->request->post('playerid');

    $client = Client::find_by_emailid($emailid);
    $response['error_code'] = 1;
    $response['message'] = 'Invalid credentials';
    $response['status'] = false;
    try {
        if ($client) {
            $client->player_id = $playerid;
            $client->save();
            $client_id = $client->client_id;
            if (sha1(md5($password) . ($client->salt)) == ($client->password)) {
                if (isset($client->profile_image)) {
                    $client->profile_image = USER_PIC_URL_PATH . $client->profile_image;
                } else {
                    $client->profile_image = null;
                }

                /*code for set the banner*/
                $contract = Contract::find_by_client_id($client->client_id);
                if (count($contract) > 0) {
                    $banner_company_id = $contract->company_id;

                    $company = Company::find_by_company_id($banner_company_id);
                    if (isset($company->company_banner)) {
                        $banner = COMPANY_BANNER_PATH . $company->company_banner;
                    } else {
                        $banner = null;
                    }
                } else {
                    $banner = null;
                }
                /*end*/

                $response['error_code'] = 0;
                $response['message'] = 'Login successfully';
                $response['status'] = true;

                $response['data'] = array(
                    'client_id' => $client->client_id,
                    'firstname' => $client->firstname,
                    'lastname' => $client->lastname,
                    'profile_image' => $client->profile_image,
                    'company_banner' => $banner,
                    'emailid' => $client->emailid,
                    'client_address' => $client->client_address,
                    'contact_number' => $client->contact_number,
                    'client_notes' => $client->client_notes,
                    'player_id' => $client->player_id,
                    'profile_status' => $client->status == 1 ? 'active' : 'inactive',

                );
            }
        }

        echoResponse(200, $response);
    } catch (RecordNotFound $e) {
    }
    $app->stop();
});

$app->post('/clientweblogin', function () use ($app) {
    verifyFields(array('emailid', 'password'));

    $emailid = $app->request->post('emailid');
    $password = $app->request->post('password');
    // $playerid = $app->request->post('playerid');

    $client = Client::find_by_emailid($emailid);
    $response['error_code'] = 1;
    $response['message'] = 'Invalid credentials';
    $response['status'] = false;
    try {
        if ($client) {
            // $client->player_id = $playerid;
            // $client->save();
            $client_id = $client->client_id;
            if (sha1(md5($password) . ($client->salt)) == ($client->password)) {
                if (isset($client->profile_image)) {
                    $client->profile_image = USER_PIC_URL_PATH . $client->profile_image;
                } else {
                    $client->profile_image = null;
                }

                /*code for set the banner*/
                $contract = Contract::find_by_client_id($client->client_id);
                if (count($contract) > 0) {
                    $banner_company_id = $contract->company_id;

                    $company = Company::find_by_company_id($banner_company_id);
                    if (isset($company->company_banner)) {
                        $banner = COMPANY_BANNER_PATH . $company->company_banner;
                    } else {
                        $banner = null;
                    }
                } else {
                    $banner = null;
                }
                /*end*/

                $response['error_code'] = 0;
                $response['message'] = 'Login successfully';
                $response['status'] = true;

                $response['data'] = array(
                    'client_id' => $client->client_id,
                    'firstname' => $client->firstname,
                    'lastname' => $client->lastname,
                    'profile_image' => $client->profile_image,
                    'company_banner' => $banner,
                    'emailid' => $client->emailid,
                    'client_address' => $client->client_address,
                    'contact_number' => $client->contact_number,
                    'client_notes' => $client->client_notes,
                    'player_id' => $client->player_id,
                    'profile_status' => $client->status == 1 ? 'active' : 'inactive',

                );
            }
        }

        echoResponse(200, $response);
    } catch (RecordNotFound $e) {
    }
    $app->stop();
});

//user registration

$app->post('/clientregister', function () use ($app) {
    verifyFields(array('password', 'firstname', 'lastname', 'emailid', 'contact_number', 'client_address', 'client_notes', 'playerid'));

    $password = $app->request->post('password');
    $playerid = $app->request->post('playerid');
    $firstname = $app->request->post('firstname');
    $lastname = $app->request->post('lastname');
    $email = $app->request->post('emailid');
    $profile_image = NULL;
    if (isset($_FILES['profile_image'])) {
        $path_parts = pathinfo($_FILES['profile_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($firstname . '' . $lastname);
        $profile_image = time() . '-' . $name . '.' . $ext;
    }
    $contact_number = $app->request->post('contact_number');
    $client_address = $app->request->post('client_address');
    $client_notes = $app->request->post('client_notes');
    isValidEmail($email);
    $client = Client::find_by_emailid($email);
    if ($client) {
        $response["error_code"] = 1;
        $response["status"] = false;
        $response["message"] = "Sorry, this email already exists!";
        echoResponse(200, $response);
    } else {
        // Begin transaction
        Client::transaction(function () use ($app, $password, $firstname, $lastname, $email, $profile_image, $contact_number, $client_address, $client_notes, $playerid) {
            $client = new Client();
            $client->firstname = $firstname;
            $client->lastname = $lastname;
            $client->profile_image = $profile_image;
            $client->emailid = $email;
            $client->contact_number = $contact_number;
            $client->client_address = $client_address;
            $client->client_notes = $client_notes;
            $client->salt = genRndDgt(8, false);
            $client->password = sha1(md5($password) . $client->salt);
            $client->player_id = $playerid;
            $client->status = true;
            $client->save();
            $client->client_id = (int) $client->client_id;


            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Error! Something went wrong. please try again later.';
            $response['data'] = [];

            if ($client->client_id > 0) {
                if ($profile_image) {
                    move_uploaded_file($_FILES['profile_image']['tmp_name'], '../' . USER_PIC_URL . $profile_image);
                    $client->profile_image = USER_PIC_URL_PATH . $profile_image;
                }
                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Successfully Registered.';
                $response['data'] = array(
                    'client_id' => $client->client_id,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'profile_image' => $client->profile_image,
                    'emailid' => $email,
                    'client_address' => $client_address,
                    'contact_number' => $contact_number,
                    'client_notes' => $client_notes,
                    'player_id' => $playerid,
                    'profile_status' => $client->status == 1 ? 'active' : 'inactive'
                );
            }
            echoResponse(200, $response);
        });
    }
});



$app->post('/client/profilepic', function () use ($app) {

    verifyFields(array('client_id', 'status'));         // checking client id

    $id = $app->request->post('client_id');
    $status = $app->request->post('status');
    $client = Client::find($id);

    $profile_image = $app->request->post('profile_image') == NULL ? $client->profile_image : $app->request->post('profile_image');

    if (isset($_FILES['profile_image'])) {
        $path_parts = pathinfo($_FILES['profile_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($client->firstname . '' . $client->lastname);
        $profile_image = time() . '-' . $name . '.' . $ext;

        if ($client->profile_image != NULL) {
            $oldimage = '../' . USER_PIC_URL . $client->profile_image;  // old image path
            file_exists($profile_image) ? unlink($oldimage) : NULL;  // delete old image
        }


        if ($profile_image) {
            // upload if user change profile picture..

            move_uploaded_file($_FILES['profile_image']['tmp_name'], '../' . USER_PIC_URL . $profile_image);

            $client->profile_image = $profile_image;
        }
    }

    $client->status = $status != NULL ? $status : $client->status;
    $client->save();
    if (!$client->save()) {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Error! Something went wrong. please try again later.';
        $response['data'] = [];
    } else {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Successfully Updated.';
        $response['data'] = [
            'client_id' => $id,
            'firstname' => $client->firstname,
            'lastname' => $client->lastname,
            'profile_image' => $client->profile_image != NULL ? USER_PIC_URL_PATH . $client->profile_image : NULL,
            'emailid' => $client->emailid,
            'client_address' => $client->client_address,
            'contact_number' => $client->contact_number,
            'client_notes' => $client->client_notes,
            'player_id' => $client->player_id,
            'profile_status' => $client->status == 1 ? 'active' : 'inactive'
        ];
    }
    echoResponse(200, $response);
});



$app->post('/pet/profile/:petid', function ($petid) use ($app) {


    if ($petid != NULL) {
        $pet = Pet::find($petid);

        $pet_name = empty($app->request->post('pet_name')) ? $pet->pet_name : $app->request->post('pet_name');
        $pet_birth = empty($app->request->post('pet_birth')) ? $pet->pet_birth : $app->request->post('pet_birth');

        $age = empty($app->request->post('age')) ? $pet->age : $app->request->post('age');
        $gender = empty($app->request->post('gender')) ? $pet->gender : $app->request->post('gender');
        $pet_type = empty($app->request->post('pet_type')) ? $pet->pet_type : $app->request->post('pet_type');
        $breed = empty($app->request->post('breed')) ? $pet->breed : $app->request->post('breed');
        $neutered = empty($app->request->post('neutered')) ? $pet->neutered : $app->request->post('neutered');
        $spayed = empty($app->request->post('spayed')) ? $pet->spayed : $app->request->post('spayed');
        $injuries = empty($app->request->post('injuries')) ? $pet->injuries : $app->request->post('injuries');
        $medical_detail = empty($app->request->post('medical_detail')) ? $pet->medical_detail : $app->request->post('medical_detail');
        $notes = empty($app->request->post('pet_notes')) ? $pet->pet_notes : $app->request->post('pet_notes');

        $pet_image = $app->request->post('pet_image') == NULL ? $pet->pet_image : $app->request->post('pet_image');
        if (isset($_FILES['pet_image'])) {
            $path_parts = pathinfo($_FILES['pet_image']['name']);
            $ext = $path_parts['extension'];
            $name = cleanUsername($pet_name);
            $pet_image = time() . '-' . $name . '.' . $ext;

            if ($pet->pet_image != NULL) {
                $oldimage = '../' . PET_PIC_URL . $pet->pet_image;  // old image path
                \file_exists($oldimage) ? unlink($oldimage) : '';
            }
            if ($pet_image) {
                move_uploaded_file($_FILES['pet_image']['tmp_name'], '../' . PET_PIC_URL . $pet_image);
                $pet->pet_image = $pet_image;
            }
        }

        $pet->pet_name = $pet_name;
        $pet->pet_birth = $pet_birth;

        $pet->age = $age;
        $pet->gender = $gender;
        $pet->pet_type = $pet_type;
        $pet->breed = $breed;
        $pet->neutered = $neutered;
        $pet->spayed = $spayed;
        $pet->injuries = $injuries;
        $pet->medical_detail = $medical_detail;
        $pet->pet_notes = $notes;
        $up = $pet->save();
    }

    if ($pet->save()) {
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Successfully updated.';
        $response['data'] = array(
            'pet_id' => $pet->pet_id,
            'pet_name' => $pet->pet_name,
            'pet_birth' => $pet->pet_birth,
            'pet_image' => $pet->pet_image != NULL ? PET_PIC_PATH . $pet->pet_image : NULL,
            'pet_age' => $pet->age,
            'gender' => $pet->gender,
            'pet_type' => $pet->pet_type,
            'breed' => $pet->breed,
            'neutered' => $pet->neutered,
            'spayed' => $pet->spayed,
            'injuries' => $pet->injuries,
            'pet_notes' => $pet->pet_notes,
        );
    }

    echoResponse(200, $response);
});




$app->post('/client/profile/:id', function ($id) use ($app) {

    $client = Client::find($id);
    $firstname = empty($app->request->post('firstname')) ? $client->firstname : $app->request->post('firstname');
    $lastname = empty($app->request->post('lastname')) ? $client->lastname : $app->request->post('lastname');
    // $email = empty($app->request->post('emailid')) ? $client->emailid : $app->request->post('emailid');
    $contact_number = empty($app->request->post('contact_number')) ? $client->contact_number : $app->request->post('contact_number');
    $client_address = empty($app->request->post('client_address')) ? $client->client_address : $app->request->post('client_address');
    $client_notes = empty($app->request->post('client_notes')) ? $client->client_notes : $app->request->post('client_notes');
    $status = empty($app->request->post('status')) ? $client->status : $app->request->post('status');

    // $pass = $app->request->post('pass');
    // $newpass = $app->request->post('new_pass');
    // $username = empty($app->request->post('username')) ? $client->username : $app->request->post('username');

    // $status = empty($app->request->post('status')) ? $client->status : $app->request->post('status');
    $profile_image = $app->request->post('profile_image') == NULL ? $client->profile_image : $app->request->post('profile_image');

    if (isset($_FILES['profile_image'])) {
        $path_parts = pathinfo($_FILES['profile_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($client->firstname . '' . $client->lastname);
        $profile_image = time() . '-' . $name . '.' . $ext;
        if ($client->profile_image != NULL) {
            $oldimage = '../' . USER_PIC_URL . $client->profile_image;  // old image path
            file_exists($profile_image) ? unlink($oldimage) : NULL;  // delete old image
        }
        if ($profile_image) {
            // upload if user change profile picture..
            move_uploaded_file($_FILES['profile_image']['tmp_name'], '../' . USER_PIC_URL . $profile_image);
            $client->profile_image = $profile_image;
        }
    }
    $client->firstname = $firstname;
    $client->lastname = $lastname;
    $client->contact_number = $contact_number;
    $client->client_address = $client_address;
    $client->client_notes = $client_notes;
    $client->status = $status;
    $client->save();
    $flag = $client->company_id != NULL ? TRUE : FALSE;
    $contract = Contract::find(array('conditions' => "client_id = {$id} AND status != 'rejected' "));
    if ($client->save()) {
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Successfully updated.';
        $response['data'] = array(
            "isManualClient" => $flag,
            "company_id" => $contract->company_id,
            "contract_id" => $contract->contract_id,
            'client_id' => $id,
            'firstname' => $client->firstname,
            'lastname' => $client->lastname,
            'profile_image' => $client->profile_image != NULL ? USER_PIC_URL_PATH . $client->profile_image : NULL,
            'emailid' => $client->emailid,
            'client_address' => $client->client_address,
            'contact_number' => $client->contact_number,
            'client_notes' => $client->client_notes,
            'player_id' => $client->player_id,
            'status' => $contract->status,
            'profile_status' => $client->status == 1 ? 'active' : 'inactive',

        );
    }
    echoResponse(200, $response);

    //     // $pass1 = sha1(md5($pass) . $client->salt);
    //     // $confirm_pass = Client::find_by_sql("SELECT * FROM tbl_clients where client_id = {$id} AND password = '{$pass1}'");

    //     // if (count($confirm_pass) == 0) {

    //     //     $response['error_code'] = 1;
    //     //     $response['status'] = true;
    //     //     $response['message'] = 'Your old password is wrong.';
    //     //     $contract = Contract::find(array('conditions' => "client_id = {$id} AND status != 'rejected' "));
    //     //     $response['data'] = array(
    //     //         "isManualClient" => $flag,
    //     //         "company_id" => $contract->company_id,
    //     //         "contract_id" => $contract->contract_id,
    //     //         'client_id' => $id,
    //     //         'firstname' => $client->firstname,
    //     //         'lastname' => $client->lastname,
    //     //         'profile_image' => $client->profile_image != NULL ? USER_PIC_URL_PATH . $client->profile_image : NULL,
    //     //         'emailid' => $client->emailid,
    //     //         'client_address' => $client->client_address,
    //     //         'contact_number' => $client->contact_number,
    //     //         'client_notes' => $client->client_notes,
    //     //         'player_id' => $client->player_id,
    //     //         'status' => $contract->status,
    //     //         'profile_status' => $client->status == 1 ? 'active' : 'inactive',

    //     //     );

    //     //     echoResponse(200, $response);

    //     //     // $app->stop();
    //     // } else {
    //         // if($email!=NULL){

    //         // $exist = Client::find_by_sql("SELECT * FROM tbl_clients where client_id != {$id} AND emailid = '{$email}'");
    //         // }else{
    //         //     $exist=array();
    //         // }
    //         // if (count($exist) > 0) {

    //         //     $response['error_code'] = 1;
    //         //     $response['status'] = true;
    //         //     $response['message'] = 'Email already exist.';
    //         //     $response['data'] = [];
    //         //     echoResponse(200, $response);

    //         //     $app->stop();
    //         // } else {
    //         $client->firstname = $firstname;
    //         $client->lastname = $lastname;
    //         // $client->emailid = $email;
    //         // $client->username = $username;
    //         // $client->password = sha1(md5($newpass) . $client->salt);

    //         $client->contact_number = $contact_number;
    //         $client->client_address = $client_address;
    //         $client->client_notes = $client_notes;
    //         $client->save();
    //         // }


    //         $flag = $client->company_id != NULL ? TRUE : FALSE;

    //         $response['error_code'] = 1;
    //         $response['status'] = false;
    //         $response['message'] = 'Error! Something went wrong. please try again later.';
    //         $response['data'] = [];
    //         $contract = Contract::find(array('conditions' => "client_id = {$id} AND status != 'rejected' "));
    //         if ($client->save()) {
    //             $response['error_code'] = 0;
    //             $response['status'] = true;
    //             $response['message'] = 'Successfully updated.';
    //             $response['data'] = array(
    //                 "isManualClient" => $flag,
    //                 "company_id" => $contract->company_id,
    //                 "contract_id" => $contract->contract_id,
    //                 'client_id' => $id,
    //                 'firstname' => $client->firstname,
    //                 'lastname' => $client->lastname,
    //                 'profile_image' => $client->profile_image != NULL ? USER_PIC_URL_PATH . $client->profile_image : NULL,
    //                 'emailid' => $client->emailid,
    //                 'client_address' => $client->client_address,
    //                 'contact_number' => $client->contact_number,
    //                 'client_notes' => $client->client_notes,
    //                 'player_id' => $client->player_id,
    //                 'status' => $contract->status,
    //                 'profile_status' => $client->status == 1 ? 'active' : 'inactive',

    //             );
    //         }

    //         echoResponse(200, $response);
    //     // }
});


$app->post('/clientresetpassword', function () use ($app) {
    verifyFields(array('client_id', 'current_password', 'new_password'));

    $client_id = $app->request->post('client_id');
    $currentPassword = $app->request->post('current_password');
    $newPassword = $app->request->post('new_password');

    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;

    try {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Please provide correct current password.';
        $client = Client::find($client_id);
        if ($client && $client->password == sha1(md5($currentPassword) . $client->salt)) {
            $client->password = sha1(md5($newPassword) . $client->salt);
            $client->save();
            $client->client_id = (int) $client->client_id;
            if ($client->client_id > 0) {
                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Your password has been updated successfully!';
            }
        }
        echoResponse(200, $response);
    } catch (RecordNotFound $e) {
        $response['error_code'] = 1;
        $response['message'] = $e->getMessage();
        $response['status'] = false;
        echoResponse(200, $response);
    }
});

$app->put('/clientlogout/:id', function ($id) use ($app) {
    try {
        $client = Client::find($id);
        $client->player_id = NULL;
        $client->save();
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'successfully logout done!';
    } catch (RecordNotFound $e) {
        $response['error_code'] = 1;
        $response['message'] = $e->getMessage();
        $response['status'] = false;
        echoResponse(200, $response);
    }
    echoResponse(200, $response);
});


/*
 * pet list for compnay side
 */
$app->get('/:id/pets', function ($id) use ($app) {



    $contract = Contract::find('all', array('conditions' => "company_id = {$id} AND status != 'rejected' "));
    $companyCheck = Client::find('all', array('conditions' => "company_id = {$id}"));   // Checking for Manual Added Client


    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;

    if ($contract || $companyCheck) {


        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Client list retrive successfully!';

        $pet_names = [];
        $temp = [];
        $pet_detail = [];
        foreach ($contract as $key => $value) {
            $flag = $value->client->company_id != NULL ? TRUE : FALSE;
            $idss = $value->client->client_id;
            $pet = Pet::find_by_sql("SELECT * FROM `tbl_pets` where client_id=$idss order by pet_id ");
            foreach ($pet as $key3 => $value3) {
                $pet_names[] = $value3->pet_name;
            }
        }
        // print_r($pet_names);
        // die;
        foreach ($contract as $key => $value) {



            $abab = [];

            if ($value->client->others != NULL && $value->client->others != 'O:8:"stdClass":0:{}') {

                $a = array();
                $b = array();
                foreach (unserialize($value->client->others) as $key5 => $value5) {
                    $a[] = $key5;
                    $b[] = $value5;
                }

                for ($i = 0; $i < count(unserialize($value->client->others)); $i++) {

                    $abab[] = array(
                        'key' => $a[$i],
                        'value' => $b[$i],
                    );
                }
            }



            $flag = $value->client->company_id != NULL ? TRUE : FALSE;
            $contractData = array(
                'client_id' => $value->client->client_id,
                'isManualClient' => $flag,
                'company_id' => $value->company->company_id,
                'contract_id' => $value->contract_id,
                'company_name' => $value->company->company_name,
                'firstname' => $value->client->firstname,
                'lastname' => $value->client->lastname,
                'profile_image' => $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
                'company_image' => $value->company->company_image != NULL ? COMPANY_PIC_PATH . $value->company->company_image : NULL,
                'emailid' => $value->client->emailid,
                'client_address' => $value->client->client_address,
                'contact_number' => $value->client->contact_number,
                'client_notes' => $value->client->client_notes,
                'player_id' => $value->client->player_id,
                'other' => count($abab) != 0  ? $abab : '',
                'profile_status' => $value->client->status == 1 ? 'active' : 'inactive',
                'status' => $value->status,


            );



            $pet = Pet::find('all', array("conditions" => "client_id = {$value->client->client_id}"));


            $counti = [];

            $backup_contact = [];
            foreach ($pet as $key2 => $value2) {


                $contact_check = Contact_backup::find(array("conditions" => "client_id={$value->client->client_id} AND pet_id={$value2->pet_id}"));
                if ($contact_check != NULL) {
                    $backup_contact = array(
                        'name' => $contact_check->name,
                        'address' => $contact_check->address,
                        'number' => $contact_check->contact_number,
                    );
                } else {
                    $backup_contact = new stdClass();;
                }


                /*New function added for check appointment of each client is present in today/future or not */
                $appointment = Appointment::find('all', array('conditions' => "company_id = {$id} AND pet_id={$value2->pet_id}"));

                $color = "none";
                $today = date('Y-m-d');

                foreach ($appointment as $k => $v) {


                    $app_date = date('Y-m-d', strtotime($v->date));
                    if ($app_date == $today) {
                        $color = "Green";

                        break;
                    } else if ($app_date > $today) {

                        if ($color == "Green") {
                            $color = "Green";
                        } else if ($color == "Yellow" or $color == "Red" or $color = "none") {
                            $color = "Yellow";
                        }
                    } else {

                        if ($color == "Yellow") {
                            $color = "Yellow";
                        } else if ($color == "Red" or $color == "none") {
                            $color = "Red";
                        }
                    }
                }


                $counti[] = 'a';


                // $acbc='';
                // $firstValue = current($pet_names);
                // echo $firstValue;
                // die;
                // foreach ($pet_names as $vals) 
                // {
                //      if ($firstValue !== $vals) 
                //      {
                //      $acbc=true;
                //      }
                // }
                $haha = '';
                $maincoin = [];
                for ($i = 0; $i < count($pet_names); $i++) {

                    if (strcasecmp($pet_names[$i], $value2->pet_name) === 0) {
                        $maincoin[] = 'a';
                        $haha = '';
                    }
                    if (count($maincoin) > 1) {
                        $haha = 'abcd';

                        break;
                    }
                }



                if ($haha == 'abcd') {
                    $petfull = $value2->pet_name . " " . $value->client->lastname;
                } else {
                    $petfull = $value2->pet_name;
                }

                $pet_detail[] = array(
                    'pet_id' => $value2->pet_id,
                    'pet_name' => $petfull,
                    'pet_birth' => $value2->pet_birth,

                    'pet_image' => $value2->pet_image != NULL ? PET_PIC_PATH . $value2->pet_image : NULL,
                    'pet_age' => $value2->age,
                    'gender' => $value2->gender,
                    'pet_type' => $value2->pet_type,
                    'breed' => $value2->breed,
                    'neutered' => $value2->neutered,
                    'spayed' => $value2->spayed,
                    'injuries' => $value2->injuries,
                    'pet_notes' => $value2->pet_notes,
                    'latitude' => $value2->latitude,
                    'longitude' => $value2->longitude,
                    'client' => $contractData,
                    'traffic_signal' => $color,
                    'backupcontact' => $backup_contact
                );
            }
        }



        array_push($temp, $pet_detail);
        $demo = [];
        foreach ($temp as  $value6) {
            $demo = $value6;
        }

        $response['data'] = $demo;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No client found.';
        $response['status'] = false;
        $response['data'] = [];
    }


    echoResponse(200, $response);
});


/*
 * Client listing for compnay side
 */
$app->get('/:id/clients', function ($id) use ($app) {



    $contract = Contract::find('all', array('conditions' => "company_id = {$id} AND status != 'rejected' "));
    $companyCheck = Client::find('all', array('conditions' => "company_id = {$id}"));   // Checking for Manual Added Client


    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;


    $contractData = [];

    if ($contract || $companyCheck) {


        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Client list retrive successfully!';

        $pet_names = [];

        foreach ($contract as $key => $value) {
            $flag = $value->client->company_id != NULL ? TRUE : FALSE;
            $idss = $value->client->client_id;
            $pet = Pet::find_by_sql("SELECT * FROM `tbl_pets` where client_id=$idss order by pet_id limit 1");
            foreach ($pet as $key3 => $value3) {
                $pet_names[] = $value3->pet_name;
            }
        }


        foreach ($contract as $key => $value) {
            $flag = $value->client->company_id != NULL ? TRUE : FALSE;
            $pet = Pet::find('all', array("conditions" => "client_id = {$value->client->client_id}"));


            $counti = [];
            $pet_detail = [];
            $backup_contact = [];
            foreach ($pet as $key2 => $value2) {

                $contact_check = Contact_backup::find(array("conditions" => "client_id={$value->client->client_id} AND pet_id={$value2->pet_id}"));
                if ($contact_check != NULL) {
                    $backup_contact = array(
                        'name' => $contact_check->name,
                        'address' => $contact_check->address,
                        'number' => $contact_check->contact_number,
                    );
                } else {
                    $backup_contact = new stdClass();;
                }

                $counti[] = 'a';


                $acbc = '';
                $firstValue = current($pet_names);
                foreach ($pet_names as $vals) {
                    if ($firstValue !== $vals) {
                        $acbc = true;
                    }
                }
                $haha = '';
                $maincoin = [];
                for ($i = 0; $i < count($pet_names); $i++) {

                    if (strcasecmp($pet_names[$i], $value2->pet_name) === 0) {
                        $maincoin[] = 'a';
                        $haha = '';
                    }
                    if (count($maincoin) > 1) {
                        $haha = 'abcd';

                        break;
                    }
                }


                if (count($counti) == 1 && $haha == 'abcd') {
                    $petfull = $value2->pet_name . " " . $value->client->lastname;
                } else {
                    $petfull = $value2->pet_name;
                }

                $pet_detail[] = array(
                    'pet_id' => $value2->pet_id,
                    'pet_name' => $petfull,
                    'pet_birth' => $value2->pet_birth,
                    'pet_image' => $value2->pet_image != NULL ? PET_PIC_PATH . $value2->pet_image : NULL,
                    'pet_age' => $value2->age,
                    'gender' => $value2->gender,
                    'pet_type' => $value2->pet_type,
                    'breed' => $value2->breed,
                    'neutered' => $value2->neutered,
                    'spayed' => $value2->spayed,
                    'injuries' => $value2->injuries,
                    'medical_detail' => $value2->medical_detail,
                    'pet_notes' => $value2->pet_notes,
                    'latitude' => $value2->latitude,
                    'longitude' => $value2->longitude,
                    'backupcontact' => $backup_contact,
                );
            }

            $abab = [];

            if ($value->client->others != NULL && $value->client->others != 'O:8:"stdClass":0:{}') {

                $a = array();
                $b = array();
                foreach (unserialize($value->client->others) as $key5 => $value5) {
                    $a[] = $key5;
                    $b[] = $value5;
                }

                for ($i = 0; $i < count(unserialize($value->client->others)); $i++) {

                    $abab[] = array(
                        'key' => $a[$i],
                        'value' => $b[$i],
                    );
                }
            }

            /*New function added for check appointment of each client is present in today/future or not */
            $appointment = Appointment::find('all', array('conditions' => "company_id = {$id} AND client_id={$value->client->client_id}"));
            $color = "none";
            $today = date('Y-m-d');

            foreach ($appointment as $k => $v) {


                $app_date = date('Y-m-d', strtotime($v->date));
                if ($app_date == $today) {
                    $color = "Green";

                    break;
                } else if ($app_date > $today) {

                    if ($color == "Green") {
                        $color = "Green";
                    } else if ($color == "Yellow" or $color == "Red" or $color = "none") {
                        $color = "Yellow";
                    }
                } else {

                    if ($color == "Yellow") {
                        $color = "Yellow";
                    } else if ($color == "Red" or $color == "none") {
                        $color = "Red";
                    }
                }
            }


            $contractData[] = array(
                'client_id' => $value->client->client_id,
                'isManualClient' => $flag,
                'company_id' => $value->company->company_id,
                'contract_id' => $value->contract_id,
                'company_name' => $value->company->company_name,
                'firstname' => $value->client->firstname,
                'lastname' => $value->client->lastname,
                'username' => $value->client->username,

                'profile_image' => $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
                'company_image' => $value->company->company_image != NULL ? COMPANY_PIC_PATH . $value->company->company_image : NULL,
                'emailid' => $value->client->emailid,
                'client_address' => $value->client->client_address,
                'contact_number' => $value->client->contact_number,
                'client_notes' => $value->client->client_notes,
                'player_id' => $value->client->player_id,
                'other' => count($abab) != 0  ? $abab : '',
                'profile_status' => $value->client->status == 1 ? 'active' : 'inactive',
                'status' => $value->status,
                'traffic_signal' => $color,
                'pet_detail' => $pet_detail,

            );
        }



        $response['data'] = $contractData;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No client found.';
        $response['status'] = false;
        $response['data'] = [];
    }


    echoResponse(200, $response);
});

$app->get('/leads', function () use ($app) {

    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Lead list retrive successfully!';

    $contractData = [];

    $leads = Lead::find('all');


    foreach ($leads as $key => $value) {
        $contractData[] = array(
            'lead_id' => $value->id,
            'firstname' => $value->firstname,
            'lastname' => $value->lastname,
            'client_address' => $value->client_address,
            'contact_number' => $value->contact_number,
            'others' => $value->others,
            'pet_name' => $value->pet_name,
            'pet_birth' => $value->pet_birth,
            'breed' => $value->breed,
            'accepted_man_id' => $value->accepted_man_id,
            'company_name' => $value->company_name,
            'accepted_image' => $value->profile_image,
            'status' => $value->status,
            'created_at' => $value->created_at->format('Y-m-d')

        );
    }



    $response['data'] = $contractData;

    echoResponse(200, $response);
});
/*
* Client backup contact informations
*/

$app->post('/:id/:pet_id/contactbackup', function ($id, $pet_id) use ($app) {
    verifyFields(array('name', 'address', 'contact_number'));


    $client_id = $id;
    $pet_id = $pet_id;
    $name = $app->request->post('name');
    $address = $app->request->post('address');
    $contact_number = $app->request->post('contact_number');

    $contact_check = Contact_backup::find(array("conditions" => "client_id={$client_id} AND pet_id={$pet_id}"));

    if ($contact_check != NULL) {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Backup contact already exists.';
    } else {

        $contact = new Contact_backup();
        $contact->client_id = $client_id;
        $contact->pet_id = $pet_id;
        $contact->name = $name;
        $contact->address = $address;
        $contact->contact_number = $contact_number;
        $contact->save();
        $contact->contact_id = (int)$contact->contact_id;



        if ($contact->contact_id > 0) {

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Backup contact added successfully.';
            $response['data'] = array(
                'contact_id' => $contact->contact_id,
                'client_id' => $contact->client_id,
                'pet_id' => $contact->pet_id,
                'name' => $contact->name,
                'address' => $contact->address,
                'number' => $contact->contact_number,

            );
        }
    }
    echoResponse(200, $response);
});



/*
 * Recover passsword page
 */

$app->get('/recover-password/:id/:token', function ($id, $token) use ($app) {

    $client = Client::find('all', array('conditions' => "client_id = {$id} AND token = '{$token}' AND DATE( DATE_SUB( NOW() , INTERVAL 1 DAY ) ) < DATE(token_time)"));
    if ($client) {

        $app->render('resetpassword.php', array('urlpath' => "clientrecoverpassword/{$id}"));
    } else {
        $app->render('resetpassword.php', array('error' => 'You not able to reset password , your token expired please try again!'));
    }
})->name('recover-password');
/*
 *  Client forgot password
 */
$app->post('/client/forgotpassword', function () use ($app) {
    verifyFields(array('emailid'));
    $emailid = $app->request->post('emailid');

    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;

    $client = Client::find_by_emailid($emailid);

    if (empty($client)) {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response["message"] = "Sorry, this email does not exists!";
        echoResponse(200, $response);
        $app->stop();
    } else {
        $token = genRndDgt(8, false);
        $client->token = $token;
        $client->token_time = date('Y-m-d H:m:i');
        $client->save();
        $url = $app->urlFor('recover-password', array('id' => $client->id, 'token' => $client->token));

        $username = $client->firstname . ' ' . $client->lastname;
        $emailid = $client->emailid;

        sendMail($username, $emailid, $url);
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Password reset link sent to your mailid!';
    }

    echoResponse(200, $response);
});



// Reset client password
$app->post('/clientrecoverpassword/:id', function ($id) use ($app) {

    verifyFields(array('new_password', 'confirm_password'));

    $newPassword = $app->request->post('new_password');
    $cpassword = $app->request->post('confirm_password');

    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;

    try {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Please provide correct email id.';
        $client = Client::find($id);

        if ($cpassword == $newPassword && $client) {
            $client->password = sha1(md5($newPassword) . $client->salt);
            $client->token = NULL;
            $client->save();
            $client->client_id = (int) $client->client_id;
            if ($client->client_id > 0) {
                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Your password has been updated successfully!';
                echo '<span style="color:green;">Your password has been updated successfully!!<span>';
            }
        } else {
            echo '<span style="color:red;">Password does not match!<span>';
        }
    } catch (RecordNotFound $e) {
        $response['error_code'] = 1;
        $response['message'] = $e->getMessage();
        $response['status'] = false;
        echo $e->getMessage();
    }
})->name('clientrecoverpassword');

/*
 * Manually add client
 */

$app->post('/clientadd', function () use ($app) {
    verifyFields(array('company_id', 'firstname', 'lastname', 'contact_number', 'client_address', 'pet_name', 'pet_birth', 'age', 'gender', 'pet_type', 'breed', 'pet_notes'));
    $other = json_decode($app->request->post('other'));

    $playerid = NULL;

    $firstname = $app->request->post('firstname');
    $lastname = $app->request->post('lastname');

    $email = NULL;
    if ($app->request->post('email')) {
        $email = $app->request->post('email');
    }
    
    $company_id = $app->request->post('company_id');
    $profile_image = NULL;
    if (isset($_FILES['profile_image'])) {
        $path_parts = pathinfo($_FILES['profile_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($firstname . '' . $lastname);
        $profile_image = time() . '-' . $name . '.' . $ext;
    }
    $pet_image = NULL;

    $contact_number = $app->request->post('contact_number');
    $client_address = $app->request->post('client_address');

    $username = NULL;
    if ($app->request->post('username')) {
        $username = $app->request->post('username');
    }
    // $username = $app->request->post('username');
    $password = $app->request->post('password');

    $password = NULL;
    if ($app->request->post('password')) {
        $password = $app->request->post('password');
    }

    $client_notes = "NA";
    $pet_name = $app->request->post('pet_name');
    $pet_birth = $app->request->post('pet_birth');

    $age = $app->request->post('age');
    $gender = $app->request->post('gender');
    $pet_type = $app->request->post('pet_type');
    $breed = $app->request->post('breed');
    $neutered = $app->request->post('neutered');
    $spayed = $app->request->post('spayed');
    $injuries = $app->request->post('injuries');
    $medical_detail = $app->request->post('medical_detail');
    $notes = $app->request->post('pet_notes');
    if (isset($_FILES['pet_image'])) {
        $path_parts = pathinfo($_FILES['pet_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($pet_name);
        $pet_image = time() . '-' . $name . '.' . $ext;
    }
    Client::transaction(function () use ($app, $company_id, $profile_image, $username, $password, $firstname, $lastname, $email, $contact_number, $client_address, $client_notes, $playerid, $pet_name, $pet_birth, $pet_image, $age, $gender, $pet_type, $breed, $neutered, $spayed, $injuries, $medical_detail, $notes, $other) {
        $client = new Client();
        $client->company_id = $company_id;
        $client->firstname = $firstname;
        $client->lastname = $lastname;
        // $client->username = $username ? $username:'';

        if (isset($username)) {
            $client->username = $username;
        }

        $client->profile_image = $profile_image;
        if (isset($email)) {
            $client->emailid = $email;
        }
        // $client->emailid = $email?$email:'';
        $client->contact_number = $contact_number;
        $client->client_address = $client_address;
        $client->client_notes = $client_notes;
        if (count($other) > 0) {
            $serialized_array = serialize($other);
            $client->others = $serialized_array;
        }
        // $client->salt = "NA";
        $client->salt = genRndDgt(8, false);

        if (isset($password)) {
            $client->password = sha1(md5($password) . $client->salt);
        }
        // $client->password = $password?sha1(md5($password) . $client->salt):'';

        // $client->password = $password;

        $client->status = true;
        $client->save();
        $client->client_id = (int) $client->client_id;
        $pet = new Pet();
        $pet->client_id = $client->client_id;
        $pet->pet_name = $pet_name;
        $pet->pet_birth = $pet_birth;

        $pet->pet_image = $pet_image;
        $pet->age = $age;
        $pet->gender = $gender;
        $pet->pet_type = $pet_type;
        $pet->breed = $breed;
        $pet->neutered = $neutered;
        $pet->spayed = $spayed;
        $pet->injuries = $injuries;
        $pet->medical_detail = $medical_detail;
        $pet->pet_notes = $notes;
        $pet->save();
        $pet->pet_id = (int) $pet->pet_id;

        /*Contract add*/
        $contract = new Contract();
        $contract->company_id = $company_id;
        $contract->client_id = $client->client_id;
        $contract->status = 'accepted';
        $contract->created_at = date('Y-m-d h:i:s');
        $contract->save();
        $contract_id = (int)$contract->contract_id;
        $status = $contract->status;


        /*price add*/
        $service = CompanyService::find('all', array("conditions" => "company_id = {$company_id}"));

        foreach ($service as $key => $value) {

            $priceCheck = Price::find(array("conditions" => "company_id = {$company_id} AND service_id = {$value->service_id} AND p_flag = '0'"));


            if (count($priceCheck) > 0) {

                $company_id = $priceCheck->company_id;
                $service_id = $priceCheck->service_id;
                $full_hour_price = $priceCheck->full_hour_price;
                $half_hour_price = $priceCheck->half_hour_price;
                $additional_hours_price = $priceCheck->additional_hours_price;
                $additional_visits_price = $priceCheck->additional_visits_price;
                $price_per_walk = $priceCheck->price_per_walk;
                $additional_pets = $priceCheck->additional_pets;
                $payment_option = $priceCheck->payment_option;
                $p_flag = $priceCheck->p_flag;


                $price = new Price();
                $price->company_id = $company_id;
                $price->client_id = $client->client_id;
                $price->service_id = $service_id;
                $price->full_hour_price = $full_hour_price;
                $price->half_hour_price = $half_hour_price;
                $price->additional_hours_price = $additional_hours_price;
                $price->additional_visits_price = $additional_visits_price;
                $price->price_per_walk = $price_per_walk;
                $price->additional_pets = $additional_pets;
                $price->payment_option = $payment_option;
                $price->p_flag = $p_flag;
                $price->save();
                $price->price_id = (int) $price->price_id;
            }
        }

        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Error! Something went wrong. please try again later.';
        $response['data'] = [];

        if ($pet->pet_id > 0) {
            if ($profile_image) {
                move_uploaded_file($_FILES['profile_image']['tmp_name'], '../' . USER_PIC_URL . $profile_image);
                $client->profile_image = USER_PIC_URL_PATH . $profile_image;
            }
            if ($pet_image) {
                move_uploaded_file($_FILES['pet_image']['tmp_name'], '../' . PET_PIC_URL . $pet_image);
                $pet->pet_image = PET_PIC_PATH . $pet_image;
            }

            $backup_contact = [];
            $contact_check = Contact_backup::find(array("conditions" => "client_id={$client->client_id} AND pet_id={$pet->pet_id}"));
            if ($contact_check != NULL) {
                $backup_contact = array(
                    'name' => $contact_check->name,
                    'address' => $contact_check->address,
                    'number' => $contact_check->contact_number,
                );
            } else {
                $backup_contact = new stdClass();;
            }

            $flag = $client->company_id != NULL ? TRUE : FALSE;
            $company = Company::find(array('conditions' => "company_id = {$client->company_id}"));
            $client_detail = array(
                'client_id' => $client->client_id,
                'isManualClient' => $flag,
                'company_id' => $client->company_id,
                'contract_id' => $contract_id,
                'company_name' => $company->company_name,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'profile_image' => $client->profile_image != NULL ? USER_PIC_URL_PATH . $client->profile_image : NULL,
                'company_image' => $company->company_image != NULL ? COMPANY_PIC_PATH . $company->company_image : NULL,
                'emailid' => $client->emailid,
                'client_address' => $client->client_address,
                'contact_number' => $client->contact_number,
                'client_notes' => $client->client_notes,
                'other' => $other,
                'player_id' => $client->player_id,
                'profile_status' => $client->status == 1 ? 'active' : 'inactive',
                'status' => $status,
                //'pet_detail' => $pet_detail2,
            );


            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Successfully inserted!.';
            $response['data'][] = array(
                'pet_id' => $pet->pet_id,
                'pet_image' => $pet->pet_image,
                'pet_name' => $pet->pet_name,
                'pet_birth' => $pet->pet_birth,

                'pet_age' => $pet->age,
                'gender' => $pet->gender,
                'pet_type' => $pet->pet_type,
                'breed' => $pet->breed,
                'neutered' => $pet->neutered,
                'spayed' => $pet->spayed,
                'injuries' => $pet->injuries,
                'medical_detail' => $pet->medical_detail,
                'pet_notes' => $pet->pet_notes,
                'latitude' => $pet->latitude,
                'longitude' => $pet->longitude,
                'client' => $client_detail,
                'backupcontact' => $backup_contact
            );
        }

        echoResponse(200, $response);
    });
});

/*
 * Manually add Lead
 */

$app->post('/leadadd', function () use ($app) {
    verifyFields(array('firstname', 'lastname', 'contact_number', 'client_address', 'pet_name', 'pet_birth', 'breed'));
    $other = json_decode($app->request->post('other'));

    $staffId = $app->request->post('staff_id');
    $companyId = $app->request->post('company_id');
    $firstname = $app->request->post('firstname');
    $lastname = $app->request->post('lastname');
    $email = NULL;
    // $company_id = $app->request->post('company_id');

    $contact_number = $app->request->post('contact_number');
    $client_address = $app->request->post('client_address');

    $pet_name = $app->request->post('pet_name');
    $pet_birth = $app->request->post('pet_birth');

    $breed = $app->request->post('breed');

    $lead = new Lead();
    $lead->add_by_admin_id = $companyId;
    $lead->add_by_staff_id = $staffId;

    $lead->firstname = $firstname;
    $lead->lastname = $lastname;
    $lead->contact_number = $contact_number;
    $lead->client_address = $client_address;
    if (count($other) > 0) {
        $serialized_array = serialize($other);
        $lead->others = $serialized_array;
    }

    $lead->pet_name = $pet_name;
    $lead->pet_birth = $pet_birth;

    $lead->breed = $breed;
    $lead->created_at = date('Y-m-d');
    $lead->save();

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];

    // $flag = $client->company_id != NULL ? TRUE : FALSE;
    // $company = Company::find(array('conditions' => "company_id = {$client->company_id}"));

    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully inserted!.';
    $response['data'][] = array(
        'lead_id' => $lead->id,
        // 'company_id' => $client->company_id,
        // 'company_name' => $company->company_name,
        'firstname' => $lead->firstname,
        'lastname' => $lead->lastname,
        'client_address' => $lead->client_address,
        'contact_number' => $lead->contact_number,
        'others' => $lead->others,

        'lead_name' => $lead->pet_name,
        'lead_birth' => $lead->pet_birth,

        'breed' => $lead->breed,
        'created_at' => $lead->created_at->format('Y-m-d')

    );

    echoResponse(200, $response);
});

/*
 * Manually remove Lead
 */

/*
 * Manually accept Lead
 */

$app->post('/leadaccept', function () use ($app) {
    verifyFields(array('lead_id', 'company_id', 'company_image'));
    $lead_ids = $app->request->post('lead_id');
    $company_id = $app->request->post('company_id');
    $company_image = $app->request->post('company_image');
    $company_name = $app->request->post('company_name');

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];

    $lead_array = explode(" ", $lead_ids);
    foreach ($lead_array as $key => $lead_id) {
        if ($lead_id != "") {
            $lead = Lead::find($lead_id);
            // $lead->company_id = $company_id;
            $lead->accepted_man_id = $company_id;
            $lead->profile_image = $company_image;
            $lead->company_name = $company_name;
            $lead->save();
            $data = array(
                'lead_id' => $lead->id,
                // 'company_id' => $client->company_id,
                // 'company_name' => $company->company_name,
                'firstname' => $lead->firstname,
                'lastname' => $lead->lastname,
                'client_address' => $lead->client_address,
                'contact_number' => $lead->contact_number,
                'others' => $lead->others,
                'profile_image' => $lead->profile_image,
                'lead_name' => $lead->pet_name,
                'lead_birth' => $lead->pet_birth,
                'breed' => $lead->breed,
                'created_at' => $lead->created_at->format('Y-m-d')

            );
            array_push($response['data'], $data);
        }
    }

    // $flag = $client->company_id != NULL ? TRUE : FALSE;
    // $company = Company::find(array('conditions' => "company_id = {$client->company_id}"));

    $response['error_code'] = 0;
    $response['status'] = true;
    // $response['message'] = 'Successfully accepted!.';
    $response['message'] = 'Successfully accepted!.';


    echoResponse(200, $response);
});

/*
 * Manually accept Lead
 */

$app->post('/leadremove', function () use ($app) {
    verifyFields(array('lead_id'));
    $lead_id = json_decode($app->request->post('lead_id'));

    $lead = Lead::find($lead_id);


    if ($lead) {
        $lead->delete();
    }
    // $response['error_code'] = 1;
    // $response['status'] = false;
    // $response['message'] = 'Error! Something went wrong. please try again later.';
    // $response['data'] = [];

    // $flag = $client->company_id != NULL ? TRUE : FALSE;
    // $company = Company::find(array('conditions' => "company_id = {$client->company_id}"));

    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully removed!.';
    // $response['data'][] = array();

    echoResponse(200, $response);
});

$app->post('/:id/addcredential', function ($id) use ($app) {
    verifyFields(array('client_id', 'email', 'password'));

    $company_id = $id;
    $client_id = $app->request->post('client_id');
    $email = $app->request->post('email');
    $password = $app->request->post('password');


    $client = Client::find(array("conditions" => "company_id=$company_id AND client_id=$client_id"));
    if ($client) {
        if ($client->emailid == NULL) {
            $client->emailid = $email;
            $client->salt = genRndDgt(8, false);
            $client->password = sha1(md5($password) . $client->salt);
            $client->save();

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Credentials added successfully!';
        } else {
            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Email id and password already exists.';
        }
    } else {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Client not found.';
    }

    echoResponse(200, $response);
});
/*
 * Client listing for Manually added
 */
$app->get('/:id/clients/manual', function ($id) use ($app) {

    $client = Client::find('all', array('conditions' => "company_id = {$id} "));

    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;


    $clientData = [];
    if ($client) {
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Client list retrive successfully!';
        foreach ($client as $key => $value) {
            $company = Company::find(array('conditions' => "company_id = {$value->company_id}"));
            $pet = Pet::find(array('conditions' => "client_id = {$value->client_id}"));
            $clientData[] = array(
                'client_id' => $value->client_id,
                'company_id' => $company->company_id,
                'contract_id' => 0,
                'company_name' => $company->company_name,
                'firstname' => $value->firstname,
                'lastname' => $value->lastname,
                'profile_image' => $value->profile_image != NULL ? USER_PIC_URL_PATH . $value->profile_image : NULL,
                'company_image' => $company->company_image != NULL ? COMPANY_PIC_PATH . $company->company_image : NULL,
                'emailid' => $value->emailid,
                'client_address' => $value->client_address,
                'contact_number' => $value->contact_number,
                'client_notes' => $value->client_notes,
                'other' => $value->others != NULL ? unserialize($value->others) : '',
                'player_id' => $value->player_id,
                'profile_status' => $value->status == 1 ? 'active' : 'inactive',
                'pet_id' => isset($pet->pet_id) ? $pet->pet_id : NULL,
                'pet_name' => isset($pet->pet_name) ? $pet->pet_name : NULL,
                'pet_age' => isset($pet->age) ? $pet->age : NULL,
                'pet_medcial_detail' => isset($pet->medical_detail) ? $pet->medical_detail : NULL,
                'pet_notes' => isset($pet->pet_notes) ? $pet->pet_notes : NULL,
            );
        }
        $response['data'] = $clientData;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No client found.';
        $response['status'] = false;
        $response['data'] = [];
    }
    echoResponse(200, $response);
});


/* 
 * Client delete
 */
$app->get('/:clientid/client/delete', function ($clientid) use ($app) {
    $client = Client::find($clientid);


    if ($client) {
        $client->delete();
        $response['error_code'] = 0;
        $response['message'] = 'Record deleted successfully!';
        $response['status'] = true;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No Client found';
        $response['status'] = false;
    }

    echoResponse(200, $response);
});


/*
 * Client wise Price adding for service
 */
$app->post('/:id/clientpriceadd', function ($id) use ($app) {

    verifyFields(array('service_id', 'client_id', 'full_hour_price', 'half_hour_price', 'additional_hours_price', 'additional_visits_price', 'price_per_walk', 'additional_pets', 'payment_option'));

    $company_id = $id;
    $client_id = $app->request->post('client_id');
    $service_id = $app->request->post('service_id');
    $full_hour_price = $app->request->post('full_hour_price');
    $half_hour_price = $app->request->post('half_hour_price');
    $additional_hours_price = $app->request->post('additional_hours_price');
    $additional_visits_price = $app->request->post('additional_visits_price');
    $price_per_walk = $app->request->post('price_per_walk');
    $additional_pets = $app->request->post('additional_pets');
    $payment_option = $app->request->post('payment_option');


    $priceCheck = Price::find(array("conditions" => "company_id = {$company_id} AND client_id= {$client_id} AND service_id = {$service_id}"));

    if (count($priceCheck) > 0) {
        $priceCheck->company_id = $company_id;
        $priceCheck->client_id = $client_id;
        $priceCheck->service_id = $service_id;
        $priceCheck->full_hour_price = $full_hour_price;
        $priceCheck->half_hour_price = $half_hour_price;
        $priceCheck->additional_hours_price = $additional_hours_price;
        $priceCheck->additional_visits_price = $additional_visits_price;
        $priceCheck->price_per_walk = $price_per_walk;
        $priceCheck->additional_pets = $additional_pets;
        $priceCheck->payment_option = $payment_option;
        $priceCheck->p_flag = 1;
        $priceCheck->save();
        $priceCheck->price_id = (int) $priceCheck->price_id;

        if ($priceCheck->price_id > 0) {

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Price Successfully updated.';
            $response['data'] = array(
                'price_id' => $priceCheck->price_id,
                'company_id' => $priceCheck->company_id,
                'client_id' => $priceCheck->client_id,
                'service_id' => $priceCheck->service_id,
                'full_hour_price' => $priceCheck->full_hour_price,
                'half_hour_price' => $priceCheck->half_hour_price,
                'additional_hours_price' => $priceCheck->additional_hours_price,
                'additional_visits_price' => $priceCheck->additional_visits_price,
                'price_per_walk' => $priceCheck->price_per_walk,
                'additional_pets' => $priceCheck->additional_pets,
                'payment_option' => $priceCheck->payment_option,
                'p_flag' => $priceCheck->p_flag
            );
        }

        echoResponse(200, $response);
    } else {


        Price::transaction(function () use ($app, $company_id, $client_id, $service_id, $full_hour_price, $half_hour_price, $additional_hours_price, $additional_visits_price, $price_per_walk, $additional_pets, $payment_option) {
            $price = new Price();
            $price->company_id = $company_id;
            $price->client_id = $client_id;
            $price->service_id = $service_id;
            $price->full_hour_price = $full_hour_price;
            $price->half_hour_price = $half_hour_price;
            $price->additional_hours_price = $additional_hours_price;
            $price->additional_visits_price = $additional_visits_price;
            $price->price_per_walk = $price_per_walk;
            $price->additional_pets = $additional_pets;
            $price->payment_option = $payment_option;
            $price->p_flag = 1;
            $price->save();
            $price->price_id = (int) $price->price_id;


            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Error! Something went wrong. please try again later.';
            $response['data'] = [];

            if ($price->price_id > 0) {

                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Price Successfully added.';
                $response['data'] = array(
                    'price_id' => $price->price_id,
                    'company_id' => $priceCheck->company_id,
                    'client_id' => $priceCheck->client_id,
                    'service_id' => $price->service_id,
                    'full_hour_price' => $full_hour_price,
                    'half_hour_price' => $half_hour_price,
                    'additional_hours_price' => $additional_hours_price,
                    'additional_visits_price' => $additional_visits_price,
                    'price_per_walk' => $price_per_walk,
                    'additional_pets' => $additional_pets,
                    'payment_option' => $payment_option,
                    'p_flag' => $priceCheck->p_flag
                );
            }

            echoResponse(200, $response);
        });
    }
});

/*
 * Shwoing price as per service according to service
 */

$app->post('/:id/clientprice', function ($id) use ($app) {


    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];

    verifyFields(array('client_id'));
    $client_id = $app->request->post('client_id');
    $company_id = $id;

    $services = CompanyService::find('all', array('conditions' => "company_id = {$id}"));
    $ServicePrice = [];
    if (count($services) > 0) {
        $x = new stdClass();
        $ServicePrice[] = array(
            'price_id' => '',
            'service_id' => '',
            'service_name' => '',
            'full_hour_price' => '',
            'half_hour_price' => '',
            'additional_hours_price' => '',
            'additional_visits_price' => '',
            'price_per_walk' => '',
            'additional_pets' => '',
            'payment_option' => '',
        );

        foreach ($services as $key => $value) {

            $price = Price::find(array('conditions' => "company_id = {$id} AND client_id={$client_id} AND service_id = {$value->service_id}"));
            if (count($price) > 0) {

                $ServicePrice[] = array(
                    'price_id' => $price->price_id,
                    'service_id' => $price->service_id,
                    'service_name' => $price->service->service_name,
                    'full_hour_price' => $price->full_hour_price,
                    'half_hour_price' => $price->half_hour_price,
                    'additional_hours_price' => $price->additional_hours_price,
                    'additional_visits_price' => $price->additional_visits_price,
                    'price_per_walk' => $price->price_per_walk,
                    'additional_pets' => $price->additional_pets,
                    'payment_option' => $price->payment_option,
                );
            } else {

                $s = Service::find($value->service_id);
                $ServicePrice[] = array(
                    'price_id' => 0,
                    'service_id' => $value->service_id,
                    'service_name' => $s->service_name,
                    'full_hour_price' => 0,
                    'half_hour_price' => 0,
                    'additional_hours_price' => 0,
                    'additional_visits_price' => 0,
                    'price_per_walk' => 0,
                    'additional_pets' => 0,
                    'payment_option' => 0,
                );
            }
        }
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Price Successfully Retrive.';
        $response['data'] = $ServicePrice;
    }

    echoResponse(200, $response);
});

/* Add Muliple pets for single client*/

$app->post('/:id/multipetinsert', function ($id) use ($app) {

    verifyFields(array('pet_name', 'age', 'gender', 'pet_type', 'breed', 'injuries'));

    $client_id = $id;
    $pet_name = $app->request->post('pet_name');
    $pet_birth = $app->request->post('pet_birth');

    $age = $app->request->post('age');
    $gender = $app->request->post('gender');
    $pet_type = $app->request->post('pet_type');
    $breed = $app->request->post('breed');
    $neutered = $app->request->post('neutered');
    $spayed = $app->request->post('spayed');
    $injuries = $app->request->post('injuries');
    $medical_detail = $app->request->post('medical_detail');
    $notes = $app->request->post('pet_notes');

    $pet_image = NULL;

    if (isset($_FILES['pet_image'])) {
        $path_parts = pathinfo($_FILES['pet_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($pet_name);
        $pet_image = time() . '-' . $name . '.' . $ext;
    }



    Pet::transaction(function () use ($app, $client_id, $pet_name, $pet_birth, $pet_image, $age, $age, $gender, $pet_type, $breed, $neutered, $spayed, $injuries, $medical_detail, $notes) {
        $pet = new Pet();
        $pet->client_id = $client_id;
        $pet->pet_name = $pet_name;
        $pet->pet_birth = $pet_birth;

        $pet->pet_image = $pet_image;
        $pet->age = $age;
        $pet->gender = $gender;
        $pet->pet_type = $pet_type;
        $pet->breed = $breed;
        $pet->neutered = $neutered;
        $pet->spayed = $spayed;
        $pet->injuries = $injuries;
        $pet->medical_detail = $medical_detail;
        $pet->pet_notes = $notes;
        $pet->save();
        $pet->pet_id = (int) $pet->pet_id;
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Error! Something went wrong. please try again later.';
        $response['data'] = [];

        if ($pet->pet_id > 0) {
            if ($pet_image) {
                move_uploaded_file($_FILES['pet_image']['tmp_name'], '../' . PET_PIC_URL . $pet_image);
                $pet->pet_image = PET_PIC_PATH . $pet_image;
            }

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Successfully Pet Inserted.';

            $petData = $pet->to_array();

            $response['data'] = $petData;

            echoResponse(200, $response);
            return TRUE;
        } else {
            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Error! Something went wrong.';

            echoResponse(200, $response);
            return FALSE;
        }
    });
});


$app->post('/get-all-appointments', function () use ($app) {
    // $appointment = Appointment::find('all')->limit(100);
    $from = $app->request->post('from');
    $to = $app->request->post('to');

    $appointment = Appointment::find_by_sql("SELECT * FROM `tbl_appointments` JOIN tbl_pets on tbl_pets.pet_id = tbl_appointments.pet_id where tbl_appointments.date >='" . $from . "' && tbl_appointments.date <= '" . $to . "' order by appointment_id DESC");
    foreach ($appointment as $k => $value2) {
        $data_detail[] = array(
            'date' => date('Y-m-d', strtotime($value2->date)),
            'visits' => $value2->visits,
            'visit_hours' => $value2->visit_hours,
            'price' => $value2->price,
            'status' => $value2->status,
            'accepted' => $value2->accepted,
            'completed' => $value2->completed,
            'pet_name' => $value2->pet_name,
        );
    }


    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully.';
    $response['data'] = $data_detail;
    echoResponse(200, $response);
    return true;
});

$app->get('/get-all-prices', function () use ($app) {
    // $appointment = Appointment::find('all')->limit(100);
    $price = Pricenew::find_by_sql("SELECT * FROM `tbl_newprices` JOIN tbl_pets on tbl_pets.pet_id = tbl_newprices.pet_id order by price_id DESC limit 100");
    foreach ($price as $k => $value2) {
        $data_detail[] = array(
            'pet_name' => $value2->pet_name,
            'full_day_price' => $value2->full_day_price,
            'full_hour_price' => $value2->full_hour_price,
            'half_hour_price' => $value2->half_hour_price,
            'additional_hours_price' => $value2->additional_hours_price,
            'additional_visits_price' => $value2->additional_visits_price,
            'payment_option' => $value2->payment_option,
        );
    }


    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully.';
    $response['data'] = $data_detail;
    echoResponse(200, $response);
    return true;
});

$app->post('/get-all-transactions', function () use ($app) {
    // $appointment = Appointment::find('all')->limit(100);
    $from = $app->request->post('from');
    $to = $app->request->post('to');
    $transaction = Transactionlog::find_by_sql("SELECT * FROM `tbl_newtransaction_log` JOIN tbl_pets on tbl_pets.pet_id = tbl_newtransaction_log.pet_id where tbl_newtransaction_log.date_of_transaction >='" . $from . "' && tbl_newtransaction_log.date_of_transaction <= '" . $to . "' order by log_id DESC");
    foreach ($transaction as $k => $value2) {
        $data_detail[] = array(
            'pet_name' => $value2->pet_name,
            'date_of_transaction' => date('Y-m-d', strtotime($value2->date_of_transaction)),
            'type' => $value2->type,
            'amount' => $value2->amount,
            'old_value' => $value2->old_value,
            'new_value' => $value2->new_value,
        );
    }


    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully.';
    $response['data'] = $data_detail;
    echoResponse(200, $response);
    return true;
});

$app->post('/get-all-credits', function () use ($app) {
    // $appointment = Appointment::find('all')->limit(100);
    $from = $app->request->post('from');
    $to = $app->request->post('to');
    $credit = Credits::find_by_sql("SELECT * FROM `tbl_newcredits` JOIN tbl_pets on tbl_pets.pet_id = tbl_newcredits.pet_id where tbl_newcredits.date_of_payment >='" . $from . "' && tbl_newcredits.date_of_payment <= '" . $to . "' order by credit_id DESC");
    foreach ($credit as $k => $value2) {
        $data_detail[] = array(
            'pet_name' => $value2->pet_name,
            'paid_amount' => $value2->paid_amount,
            'old_amount' => $value2->old_amount,
            'last_check' => date('Y-m-d', strtotime($value2->last_check)),
            'date_of_payment' => date('Y-m-d', strtotime($value2->date_of_payment)),
            'remaining' => $value2->remaining,
        );
    }


    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully.';
    $response['data'] = $data_detail;
    echoResponse(200, $response);
    return true;
});

$app->get('/get-all-companies', function () use ($app) {
    // $appointment = Appointment::find('all')->limit(100);
    $company = Company::find_by_sql("SELECT * FROM `tbl_companies` order by company_id DESC limit 100");
    foreach ($company as $k => $value2) {
        $data_detail[] = array(
            'company_name' => $value2->company_name,
            'contact_number' => $value2->contact_number,
            'company_banner' => $value2->company_banner,
            'website' => $value2->website,
            'address' => $value2->address,
            'organization' => $value2->organization,
            'about' => $value2->about,
            'is_active' => $value2->is_active,

        );
    }


    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully.';
    $response['data'] = $data_detail;
    echoResponse(200, $response);
    return true;
});

$app->get('/get-all-pets', function () use ($app) {
    // $appointment = Appointment::find('all')->limit(100);
    $pet = Pet::find_by_sql("SELECT * FROM `tbl_pets` order by pet_id DESC limit 100");
    foreach ($pet as $k => $value2) {
        $data_detail[] = array(
            'pet_name' => $value2->pet_name,
            'pet_birth' => date($value2->pet_birth),
            'age' => $value2->age,
            'gender' => $value2->gender,
            'pet_type' => $value2->pet_type,
            'breed' => $value2->breed,
            'neutered' => $value2->neutered,
            'spayed' => $value2->spayed,
            'injuries' => $value2->injuries,
            'medical_detail' => $value2->medical_detail,
            'pet_notes' => $value2->pet_notes,

        );
    }


    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully.';
    $response['data'] = $data_detail;
    echoResponse(200, $response);
    return true;
});

$app->get('/get-all-staffs', function () use ($app) {
    // $appointment = Appointment::find('all')->limit(100);
    $staff = staff::find_by_sql("SELECT * FROM `tbl_staffs` order by staff_id DESC limit 50");
    foreach ($staff as $k => $value2) {
        $data_detail[] = array(
            'username' => $value2->username,
            'salt' => $value2->salt,
            'firstname' => $value2->firstname,
            'lastname' => $value2->lastname,
            'contact_number' => $value2->contact_number,
            'address' => $value2->address,
            'status' => $value2->status,

        );
    }


    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully.';
    $response['data'] = $data_detail;
    echoResponse(200, $response);
    return true;
});

$app->post('/client-profile', function () use ($app) {
    // $appointment = Appointment::find('all')->limit(100);
    $client_id = $app->request->post('client_id');
    $client = Client::find_by_sql("SELECT * FROM `tbl_clients` where client_id={$client_id}");

    // $flag = $value->client->company_id != NULL ? TRUE : FALSE;
    $pet = Pet::find('all', array("conditions" => "client_id = {$client_id}"));
    foreach ($pet as $k => $value) {

        $pet_detail[] = array(
            'pet_id' => $value->pet_id,
            'pet_name' => $value->pet_name,
            'pet_image' => $value->pet_image,
            'pet_birth' => $value->pet_birth,
            'gender' => $value->gender,
            'breed' => $value->breed,
            'pet_type' => $value->pet_type,
            'medical_detail' => $value->medical_detail,
            'injuries' => $value->injuries,
            'pet_notes' => $value->pet_notes,
        );
    }
    // die(print_r($client));
    $data_detail[] = array(
        'client_id' => $client[0]->client_id,
        'company_id' => $client[0]->company_id,

        'firstname' => $client[0]->firstname,
        'lastname' => $client[0]->lastname,
        'profile_image' => $client[0]->profile_image,
        'contact_number' => $client[0]->contact_number,
        'client_address' => $client[0]->client_address,
        'pet_detail' => $pet_detail,
    );

    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully.';
    $response['data'] = $data_detail;
    echoResponse(200, $response);
    return true;
});
