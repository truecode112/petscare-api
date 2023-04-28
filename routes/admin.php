<?php

$app->get('/admin', function() use ($app) {
    $app->render('index.php', ['page' => 'dashboard']);
});

/*
 * post 
 * 
 */
$app->post('/admin', function() use ($app) {
    $user = authenticattion($app);
    if ($user != 'please login') {

        $_SESSION['user'] = $user;
    } else {
        $_SESSION['msg'] = 'Please enter valid username and passworrd';
    }
	
    $app->render('index.php', ['page' => 'dashboard']);
});

/*
 * Compnay Listing all
 */

$app->get('/companies', function() use ($app) {
    
    $response['error_code'] = 1;
    $response['message'] = 'No Company found';
    $response['status'] = false;
    
    $companies = Company::all();

    foreach ($companies as $key => &$company) {
        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$company->company_id}");
        $companyServices = array();
        foreach ($services as $companyService) {
            $companyServices[] = array(
                'id' => $companyService->service_id,
                'name' => $companyService->service_name
            );
        }
        $contract = Contract::find(array('conditions' => "company_id = {$company->company_id}"));
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
        );
        $companies[$key]['services'] = $companyServices;
        
    }
    
    if (count($companies) > 0) {
        $response['error_code'] = 0;
        $response['message'] = 'Company list retrived successfully.';
        $response['status'] = true;
        $response['data'] = array_values($companies);
    }

    $app->render('index.php', ['page' => 'company']);
    
})->name("company");

/*
 * Compnay Listing all
 */

$app->get('/company/:id/details', function($id) use ($app) {
    
    $response['error_code'] = 1;
    $response['message'] = 'No Company found';
    $response['status'] = false;


    $companies = Company::find('all', array('conditions' => "company_id = {$id} "));


    foreach ($companies as $key => &$company) {
        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$company->company_id}");
        $companyServices = array();
        foreach ($services as $companyService) {
            $companyServices[] = array(
                'id' => $companyService->service_id,
                'name' => $companyService->service_name
            );
        }

        $appointment = Appointment::find('all', array('conditions' => "company_id = {$company->company_id} order by(date)"));
		$clientData=[];
        $appointmentData = [];
        if (count($appointment) > 0) {
				
            foreach ($appointment as $key2 => $value) {
				$flag = $value->client->company_id != NULL ? TRUE : FALSE;
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
					'isManualClient' => $flag,
                );
				 $clientData[] = $ownerDetail;
                $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
                $serviceName = empty($service) ? NULL : $service->service_name;

                $appointmentData[] = array(
                    'appointment_id' => $value->appointment_id,
                    //'company_detail' => $companyDetail,
                    'owner_detail' => $ownerDetail,
                    'service_id' => $value->service_id,
                    'service_name' => $serviceName,
                    'date' => $value->date,
                    'visits' => $value->visits,
                    'visit_hours' => $value->visit_hours,
                    'price' => $value->price,
                    'status' => $value->status,
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
                    'created_at' => $value->created_at,
                );
            }
        }
        $contract = Contract::find(array('conditions' => "company_id = {$company->company_id}"));
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
            'is_active' => $company->is_active,
            'status' => empty($contract) ? NULL : $contract->status,
        );

        $companies[$key]['services'] = $companyServices;
        $companies[$key]['appointmnets'] = $appointmentData;
		$clientUnique = [];
       	$companies[$key]['clients'] = array_values(clientsdata($id));
        
    }
    if (count($companies) > 0) {
        $response['error_code'] = 0;
        $response['message'] = 'Company list retrived successfully.';
        $response['status'] = true;
        $response['data'] = array_values($companies);
    }
   
    $app->render('index.php', ['page' => 'companydetail', 'companies' => $companies]);
    
});



/*
 * Client listing for compnay side
 */
$app->get('/clients', function() use ($app) {


    $client = Client::all();

    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;


    $clientData = [];
    if ($client) {
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Client list retrive successfully!';
        foreach ($client as $key => $value) {
            $clientData[] = array(
                'client_id' => $value->client_id,
                'firstname' => $value->firstname,
                'lastname' => $value->lastname,
                'profile_image' => $value->profile_image != NULL ? USER_PIC_URL_PATH . $value->profile_image : NULL,
                'emailid' => $value->emailid,
                'client_address' => $value->client_address,
                'contact_number' => $value->contact_number,
                'client_notes' => $value->client_notes,
                'player_id' => $value->player_id,
                
            );
        }
        $response['data'] = $clientData;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No client found.';
        $response['status'] = false;
        $response['data'] = [];
    }
    
    $app->render('index.php', ['page' => 'client']);
    
})->name("clients");

