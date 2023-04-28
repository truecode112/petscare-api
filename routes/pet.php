<?php

//Company Registration
$app->post('/petsinsert', function() use ($app) {
    verifyFields(array('client_id', 'pet_name', 'pet_birth', 'age', 'medical_detail', 'pet_notes', 'latitude', 'longitude'));

    $client_id = $app->request->post('client_id');
    $pet_name = $app->request->post('pet_name');
    $pet_birth = $app->request->post('pet_birth');

    $age = $app->request->post('age');
    $medical_detail = $app->request->post('medical_detail');
    $notes = $app->request->post('pet_notes');
    $latitude = $app->request->post('latitude');
    $longitude = $app->request->post('longitude');

    $pet_image = NULL;
    
    if (isset($_FILES['pet_image'])) {
        $path_parts = pathinfo($_FILES['pet_image']['name']);
        $ext = $path_parts['extension'];
        $name = cleanUsername($pet_name);
        $pet_image = time() . '-' . $name . '.' . $ext;
    }


    Pet::transaction(function() use($app, $client_id, $pet_name, $pet_birth, $pet_image, $age, $medical_detail, $notes, $latitude, $longitude) {
        $pet = new Pet();
        $pet->client_id = $client_id;
        $pet->pet_name = $pet_name;
        $pet->pet_birth = $pet_birth;

        $pet->pet_image = $pet_image;
        $pet->age = $age;
        $pet->medical_detail = $medical_detail;
        $pet->pet_notes = $notes;
        $pet->latitude = $latitude;
        $pet->longitude = $longitude;
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
            $response['message'] = 'Successfully Registered.';

            $petData = $pet->to_array();

            $response['data'] = $petData;

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
});


$app->get('/:clientid/petslist', function($id) use ($app) {

    $response['error_code'] = 1;
    $response['message'] = 'No Pets found';
    $response['status'] = false;

    $pet = Pet::find_by_sql("SELECT * FROM `tbl_pets` where client_id = {$id}");

    if(count($pet)>0){
        
    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Pet list retrive successfully.';


    $petData = [];
    foreach ($pet as $value) {
        $petData[] = array(
            'pet_id' => $value->pet_id,
            'client_id' => $value->client_id,
            'pet_name' => $value->pet_name,
            'pet_birth' => $value->pet_birth,

            'pet_image' => $value->pet_image != NULL ? PET_PIC_PATH . $value->pet_image : NULL,
            'age' => $value->age,
            'medical_detail' => $value->medical_detail,
            'pet_notes' => $value->pet_notes,
            'latitude' => $value->latitude,
            'longitude' => $value->longitude,
        );
    }
    $response['data'] = $petData;
    }

    echoResponse(200, $response);
});

/*
 * Pet list company side
 */

$app->get('/company/:id/pets', function($id) use ($app) {

    $response['error_code'] = 1;
    $response['message'] = 'No Pets found';
    $response['status'] = false;


 $contract = Contract::find('all', array("conditions" => "company_id = {$id}"));
 
	if (count($contract) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Pet list retrive successfully.';
        foreach ($contract as $k => $c) {
            $pet = Pet::find('all', array("conditions" => "client_id = {$c->client_id} order by pet_id"));
            $client = Client::find($c->client_id);
            foreach ($pet as $key => $value) {
                $petData[] = array(
                    'pet_id' => $value->pet_id,
                    'client_id' => $value->client_id,
                    'client_firstname' => $client->firstname,
                    'client_lastname' => $client->lastname,
                    'pet_name' => $value->pet_name,
                    'pet_birth' => $value->pet_birth,

                    'pet_image' => $value->pet_image != NULL ? PET_PIC_PATH . $value->pet_image : NULL,
                    'age' => $value->age,
                    'medical_detail' => $value->medical_detail,
                    'pet_notes' => $value->pet_notes,
                    'latitude' => $value->latitude,
                    'longitude' => $value->longitude,
                );
            }
        }
    }
   
	$response['data'] =  array_values(array_unique($petData, SORT_REGULAR));
    echoResponse(200, $response);
});


$app->post('/petupdate/:id', function($id) use ($app) {
    verifyFields(array('client_id'));
    $client_id = $app->request->post('client_id');
    $pet = Pet::find(array('pet_id' => $id, 'client_id' => $client_id));
	
	if(!$pet){
		$response['error_code'] = 1;
        $response['message'] = 'Pet not found!';
        $response['status'] = false;
		$app->stop();
	}

    
    $pet_name = empty($app->request->post('pet_name')) ? $pet->pet_name : $app->request->post('pet_name');
    $pet_birth = empty($app->request->post('pet_birth')) ? $pet->pet_birth : $app->request->post('pet_birth');

    $age = empty($app->request->post('age')) ? $pet->age : $app->request->post('age');
    $medical_detail = empty($app->request->post('medical_detail')) ? $pet->medical_detail : $app->request->post('medical_detail');
    $notes = empty($app->request->post('pet_notes')) ? $pet->pet_notes : $app->request->post('pet_notes');
    $latitude = empty($app->request->post('latitude')) ? $pet->latitude : $app->request->post('latitude');
    $longitude = empty($app->request->post('longitude')) ? $pet->longitude : $app->request->post('longitude');

    $pet_image = $app->request->post('pet_image') ?  $app->request->post('pet_image') : $pet->pet_image;
    
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

    $response['error_code'] = 1;
    $response['message'] = 'No pet found';
    $response['status'] = false;
    $response['data'] = [];



    $pet->client_id = $client_id;
    $pet->pet_name = $pet_name;
    $pet->pet_birth = $pet_birth;

    $pet->age = $age;
    $pet->medical_detail = $medical_detail;
    $pet->pet_notes = $notes;
    $pet->latitude = $latitude;
    $pet->longitude = $longitude;
    $up = $pet->save();

    if ($up) {
        $response['error_code'] = 0;
        $response['message'] = 'Successfully updated.';
        $response['status'] = true;
        
        $response['data'] = array(
            'pet_id' => $pet->pet_id,
            'client_id' => $client_id,
            'pet_name' => $pet_name,
            'pet_birth' => $pet_birth,

            'pet_image' => PET_PIC_PATH . $pet->pet_image,
            'pet_age' => $age,
            'medical_detail' => $medical_detail,
            'pet_notes' => $notes,
            'latitude' => $latitude,
            'longitude' => $longitude,
        );
    }

    echoResponse(200, $response);
});

$app->get('/pet/:id/:clientid/delete', function($id, $clientid) use ($app) {

    $pet_exist = Pet::exists(array('pet_id' => $id, 'client_id' => $clientid));
    
    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'You are not able to delete pets.';

    if ($pet_exist) {
        $pet = Pet::find($id);
        $pet->delete();
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Pet deleted successfully';
    }
    echoResponse(200, $response);
});


