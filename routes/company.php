<?php
header('Access-Control-Allow-Origin: *');

header('Content-Type: text/plain');

$app->post('/companylogin', function() use ($app) {
    verifyFields(array('emailid', 'password', 'playerid'));

    $emailid = $app->request->post('emailid');
    $password = $app->request->post('password');
    $playerid = $app->request->post('playerid');

    
    $company = Company::find_by_emailid($emailid);


    $response['error_code'] = 1;
    $response['message'] = 'Invalid credentials';
    $response['status'] = false;
    $response['tes'] = sha1(md5($password) . ($company->salt));
    $companyServices = [];
    try {
        if ($company) {
            $company->player_id = $playerid;
            $company->save();

            $company_id = $company->company_id;
            if (sha1(md5($password) . ($company->salt)) == ($company->password)) {
                if (isset($company->company_image)) {
                    $company->company_image = COMPANY_PIC_PATH . $company->company_image;
                } else {
                    $company->company_image = null;
                }
                if (isset($company->company_banner)) {
                    $company->company_banner = COMPANY_BANNER_PATH . $company->company_banner;
                } else {
                    $company->company_banner = null;
                }

                $services = CompanyService::find_by_sql("select service_id from tbl_company_services where `company_id` = {$company_id}");

                foreach ($services as $service) {
                    $service = Service::find($service->service_id);
                    $companyServices[] = array(
                        'id' => $service->service_id,
                        'name' => $service->service_name
                    );
                }


                $response['error_code'] = 0;
                $response['message'] = 'Login successfully';
                $response['status'] = true;
                $response['data'] = $company->to_array(array('except' => array('salt', 'password', 'added_on', 'updated_on')));
                $response['data']['services'] = $companyServices;
                //$response['data']['t'] = $companyServices;
            }
        }

        echoResponse(200, $response);
    } catch (RecordNotFound $e) {
        
    }
});

$app->post('/companyweblogin', function() use ($app) {
    // verifyFields(array('emailid', 'password', 'playerid'));

    $emailid = $app->request->post('emailid');
    $password = $app->request->post('password');
   
    
    $company = Company::find_by_emailid($emailid);

    // die(print_r($company));
    $response['error_code'] = 1;
    $response['message'] = 'Invalid credentials';
    $response['status'] = false;
    // $response['tes'] = sha1(md5($password) . ($company->salt));
    $companyServices = [];
    try {
        if ($company) {
            // $company->player_id = $playerid;
            // $company->save();

            $company_id = $company->company_id;
            if (sha1(md5($password) . ($company->salt)) == ($company->password)) {
                

                $services = CompanyService::find_by_sql("select service_id from tbl_company_services where `company_id` = {$company_id}");

                foreach ($services as $service) {
                    $service = Service::find($service->service_id);
                    $companyServices[] = array(
                        'id' => $service->service_id,
                        'name' => $service->service_name
                    );
                }


                $response['error_code'] = 0;
                $response['message'] = 'Login successfully';
                $response['status'] = true;
                $response['data'] = $company->to_array(array('except' => array('salt', 'password', 'added_on', 'updated_on')));
                $response['data']['services'] = $companyServices;
                //$response['data']['t'] = $companyServices;
            }
        }

        echoResponse(200, $response);
    } catch (RecordNotFound $e) {
        
    }
});


