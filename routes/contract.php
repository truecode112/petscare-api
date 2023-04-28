<?php

$app->post('/contractrequest', function() use($app) {


    verifyFields(array('company_id', 'client_id'));


    $company_id = $app->request->post('company_id');
    $client_id = $app->request->post('client_id');
    
    $contract = Contract::find(array('company_id' => $company_id, 'client_id' => $client_id));
	 $petCheck = Pet::find(array('conditions' => "client_id = {$client_id}")); 

    if ($contract && $contract->status == 'pending') {
        $response['error_code'] = 0;
        $response['message'] = 'Contract already exist';
        $response['status'] = true;
        echoResponse(200, $response);
        $app->stop();
    }else if (!$contract && count($petCheck) == 0) {
        $response['error_code'] = 2;
        $response['message'] = 'Add pet to send contract request!';
        $response['status'] = true;
        echoResponse(200, $response);
        $app->stop();
    } else if ($contract && $contract->status != 'pending') {
        $contract->status = 'pending';
		$contract->created_at = date('Y-m-d h:i:s');
        $contract->save();
    } else if (!$contract) {
        $contract = new Contract();
        $contract->company_id = $company_id;
        $contract->client_id = $client_id;
        $contract->status = 'pending';
		$contract->created_at = date('Y-m-d h:i:s');
        $contract->save();
    }


    $contract->contract_id = (int) $contract->contract_id;
    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';


    if ($contract->contract_id > 0) {
        $response['error_code'] = 0;
        $response['message'] = 'Contract request sent';
        $response['status'] = true;
        $response['data'] = array(
            'contract_id' => $contract->contract_id,
            'client_id' => $contract->client->client_id,
            'firstname' => $contract->client->firstname,
            'lastname' => $contract->client->lastname,
            'emailid' => $contract->client->emailid,
            'client_address' => $contract->client->client_address,
            'contact_number' => $contract->client->contact_number,
            'client_notes' => $contract->client->client_notes,
            'profile_image' => $contract->client->profile_image ? USER_PIC_URL_PATH . $contract->client->profile_image : NULL,
            'company_image' => $contract->company->company_image ? COMPANY_PIC_PATH . $contract->company->company_image : NULL,
            'company_id' => $contract->company->company_id,
            'company_name' => $contract->company->company_name,
            'status' => $contract->status,
            'notification_flag' => 'contract_request'
        );
        $username = $contract->client->firstname.' '.$contract->client->lastname;
        
        $notification = array('message' =>"New contract request from ".$username,
            'player_ids' => array($contract->company->player_id),
            'data' => $response['data'],
        );
        sendMessage($notification);


    }

    echoResponse(200, $response);
});


/* 
 * Contract status update
 */
$app->post('/contractstatusupdate', function() use($app) {

    verifyFields(array('contract_id', 'status'));

    $contract_id = $app->request->post('contract_id');
    $status = $app->request->post('status');


    $contract = Contract::find($contract_id);
    $contract->status = $status;
    $contract->save();

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';

    

    if ($contract) {


        if($status == 'accepted')
            {
               $company_id=$contract->company_id;
               $client_id=$contract->client_id;

              $service=CompanyService::find_by_sql("select * from tbl_company_services where company_id=$company_id");
        
        if($service)
            {
                
                foreach ($service as $key1 => $value1) 
                {
                    $service_id=$value1->service_id;
               
                        $checkPrice = Price::find(array("conditions" => "company_id=$company_id AND client_id is null AND service_id=$service_id"));

                        $full_hour_price = $checkPrice->full_hour_price;
                        $half_hour_price = $checkPrice->half_hour_price;
                        $additional_hours_price = $checkPrice->additional_hours_price;
                        $additional_visits_price = $checkPrice->additional_visits_price;
                        $price_per_walk = $checkPrice->price_per_walk;
                        $additional_pets = $checkPrice->additional_pets;
                        $payment_option = $checkPrice->payment_option;
                        $p_flag = $checkPrice->p_flag;



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
                        $price->save();
                        $price->price_id = (int) $price->price_id;

            }

        }
}

        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$contract->company->company_id}");
        $companyServices = array();
        foreach ($services as $companyService) {
            $companyServices[] = array(
                'id' => $companyService->service_id,
                'name' => $companyService->service_name
            );
        }
        $response['error_code'] = 0;
        $response['message'] = 'Contract request ' . $status;
        $response['status'] = true;
        $response['data'] = array(
            'contract_id' => $contract->contract_id,
            'client_id' => $contract->client->client_id,
            'firstname' => $contract->client->firstname,
            'lastname' => $contract->client->lastname,
            'profile_image' => $contract->client->profile_image ? USER_PIC_URL_PATH . $contract->client->profile_image : NULL,
            'company_image' => $contract->company->company_image ? COMPANY_PIC_PATH . $contract->company->company_image : NULL,
            'id' => $contract->company->company_id,
            'name' => $contract->company->company_name,
            'website' => $contract->company->website,
            'emailid' => $contract->company->emailid,
            'contact_number' => $contract->company->contact_number,
            'address' => $contract->company->address,
            'about' => $contract->company->about,
            'status' => $contract->status,
            'services' => $companyServices,
            'status' => $status,
            'notification_flag' => "contract_request_status"
        );


        $notification = array('message' => '"' . $contract->company->company_name . '" ' . $status . ' your contract request',
            'player_ids' => array($contract->client->player_id),
            'data' => $response['data'],
        );

        sendMessage($notification);
    }

    echoResponse(200, $response);
});

$app->delete('/deletecontract', function() use ($app) {
    Contract::delete_all();
     $response['error_code'] = 0;
        $response['message'] = 'All Contract deleted ';
        $response['status'] = true;
     echoResponse(200, $response);
});