/*
 * Client details
 */
$app->get('/clients/:id/details', function($id) use ($app) {


    $client = Client::find('all', array('conditions' => "client_id = {$id}"));

    $response['error_code'] = 1;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['status'] = false;

    $clientData = [];
    if ($client) {
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Client list retrive successfully!';
        foreach ($client as $key => $value) {


            $appointment = Appointment::find('all', array('conditions' => "client_id = {$id} order by(date)"));
            
            $appointmentData = [];
            $pet_detail = [];
            if (count($appointment) > 0) {

                foreach ($appointment as $key => $avalue) {
                    
                    $companyDetail = array(
                        'company_id' => $avalue->company->company_id,
                        'account_id' => $avalue->company->account_id,
                        'company_name' => $avalue->company->company_name,
                        'emailid' => $avalue->company->emailid,
                        'contact_number' => $avalue->company->contact_number,
                        'company_image' => $avalue->company->company_image != NULL ? COMPANY_PIC_PATH . $avalue->company->company_image : NULL,
                        'website' => $avalue->company->website,
                        'address' => $avalue->company->address,
                        'about' => $avalue->company->about,
                    );

                    $service = Service::find(array('conditions' => "service_id = {$avalue->service_id}"));
                    $serviceName = empty($service) ? NULL : $service->service_name;

                    $appointmentData[] = array(
                        'appointment_id' => $avalue->appointment_id,
                        'company_detail' => $companyDetail,
                        'service_id' => $avalue->service_id,
                        'service_name' => $serviceName,
                        'date' => $avalue->date,
                        'created_at' => $avalue->created_at,
                        'visits' => $avalue->visits,
                        'visit_hours' => $avalue->visit_hours,
                        'price' => $avalue->price,
                        'status' => $avalue->status,
                        'message' => $avalue->message,
                    );
                }
            }
            if(!empty($avalue->client_id)){   
            $pet = Pet::find('all', array('conditions' => "client_id = {$avalue->client_id}"));
            }else{
            $pet = Pet::find('all', array('conditions' => "client_id = {$id}"));
            }
            if ($pet) {

                foreach ($pet as $p => $pets) {

                    $pet_detail[] = array(
                        'pet_id' => $pets->pet_id,
                        'pet_name' => $pets->pet_name,
                        'pet_image' => $pets->pet_image != NULL ? PET_PIC_PATH . $pets->pet_image : NULL,
                        'pet_age' => $pets->age,
                        'medical_detail' => $pets->medical_detail,
                        'pet_notes' => $pets->pet_notes,
                        'latitude' => $pets->latitude,
                        'longitude' => $pets->longitude,
                    );
                }
            }
            $clientData[$key] = array(
                'client_id' => $value->client_id,
                'firstname' => $value->firstname,
                'lastname' => $value->lastname,
                'profile_image' => $value->profile_image != NULL ? USER_PIC_URL_PATH . $value->profile_image : NULL,
                'emailid' => $value->emailid,
                'client_address' => $value->client_address,
                'contact_number' => $value->contact_number,
                'client_notes' => $value->client_notes,
                'player_id' => $value->player_id,
                 
            );
        }
        $clientData[$key]['pet_detail'] = $pet_detail;
        $clientData[$key]['appointments'] = $appointmentData;
        $response['data'] = $clientData;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No client found.';
        $response['status'] = false;
        $response['data'] = [];
    }
    
    $app->render('index.php', ['page' => 'companydetail', 'clientData' => $clientData]);
    
});

/*
 * delete Client / Company from contract 
 */

$app->get('/:clientid/delete/client', function($clientid) use ($app) {

    $client = Client::find(array('conditions' => "client_id = {$clientid}"));

    if ($client) {
        $client->delete();
        $response['error_code'] = 0;
        $response['message'] = 'Client deleted successfully!';
        $response['status'] = true;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No Client found';
        $response['status'] = false;
    }

   
     $app->redirect($app->urlFor('clients'),200);
});

/*
 * delete Company from contract 
 */

$app->get('/:companytid/delete/company', function($companyid) use ($app) {

    $company = Company::find($companyid);

    if ($company) {
        $company->delete();
        $response['error_code'] = 0;
        $response['message'] = 'Company deleted successfully!';
        $response['status'] = true;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No Compnay found';
        $response['status'] = false;
    }
   
	$app->redirect($app->urlFor('company'),200);
    
});

/*
 * logout
 */
$app->get('/logout', function() use($app) {

    unset($_SESSION);
    session_destroy();
    $app->redirect('admin');
});