//Company Registration
$app->post('/companyregister', function() use ($app) {
    verifyFields(array('company_name', 'website', 'address', 'emailid', 'contact_number', 'organization', 'about', 'password', 'playerid'));

    $company_name = $app->request->post('company_name');
    $website = $app->request->post('website');
    $address = $app->request->post('address');
    $emailid = $app->request->post('emailid');
    $contact_number = $app->request->post('contact_number');
    $about = $app->request->post('about');
    $organization = $app->request->post('organization');

    $password = $app->request->post('password');
    $playerid = $app->request->post('playerid');

    $company_image = NULL;
    //by naveen enushan
    $company_banner = NULL;
    if (isset($_FILES['company_image'])) {
        $path_parts = pathinfo($_FILES['company_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($company_name);
        $company_image = time() . '-' . $name . '.' . $ext;
    }

 if (isset($_FILES['company_banner'])) {
        $path_parts = pathinfo($_FILES['company_banner']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($company_name);
        $company_banner = time() . '-' . $name . '.' . $ext;
    }

    $service_ids = $app->request->post('service_id');

    $company = Company::find_by_emailid($emailid);

    if ($company) {
        $response["error_code"] = 1;
        $response["status"] = false;
        $response["message"] = "Sorry, this email already exists!";
        echoResponse(200, $response);
    } else {
        // Begin transaction
        Company::transaction(function() use($app, $company_name, $company_image, $company_banner, $website, $address, $emailid, $contact_number, $organization, $about, $password, $service_ids, $playerid) {
            $company = new Company();
            $company->company_name = $company_name;
            $company->emailid = $emailid;
            $company->contact_number = $contact_number;
            $company->company_image = $company_image;
            $company->company_banner=$company_banner;
            $company->website = $website;
            $company->address = $address;
            $company->organization = $organization;
            $company->about = $about;

            $company->salt = genRndDgt(8, false);
            $company->password = sha1(md5($password) . $company->salt);
            $company->player_id = $playerid;
            $company->save();
            $company->company_id = (int) $company->company_id;
            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Error! Something went wrong. please try again later.';
            $response['data'] = [];
            $services = array();
            if ($company->company_id > 0) {
                if ($company_image) {
                    move_uploaded_file($_FILES['company_image']['tmp_name'], '../' . COMPANY_PIC_URL . $company_image);
                    $company->company_image = COMPANY_PIC_PATH . $company_image;
                }
                if ($company_banner) {
                    move_uploaded_file($_FILES['company_banner']['tmp_name'], '../' . COMPANY_BANNER_URL . $company_banner);
                    $company->company_banner = COMPANY_BANNER_PATH . $company_banner;
                }

                if (array_filter($service_ids)&& !in_array('null',array_map("strtolower", $service_ids))) {
                    foreach ($service_ids as $service_id) {
                        $companyService = new CompanyService();
                        $companyService->company_id = $company->company_id;
                        $companyService->service_id = $service_id;
                        $companyService->no_of_visits = 0;
                        $companyService->price = 0;
                        $companyService->additional_walks = 0;
                        $companyService->addtional_visits = 0;
                        $companyService->save();
                        $service = Service::find($service_id);
                        $services[] = array(
                            'id' => $service_id,
                            'name' => $service->service_name
                        );
                    }
                }


                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Successfully Registered.';

                $companyData = $company->to_array(array('except' => array('salt', 'password', 'added_on', 'updated_on')));

                $response['data'] = $companyData;
                $response['data']['services'] = $services;
                echoResponse(200, $response);
                return TRUE;
            } else {
                $response['error_code'] = 1;
                $response['status'] = false;
                $response['message'] = 'Error! Something went wrong';

                echoResponse(200, $response);
                return FALSE;
            }
        });
    }
});


$app->get('/:id/companies', function($id) use ($app) {
    

    $companies = Company::find_by_sql("SELECT cpm.*,con.status,con.client_id as cclient_id FROM `tbl_companies` cpm join tbl_contracts con on cpm.company_id = con.company_id AND con.client_id = {$id} AND con.status = 'accepted' ");


    if (count($companies) <= 0) {

        $response['error_code'] = 1;
        $response['message'] = 'No Company found';
        $response['status'] = false;
    }

    foreach ($companies as $key => &$company) {
        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$company->company_id}");
        $companyServices = array();
        foreach ($services as $companyService) {
            $companyServices[] = array(
                'id' => $companyService->service_id,
                'name' => $companyService->service_name
            );
        }
        $companies[$key] = array(
            'id' => $company->company_id,
            'name' => $company->company_name,
            'website' => $company->website,
            'company_image' => $company->company_image != NULL ? COMPANY_PIC_PATH . $company->company_image : NULL,
            'address' => $company->address,
            'emailid' => $company->emailid,
            'contact_number' => $company->contact_number,
            'about' => $company->about,
            'player_id' => $company->player_id,
            'status' => $company->status
        );
        
        $companies[$key]['services'] = $companyServices;
    }

    if (count($companies) > 0) {
        $response['error_code'] = 0;
        $response['message'] = 'Company list retrived successfully.';
        $response['status'] = true;
        $response['data'] = $companies;
    }
    echoResponse(200, $response);
});


/*
 * Compnay Listing all
 */

$app->get('/:id/all/companies', function($id) use ($app) {
    

    $response['error_code'] = 1;
    $response['message'] = 'No Company found';
    $response['status'] = false;

    $serviceAcceped = Company::find_by_sql("SELECT cpm.service_id , con.company_id FROM `tbl_company_services` cpm join tbl_contracts con on cpm.company_id = con.company_id AND con.client_id = {$id} AND con.status = 'accepted' ");
    if ($serviceAcceped) {

        $companyAcceptedService = array();
        foreach ($serviceAcceped as $sa) {
           
            $comp = Company::find_by_sql("SELECT * FROM `tbl_company_services` where service_id = {$sa->service_id} ");

            foreach ($comp as $c) {
                

                if ($c->company_id != $sa->company_id) {
                    $companyAcceptedService[] = $c->company_id;
                }
            }

            $companies = Company::find('all', array('conditions' => array('company_id NOT in (?) AND is_active = ?', $companyAcceptedService,1)));
        }
        
    } else {
        
     $companies = Company::find('all',array('conditions' => "is_active = 1"));
    }


    foreach ($companies as $key => &$company) {
        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$company->company_id}");
        $companyServices = array();
        foreach ($services as $companyService) {
            $companyServices[] = array(
                'id' => $companyService->service_id,
                'name' => $companyService->service_name
            );
        }
        $contract = Contract::find(array('conditions' => "company_id = {$company->company_id} AND client_id = {$id}"));
        $companies[$key] = array(
            'id' => $company->company_id,
            'name' => $company->company_name,
            'website' => $company->website,
            'company_image' => $company->company_image != NULL ? COMPANY_PIC_PATH . $company->company_image : NULL,
            'address' => $company->address,
            'emailid' => $company->emailid,
            'contact_number' => $company->contact_number,
            'about' => $company->about,
            'client_id' => empty($contract) ? NULL : $contract->client_id,
            'contract_id' => empty($contract) ? NULL : $contract->contract_id,
            'player_id' => $company->player_id,
            'status' => empty($contract) ? NULL : $contract->status,
        );

        
        $companies[$key]['services'] = $companyServices;
        if (empty($companies[$key]['services'])) {
            unset($companies[$key]);
        }
    }
   
    if (count($companies) > 0) {
        $response['error_code'] = 0;
        $response['message'] = 'Company list retrived successfully.';
        $response['status'] = true;
        $response['data'] = array_values($companies);
    }

echoResponse(200, $response);
});



$app->post('/company/profilepic', function() use ($app) {

    verifyFields(array('company_id'));         // checking client id

    $id = $app->request->post('company_id');
    $company = Company::find($id);

    $company_image = $app->request->post('company_image') == NULL ? $company->company_image : $app->request->post('company_image');

    if (isset($_FILES['company_image'])) {
        $path_parts = pathinfo($_FILES['company_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($company->company_name);
        $company_image = time() . '-' . $name . '.' . $ext;

        if ($company->company_image != NULL) {
            $oldimage = '../' . COMPANY_PIC_URL . $company->company_image;  // old image path
            file_exists($company_image) ? unlink($oldimage) : NULL;  // delete old image
        }


        if ($company_image) {
            // upload if user change profile picture..

            move_uploaded_file($_FILES['company_image']['tmp_name'], '../' . COMPANY_PIC_URL . $company_image);

            $company->company_image = $company_image;
        }
    }
	$services = CompanyService::find_by_sql("select service_id from tbl_company_services where `company_id` = {$id}");

                foreach ($services as $service) {
                    $service = Service::find($service->service_id);
                    $companyServices[] = array(
                        'id' => $service->service_id,
                        'name' => $service->service_name
                    );
                }

    $company->save();
    if (!$company->save()) {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Error! Something went wrong. please try again later.';
        $response['data'] = [];
    } else {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Successfully Updated.';
        $response['data'] = [
            'company_id' => $id,
            'account_id' => $company->account_id,
            'company_name' => $company->company_name,
            'company_image' => $company->company_image != NULL ? COMPANY_PIC_PATH . $company->company_image : NULL,
            'website' => $company->website,
            'address' => $company->address,
            'emailid' => $company->emailid,
            'contact_number' => $company->contact_number,
            'about' => $company->about,
            'is_active' => $company->is_active,
            'player_id' => $company->player_id,
        ];
	
                $response['data']['services'] = $companyServices;
    }
    echoResponse(200, $response);
});


/*
 * Companuy banner edit
 */
$app->post('/company/banner', function() use ($app) {

    verifyFields(array('company_id'));         // checking client id

    $id = $app->request->post('company_id');
    $company = Company::find($id);
    $compnayservice = CompanyService::find_by_company_id($company->company_id);
    $service = Service::find($compnayservice->service_id);
    if ($service) {

        $services[] = array(
            'id' => $service->service_id,
            'name' => $service->service_name
        );
    } else {
        $services[] = '';
    }
    $company_banner = $app->request->post('company_banner') == NULL ? $company->company_banner : $app->request->post('company_banner');

    if (isset($_FILES['company_banner'])) {
        $path_parts = pathinfo($_FILES['company_banner']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($company->company_name);
        $company_banner = time() . '-' . $name . '.' . $ext;

        if ($company->company_banner != NULL) {
            $oldimage = '../' . COMPANY_BANNER_URL . $company->company_banner;  // old image path
            file_exists($company_banner) ? unlink($oldimage) : NULL;  // delete old image
        }


        if ($company_banner) {
            // upload if user change profile picture..

            move_uploaded_file($_FILES['company_banner']['tmp_name'], '../' . COMPANY_BANNER_URL . $company_banner);

            $company->company_banner = $company_banner;
        }
    }


    $company->save();
    if (!$company->save()) {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Error! Something went wrong. please try again later.';
        $response['data'] = [];
    } else {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Successfully Updated.';
        $response['data'] = [
            'company_id' => $id,
            'account_id' => $company->account_id,
            'company_name' => $company->company_name,
            'company_image' => $company->company_image != NULL ? COMPANY_PIC_PATH . $company->company_image : NULL,
            'company_banner' => $company->company_banner != NULL ? COMPANY_BANNER_PATH . $company->company_banner : NULL,
            'address' => $company->address,
            'website' => $company->website,
            'emailid' => $company->emailid,
            'contact_number' => $company->contact_number,
            'about' => $company->about,
            'is_active' => $company->is_active,
            'player_id' => $company->player_id,
        ];
        $response['data']['services'] = $services;
    }
    echoResponse(200, $response);
});

$app->post('/company/profile/:id', function($id) use ($app) {

    $company = Company::find($id);


    $company_name = empty($app->request->post('company_name')) ? $company->company_name : $app->request->post('company_name');
    $website = empty($app->request->post('website')) ? $company->website : $app->request->post('website');
    $address = empty($app->request->post('address')) ? $company->address : $app->request->post('address');
    $emailid = empty($app->request->post('emailid')) ? $company->emailid : $app->request->post('emailid');
    $contact_number = empty($app->request->post('contact_number')) ? $company->contact_number : $app->request->post('contact_number');
    $about = empty($app->request->post('about')) ? $company->about : $app->request->post('about');

$exist = Company::find_by_sql("SELECT * FROM tbl_companies where company_id != {$id} AND emailid = '{$emailid}'");
$services = CompanyService::find_by_sql("select service_id from tbl_company_services where `company_id` = {$id}");

                foreach ($services as $service) {
                    $service = Service::find($service->service_id);
                    $companyServices[] = array(
                        'id' => $service->service_id,
                        'name' => $service->service_name
                    );
                }
    if (count($exist) > 0) {

        $response['error_code'] = 1;
        $response['status'] = true;
        $response['message'] = 'Email already exist.';
        $response['data'] = [];
        echoResponse(200, $response);

        $app->stop();
    } else {
        $company->company_name = $company_name;
        $company->website = $website;
        $company->address = $address;
        $company->emailid = $emailid;
        $company->contact_number = $contact_number;
        $company->about = $about;
        $company->save();
    }

    if (!$company->save()) {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Error! Something went wrong. please try again later.';
        $response['data'] = [];
    } else {
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Successfully updated.';
        $response['data'] = [
            'company_id' => $id,
            'account_id' => $company->account_id,
            'company_name' => $company->company_name,
            'company_image' => $company->company_image != NULL ? COMPANY_PIC_PATH . $company->company_image : NULL,
            'website' => $company->website,
            'address' => $company->address,
            'emailid' => $company->emailid,
            'contact_number' => $company->contact_number,
            'about' => $company->about,
            'is_active' => $company->is_active,
            'player_id' => $company->player_id,
        ];
	
                $response['data']['services'] = $companyServices;
    }

    echoResponse(200, $response);
});



//company logout

$app->put('/companylogout/:id', function($id) use ($app) {
    try {
        $company = Company::find($id);
        $company->player_id = NULL;
        $company->save();
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
 *  Recover password page
 */
$app->get('/company/recover-password/:id/:token', function($id, $token) use ($app) {

    $company = Company::find('all', array('conditions' => "company_id = {$id} AND token = '{$token}' AND  DATE(token_time) <= DATE( DATE_SUB( NOW() , INTERVAL 1 DAY )  ) < DATE(token_time)"));
    if ($company) {
        
        $app->render('companyresetpassword.php', array('path' => "companyrecoverpassword/{$id}"));
    } else {
        $app->render('companyresetpassword.php', array('message' => 'You not able to reset password , your token expired please try again!'));
    }
})->name('companyrecover-password');

/*
 *  Company forgot password
 */
$app->post('/company/forgotpassword', function() use ($app) {
    verifyFields(array('emailid'));
    $emailid = $app->request->post('emailid');

    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;

    $company = Company::find_by_emailid($emailid);
    
    if (empty($company)) {
         $response['error_code'] = 1;
        $response['status'] = false;
        $response["message"] = "Sorry, this email does not exists!";
        echoResponse(200, $response);
        $app->stop();
    } else {
        $token = genRndDgt(8, false);
        $company->token = $token;
        $company->token_time = date('Y-m-d H:m:i');
        $company->save();
        $url = $app->urlFor('companyrecover-password', array('id' => $company->id, 'token' => $company->token));

        $username = $company->company_name;
        $emailid = $company->emailid;
        sendMail($username, $emailid, $url);
        
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'New password sent to your mailid!';
        
    }
    
    echoResponse(200, $response);
});



// Recover company password
$app->post('/companyrecoverpassword/:id', function($id) use ($app) {

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
        // $client = Client::find($client_id);
        $company = Company::find($id);
        if ($cpassword == $newPassword && $company) {
            $company->password = sha1(md5($newPassword) . $company->salt);
            $company->token = NULL;
            $company->save();
            $company->company_id = (int) $company->company_id;
            if ($company->company_id > 0) {
                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Your password has been updated successfully!';
                echo '<span style="color:green;">Your password has been updated successfully!<span>';
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
}); 

/*
 * Change compnay password
 */
$app->post('/companyresetpassword', function() use ($app) {
    verifyFields(array('company_id', 'current_password', 'new_password'));
    
    $company_id = $app->request->post('company_id');
    
    $currentPassword = $app->request->post('current_password');
    $newPassword = $app->request->post('new_password');

    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;

    try {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Please provide correct current password.';
        $company = Company::find($company_id);
       
        if ($company && $company->password == sha1(md5($currentPassword) . $company->salt)) {
            $company->password = sha1(md5($newPassword) . $company->salt);
            $company->save();
            $company->company_id = (int) $company->company_id;
            if ($company->company_id > 0) {
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

/*
 * Client / Company delete
 */

$app->get('/:companytid/:clientid/delete', function($companyid, $clientid) use ($app) {

$client = Client::find(array('conditions' => "client_id = {$clientid}"));
if($client){
$client->status=0;
$client->save();
        $response['error_code'] = 0;
        $response['message'] = 'Client status is set to inactive.';
        $response['status'] = true;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No Client found';
        $response['status'] = false;
    }
    echoResponse(200, $response);
});

/*
 *  Company account id and status change
 */

$app->post('/compnay/account', function() use ($app) {
    verifyFields(array('company_id', 'account_id'));

    $company_id = $app->request->post('company_id');
    $account_id = $app->request->post('account_id');

    $company = Company::find($company_id);


    if ($company) {

        $company->account_id = $account_id;
        $company->is_active = TRUE;
        $company->save();

        $response['error_code'] = 0;
        $response['message'] = 'Your account now activated!';
        $response['status'] = true;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No Company found';
        $response['status'] = false;
    }
    echoResponse(200, $response);
});




