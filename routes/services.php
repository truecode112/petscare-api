<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
 *  Service listing
 */
$app->get('/services', function() use ($app) {
    $response['error_code'] = 1;
    $response['message'] = 'No services found';
    $response['status'] = false;

    $services = Service::find_by_sql('SELECT * FROM tbl_services');
    foreach ($services as $key => &$service) {
        $services[$key] = array(
            'id' => $service->service_id,
            'name' => $service->service_name
        );
    }

    if (count($services) > 0) {
        $response['error_code'] = 0;
        $response['message'] = 'services List';
        $response['status'] = true;
        $response['data'] = $services;
    }
    echoResponse(200, $response);
});

// /*
// * temporary api to fill old data to new table
// */

// $app->get('/:id/retrivecredit', function($id) use ($app)
// {

//     $company_id=$id;
//     $c_id=[];
//     $check = Credits::find_by_sql("SELECT client_id from tbl_credits WHERE company_id=$company_id");
//     foreach ($check as $value1) {
//         $c_id[]=$value1->client_id;
//     }
//     $temp1=[];
// $temp1=array_unique($c_id);

// foreach ($temp1 as $val) {


//     $credit = Credits::find('all',array("conditions"=>"company_id=$company_id AND client_id=$val"));

//     $totalcredit=0.0;
//     $totalpaid=0;
//     $totalold=0;
//     $totalremaining=0;
//    foreach ($credit as $value) {

//     $totalcredit +=$value->credits;
//     $totalpaid +=$value->paid_amount;
//     $totalold +=$value->old_amount;
//     $totalremaining +=$value->remaining;
//     $last_check = $value->last_check;
//     $f_flag = $value->flag;
//     $r_flag = $value->r_flag;

//    }

//    $creditnew = Credits1::find(array("conditions" => "company_id=$company_id AND client_id=$val"));


//    if(count($creditnew)>0)
//    {
//         $creditnew->credits=$totalcredit;
//         $creditnew->paid_amount=$totalpaid;
//         $creditnew->old_amount=$totalold;
//         $creditnew->remaining=$totalremaining;
//         $creditnew->last_check=date('Y-m-d',strtotime($last_check));
//         $creditnew->f_flag=$f_flag;
//         $creditnew->r_flag=$r_flag;
//         $creditnew->save();

//             $response['error_code'] = 0;
//             $response['status'] = true;
//             $response['message'] = 'New credits update successfully .';

//    }else{

//             $credit1= new Credits1();
//             $credit1->company_id=$company_id;
//             $credit1->client_id=$val;
//             $credit1->credits=$totalcredit;
//             $credit1->paid_amount=$totalpaid;
//             $credit1->old_amount=$totalold;
//             $credit1->remaining=$totalremaining;
//             $credit1->last_check=date('Y-m-d',strtotime($last_check));
//             $credit1->f_flag=$f_flag;
//             $credit1->r_flag=$r_flag;
//             $credit1->save();
//             $credit1->creditnew_id=(int)$credit1->creditnew_id;

//             $response['error_code'] = 0;
//             $response['status'] = true;
//             $response['message'] = 'New credits add successfully .';


//    }
// }

//     echoResponse(200, $response);
// });


/*
 * Add appointment
 */
$app->post('/appointmentpast', function() use ($app) {


    verifyFields(array('company_id', 'service_id', 'client_id', 'date', 'visits', 'visit_hours', 'pet_id'));    // checking fields are empty or not

    $company_id = $app->request->post('company_id');
    $service_id = $app->request->post('service_id');
    $stringdate = strtotime($app->request->post('date'));
    $date = date('Y-m-d', $stringdate);
    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');

    $pet_id = $app->request->post('pet_id');
    $message = $app->request->post('message');
    if(!isset($message)){
        $message = '';
    }
    $client_id = (int) $app->request->post('client_id');
    $client = Client::find($client_id);
    $client_name = $client->firstname . ' ' . $client->lastname;
    $rf_company_id = $client->company_id;

    $clientCheck = Client::find($client_id);
    $flag = $clientCheck->company_id != NULL ?  true : NULL;


    $appoint_check=Appointment::find_by_sql("SELECT * from `tbl_appointments` where company_id=$company_id AND client_id=$client_id and service_id=$service_id and pet_id=$pet_id and date='$date'");

    if($appoint_check != NULL)
    {

        $response['error_code'] = 2;
        $response['status'] = false;
        $response['message'] = 'Appointment for this date is already exists.';
        echoResponse(200, $response);
    }
    else{

        Appointment::transaction(function() use($app, $company_id, $service_id, $client_name, $client_id, $date, $visits, $visit_hours, $pet_id, $message,$rf_company_id,$flag) {

            $price = Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id}"));

            $whole_visit= floor($visits);
            $whole = floor($visit_hours);
            $fraction = $visit_hours - $whole; // getting part after decimal point
            /*
         * Price calculating
         */
            if ($fraction)
            {

                if($whole>1 && $whole_visit > 1)
                {

                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                }
                else if($whole > 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }
                else if($whole < 1 && $whole_visit == 1)
                {

                    $total = $price->half_hour_price;

                }else if($whole < 1 && $whole_visit > 1)
                {
                    $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }

            } else {

                if($whole>1 && $whole_visit>1)
                {
                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                }
                else if($whole>1 && $whole_visit == 1)
                {
                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                }
                else if($whole ==1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                }
                else
                {
                    $total = $price->full_hour_price;

                }
            }



            /*
         *  Stroing to table
         */

            $appointment = new Appointment();
            $appointment->company_id = $company_id;
            $appointment->client_id = $client_id;
            $appointment->service_id = $service_id;
            $appointment->date = $date;
            $appointment->visits = $visits;
            $appointment->visit_hours = $visit_hours;
            $appointment->price = $total;
            if($flag == true){
                $appointment->status = 'assign staff';
            }else{
                $appointment->status = 'pending';
            }
            $appointment->pet_id = $pet_id;
            $appointment->message = $message;
            if (empty($rf_company_id)) {
                $appointment->created_by = 'client';
            } else {
                $appointment->created_by = 'company';
            }
            $appointment->created_at = date('Y-m-d H:i:s');
            $appointment->save();
            $appointment->appointment_id = (int) $appointment->appointment_id;


            $nn=(int) $appointment->appointment_id;


            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Error! Something went wrong. please try again later.';
            $response['data'] = [];


            $appointment1 = Appointment::find('all', array('conditions' => "appointment_id = {$appointment->appointment_id}"));
            $today=date('Y-m-d');
            $color='';
            foreach ($appointment1 as $k => $v) {

                $app_date=date('Y-m-d',strtotime($v->date));

                if ($app_date > $today)
                {
                    $color="Yellow";
                } elseif ($app_date == $today)
                {
                    $color="Green";
                }
            }

            $service = Service::find($service_id);
            $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;

            if ($appointment->appointment_id > 0) {

                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Appointment Successfully booked.';
                $response['data'] = array(
                    'appointment_id' => $appointment->appointment_id,
                    'compnay_id' => $appointment->company_id,
                    'client_id' => $client_id,
                    'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                    'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                    'service_id' => $appointment->service_id,
                    'service' => $service->service_name,
                    'pet_id' => $appointment->pet_id,
                    'pet_name' => $appointment->pet->pet_name,
                    'pet_birth' => $appointment->pet->pet_birth,

                    'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                    'date' => $date,
                    'visits' => $visits,
                    'visit_hours' => $visit_hours,
                    'status' => $flag!= NULL ? 'assign staff' : $appointment->status,
                    'traffic_light' =>$color,
                    'message' => $message,
                    'notification_flag' => 'appointment_booking'
                );


                $playerid = $appointment->company->player_id != NULL?$appointment->company->player_id:NULL;

                $notification = array('message' => $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on ' .  date('d-m-Y',strtotime($date)),
                    'player_ids' => array($playerid),
                    'data' => $response['data'],
                );

                if($flag == false){
                    sendMessage($notification);
                }
            }

            echoResponse(200, $response);


            $creditCheck = Credits1::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));


            if (count($creditCheck)>0)

            {
                foreach ($creditCheck as  $valu)
                {
                    $last_date=$valu->last_check;
                    $paid_amt=$valu->paid_amount;
                    $remains=$valu->remaining;
                }

                $last_check=date('Y-m-d',strtotime($last_date));




                $ab=date('Y-m-d');



                $appoint=Appointment::find_by_sql("select price from tbl_appointments where appointment_id=$nn");
                if($appoint>0)
                {

                    foreach ($appoint as  $value1)
                    {

                        $total=(float)$paid_amt;


                        $remaining=$remains;


                        $remaining-=(float)$value1->price;

                        if($remaining == 0)
                        {

                            $creditCheck4 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));


                            //$creditCheck4->check_date=date('Y-m-d H:i:s');
                            $creditCheck4->paid_amount=0;
                            $creditCheck4->credits=0;

                            $creditCheck4->save();

                            $credit =0;
                            $used =0;
                            $total =0;

                        }

                        //  $check_amt = $credit - $walks;
                        /*For log status dynamically*/
                        $status='';

                        if($remaining <= 0)
                        {
                            $status = 'Completed';
                        }else{
                            $status = 'Active';
                        }
                        /*end*/

                        $used=(float)$total - $remaining;

                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new Log();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->l_status = $status;
                        $log->amount = $value1->price;
                        $log->l_flag = "Deducted";
                        $log->save();
                        $log->log_id = (int) $log->log_id;


                    }
                    $creditCheck1 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

                    if(count($creditCheck1) >0)
                    {

                        $creditCheck1->remaining=$remaining;
                        $creditCheck1->save();

                    }
                }


            }
        });
    }
});

/*
 * Add appointment
 */
$app->post('/appointmentpastmulti', function() use ($app) {
    verifyFields(array('company_id', 'service_id', 'client_id', 'date', 'visits', 'visit_hours', 'pet_id'));    // checking fields are empty or not

    $company_id = $app->request->post('company_id');

    $service_id = $app->request->post('service_id');
    $date = $app->request->post('date');
    //$date = date('Y-m-d',strtotime($stringdate));

    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');
    //$price = $app->request->post('price');
    //$status = $app->request->post('status');
    $pet_id = $app->request->post('pet_id');
    $message = $app->request->post('message');
    if(!isset($message)){
        $message = '';
    }
    $client_id = (int) $app->request->post('client_id');
    $client = Client::find($client_id);
    $client_name = $client->firstname . ' ' . $client->lastname;
    $rf_company_id = $client->company_id;

    $clientCheck = Client::find($client_id);
    $flag = $clientCheck->company_id != NULL ?  true : NULL;


    foreach ($date as $key => $value) {
        // Appointment::transaction(function() use($app, $company_id, $service_id, $client_name, $client_id, $date, $visits, $visit_hours, $pet_id, $message,$rf_company_id,$flag) {
        //$i=0;
//$temp_arr=[];
        $appoint_check=Appointment::find_by_sql("SELECT * from `tbl_appointments` where company_id=$company_id AND client_id=$client_id and service_id=$service_id and pet_id=$pet_id and date='$value'");

        if($appoint_check != NULL)
        {


            //die;

            $response['error_code'] = 2;
            $response['status'] = false;
            $response['message'] = 'Appointment for this date is already exists.';
            //$response['data']=[];




        }
        else
        {

            $credit = Credits::find_by_sql("SELECT * from tbl_newcredits where company_id=$company_id AND client_id=$client_id AND service_id=$service_id AND pet_id=$pet_id");

            if($credit == null)
            {

                //crditnw table if null dev st 00 values to it


                $cservice=CompanyService::find('all',array("conditions" => "company_id={$company_id}"));

                foreach ($cservice as $key => $value)
                {
                    $c=new Credits();
                    $c->company_id = $company_id;
                    $c->client_id = $client_id;
                    $c->pet_id = $pet_id;
                    $c->service_id = $value->service_id;
                    $c->paid_amount = 0;
                    $c->old_amount = 0;
                    $c->last_check = date('Y-m-d');
                    $c->date_of_payment =NULL;
                    $c->remaining = 0;
                    $c->flag=0;
                    $c->r_flag=0;
                    $c->save();
                    $c->credit_id = (int)$c->credit_id;
                }

            }

            $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
            if($price == NULL)
            {
                $response['error_code'] = 2;
                $response['status'] = false;
                $response['message'] = 'Set price for this pet to book an appointment.';
            }elseif($price->full_hour_price == 0 && $price->half_hour_price == 0){
                $response['error_code'] = 2;
                $response['status'] = false;
                $response['message'] = 'Set price for this pet to book an appointment.';
            }else{


                $whole_visit=$visits;
                $whole = floor($visit_hours);      // whole number from
                $fraction = $visit_hours - $whole; // getting part after decimal point

                /*
         * Price calculating
         */
                if ($fraction)
                {

                    if($whole>1 && $whole_visit > 1)
                    {

                        $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                    }
                    else if($whole > 1 && $whole_visit == 1)
                    {

                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                    }
                    else if($whole == 1 && $whole_visit == 1)
                    {

                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                    }
                    else if($whole == 1 && $whole_visit>1)
                    {
                        $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                    }
                    else if($whole < 1 && $whole_visit == 1)
                    {

                        $total = $price->half_hour_price;

                    }else if($whole < 1 && $whole_visit > 1)
                    {
                        $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                    }

                } else {

                    if($whole>1 && $whole_visit>1)
                    {
                        $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                    }
                    else if($whole>1 && $whole_visit == 1)
                    {
                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                    }
                    else if($whole ==1 && $whole_visit>1)
                    {
                        $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                    }
                    else
                    {
                        $total = $price->full_hour_price;

                    }
                }


                /*
                     *  Stroing to table
                     */

                $appointment = new Appointment();
                $appointment->company_id = $company_id;
                $appointment->client_id = $client_id;
                $appointment->service_id = $service_id;
                $appointment->date = $value;
                $appointment->visits = $visits;
                $appointment->visit_hours = $visit_hours;
                $appointment->price = $total;
                if($flag == true){
                    $appointment->status = 'assign staff';
                }else{
                    $appointment->status = 'pending';
                }
                $appointment->pet_id = $pet_id;
                $appointment->message = $message;
                if (empty($rf_company_id)) {
                    $appointment->created_by = 'client';
                } else {
                    $appointment->created_by = 'company';
                }
                $appointment->created_at = date('Y-m-d H:i:s');
                $appointment->save();
                $appointment->appointment_id = (int) $appointment->appointment_id;


                $nn[]=(int) $appointment->appointment_id;


                // echo $total;
                // var_dump($price);
                //die;




                $appointment1 = Appointment::find('all', array('conditions' => "appointment_id = {$appointment->appointment_id}"));
                $today=date('Y-m-d');
                $color='';
                foreach ($appointment1 as $k => $v)
                {

                    $app_date=date('Y-m-d',strtotime($v->date));

                    if ($app_date > $today)
                    {
                        $color="Yellow";
                    } elseif ($app_date == $today)
                    {
                        $color="Green";
                    }
                }



                $service = Service::find($service_id);
                $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;

                $appointmentData=array(
                    'appointment_id' => $appointment->appointment_id,
                    'compnay_id' => $appointment->company_id,
                    'client_id' => $client_id,
                    'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                    'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                    'service_id' => $appointment->service_id,
                    //'service_name' => $service->service_name,
                    'service' => $service->service_name,
                    'pet_id' => $appointment->pet_id,
                    'pet_name' => $appointment->pet->pet_name,
                    'pet_birth' => $appointment->pet->pet_birth,

                    'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                    'date' => $value,
                    'visits' => $visits,
                    'visit_hours' => $visit_hours,
                    //  'price' => $appointment->price,
                    //'status' => $appointment->status,
                    'status' => $flag!= NULL ? 'assign staff' : $appointment->status,
                    'traffic_light' =>$color,
                    'message' => $message,
                    'notification_flag' => 'appointment_booking'
                );

                // print_r($appointmentData);die;
                if ($appointment->appointment_id > 0)
                {

                    $response['error_code'] = 0;
                    $response['status'] = true;
                    $response['message'] = 'Appointment Successfully booked.';
                    $response['data'] = $appointmentData;
                }

                /*credit calculation*/

                //remove
                // $response['message'] = 'test';

                $ab=date('Y-m-d');
                $today = strtotime($ab);
                $old = strtotime($value);
//remove
                // $response['message'] = 'ab= '.$ab.' $value= '.$value;

                if($old <= $today)
                {

                    $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND service_id={$service_id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

                    if (count($creditCheck)>0)

                    {
                        //remove
                        // $response['message'] = 'count($creditCheck)>0';
                        foreach ($creditCheck as  $valu)
                        {
                            $last_date=$valu->last_check;
                            //$credits2=(float)$valu->credits;
                            $paid_amt=$valu->paid_amount;
                            $remains=$valu->remaining;
                        }


                        $last_check=date('Y-m-d',strtotime($last_date));



                        // $appointm=Appointment::find_by_sql("select appointment_id as aid from tbl_appointments where date='$value'");

                        // foreach ($appointm as $va)
                        // {
                        //     $check[]=$va->aid;
                        // }


                        // $a_result=array_intersect($nn, $check);
                        //remove
                        //  $response['message'] = '$a_result';

                        // foreach ($a_result as $ar) {


                            $aa=Appointment::find('all',array("conditions" => "appointment_id=$appointment->appointment_id"));

                            if(count($aa)>0)
                            {

                                //remove
                                // $response['message'] = 'count($aa)>0';
                                foreach ($aa as  $value1)
                                {

                                    // $total=(float)$paid_amt;
                                    $remaining=$remains;
                                    $ddd=$value1->price;
                                    $remaining-=(float)$value1->price;


                                    if($remaining == 0)
                                    {

                                        $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND service_id={$service_id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

                                        $creditCheck4->paid_amount=0;
                                        $creditCheck4->save();

                                    }


                                    $pet=Pet::find($pet_id);
                                    $pet_name = $pet->pet_name;
                                    $log = new transactionlog();
                                    $log->company_id = $company_id;
                                    $log->client_id = $client_id;
                                    $log->pet_id = $pet_id;
                                    $log->service_id = $service_id;
                                    $log->pet_name = $pet_name;
                                    // $log->date_of_transaction = date('Y-m-d H:i:s');
                                    $log->date_of_transaction = date('Y-m-d H:i:s', strtotime($value));

                                    $log->type = "Charge";
                                    $log->amount = $value1->price;
                                    $log->l_flag = "Deducted";
                                    $log->old_value = $remains;
                                    $log->new_value = $remaining;
                                    $log->save();
                                    $log->log_id = (int) $log->log_id;



                                }

                                /// no service id from old dev so im naven add it
                                ///  Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

                                $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND service_id={$service_id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

                                if(count($creditCheck1) >0)
                                {

                                    // $creditCheck1->last_check=$value;
                                    $creditCheck1->remaining=$remaining;
                                    $creditCheck1->save();

                                }



                            }else{
                                //$total=(float)$paid_amt;
                                $remaining=(float)$remains;
                                // $used=(float)$total - $remaining;
                            }
                        // }

                    }
                }else{
                    //remove
                    //remove
                    //$response['message'] = 'ab= '.$ab.' $value= '.$value;
                }
            }
        }
    }
    echoResponse(200, $response);

});

$app->post('/appointmentpastnew', function() use ($app) {


    verifyFields(array('company_id', 'service_id', 'client_id', 'date', 'visits', 'visit_hours', 'pet_id'));    // checking fields are empty or not

    $company_id = $app->request->post('company_id');
    $service_id = $app->request->post('service_id');
    $stringdate = strtotime($app->request->post('date'));
    $date = date('Y-m-d', $stringdate);
    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');

    $pet_id = $app->request->post('pet_id');
    $message = $app->request->post('message');
    if(!isset($message)){
        $message = '';
    }
    $client_id = (int) $app->request->post('client_id');
    $client = Client::find($client_id);
    $client_name = $client->firstname . ' ' . $client->lastname;
    $rf_company_id = $client->company_id;

    $clientCheck = Client::find($client_id);
    $flag = $clientCheck->company_id != NULL ?  true : NULL;


    $appoint_check=Appointment::find_by_sql("SELECT * from `tbl_appointments` where company_id=$company_id AND client_id=$client_id and service_id=$service_id and pet_id=$pet_id and date='$date'");

    if($appoint_check != NULL)
    {

        $response['error_code'] = 2;
        $response['status'] = false;
        $response['message'] = 'Appointment for this date is already exists.';
        echoResponse(200, $response);
    }
    else{

        Appointment::transaction(function() use($app, $company_id, $service_id, $client_name, $client_id, $date, $visits, $visit_hours, $pet_id, $message,$rf_company_id,$flag) {



            $credit = Credits::find_by_sql("SELECT * from tbl_newcredits where company_id=$company_id AND client_id=$client_id AND service_id=$service_id AND pet_id=$pet_id");

            if($credit == null)
            {

                $cservice=CompanyService::find('all',array("conditions" => "company_id={$company_id}"));

                foreach ($cservice as $key => $value)
                {
                    $c=new Credits();
                    $c->company_id = $company_id;
                    $c->client_id = $client_id;
                    $c->pet_id = $pet_id;
                    $c->service_id = $value->service_id;
                    $c->paid_amount = 0;
                    $c->old_amount = 0;
                    $c->last_check = date('Y-m-d');
                    $c->date_of_payment =NULL;
                    $c->remaining = 0;
                    $c->flag=0;
                    $c->r_flag=0;
                    $c->save();
                    $c->credit_id = (int)$c->credit_id;
                }
            }

            $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));

            $whole_visit= floor($visits);
            $whole = floor($visit_hours);
            $fraction = $visit_hours - $whole; // getting part after decimal point
            $total = 0;
            /*
         * Price calculating
         */
            if(isset($price->full_hour_price)){
                if ($fraction)
                {

                    if($whole>1 && $whole_visit > 1)
                    {

                        $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                    }
                    else if($whole > 1 && $whole_visit == 1)
                    {

                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                    }
                    else if($whole == 1 && $whole_visit == 1)
                    {

                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                    }
                    else if($whole == 1 && $whole_visit>1)
                    {
                        // die(print_r($price));
                        $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                    }
                    else if($whole < 1 && $whole_visit == 1)
                    {

                        $total = $price->half_hour_price;

                    }else if($whole < 1 && $whole_visit > 1)
                    {
                        $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                    }

                } else {

                    if($whole>1 && $whole_visit>1)
                    {
                        $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                    }
                    else if($whole>1 && $whole_visit == 1)
                    {
                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                    }
                    else if($whole ==1 && $whole_visit>1)
                    {
                        $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                    }
                    else
                    {
                        $total = $price->full_hour_price;

                    }
                }
            }
            

            /*
         *  Stroing to table
         */

            $appointment = new Appointment();
            $appointment->company_id = $company_id;
            $appointment->client_id = $client_id;
            $appointment->service_id = $service_id;
            $appointment->date = $date;
            $appointment->visits = $visits;
            $appointment->visit_hours = $visit_hours;
            $appointment->price = $total;
            if($flag == true){
                $appointment->status = 'assign staff';
            }else{
                $appointment->status = 'pending';
            }
            $appointment->pet_id = $pet_id;
            $appointment->message = $message;
            if (empty($rf_company_id)) {
                $appointment->created_by = 'client';
            } else {
                $appointment->created_by = 'company';
            }
            $appointment->created_at = date('Y-m-d H:i:s');
            $appointment->save();
            $appointment->appointment_id = (int) $appointment->appointment_id;


            $nn=(int) $appointment->appointment_id;


            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Error! Something went wrong. please try again later.';
            $response['data'] = [];


            $appointment1 = Appointment::find('all', array('conditions' => "appointment_id = {$appointment->appointment_id}"));
            $today=date('Y-m-d');
            $color='';
            foreach ($appointment1 as $k => $v) {

                $app_date=date('Y-m-d',strtotime($v->date));

                if ($app_date > $today)
                {
                    $color="Yellow";
                } elseif ($app_date == $today)
                {
                    $color="Green";
                }
            }

            $service = Service::find($service_id);
            $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;

            if ($appointment->appointment_id > 0) {

                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Appointment Successfully booked.';
                $response['data'] = array(
                    'appointment_id' => $appointment->appointment_id,
                    'compnay_id' => $appointment->company_id,
                    'client_id' => $client_id,
                    'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                    'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                    'service_id' => $appointment->service_id,
                    'service' => $service->service_name,
                    'pet_id' => $appointment->pet_id,
                    'pet_name' => $appointment->pet->pet_name,
                    'pet_birth' => $appointment->pet->pet_birth,

                    'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                    'date' => $date,
                    'visits' => $visits,
                    'visit_hours' => $visit_hours,
                    'status' => $flag!= NULL ? 'assign staff' : $appointment->status,
                    'traffic_light' =>$color,
                    'message' => $message,
                    'notification_flag' => 'appointment_booking'
                );


                // $playerid = $appointment->company->player_id != NULL?$appointment->company->player_id:NULL;

                //     $notification = array('message' => $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on ' .  date('d-m-Y',strtotime($date)),
                //         'player_ids' => array($playerid),
                //         'data' => $response['data'],
                //     );

                //     if($flag == false){
                //         sendMessage($notification);
                //     }
            }
            echoResponse(200, $response);

//        $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));

//  $ab=date('Y-m-d');
// foreach ($aa as $val)
// {
//      if($val->service_id>0)
//      {
//         $service_id1=$val->service_id;
            $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} and service_id={$service_id}"));


            if (count($creditCheck)>0)

            {
                foreach ($creditCheck as  $valu)
                {
                    $last_date=$valu->last_check;
                    $paid_amt=$valu->paid_amount;
                    $remains=$valu->remaining;
                }

                $last_check=date('Y-m-d',strtotime($last_date));




                $ab=date('Y-m-d');



                $appoint=Appointment::find_by_sql("select price, date from tbl_appointments where appointment_id=$nn");
                if($appoint>0)
                {

                    foreach ($appoint as  $value1)
                    {

                        //$total=(float)$paid_amt;


                        $remaining=$remains;


                        $remaining-=(float)$value1->price;

                        if($remaining == 0)
                        {

                            $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));


                            //$creditCheck4->check_date=date('Y-m-d H:i:s');
                            $creditCheck4->paid_amount=0;
                            //$creditCheck4->credits=0;

                            $creditCheck4->save();

                            // $credit =0;
                            // $used =0;
                            // $total =0;

                        }

                        //  $check_amt = $credit - $walks;
                        /*For log status dynamically*/
                        // $status='';

                        // if($remaining <= 0)
                        //     {
                        //          $status = 'Completed';
                        //     }else{
                        //          $status = 'Active';
                        //     }
                        /*end*/

                        //$used=(float)$total - $remaining;

                        $pet=Pet::find($pet_id);
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s',strtotime($value1->date));
                        $log->type = "Charge";
                        $log->amount = $value1->price;
                        $log->l_flag = "Deducted";

                        $log->old_value = $remains;
                        $log->new_value = $remaining;


                        $log->save();
                        $log->log_id = (int) $log->log_id;

                        // $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        // $pet_name = $pet->pet_name;
                        // $log = new Log();
                        // $log->company_id = $company_id;
                        // $log->client_id = $client_id;
                        // $log->pet_name = $pet_name;
                        // $log->date_of_transaction = date('Y-m-d H:i:s');
                        // $log->l_status = $status;
                        // $log->amount = $value1->price;
                        // $log->l_flag = "Deducted";
                        // $log->save();
                        // $log->log_id = (int) $log->log_id;


                    }
                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

                    if(count($creditCheck1) >0)
                    {

                        $creditCheck1->remaining=$remaining;
                        $creditCheck1->save();

                    }
                }


            }
        });
    }



});


/*
 * Add multiappointment
 */
$app->post('/multiappointment', function() use ($app) {


    verifyFields(array('company_id', 'service_id', 'client_id', 'date', 'visits', 'visit_hours', 'pet_id'));    // checking fields are empty or not

    $company_id = $app->request->post('company_id');

    $service_id = $app->request->post('service_id');
    $date = $app->request->post('date');

    //$date = date('Y-m-d',strtotime($stringdate));

    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');
    //$price = $app->request->post('price');
    //$status = $app->request->post('status');
    $pet_id = $app->request->post('pet_id');
    $message = $app->request->post('message');
    if(!isset($message)){
        $message = '';
    }
    $client_id = (int) $app->request->post('client_id');
    $client = Client::find($client_id);
    $client_name = $client->firstname . ' ' . $client->lastname;
    $rf_company_id = $client->company_id;

    $clientCheck = Client::find($client_id);
    $flag = $clientCheck->company_id != NULL ?  true : NULL;


    foreach ($date as $key => $value) {
        // Appointment::transaction(function() use($app, $company_id, $service_id, $client_name, $client_id, $date, $visits, $visit_hours, $pet_id, $message,$rf_company_id,$flag) {
        //$i=0;
//$temp_arr=[];
        $appoint_check=Appointment::find_by_sql("SELECT * from `tbl_appointments` where company_id=$company_id AND client_id=$client_id and service_id=$service_id and pet_id=$pet_id and date='$value'");

        if($appoint_check != NULL)
        {


            //die;

            $response['error_code'] = 2;
            $response['status'] = false;
            $response['message'] = 'Appointment for this date is already exists.';
            //$response['data']=[];




        }
        else
        {

            $price = Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id}"));
            $whole_visit=$visits;
            $whole = floor($visit_hours);      // whole number from
            $fraction = $visit_hours - $whole; // getting part after decimal point

            /*
         * Price calculating
         */
            if ($fraction)
            {

                if($whole>1 && $whole_visit > 1)
                {

                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                }
                else if($whole > 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }
                else if($whole < 1 && $whole_visit == 1)
                {

                    $total = $price->half_hour_price;

                }else if($whole < 1 && $whole_visit > 1)
                {
                    $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }

            } else {

                if($whole>1 && $whole_visit>1)
                {
                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                }
                else if($whole>1 && $whole_visit == 1)
                {
                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                }
                else if($whole ==1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                }
                else
                {
                    $total = $price->full_hour_price;

                }
            }


            /*
			         *  Stroing to table
			         */

            $appointment = new Appointment();
            $appointment->company_id = $company_id;
            $appointment->client_id = $client_id;
            $appointment->service_id = $service_id;
            $appointment->date = $value;
            $appointment->visits = $visits;
            $appointment->visit_hours = $visit_hours;
            $appointment->price = $total;
            if($flag == true){
                $appointment->status = 'assign staff';
            }else{
                $appointment->status = 'pending';
            }
            $appointment->pet_id = $pet_id;
            $appointment->message = $message;
            if (empty($rf_company_id)) {
                $appointment->created_by = 'client';
            } else {
                $appointment->created_by = 'company';
            }
            $appointment->created_at = date('Y-m-d H:i:s');
            $appointment->save();
            $appointment->appointment_id = (int) $appointment->appointment_id;


            $nn[]=(int) $appointment->appointment_id;


            // echo $total;
            // var_dump($price);
            //die;




            $appointment1 = Appointment::find('all', array('conditions' => "appointment_id = {$appointment->appointment_id}"));
            $today=date('Y-m-d');
            $color='';
            foreach ($appointment1 as $k => $v)
            {

                $app_date=date('Y-m-d',strtotime($v->date));

                if ($app_date > $today)
                {
                    $color="Yellow";
                } elseif ($app_date == $today)
                {
                    $color="Green";
                }
            }



            $service = Service::find($service_id);
            $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;

            $appointmentData=array(
                'appointment_id' => $appointment->appointment_id,
                'compnay_id' => $appointment->company_id,
                'client_id' => $client_id,
                'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                'service_id' => $appointment->service_id,
                //'service_name' => $service->service_name,
                'service' => $service->service_name,
                'pet_id' => $appointment->pet_id,
                'pet_name' => $appointment->pet->pet_name,
                'pet_birth' => $appointment->pet->pet_birth,

                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                'date' => $value,
                'visits' => $visits,
                'visit_hours' => $visit_hours,
                //  'price' => $appointment->price,
                //'status' => $appointment->status,
                'status' => $flag!= NULL ? 'assign staff' : $appointment->status,
                'traffic_light' =>$color,
                'message' => $message,
                'notification_flag' => 'appointment_booking'
            );

            // print_r($appointmentData);die;
            if ($appointment->appointment_id > 0)
            {

                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Appointment Successfully booked.';
                $response['data'] = $appointmentData;
            }


            // $playerid = $appointment->company->player_id != NULL?$appointment->company->player_id:NULL;

            //     $notification = array('message' => $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on ' .  date('d-m-Y',strtotime($value)),
            //         'player_ids' => array($playerid),
            //         'data' => $response['data'],
            //     );

            //    print_r($notification);
            //   die;
            //     if($flag == false){
            //         sendMessage($notification);
            //     }
            // }

            //echoResponse(200, $response);


            $ab=date('Y-m-d');

            if($value == $ab)
            {

                $creditCheck = Credits1::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));


                if (count($creditCheck)>0)

                {
                    foreach ($creditCheck as  $valu)
                    {
                        $last_date=$valu->last_check;
                        //$credits2=(float)$valu->credits;
                        $paid_amt=$valu->paid_amount;
                        $remains=$valu->remaining;
                    }

                    $last_check=date('Y-m-d',strtotime($last_date));



                    $appointm=Appointment::find_by_sql("select appointment_id as aid from tbl_appointments where date='$ab'");

                    foreach ($appointm as $va)
                    {
                        $check[]=$va->aid;
                    }


                    $a_result=array_intersect($nn, $check);

                    foreach ($a_result as $ar) {



                        $aa=Appointment::find('all',array("conditions" => "appointment_id=$ar"));

                        if(count($aa)>0)
                        {
                            foreach ($aa as  $value1)
                            {

                                $total=(float)$paid_amt;
                                $remaining=$remains;


                                $remaining-=(float)$value1->price;

                                if($remaining == 0)
                                {

                                    $creditCheck4 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));


                                    //$creditCheck4->check_date=date('Y-m-d H:i:s');
                                    $creditCheck4->paid_amount=0;
                                    $creditCheck4->credits=0;

                                    $creditCheck4->save();

                                    // $credit =0;
                                    $used =0;
                                    $total =0;

                                }

                                /*For log status dynamically*/
                                $status='';

                                if($remaining <= 0)
                                {
                                    $status = 'Completed';
                                }else{
                                    $status = 'Active';
                                }
                                /*end*/

                                $used=(float)$total - $remaining;

                                $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                                $pet_name = $pet->pet_name;
                                $log = new Log();
                                $log->company_id = $company_id;
                                $log->client_id = $client_id;
                                $log->pet_name = $pet_name;
                                $log->date_of_transaction = date('Y-m-d H:i:s');
                                $log->l_status = $status;
                                $log->amount = $value1->price;
                                $log->l_flag = "Deducted";
                                $log->save();
                                $log->log_id = (int) $log->log_id;

                            }



                            $creditCheck1 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

                            if(count($creditCheck1) >0)
                            {

                                //$creditCheck1->last_check=$ab;
                                $creditCheck1->remaining=$remaining;
                                $creditCheck1->save();

                            }



                        }else{
                            $total=(float)$paid_amt;
                            $remaining=(float)$remains;
                            $used=(float)$total - $remaining;
                        }
                    }

                }
            }

        }
    }
    echoResponse(200, $response);



});


/*
 * Add multiappointment
 */
$app->post('/multiappointmentnew', function() use ($app) {


    verifyFields(array('company_id', 'service_id', 'client_id', 'date', 'visits', 'visit_hours', 'pet_id'));    // checking fields are empty or not

    $company_id = $app->request->post('company_id');

    $service_id = $app->request->post('service_id');
    $date = $app->request->post('date');

    //$date = date('Y-m-d',strtotime($stringdate));

    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');
    //$price = $app->request->post('price');
    //$status = $app->request->post('status');
    $pet_id = $app->request->post('pet_id');
    $message = $app->request->post('message');
    if(!isset($message)){
        $message = '';
    }
    $client_id = (int) $app->request->post('client_id');
    $client = Client::find($client_id);
    $client_name = $client->firstname . ' ' . $client->lastname;
    $rf_company_id = $client->company_id;

    $clientCheck = Client::find($client_id);
    $flag = $clientCheck->company_id != NULL ?  true : NULL;


    foreach ($date as $key => $value) {
        // Appointment::transaction(function() use($app, $company_id, $service_id, $client_name, $client_id, $date, $visits, $visit_hours, $pet_id, $message,$rf_company_id,$flag) {
        //$i=0;
//$temp_arr=[];
        $appoint_check=Appointment::find_by_sql("SELECT * from `tbl_appointments` where company_id=$company_id AND client_id=$client_id and service_id=$service_id and pet_id=$pet_id and date='$value'");

        if($appoint_check != NULL)
        {


            //die;

            $response['error_code'] = 2;
            $response['status'] = false;
            $response['message'] = 'Appointment for this date is already exists.';
            //$response['data']=[];




        }
        else
        {

            $credit = Credits::find_by_sql("SELECT * from tbl_newcredits where company_id=$company_id AND client_id=$client_id AND service_id=$service_id AND pet_id=$pet_id");

            if($credit == null)
            {

                //crditnw table if null dev st 00 values to it


                $cservice=CompanyService::find('all',array("conditions" => "company_id={$company_id}"));

                foreach ($cservice as $key => $value)
                {
                    $c=new Credits();
                    $c->company_id = $company_id;
                    $c->client_id = $client_id;
                    $c->pet_id = $pet_id;
                    $c->service_id = $value->service_id;
                    $c->paid_amount = 0;
                    $c->old_amount = 0;
                    $c->last_check = date('Y-m-d');
                    $c->date_of_payment =NULL;
                    $c->remaining = 0;
                    $c->flag=0;
                    $c->r_flag=0;
                    $c->save();
                    $c->credit_id = (int)$c->credit_id;
                }

            }

            $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
            if($price == NULL)
            {
                $response['error_code'] = 2;
                $response['status'] = false;
                $response['message'] = 'Set price for this pet to book an appointment.';
            }elseif($price->full_hour_price == 0 && $price->half_hour_price == 0){
                $response['error_code'] = 2;
                $response['status'] = false;
                $response['message'] = 'Set price for this pet to book an appointment.';
            }else{


                $whole_visit=$visits;
                $whole = floor($visit_hours);      // whole number from
                $fraction = $visit_hours - $whole; // getting part after decimal point

                /*
         * Price calculating
         */
                if ($fraction)
                {

                    if($whole>1 && $whole_visit > 1)
                    {

                        $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                    }
                    else if($whole > 1 && $whole_visit == 1)
                    {

                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                    }
                    else if($whole == 1 && $whole_visit == 1)
                    {

                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                    }
                    else if($whole == 1 && $whole_visit>1)
                    {
                        $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                    }
                    else if($whole < 1 && $whole_visit == 1)
                    {

                        $total = $price->half_hour_price;

                    }else if($whole < 1 && $whole_visit > 1)
                    {
                        $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                    }

                } else {

                    if($whole>1 && $whole_visit>1)
                    {
                        $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                    }
                    else if($whole>1 && $whole_visit == 1)
                    {
                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                    }
                    else if($whole ==1 && $whole_visit>1)
                    {
                        $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                    }
                    else
                    {
                        $total = $price->full_hour_price;

                    }
                }


                /*
			         *  Stroing to table
			         */

                $appointment = new Appointment();
                $appointment->company_id = $company_id;
                $appointment->client_id = $client_id;
                $appointment->service_id = $service_id;
                $appointment->date = $value;
                $appointment->visits = $visits;
                $appointment->visit_hours = $visit_hours;
                $appointment->price = $total;
                if($flag == true){
                    $appointment->status = 'assign staff';
                }else{
                    $appointment->status = 'pending';
                }
                $appointment->pet_id = $pet_id;
                $appointment->message = $message;
                if (empty($rf_company_id)) {
                    $appointment->created_by = 'client';
                } else {
                    $appointment->created_by = 'company';
                }
                $appointment->created_at = date('Y-m-d H:i:s');
                $appointment->save();
                $appointment->appointment_id = (int) $appointment->appointment_id;


                $nn[]=(int) $appointment->appointment_id;


                // echo $total;
                // var_dump($price);
                //die;




                $appointment1 = Appointment::find('all', array('conditions' => "appointment_id = {$appointment->appointment_id}"));
                $today=date('Y-m-d');
                $color='';
                foreach ($appointment1 as $k => $v)
                {

                    $app_date=date('Y-m-d',strtotime($v->date));

                    if ($app_date > $today)
                    {
                        $color="Yellow";
                    } elseif ($app_date == $today)
                    {
                        $color="Green";
                    }
                }



                $service = Service::find($service_id);
                $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;

                $appointmentData=array(
                    'appointment_id' => $appointment->appointment_id,
                    'compnay_id' => $appointment->company_id,
                    'client_id' => $client_id,
                    'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                    'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                    'service_id' => $appointment->service_id,
                    //'service_name' => $service->service_name,
                    'service' => $service->service_name,
                    'pet_id' => $appointment->pet_id,
                    'pet_name' => $appointment->pet->pet_name,
                    'pet_birth' => $appointment->pet->pet_birth,

                    'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                    'date' => $value,
                    'visits' => $visits,
                    'visit_hours' => $visit_hours,
                    //  'price' => $appointment->price,
                    //'status' => $appointment->status,
                    'status' => $flag!= NULL ? 'assign staff' : $appointment->status,
                    'traffic_light' =>$color,
                    'message' => $message,
                    'notification_flag' => 'appointment_booking'
                );

                // print_r($appointmentData);die;
                if ($appointment->appointment_id > 0)
                {

                    $response['error_code'] = 0;
                    $response['status'] = true;
                    $response['message'] = 'Appointment Successfully booked.';
                    $response['data'] = $appointmentData;
                }

                /*credit calculation*/

                //remove
                // $response['message'] = 'test';

                $ab=date('Y-m-d');

//remove
                // $response['message'] = 'ab= '.$ab.' $value= '.$value;

                if($value == $ab)
                {

                    $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND service_id={$service_id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

                    if (count($creditCheck)>0)

                    {
                        //remove
                        // $response['message'] = 'count($creditCheck)>0';
                        foreach ($creditCheck as  $valu)
                        {
                            $last_date=$valu->last_check;
                            //$credits2=(float)$valu->credits;
                            $paid_amt=$valu->paid_amount;
                            $remains=$valu->remaining;
                        }


                        $last_check=date('Y-m-d',strtotime($last_date));



                        $appointm=Appointment::find_by_sql("select appointment_id as aid from tbl_appointments where date='$value'");

                        foreach ($appointm as $va)
                        {
                            $check[]=$va->aid;
                        }


                        $a_result=array_intersect($nn, $check);
                        //remove
                        //  $response['message'] = '$a_result';

                        foreach ($a_result as $ar) {


                            $aa=Appointment::find('all',array("conditions" => "appointment_id=$ar"));

                            if(count($aa)>0)
                            {

                                //remove
                                // $response['message'] = 'count($aa)>0';
                                foreach ($aa as  $value1)
                                {

                                    // $total=(float)$paid_amt;
                                    $remaining=$remains;
                                    $ddd=$value1->price;
                                    $remaining-=(float)$value1->price;


                                    if($remaining == 0)
                                    {

                                        $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND service_id={$service_id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

                                        $creditCheck4->paid_amount=0;
                                        $creditCheck4->save();

                                    }


                                    $pet=Pet::find($pet_id);
                                    $pet_name = $pet->pet_name;
                                    $log = new transactionlog();
                                    $log->company_id = $company_id;
                                    $log->client_id = $client_id;
                                    $log->pet_id = $pet_id;
                                    $log->service_id = $service_id;
                                    $log->pet_name = $pet_name;
                                    $log->date_of_transaction = date('Y-m-d H:i:s');
                                    // $log->date_of_transaction = date('Y-m-d H:i:s', strtotime($value));

                                    $log->type = "Charge";
                                    $log->amount = $value1->price;
                                    $log->l_flag = "Deducted";
                                    $log->old_value = $remains;
                                    $log->new_value = $remaining;
                                    $log->save();
                                    $log->log_id = (int) $log->log_id;



                                }

                                /// no service id from old dev so im naven add it
                                ///  Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

                                $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND service_id={$service_id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

                                if(count($creditCheck1) >0)
                                {

                                    $creditCheck1->last_check=$value;
                                    $creditCheck1->remaining=$remaining;
                                    $creditCheck1->save();

                                }



                            }else{
                                //$total=(float)$paid_amt;
                                $remaining=(float)$remains;
                                // $used=(float)$total - $remaining;
                            }
                        }

                    }
                }else{
                    //remove
                    //remove
                    //$response['message'] = 'ab= '.$ab.' $value= '.$value;
                }
            }
        }
    }
    echoResponse(200, $response);



});

/*
 * Add appointment
 */
$app->post('/appointmentnew', function() use ($app) {


    verifyFields(array('company_id', 'service_id', 'client_id', 'date', 'visits', 'visit_hours', 'pet_id'));    // checking fields are empty or not

    $company_id = $app->request->post('company_id');
    $service_id = $app->request->post('service_id');
    $stringdate = strtotime($app->request->post('date'));
    $date = date('Y-m-d', $stringdate);
    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');

    $pet_id = $app->request->post('pet_id');
    $message = $app->request->post('message');
    if(!isset($message)){
        $message = '';
    }
    $client_id = (int) $app->request->post('client_id');
    $client = Client::find($client_id);
    $client_name = $client->firstname . ' ' . $client->lastname;
    $rf_company_id = $client->company_id;

    $clientCheck = Client::find($client_id);
    $flag = $clientCheck->company_id != NULL ?  true : NULL;


    $appoint_check=Appointment::find_by_sql("SELECT * from `tbl_appointments` where company_id=$company_id AND client_id=$client_id and service_id=$service_id and pet_id=$pet_id and date='$date'");

    if($appoint_check != NULL)
    {

        $response['error_code'] = 2;
        $response['status'] = false;
        $response['message'] = 'Appointment for this date is already exists.';
        echoResponse(200, $response);
    }
    else{

        Appointment::transaction(function() use($app, $company_id, $service_id, $client_name, $client_id, $date, $visits, $visit_hours, $pet_id, $message,$rf_company_id,$flag) {

            $price = Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id}"));

            $whole_visit= floor($visits);
            $whole = floor($visit_hours);
            $fraction = $visit_hours - $whole; // getting part after decimal point
            /*
         * Price calculating
         */
            if ($fraction)
            {

                if($whole>1 && $whole_visit > 1)
                {

                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                }
                else if($whole > 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }
                else if($whole < 1 && $whole_visit == 1)
                {

                    $total = $price->half_hour_price;

                }else if($whole < 1 && $whole_visit > 1)
                {
                    $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }

            } else {

                if($whole>1 && $whole_visit>1)
                {
                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                }
                else if($whole>1 && $whole_visit == 1)
                {
                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                }
                else if($whole ==1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                }
                else
                {
                    $total = $price->full_hour_price;

                }
            }



            /*
         *  Stroing to table
         */

            $appointment = new Appointment();
            $appointment->company_id = $company_id;
            $appointment->client_id = $client_id;
            $appointment->service_id = $service_id;
            $appointment->date = $date;
            $appointment->visits = $visits;
            $appointment->visit_hours = $visit_hours;
            $appointment->price = $total;
            if($flag == true){
                $appointment->status = 'assign staff';
            }else{
                $appointment->status = 'pending';
            }
            $appointment->pet_id = $pet_id;
            $appointment->message = $message;
            if (empty($rf_company_id)) {
                $appointment->created_by = 'client';
            } else {
                $appointment->created_by = 'company';
            }
            $appointment->created_at = date('Y-m-d H:i:s');
            $appointment->save();
            $appointment->appointment_id = (int) $appointment->appointment_id;


            $nn=(int) $appointment->appointment_id;


            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Error! Something went wrong. please try again later.';
            $response['data'] = [];


            $appointment1 = Appointment::find('all', array('conditions' => "appointment_id = {$appointment->appointment_id}"));
            $today=date('Y-m-d');
            $color='';
            foreach ($appointment1 as $k => $v) {

                $app_date=date('Y-m-d',strtotime($v->date));

                if ($app_date > $today)
                {
                    $color="Yellow";
                } elseif ($app_date == $today)
                {
                    $color="Green";
                }
            }

            $service = Service::find($service_id);
            $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;

            if ($appointment->appointment_id > 0) {

                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Appointment Successfully booked.';
                $response['data'] = array(
                    'appointment_id' => $appointment->appointment_id,
                    'compnay_id' => $appointment->company_id,
                    'client_id' => $client_id,
                    'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                    'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                    'service_id' => $appointment->service_id,
                    'service' => $service->service_name,
                    'pet_id' => $appointment->pet_id,
                    'pet_name' => $appointment->pet->pet_name,
                    'pet_birth' => $appointment->pet->pet_birth,

                    'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                    'date' => $date,
                    'visits' => $visits,
                    'visit_hours' => $visit_hours,
                    'status' => $flag!= NULL ? 'assign staff' : $appointment->status,
                    'traffic_light' =>$color,
                    'message' => $message,
                    'notification_flag' => 'appointment_booking'
                );


                $playerid = $appointment->company->player_id != NULL?$appointment->company->player_id:NULL;

                $notification = array('message' => $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on ' .  date('d-m-Y',strtotime($date)),
                    'player_ids' => array($playerid),
                    'data' => $response['data'],
                );

                if($flag == false){
                    sendMessage($notification);
                }
            }

            echoResponse(200, $response);


            // /*Walks calculation start*/
            // $test = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

            // $check_date=date('Y-m-d',strtotime($test->check_date));
            // $cd=date('Y-m-d');

            // $appoint = Appointment::find_by_sql("SELECT visits AS v, visit_hours AS vh FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and (status='accepted' or status='assign staff') and date BETWEEN '$check_date' and '$cd'");

            //                 $walks = 0;
            //                 $additional_walks = 0;
            //                 $extra_visits = 0;

            //      foreach ($appoint as  $value1)
            //             {

            //                 $walks += count($value1) ;
            //                 if($walks == 0.5)
            //                 {
            //                     $additional_walks += 1-1;
            //                 }else{
            //                     $additional_walks += (float)($value1->vh)-1;
            //                 }
            //                 $extra_visits += (float)($value1->v)-1;

            //             }
            //              /*Walks calculation end*/


            $creditCheck = Credits1::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));


            if (count($creditCheck)>0)

            {
                foreach ($creditCheck as  $valu)
                {
                    $last_date=$valu->last_check;
                    $paid_amt=$valu->paid_amount;
                    $remains=$valu->remaining;
                }

                $last_check=date('Y-m-d',strtotime($last_date));


                //     $net=Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$appointment->service_id}"));
                //   if(isset($net))
                //   {
                //                 $act_price=$net->full_hour_price;


                //                 if($act_price == 0)
                //                 {
                //                     $credit=0;
                //                 }
                //                 else{
                //                     $credit=(float)($paid_amt/$act_price);
                //                 }

                // }else{
                //         $response['error_code'] = 1;
                //         $response['message'] = 'you have not set price for this service';
                //         $response['status'] = false;
                //     }

                $ab=date('Y-m-d');

                if($date == $ab && $date == $last_check)
                {

                    $appoint=Appointment::find_by_sql("select price from tbl_appointments where appointment_id=$nn");
                    if($appoint>0)
                    {

                        foreach ($appoint as  $value1)
                        {

                            $total=(float)$paid_amt;

                            // if($act_price == 0)
                            // {
                            //     $credit=0;
                            // }
                            // else{
                            //      $credit=number_format((float)($total/$act_price),1,'.','');
                            // }


                            $remaining=$remains;


                            $remaining-=(float)$value1->price;

                            if($remaining == 0)
                            {

                                $creditCheck4 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));


                                //$creditCheck4->check_date=date('Y-m-d H:i:s');
                                $creditCheck4->paid_amount=0;
                                $creditCheck4->credits=0;

                                $creditCheck4->save();

                                $credit =0;
                                $used =0;
                                $total =0;

                            }

                            //  $check_amt = $credit - $walks;
                            /*For log status dynamically*/
                            $status='';

                            if($remaining <= 0)
                            {
                                $status = 'Completed';
                            }else{
                                $status = 'Active';
                            }
                            /*end*/

                            $used=(float)$total - $remaining;

                            $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                            $pet_name = $pet->pet_name;
                            $log = new Log();
                            $log->company_id = $company_id;
                            $log->client_id = $client_id;
                            $log->pet_name = $pet_name;
                            $log->date_of_transaction = date('Y-m-d H:i:s');
                            $log->l_status = $status;
                            $log->amount = $value1->price;
                            $log->l_flag = "Deducted";
                            $log->save();
                            $log->log_id = (int) $log->log_id;


                        }
                        $creditCheck1 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

                        if(count($creditCheck1) >0)
                        {

                            $creditCheck1->remaining=$remaining;
                            $creditCheck1->save();

                        }
                    }

                }else{
                    $total=(float)$paid_amt;
                    $remaining=(float)$remains;
                    $used=(float)$total - $remaining;
                }



            }
        });
    }
});


/*
 * Add appointment
 */
$app->post('/appointment', function() use ($app) {


    verifyFields(array('company_id', 'service_id', 'client_id', 'date', 'visits', 'visit_hours', 'pet_id'));    // checking fields are empty or not

    $company_id = $app->request->post('company_id');
    $service_id = $app->request->post('service_id');
    $stringdate = strtotime($app->request->post('date'));
    $date = date('Y-m-d', $stringdate);
    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');

    $pet_id = $app->request->post('pet_id');
    $message = $app->request->post('message');
    if(!isset($message)){
        $message = '';
    }
    $client_id = (int) $app->request->post('client_id');
    $client = Client::find($client_id);
    $client_name = $client->firstname . ' ' . $client->lastname;
    $rf_company_id = $client->company_id;

    $clientCheck = Client::find($client_id);
    $flag = $clientCheck->company_id != NULL ?  true : NULL;


    $appoint_check=Appointment::find_by_sql("SELECT * from `tbl_appointments` where company_id=$company_id AND client_id=$client_id and service_id=$service_id and pet_id=$pet_id and date='$date'");

    if($appoint_check != NULL)
    {

        $response['error_code'] = 2;
        $response['status'] = false;
        $response['message'] = 'Appointment for this date is already exists.';
        echoResponse(200, $response);
    }
    else{

        Appointment::transaction(function() use($app, $company_id, $service_id, $client_name, $client_id, $date, $visits, $visit_hours, $pet_id, $message,$rf_company_id,$flag) {

            $credit = Credits::find_by_sql("SELECT * from tbl_newcredits where company_id=$company_id AND client_id=$client_id AND service_id=$service_id AND pet_id=$pet_id");

            if($credit == null)
            {

                $cservice=CompanyService::find('all',array("conditions" => "company_id={$company_id}"));

                foreach ($cservice as $key => $value)
                {
                    $c=new Credits();
                    $c->company_id = $company_id;
                    $c->client_id = $client_id;
                    $c->pet_id = $pet_id;
                    $c->service_id = $value->service_id;
                    $c->paid_amount = 0;
                    $c->old_amount = 0;
                    $c->last_check = date('Y-m-d');
                    $c->date_of_payment =NULL;
                    $c->remaining = 0;
                    $c->flag=0;
                    $c->r_flag=0;
                    $c->save();
                    $c->credit_id = (int)$c->credit_id;
                }

            }
            $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));

            $whole_visit= floor($visits);
            $whole = floor($visit_hours);
            $fraction = $visit_hours - $whole; // getting part after decimal point
            /*
         * Price calculating
         */
            if ($fraction)
            {

                if($whole>1 && $whole_visit > 1)
                {

                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                }
                else if($whole > 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }
                else if($whole < 1 && $whole_visit == 1)
                {

                    $total = $price->half_hour_price;

                }else if($whole < 1 && $whole_visit > 1)
                {
                    $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }

            } else {

                if($whole>1 && $whole_visit>1)
                {
                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                }
                else if($whole>1 && $whole_visit == 1)
                {
                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                }
                else if($whole ==1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                }
                else
                {
                    $total = $price->full_hour_price;

                }
            }



            /*
         *  Stroing to table
         */

            $appointment = new Appointment();
            $appointment->company_id = $company_id;
            $appointment->client_id = $client_id;
            $appointment->service_id = $service_id;
            $appointment->date = $date;
            $appointment->visits = $visits;
            $appointment->visit_hours = $visit_hours;
            $appointment->price = $total;
            if($flag == true){
                $appointment->status = 'assign staff';
            }else{
                $appointment->status = 'pending';
            }
            $appointment->pet_id = $pet_id;
            $appointment->message = $message;
            if (empty($rf_company_id)) {
                $appointment->created_by = 'client';
            } else {
                $appointment->created_by = 'company';
            }
            $appointment->created_at = date('Y-m-d H:i:s');
            $appointment->save();
            $appointment->appointment_id = (int) $appointment->appointment_id;


            $nn=(int) $appointment->appointment_id;


            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'Error! Something went wrong. please try again later.';
            $response['data'] = [];


            $appointment1 = Appointment::find('all', array('conditions' => "appointment_id = {$appointment->appointment_id}"));
            $today=date('Y-m-d');
            $color='';
            foreach ($appointment1 as $k => $v) {

                $app_date=date('Y-m-d',strtotime($v->date));

                if ($app_date > $today)
                {
                    $color="Yellow";
                } elseif ($app_date == $today)
                {
                    $color="Green";
                }
            }

            $service = Service::find($service_id);
            $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;

            if ($appointment->appointment_id > 0) {

                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Appointment Successfully booked.';
                $response['data'] = array(
                    'appointment_id' => $appointment->appointment_id,
                    'compnay_id' => $appointment->company_id,
                    'client_id' => $client_id,
                    'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                    'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                    'service_id' => $appointment->service_id,
                    'service' => $service->service_name,
                    'pet_id' => $appointment->pet_id,
                    'pet_name' => $appointment->pet->pet_name,
                    'pet_birth' => $appointment->pet->pet_birth,

                    'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                    'date' => $date,
                    'visits' => $visits,
                    'visit_hours' => $visit_hours,
                    'status' => $flag!= NULL ? 'assign staff' : $appointment->status,
                    'traffic_light' =>$color,
                    'message' => $message,
                    'notification_flag' => 'appointment_booking'
                );


                $playerid = $appointment->company->player_id != NULL?$appointment->company->player_id:NULL;

                $notification = array('message' => $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on ' .  date('d-m-Y',strtotime($date)),
                    'player_ids' => array($playerid),
                    'data' => $response['data'],
                );

                if($flag == false){
                    sendMessage($notification);
                }
            }

            echoResponse(200, $response);

            $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));

            $ab=date('Y-m-d');
            foreach ($aa as $val)
            {
                if($val->service_id>0)
                {
                    $service_id1=$val->service_id;
                    // /*Walks calculation start*/
                    // $test = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id1}"));

                    // $check_date=date('Y-m-d H:i:s',strtotime($test->check_date));
                    // $cd=date('Y-m-d H:i:s');

                    // $appoint = Appointment::find_by_sql("SELECT visits AS v, visit_hours AS vh FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$check_date' and '$cd'");

                    //                 $walks = 0;
                    //                 $additional_walks = 0;
                    //                 $extra_visits = 0;

                    //      foreach ($appoint as  $value1)
                    //             {

                    //                 $walks += count($value1) ;
                    //                 if($walks == 0.5)
                    //                 {
                    //                     $additional_walks += 1-1;
                    //                 }else{
                    //                     $additional_walks += (float)($value1->vh)-1;
                    //                 }
                    //                 $extra_visits += (float)($value1->v)-1;

                    //             }
                    //              /*Walks calculation end*/


                    $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$appointment->service_id}"));


                    if (count($creditCheck)>0)

                    {
                        foreach ($creditCheck as  $valu) {
                            $last_date=$valu->last_check;
                            $paid_amt=$valu->paid_amount;
                            $remains=$valu->remaining;
                        }

                        $last_check=date('Y-m-d',strtotime($last_date));


                        //       $net=Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id1}"));
                        // if(isset($net)){
                        //   $act_price=$net->full_hour_price;


                        //   if($act_price == 0)
                        //   {
                        //       $credit=0;
                        //   }
                        //   else{
                        //       $credit=(float)($paid_amt/$act_price);
                        //   }

                        //     }
                        //     else{
                        //       $response['error_code'] = 1;
                        //   $response['message'] = 'you have not set price for this service';
                        //   $response['status'] = false;
                        //     }


                        if($service_id1 == $appointment->service_id)
                        {
                            if($date == $ab && $date == $last_check)
                            {

                                $appoint=Appointment::find_by_sql("select price from tbl_appointments where appointment_id=$nn");
                                if($appoint>0)
                                {

                                    foreach ($appoint as  $value1)
                                    {

                                        // $total=(float)$paid_amt;

                                        //     if($act_price == 0)
                                        //     {
                                        //         $credit=0;
                                        //     }
                                        //     else{
                                        //          $credit=number_format((float)($total/$act_price),1,'.','');
                                        //     }


                                        $remaining=$remains;


                                        $remaining-=(float)$value1->price;

                                        if($remaining == 0)
                                        {

                                            $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id1}"));


                                            $creditCheck4->check_date=date('Y-m-d H:i:s');
                                            $creditCheck4->paid_amount=0;
                                            //$creditCheck4->credits=0;

                                            $creditCheck4->save();

                                            //   $credit =0;
                                            // $used =0;
                                            // $total =0;
                                            // $walks =0;
                                            // $additional_walks =0;
                                            // $extra_visits =0;
                                        }

                                        //$check_amt = $credit - $walks;
                                        //      /*For log status dynamically*/
                                        // $status='';

                                        // if($remaining <= 0)
                                        // {
                                        //      $status = 'Completed';
                                        // }else{
                                        //      $status = 'Active';
                                        // }
                                        // /*end*/

                                        // $used=(float)$total - $remaining;

                                        $pet=Pet::find($pet_id);
                                        $pet_name = $pet->pet_name;
                                        $log = new transactionlog();
                                        $log->company_id = $company_id;
                                        $log->client_id = $client_id;
                                        $log->pet_id = $pet_id;
                                        $log->service_id = $service_id;
                                        $log->pet_name = $pet_name;
                                        $log->date_of_transaction = date('Y-m-d H:i:s');
                                        $log->type = "Charge";
                                        $log->amount = $value1->price;
                                        $log->l_flag = "Deducted";
                                        $log->old_value = $remains;
                                        $log->new_value = $remaining;
                                        $log->save();
                                        $log->log_id = (int) $log->log_id;

                                        // $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                                        // $pet_name = $pet->pet_name;
                                        // $log = new Log();
                                        // $log->company_id = $company_id;
                                        // $log->client_id = $client_id;
                                        // $log->pet_name = $pet_name;
                                        // $log->date_of_transaction = date('Y-m-d H:i:s');
                                        // $log->l_status = $status;
                                        // $log->amount = $value1->price;
                                        // $log->l_flag = "Deducted";
                                        // $log->save();
                                        // $log->log_id = (int) $log->log_id;


                                    }


                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$appointment->service_id}"));

                                    if(count($creditCheck1) >0)
                                    {


                                        $creditCheck1->remaining=$remaining;
                                        $creditCheck1->save();

                                    }
                                }
                            }

                        }else{
                            //$total=(float)$paid_amt;
                            $remaining=(float)$remains;
                            //$used=(float)$total - $remaining;
                        }

                    }
                }

            }
        });
    }
});



/*
 * Repeat Booking...
 */
$app->post('/RepeatBookingnew', function() use ($app) {

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data']=[];
    verifyFields(array('company_id','service_id', 'startdate', 'visits', 'visit_hours', 'pet_id'));    // checking fields are empty or not
    //$Edate='';
    $company_id = $app->request->post('company_id');
    $service_id = $app->request->post('service_id');
    $stringSdate = strtotime($app->request->post('startdate'));
    $Sdate = date('m/d/Y', $stringSdate);
    $sta_date = date('m/d/Y', $stringSdate);

    $stringEdate = strtotime($app->request->post('enddate'));
    $Edate = date('m/d/Y', $stringEdate);
    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');
    $pet_id = $app->request->post('pet_id');
    $message = $app->request->post('message');
    $client_id = (int) $app->request->post('client_id');
    $client = Client::find($client_id);
    $client_name = $client->firstname . ' ' . $client->lastname;
    $rf_company_id = $client->company_id;

    $clientCheck = Client::find($client_id);
    $flag = $clientCheck->company_id != NULL ? true : NULL;

    $days = array();
    $start = new DateTime( $Sdate);
    $temp= new DateTime( $Sdate);

    if($stringEdate!=NULL)
    {
        $end = new DateTime( $Edate);

    }

    else
    {

        $temp->modify('+1 Years');
        $end = new DateTime($temp->format('d-m-Y'));

    }

    $oneday = new DateInterval("P1D");

    foreach(new DatePeriod($start, $oneday, $end->add($oneday)) as $day)
    {

        $day_num = $day->format("N"); /* 'N' number days 1 (mon) to 7 (sun) */
        if($day_num < 6)
        { /* weekday */
            $days[]= $day->format("d-m-Y");


        }

    }

    $abcd=0;
    $flag='no';
    $i=0;
    foreach($days as $ke => $val)
    {
        $new_date=date('Y-m-d',strtotime($val));
        // echo $new_date;
        // die;
        $appoint_check=Appointment::find_by_sql("SELECT * from `tbl_appointments` where company_id=$company_id AND client_id=$client_id and service_id=$service_id and pet_id=$pet_id and date='$new_date'");

        if($appoint_check != NULL)
        {
            $i++;

        }
        else{
            $xy=date('Y-m-d');
            if($new_date == $xy)
            {
                $flag='yes';
            }
            $d_no=getWeekday($val)-1;



            $price = Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id}"));

            $absc=count($visit_hours)-1;

            if($abcd>$absc)
            {
                $abcd=0;

            }

            $whole_visit= floor($visits[$d_no]);
            $whole = floor($visit_hours[$d_no]);// whole number from
            $fraction = $visit_hours[$d_no] - $whole; // getting part after decimal point


            /*
         * Price calculating
         */


            if ($fraction)
            {

                if($whole>1 && $whole_visit > 1)
                {

                    $total[] =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                }
                else if($whole > 1 && $whole_visit == 1)
                {

                    $total[] = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit == 1)
                {

                    $total[] = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit>1)
                {
                    $total[] = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }
                else if($whole < 1 && $whole_visit == 1)
                {

                    $total[] = $price->half_hour_price;

                }else if($whole < 1 && $whole_visit > 1)
                {
                    $total[] = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }

            } else {

                if($whole>1 && $whole_visit>1)
                {
                    $total[] =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                }
                else if($whole>1 && $whole_visit == 1)
                {
                    $total[] = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                }
                else if($whole ==1 && $whole_visit>1)
                {
                    $total[] = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                }
                else
                {
                    $total[] = $price->full_hour_price;

                }
            }


            /*
         *  Stroing to table
         */
            if($visits[$d_no] != 0 && $visit_hours[$d_no] != 0)
            {
                $appointment = new Appointment();
                $appointment->company_id = $company_id;
                $appointment->client_id = $client_id;
                $appointment->service_id = $service_id;

                $appointment->date = $val;

                $appointment->visits = $visits[$d_no];

                $appointment->visit_hours = $visit_hours[$d_no];
                $appointment->price = $total[$abcd];

                $appointment->status = 'assign staff';
                $appointment->pet_id = $pet_id;
                $appointment->message = $message;
                if (empty($rf_company_id)) {
                    $appointment->created_by = 'client';
                } else {
                    $appointment->created_by = 'company';
                }
                $appointment->created_at = date('Y-m-d H:i:s');
                $appointment->save();
                $appointment->appointment_id = (int) $appointment->appointment_id;

                $nn=(int) $appointment->appointment_id;
            }

            $abcd=$abcd+1;
        }
    }


    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Appointment Successfully booked and '.$i.' apointments are exists.';
    $response['data']=[];

    echoResponse(200, $response);


    if($flag=='yes'){
        $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
        $ab=date('Y-m-d');
        foreach ($aa as $val)
        {
            if($val->service_id>0)
            {
                $service_id1=$val->service_id;

                //              /*Walk calculation start*/
                // $test = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

                //     $start_date=date('Y-m-d H:i:s',strtotime($test->check_date));
                //     $cd=date('Y-m-d H:i:s');
                //     $appoint = Appointment::find_by_sql("SELECT visits AS v, visit_hours AS vh FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$start_date' and '$cd'");
                //                     $walks = 0;
                //                     $additional_walks = 0;
                //                     $extra_visits = 0;

                //          foreach ($appoint as  $value1)
                //                 {

                //                     $walks += count($value1);
                //                     if($walks == 0.5)
                //                     {
                //                         $additional_walks += 1-1;
                //                     }else{
                //                         $additional_walks += (float)($value1->vh)-1;
                //                     }
                //                     $extra_visits += (float)($value1->v)-1;

                //                 }
                //                 /*Walk calculation end*/

                $creditCheck = Credits1::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));


                if (count($creditCheck)>0)

                {
                    foreach ($creditCheck as  $valu) {
                        $last_date=$valu->last_check;
                        $paid_amt = $valu->paid_amount;
                        $remains=$valu->remaining;
                    }


                    $dx=date('m/d/Y',strtotime($last_date));


                    // $net=Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id1}"));
                    //           if(isset($net)){
                    //             $act_price=$net->full_hour_price;


                    //             if($act_price == 0)
                    //             {
                    //                 $credit=0;
                    //             }
                    //             else{
                    //                 $credit=(float)($paid_amt/$act_price);
                    //             }

                    //               }
                    //               else{
                    //                 $response['error_code'] = 1;
                    //             $response['message'] = 'you have not set price for this service';
                    //             $response['status'] = false;
                    //               }

                    $dt=date('m/d/Y');
                    if($service_id1 == $appointment->service_id)
                    {
                        if($sta_date == $dt && $sta_date == $dx)
                        {

                            $appoint=Appointment::find_by_sql("select price from tbl_appointments where appointment_id=$nn");
                            if($appoint>0)
                            {

                                foreach ($appoint as  $value1)
                                {
                                    $total=(float)$paid_amt;

                                    // if($act_price == 0)
                                    // {
                                    //     $credit=0;
                                    // }
                                    // else{
                                    //      $credit=number_format((float)($total/$act_price),1,'.','');
                                    // }


                                    $remaining=$remains;


                                    $remaining-=(float)$value1->price;

                                    if($remaining == 0)
                                    {
                                        $creditCheck4 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

                                        $creditCheck4->check_date=date('Y-m-d');
                                        $creditCheck4->paid_amount=0;
                                        $creditCheck4->credits=0;

                                        $creditCheck4->save();

                                        $credit =0;
                                        $used =0;
                                        $total =0;

                                    }

                                    //$check_amt = $credit - $walks;

                                    /*For log status dynamically*/
                                    $status='';

                                    if($remaining <= 0)
                                    {
                                        $status = 'Completed';
                                    }else{
                                        $status = 'Active';
                                    }
                                    /*end*/


                                    $used=(float)$total - $remaining;

                                    $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                                    $pet_name = $pet->pet_name;
                                    $log = new Log();
                                    $log->company_id = $company_id;
                                    $log->client_id = $client_id;
                                    $log->pet_name = $pet_name;
                                    $log->date_of_transaction = date('Y-m-d H:i:s');
                                    $log->l_status = $status;
                                    $log->amount = $value1->price;
                                    $log->l_flag = "Deducted";
                                    $log->save();
                                    $log->log_id = (int) $log->log_id;

                                }
                                $creditCheck1 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

                                if(count($creditCheck1) >0)
                                {


                                    $creditCheck1->remaining=$remaining;
                                    $creditCheck1->save();

                                }
                            }
                        }

                    }else{
                        $total=(float)$paid_amt;
                        $remaining=(float)$remains;
                        $used=(float)$total - $remaining;
                    }
                }else{


                    $credit = new Credits1();
                    $credit->company_id = $company_id;
                    $credit->client_id = $client_id;
                    //$credit->service_id = $service_id1;
                    $credit->credits = 0;
                    $credit->paid_amount = 0;
                    $credit->date_of_payment = null;
                    $credit->last_check = $ab;
                    $credit->remaining=0;
                    $credit->save();
                    $credit->creditnew_id = (int) $credit->creditnew_id;

                    // $net=Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id1}"));
                    //       if(isset($net)){
                    //         $act_price=$net->full_hour_price;


                    //         if($act_price == 0)
                    //         {
                    //             $credit=0;
                    //         }
                    //         else{
                    //             $credit=(float)($paid_amt/$act_price);
                    //         }

                    //           }
                    //           else{
                    //             $response['error_code'] = 1;
                    //         $response['message'] = 'you have not set price for this service';
                    //         $response['status'] = false;
                    //           }

                    if($sta_date == $dt && $sta_date == $dx)
                    {

                        $total=(float)$paid_amt;

                        // if($act_price == 0)
                        // {
                        //     $credit=0;
                        // }
                        // else{
                        //      $credit=number_format((float)($total/$act_price),1,'.','');
                        // }


                        $remaining=$remains;


                        $remaining-=(float)$value1->price;

                        if($remaining == 0)
                        {
                            $creditCheck4 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

                            // $creditCheck4->check_date=date('Y-m-d');
                            $creditCheck4->paid_amount=0;
                            $creditCheck4->credits=0;

                            $creditCheck4->save();

                            $credit =0;
                            $used =0;
                            $total =0;

                        }

                        //$check_amt = $credit - $walks;

                        /*For log status dynamically*/
                        $status='';

                        if($remaining <= 0)
                        {
                            $status = 'Completed';
                        }else{
                            $status = 'Active';
                        }
                        /*end*/

                        $used=(float)$total - $remaining;

                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new Log();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->l_status = $status;
                        $log->amount = $value1->price;
                        $log->l_flag = "Deducted";
                        $log->save();
                        $log->log_id = (int) $log->log_id;

                    }

                    $creditCheck2 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));
                    if(count($creditCheck2) >0)
                    {

                        $creditCheck2->last_check=$ab;
                        $creditCheck2->remaining=$remaining;
                        $creditCheck2->save();
                    }
                }

            }

        }


    }

});



/*
 * Repeat Booking...
 */
$app->post('/RepeatBooking', function() use ($app) {

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data']=[];
    verifyFields(array('company_id','service_id', 'startdate', 'visits', 'visit_hours', 'pet_id'));    // checking fields are empty or not
    //$Edate='';
    $company_id = $app->request->post('company_id');
    $service_id = $app->request->post('service_id');
    $stringSdate = strtotime($app->request->post('startdate'));
    $Sdate = date('m/d/Y', $stringSdate);
    $sta_date = date('m/d/Y', $stringSdate);

    $stringEdate = strtotime($app->request->post('enddate'));
    $Edate = date('m/d/Y', $stringEdate);
    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');
    $pet_id = $app->request->post('pet_id');
    $message = $app->request->post('message');
    $client_id = (int) $app->request->post('client_id');
    $client = Client::find($client_id);
    $client_name = $client->firstname . ' ' . $client->lastname;
    $rf_company_id = $client->company_id;

    $clientCheck = Client::find($client_id);
    $flag = $clientCheck->company_id != NULL ? true : NULL;

    $days = array();
    $start = new DateTime( $Sdate);
    $temp= new DateTime( $Sdate);

    if($stringEdate!=NULL)
    {
        $end = new DateTime( $Edate);

    }

    else
    {

        $temp->modify('+1 Years');
        $end = new DateTime($temp->format('d-m-Y'));

    }

    $oneday = new DateInterval("P1D");

    foreach(new DatePeriod($start, $oneday, $end->add($oneday)) as $day)
    {

        $day_num = $day->format("N"); /* 'N' number days 1 (mon) to 7 (sun) */
        if($day_num < 6)
        { /* weekday */
            $days[]= $day->format("d-m-Y");


        }

    }

    $credit = Credits::find_by_sql("SELECT * from tbl_newcredits where company_id=$company_id AND client_id=$client_id AND service_id=$service_id AND pet_id=$pet_id");

    if($credit == null)
    {

        $cservice=CompanyService::find('all',array("conditions" => "company_id={$company_id}"));

        foreach ($cservice as $key => $value)
        {
            $c=new Credits();
            $c->company_id = $company_id;
            $c->client_id = $client_id;
            $c->pet_id = $pet_id;
            $c->service_id = $value->service_id;
            $c->paid_amount = 0;
            $c->old_amount = 0;
            $c->last_check = date('Y-m-d');
            $c->date_of_payment =NULL;
            $c->remaining = 0;
            $c->flag=0;
            $c->r_flag=0;
            $c->save();
            $c->credit_id = (int)$c->credit_id;
        }

    }

    $abcd=0;
    $flag='no';
    $i=0;
    foreach($days as $ke => $val)
    {
        $new_date=date('Y-m-d',strtotime($val));
        // echo $new_date;
        // die;
        $appoint_check=Appointment::find_by_sql("SELECT * from `tbl_appointments` where company_id=$company_id AND client_id=$client_id and service_id=$service_id and pet_id=$pet_id and date='$new_date'");

        if($appoint_check != NULL)
        {
            $i++;

        }
        else{
            $xy=date('Y-m-d');
            if($new_date == $xy)
            {
                $flag='yes';
            }
            $d_no=getWeekday($val)-1;



            $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));

            $absc=count($visit_hours)-1;

            if($abcd>$absc)
            {
                $abcd=0;

            }

            $whole_visit= floor($visits[$d_no]);
            $whole = floor($visit_hours[$d_no]);// whole number from
            $fraction = $visit_hours[$d_no] - $whole; // getting part after decimal point


            /*
         * Price calculating
         */


            if ($fraction)
            {

                if($whole>1 && $whole_visit > 1)
                {

                    $total[] =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                }
                else if($whole > 1 && $whole_visit == 1)
                {

                    $total[] = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit == 1)
                {

                    $total[] = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit>1)
                {
                    $total[] = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }
                else if($whole < 1 && $whole_visit == 1)
                {

                    $total[] = $price->half_hour_price;

                }else if($whole < 1 && $whole_visit > 1)
                {
                    $total[] = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }

            } else {

                if($whole>1 && $whole_visit>1)
                {
                    $total[] =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                }
                else if($whole>1 && $whole_visit == 1)
                {
                    $total[] = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                }
                else if($whole ==1 && $whole_visit>1)
                {
                    $total[] = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                }
                else
                {
                    $total[] = $price->full_hour_price;

                }
            }


            /*
         *  Stroing to table
         */
            if($visits[$d_no] != 0 && $visit_hours[$d_no] != 0)
            {
                $appointment = new Appointment();
                $appointment->company_id = $company_id;
                $appointment->client_id = $client_id;
                $appointment->service_id = $service_id;

                $appointment->date = $val;

                $appointment->visits = $visits[$d_no];

                $appointment->visit_hours = $visit_hours[$d_no];
                $appointment->price = $total[$abcd];

                $appointment->status = 'assign staff';
                $appointment->pet_id = $pet_id;
                $appointment->message = $message;
                if (empty($rf_company_id)) {
                    $appointment->created_by = 'client';
                } else {
                    $appointment->created_by = 'company';
                }
                $appointment->created_at = date('Y-m-d H:i:s');
                $appointment->save();
                $appointment->appointment_id = (int) $appointment->appointment_id;

                $nn=(int) $appointment->appointment_id;
            }

            $abcd=$abcd+1;
        }
    }


    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Appointment Successfully booked and '.$i.' apointments are exists.';
    $response['data']=[];

    echoResponse(200, $response);


    if($flag=='yes'){
        $total=0;
        $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
        $ab=date('Y-m-d');
        foreach ($aa as $val)
        {
            if($val->service_id>0)
            {
                $service_id1=$val->service_id;

                //              /*Walk calculation start*/
                // $test = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1}"));

                //     $start_date=date('Y-m-d H:i:s',strtotime($test->check_date));
                //     $cd=date('Y-m-d H:i:s');
                //     $appoint = Appointment::find_by_sql("SELECT visits AS v, visit_hours AS vh FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$start_date' and '$cd'");
                //                     $walks = 0;
                //                     $additional_walks = 0;
                //                     $extra_visits = 0;

                //          foreach ($appoint as  $value1)
                //                 {

                //                     $walks += count($value1);
                //                     if($walks == 0.5)
                //                     {
                //                         $additional_walks += 1-1;
                //                     }else{
                //                         $additional_walks += (float)($value1->vh)-1;
                //                     }
                //                     $extra_visits += (float)($value1->v)-1;

                //                 }
                //                 /*Walk calculation end*/

                $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$appointment->service_id}"));


                if (count($creditCheck)>0)

                {
                    foreach ($creditCheck as  $valu) {
                        $last_date=$valu->last_check;
                        $paid_amt = $valu->paid_amount;
                        $remains=$valu->remaining;
                    }


                    $dx=date('m/d/Y',strtotime($last_date));


                    // $net=Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id1}"));
                    //           if(isset($net)){
                    //             $act_price=$net->full_hour_price;


                    //             if($act_price == 0)
                    //             {
                    //                 $credit=0;
                    //             }
                    //             else{
                    //                 $credit=(float)($paid_amt/$act_price);
                    //             }

                    //               }
                    //               else{
                    //                 $response['error_code'] = 1;
                    //             $response['message'] = 'you have not set price for this service';
                    //             $response['status'] = false;
                    //               }

                    $dt=date('m/d/Y');
                    if($service_id1 == $appointment->service_id)
                    {
                        if($sta_date == $dt && $sta_date == $dx)
                        {

                            $appoint=Appointment::find_by_sql("select price from tbl_appointments where appointment_id=$nn");
                            if($appoint>0)
                            {

                                foreach ($appoint as  $value1)
                                {
                                    // $total=(float)$paid_amt;

                                    //             if($act_price == 0)
                                    //             {
                                    //                 $credit=0;
                                    //             }
                                    //             else{
                                    //                  $credit=number_format((float)($total/$act_price),1,'.','');
                                    //             }


                                    $remaining=$remains;


                                    $remaining-=(float)$value1->price;

                                    if($remaining == 0)
                                    {
                                        $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id1}"));

                                        //$creditCheck4->check_date=date('Y-m-d H:i:s');
                                        $creditCheck4->paid_amount=0;
                                        //$creditCheck4->credits=0;

                                        $creditCheck4->save();

                                        //   $credit =0;
                                        // $used =0;
                                        // $total =0;
                                        // $walks =0;
                                        // $additional_walks =0;
                                        // $extra_visits =0;
                                    }

                                    // $check_amt = $credit - $walks;

                                    /*For log status dynamically*/
                                    // $status='';

                                    // if($remaining == 0)
                                    // {
                                    //      $status = 'Completed';
                                    // }else{
                                    //      $status = 'Active';
                                    // }
                                    /*end*/


                                    //$used=(float)$total - $remaining;


                                    $pet=Pet::find($pet_id);
                                    $pet_name = $pet->pet_name;
                                    $log = new transactionlog();
                                    $log->company_id = $company_id;
                                    $log->client_id = $client_id;
                                    $log->pet_id = $pet_id;
                                    $log->service_id = $service_id;
                                    $log->pet_name = $pet_name;
                                    $log->date_of_transaction = date('Y-m-d H:i:s');
                                    $log->type = "Charge";
                                    $log->amount = $value1->price;
                                    $log->l_flag = "Deducted";
                                    $log->old_value = $remains;
                                    $log->new_value = $remaining;
                                    $log->save();
                                    $log->log_id = (int) $log->log_id;

                                    // $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                                    // $pet_name = $pet->pet_name;
                                    // $log = new Log();
                                    // $log->company_id = $company_id;
                                    // $log->client_id = $client_id;
                                    // $log->pet_name = $pet_name;
                                    // $log->date_of_transaction = date('Y-m-d H:i:s');
                                    // $log->l_status = $status;
                                    // $log->amount = $value1->price;
                                    // $log->l_flag = "Deducted";
                                    // $log->save();
                                    // $log->log_id = (int) $log->log_id;

                                }
                                $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$appointment->service_id}"));

                                if(count($creditCheck1) >0)
                                {


                                    $creditCheck1->remaining=$remaining;
                                    $creditCheck1->save();

                                }
                            }
                        }

                    }else{
                        //$total=(float)$paid_amt;
                        $remaining=(float)$remains;
                        //$used=(float)$total - $remaining;
                    }
                }else{


                    $credit = new Credits();
                    $credit->company_id = $company_id;
                    $credit->client_id = $client_id;
                    $credit->pet_id = $pet_id;
                    $credit->service_id = $service_id1;
                    //$credit->credits = 0;
                    $credit->paid_amount = 0;
                    $credit->date_of_payment = null;
                    $credit->last_check = $ab;
                    $credit->remaining=0;
                    $credit->save();
                    $credit->credit_id = (int) $credit->credit_id;

                    // $net=Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));
                    //       if(isset($net)){
                    //         $act_price=$net->full_hour_price;


                    //         if($act_price == 0)
                    //         {
                    //             $credit=0;
                    //         }
                    //         else{
                    //             $credit=(float)($paid_amt/$act_price);
                    //         }

                    //           }
                    //           else{
                    //             $response['error_code'] = 1;
                    //         $response['message'] = 'you have not set price for this service';
                    //         $response['status'] = false;
                    //           }

                    if($sta_date == $dt && $sta_date == $dx)
                    {

                        // $total=(float)$paid_amt;

                        //         if($act_price == 0)
                        //         {
                        //             $credit=0;
                        //         }
                        //         else{
                        //              $credit=number_format((float)($total/$act_price),1,'.','');
                        //         }


                        $remaining=$remains;


                        $remaining-=(float)$value1->price;

                        if($remaining == 0)
                        {
                            $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                            //$creditCheck4->check_date=date('Y-m-d H:i:s');
                            $creditCheck4->paid_amount=0;
                            //$creditCheck4->credits=0;

                            $creditCheck4->save();

                            //    $credit =0;
                            // $used =0;
                            // $total =0;
                            // $walks =0;
                            // $additional_walks =0;
                            // $extra_visits =0;
                        }

                        //$check_amt = $credit - $walks;

                        /*For log status dynamically*/
                        // $status='';

                        // if($remaining == 0)
                        // {
                        //      $status = 'Completed';
                        // }else{
                        //      $status = 'Active';
                        // }
                        /*end*/

                        //$used=(float)$total - $remaining;

                        $pet=Pet::find($pet_id);
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Charge";
                        $log->amount = $value1->price;
                        $log->l_flag = "Deducted";
                        $log->old_value = $remains;
                        $log->new_value = $remaining;
                        $log->save();
                        $log->log_id = (int) $log->log_id;

                        // $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        // $pet_name = $pet->pet_name;
                        // $log = new Log();
                        // $log->company_id = $company_id;
                        // $log->client_id = $client_id;
                        // $log->pet_name = $pet_name;
                        // $log->date_of_transaction = date('Y-m-d H:i:s');
                        // $log->l_status = $status;
                        // $log->amount = $value1->price;
                        // $log->l_flag = "Deducted";
                        // $log->save();
                        // $log->log_id = (int) $log->log_id;

                    }

                    $creditCheck2 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$appointment->service_id} AND pet_id={$pet_id}"));
                    if(count($creditCheck2) >0)
                    {

                        $creditCheck2->last_check=$ab;
                        $creditCheck2->remaining=$remaining;
                        $creditCheck2->save();
                    }
                }

            }

        }


    }

});


//  /*
//  * Repeat Booking...
//  */
// $app->post('/RepeatBooking', function() use ($app) {

//  $response['error_code'] = 1;
//         $response['status'] = false;
//         $response['message'] = 'Error! Something went wrong. please try again later.';
//         $response['data']=[];
//     verifyFields(array('company_id','service_id', 'startdate', 'visits', 'visit_hours', 'pet_id'));    // checking fields are empty or not
//     //$Edate='';
//     $company_id = $app->request->post('company_id');
//     $service_id = $app->request->post('service_id');
//     $stringSdate = strtotime($app->request->post('startdate'));
//     $Sdate = date('m/d/Y', $stringSdate);
//     $sta_date = date('m/d/Y', $stringSdate);

//     $stringEdate = strtotime($app->request->post('enddate'));
//     $Edate = date('m/d/Y', $stringEdate);
//     $visits = $app->request->post('visits');
//     $visit_hours = $app->request->post('visit_hours');
//     $pet_id = $app->request->post('pet_id');
//     $message = $app->request->post('message');
//     $client_id = (int) $app->request->post('client_id');
//     $client = Client::find($client_id);
//     $client_name = $client->firstname . ' ' . $client->lastname;
//     $rf_company_id = $client->company_id;

//     $clientCheck = Client::find($client_id);
//     $flag = $clientCheck->company_id != NULL ? true : NULL;

//      $days = array();
//      $start = new DateTime( $Sdate);
//      $temp= new DateTime( $Sdate);

//         if($stringEdate!=NULL)
//         {
//          $end = new DateTime( $Edate);

//         }

//       else
//       {

//         $temp->modify('+1 Years');
//         $end = new DateTime($temp->format('d-m-Y'));

//       }

//      $oneday = new DateInterval("P1D");

// foreach(new DatePeriod($start, $oneday, $end->add($oneday)) as $day)
// {

//     $day_num = $day->format("N"); /* 'N' number days 1 (mon) to 7 (sun) */
//     if($day_num < 6)
//     { /* weekday */
//        $days[]= $day->format("d-m-Y");


//     }

// }

// $abcd=0;
// $flag='no';
// $i=0;
//  foreach($days as $ke => $val)
//  {
//    $new_date=date('Y-m-d',strtotime($val));
//     // echo $new_date;
//     // die;
//      $appoint_check=Appointment::find_by_sql("SELECT * from `tbl_appointments` where company_id=$company_id AND client_id=$client_id and service_id=$service_id and pet_id=$pet_id and date='$new_date'");

//     	if($appoint_check != NULL)
//     	{
//        $i++;

//     	}
//     	else{
// $xy=date('Y-m-d');
//     		if($new_date == $xy)
//     		{
//     			$flag='yes';
//     		}
//     			$d_no=getWeekday($val)-1;



//         		$price = Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id}"));

//         		$absc=count($visit_hours)-1;

//         		if($abcd>$absc)
//         			{
//             			$abcd=0;

//         			}

// 				        $whole_visit= floor($visits[$d_no]);
// 				        $whole = floor($visit_hours[$d_no]);// whole number from
// 				        $fraction = $visit_hours[$d_no] - $whole; // getting part after decimal point


//         /*
//          * Price calculating
//          */


// 		if ($fraction)
//             {

//                 if($whole>1 && $whole_visit > 1)
//                 {

//                     $total[] =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


//                 }
//                 else if($whole > 1 && $whole_visit == 1)
//                 {

//                       $total[] = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

//                 }
//                 else if($whole == 1 && $whole_visit == 1)
//                 {

//                       $total[] = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

//                 }
//                 else if($whole == 1 && $whole_visit>1)
//                 {
//                    $total[] = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

//                 }
//                 else if($whole < 1 && $whole_visit == 1)
//                 {

//                         $total[] = $price->half_hour_price;

//                 }else if($whole < 1 && $whole_visit > 1)
//                 {
//                     $total[] = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

//                 }

//         } else {

//                         if($whole>1 && $whole_visit>1)
//                         {
//                             $total[] =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

//                         }
//                         else if($whole>1 && $whole_visit == 1)
//                         {
//                               $total[] = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

//                         }
//                         else if($whole ==1 && $whole_visit>1)
//                         {
//                            $total[] = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

//                         }
//                         else
//                         {
//                             $total[] = $price->full_hour_price;

//                         }
//                 }


//          /*
//          *  Stroing to table
//          */
// 				if($visits[$d_no] != 0 && $visit_hours[$d_no] != 0)
// 				          {
// 				        $appointment = new Appointment();
// 				        $appointment->company_id = $company_id;
// 				        $appointment->client_id = $client_id;
// 				        $appointment->service_id = $service_id;

// 				        $appointment->date = $val;

// 				        $appointment->visits = $visits[$d_no];

// 				        $appointment->visit_hours = $visit_hours[$d_no];
// 				        $appointment->price = $total[$abcd];

// 				        $appointment->status = 'assign staff';
// 				        $appointment->pet_id = $pet_id;
// 				        $appointment->message = $message;
// 				        if (empty($rf_company_id)) {
// 				            $appointment->created_by = 'client';
// 				        } else {
// 				            $appointment->created_by = 'company';
// 				        }
// 				        $appointment->created_at = date('Y-m-d H:i:s');
// 				        $appointment->save();
// 				        $appointment->appointment_id = (int) $appointment->appointment_id;

// 				        $nn=(int) $appointment->appointment_id;
// 				    }

//      				$abcd=$abcd+1;
// 			}
// }


//             $response['error_code'] = 0;
//             $response['status'] = true;
//             $response['message'] = 'Appointment Successfully booked and '.$i.' apointments are exists.';
//             $response['data']=[];

// echoResponse(200, $response);


//  if($flag=='yes'){
// $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
//    $ab=date('Y-m-d');
// foreach ($aa as $val)
// {
//     if($val->service_id>0)
//     {
//               $service_id1=$val->service_id;

//                              /*Walk calculation start*/
//                 $test = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1}"));

//                     $start_date=date('Y-m-d H:i:s',strtotime($test->check_date));
//                     $cd=date('Y-m-d H:i:s');
//                     $appoint = Appointment::find_by_sql("SELECT visits AS v, visit_hours AS vh FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$start_date' and '$cd'");
//                                     $walks = 0;
//                                     $additional_walks = 0;
//                                     $extra_visits = 0;

//                          foreach ($appoint as  $value1)
//                                 {

//                                     $walks += count($value1);
//                                     if($walks == 0.5)
//                                     {
//                                         $additional_walks += 1-1;
//                                     }else{
//                                         $additional_walks += (float)($value1->vh)-1;
//                                     }
//                                     $extra_visits += (float)($value1->v)-1;

//                                 }
//                                 /*Walk calculation end*/

//         $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$appointment->service_id}"));


//         if (count($creditCheck)>0)

//             {
//                  foreach ($creditCheck as  $valu) {
//                       $last_date=$valu->last_check;
//                       $paid_amt = $valu->paid_amount;
//                       $remains=$valu->remaining;
//                   }


//                     $dx=date('m/d/Y',strtotime($last_date));


//     $net=Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id1}"));
//               if(isset($net)){
//                 $act_price=$net->full_hour_price;


//                 if($act_price == 0)
//                 {
//                     $credit=0;
//                 }
//                 else{
//                     $credit=(float)($paid_amt/$act_price);
//                 }

//                   }
//                   else{
//                     $response['error_code'] = 1;
//                 $response['message'] = 'you have not set price for this service';
//                 $response['status'] = false;
//                   }

//       $dt=date('m/d/Y');
//         if($service_id1 == $appointment->service_id)
//             {
//                if($sta_date == $dt && $sta_date == $dx)
//                     {

//                         $appoint=Appointment::find_by_sql("select price from tbl_appointments where appointment_id=$nn");
//                             if($appoint>0)
//                              {

//                                 foreach ($appoint as  $value1)
//                                 {
//                                     $total=(float)$paid_amt;

//                                                 if($act_price == 0)
//                                                 {
//                                                     $credit=0;
//                                                 }
//                                                 else{
//                                                      $credit=number_format((float)($total/$act_price),1,'.','');
//                                                 }


//                                     $remaining=$remains;


//                                         $remaining-=(float)$value1->price;

//                                     if($remaining == 0)
//                                    {
//                             $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1}"));

//                                     $creditCheck4->check_date=date('Y-m-d H:i:s');
//                                     $creditCheck4->paid_amount=0;
//                                     $creditCheck4->credits=0;

//                                     $creditCheck4->save();

//                                       $credit =0;
//                                     $used =0;
//                                     $total =0;
//                                     $walks =0;
//                                     $additional_walks =0;
//                                     $extra_visits =0;
//                                    }

//                                        $check_amt = $credit - $walks;

//                                     /*For log status dynamically*/
//                                    $status='';

//                                    if($check_amt <= 0)
//                                    {
//                                         $status = 'Completed';
//                                    }else{
//                                         $status = 'Active';
//                                    }
//                                    /*end*/


//                                     $used=(float)$total - $remaining;

//                                     $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
//                                     $pet_name = $pet->pet_name;
//                                     $log = new Log();
//                                     $log->company_id = $company_id;
//                                     $log->client_id = $client_id;
//                                     $log->pet_name = $pet_name;
//                                     $log->date_of_transaction = date('Y-m-d H:i:s');
//                                     $log->l_status = $status;
//                                     $log->amount = $value1->price;
//                                     $log->l_flag = "Deducted";
//                                     $log->save();
//                                     $log->log_id = (int) $log->log_id;

//                                 }
//                                      $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$appointment->service_id}"));

//                                     if(count($creditCheck1) >0)
//                                         {


//                                         $creditCheck1->remaining=$remaining;
//                                         $creditCheck1->save();

//                                         }
//                               }
//                     }

//                 }else{
//                              $total=(float)$paid_amt;
//                             $remaining=(float)$remains;
//                             $used=(float)$total - $remaining;
//                             }
//             }else{


//                             $credit = new Credits();
//                             $credit->company_id = $company_id;
//                             $credit->client_id = $client_id;
//                             $credit->service_id = $service_id1;
//                             $credit->credits = 0;
//                             $credit->paid_amount = 0;
//                             $credit->date_of_payment = null;
//                             $credit->last_check = $ab;
//                             $credit->remaining=0;
//                             $credit->save();
//                             $credit->credit_id = (int) $credit->credit_id;

// 		$net=Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id1}"));
//               if(isset($net)){
//                 $act_price=$net->full_hour_price;


//                 if($act_price == 0)
//                 {
//                     $credit=0;
//                 }
//                 else{
//                     $credit=(float)($paid_amt/$act_price);
//                 }

//                   }
//                   else{
//                     $response['error_code'] = 1;
//                 $response['message'] = 'you have not set price for this service';
//                 $response['status'] = false;
//                   }

//                              if($sta_date == $dt && $sta_date == $dx)
//                                     {

// 										$total=(float)$paid_amt;

//                                                 if($act_price == 0)
//                                                 {
//                                                     $credit=0;
//                                                 }
//                                                 else{
//                                                      $credit=number_format((float)($total/$act_price),1,'.','');
//                                                 }


//                                     $remaining=$remains;


//                                         $remaining-=(float)$value1->price;

//                                     if($remaining == 0)
//                                    {
//                             $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1}"));

//                                     $creditCheck4->check_date=date('Y-m-d H:i:s');
//                                     $creditCheck4->paid_amount=0;
//                                     $creditCheck4->credits=0;

//                                     $creditCheck4->save();

//                                        $credit =0;
//                                     $used =0;
//                                     $total =0;
//                                     $walks =0;
//                                     $additional_walks =0;
//                                     $extra_visits =0;
//                                    }

//                                      $check_amt = $credit - $walks;

//                                     /*For log status dynamically*/
//                                    $status='';

//                                    if($check_amt <= 0)
//                                    {
//                                         $status = 'Completed';
//                                    }else{
//                                         $status = 'Active';
//                                    }
//                                    /*end*/

//                                     $used=(float)$total - $remaining;

//                                     $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
//                                     $pet_name = $pet->pet_name;
//                                     $log = new Log();
//                                     $log->company_id = $company_id;
//                                     $log->client_id = $client_id;
//                                     $log->pet_name = $pet_name;
//                                     $log->date_of_transaction = date('Y-m-d H:i:s');
//                                     $log->l_status = $status;
//                                     $log->amount = $value1->price;
//                                     $log->l_flag = "Deducted";
//                                     $log->save();
//                                     $log->log_id = (int) $log->log_id;

//                                     }

//                                     $creditCheck2 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$appointment->service_id}"));
//                                     if(count($creditCheck2) >0)
//                                     {

//                                     $creditCheck2->last_check=$ab;
//                                    $creditCheck2->remaining=$remaining;
//                                     $creditCheck2->save();
//                                     }
//                         }

//             }

// }


//  }

// });

/*
*
* credit calculation based on appointments.
*/

$app->post('/:id/creditcalcnew', function($id) use ($app)
{
    verifyFields(array('client_id'));

    $response['error_code'] = 1;
    $response['message'] = 'No Credit List found';
    $response['status'] = false;
    $response['data']=0;

    $client_id=$app->request->post('client_id');
    $company_id=$id;
    $creditData=[];


    //$aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
    $ab=date('Y-m-d');


// foreach ($aa as $val)
// {


    //$service_id1=$val->service_id;
    $test1 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

    if(count($test1) == 0)
    {


        $credit = new Credits1();
        $credit->company_id = $company_id;
        $credit->client_id = $client_id;
        //$credit->service_id = $service_id1;
        $credit->credits = 0;
        $credit->paid_amount = 0;
        $credit->old_amount = 0;
        $credit->date_of_payment = null;
        $credit->last_check = $ab;
        $credit->remaining=0;
        $credit->save();
        $credit->creditnew_id = (int) $credit->creditnew_id;
    }

    //         /*walk calculation*/
    //   $test = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

    //         $check_date=date('Y-m-d',strtotime($test->check_date));
    //         $p_amt= $test->paid_amount;
    //         $rem = $test->remaining;

    // if($p_amt == 0 && $rem == 0 && $check_date == $ab)
    //             {
    //                 $walks = 0;
    //                 $additional_walks = 0;
    //                         $extra_visits = 0;
    //             }else{
    //         $appoint = Appointment::find_by_sql("SELECT visits AS v, visit_hours AS vh FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$check_date' and '$ab'");

    //                         $walks = 0;
    //                         $additional_walks = 0;
    //                         $extra_visits = 0;

    //              foreach ($appoint as  $value1)
    //                     {

    //                         $walks += (float)($value1->v)*($value1->vh);
    //                         if($walks == 0.5)
    //                         {
    //                             $additional_walks += 1-1;
    //                         }else{
    //                             $additional_walks += (float)($value1->vh)-1;
    //                         }
    //                         $extra_visits += (float)($value1->v)-1;

    //                     }
    //                 }
    //                     /*end walk calculation*/
    // if($additional_walks < 0)
    // {
    //  $additional_walks = 0;
    // }

    // $service=Service::find(array("conditions" => "service_id = $service_id1"));
    // $service_name=$service->service_name;

    $creditCheck = Credits1::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

    if (count($creditCheck)>0)

    {
        foreach ($creditCheck as  $valu)
        {
            $last_date=$valu->last_check;
            $credits2=(float)$valu->paid_amount;
            $remains=$valu->remaining;

        }


        $last_check=date('Y-m-d',strtotime($last_date));

        if($last_check != $ab)
        {
            //$last_check=date('Y-m-d',strtotime($creditCheck->last_check));
            $datetime = new DateTime($last_check);
            $datetime->modify('+1 day');
            $l_check=$datetime->format('Y-m-d');


            //$appoint=Appointment::find('all',array("conditions" => "company_id={$company_id} and client_id={$client_id} and service_id={$service_id1} and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab'")and date<='$ab');
            $appoint = Appointment::find_by_sql("SELECT sum(price) as p, date as date FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab' GROUP BY date");


            $t_price=0;
            if(count($appoint)>0)
            {
                //$totalcredit=[];
                foreach ($appoint as  $value1)
                {
                    $t_price += $value1->p;

                    $total=(float)$credits2;



                    //$credit=(float)($total/$act_price);

                    $remaining=$remains;

                    $remaining-=(float)$t_price;



                    if($remaining == 0)
                    {
                        $creditCheck4 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

                        //$creditCheck4->check_date=date('Y-m-d',strtotime($value1->date));
                        $creditCheck4->paid_amount=0;
                        $creditCheck4->credits=0;

                        $creditCheck4->save();
                        $used=0;
                    }



                    $used=(float)$total - $remaining;

                    // $check_amt = $credit - $walks;

                    /*For log status dynamically*/
                    $status='';

                    if($remaining == 0)
                    {
                        $status = 'Completed';
                    }else{
                        $status = 'Active';
                    }
                    /*end*/

                    //$d=Log::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));

                    $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                    $pet_name = $pet->pet_name;
                    $log = new Log();
                    $log->company_id = $company_id;
                    $log->client_id = $client_id;
                    $log->pet_name = $pet_name;
                    $log->date_of_transaction = date('Y-m-d H:i:s');
                    $log->l_status = $status;
                    $log->amount = $value1->p;
                    $log->l_flag = "Deducted";
                    $log->save();
                    $log->log_id = (int) $log->log_id;

                }
                $creditCheck1 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));
                if(count($creditCheck1) >0)
                {
                    $creditCheck1->last_check=$ab;
                    //$creditCheck1->credits=$credit;
                    $creditCheck1->remaining=$remaining;
                    $creditCheck1->save();
                }
            }else{

                $creditCheck1 = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));
                if(count($creditCheck1) >0)
                {
                    $creditCheck1->last_check=$ab;
                    if($creditCheck1->remaining == 0)
                    {
                        //$creditCheck1->credits=0;
                        $creditCheck1->paid_amount=0;
                        //$creditCheck1->old_amount=0;
                        $creditCheck1->date_of_payment=null;
                        //$creditCheck1->save();
                    }

                    //$creditCheck2->remaining=$remaining;
                    $creditCheck1->save();
                }




                $total=(float)$credits2;
                $remaining=(float)$remains;
                $used=(float)$total - $remaining;
                //  //$credit=(float)($total/$act_price);
                // if($act_price == 0)
                //                     {
                //                         $credit=0;
                //                     }
                //                     else{
                //                          $credit=number_format((float)($total/$act_price),1,'.','');
                //                     }
                // //$credit=number_format((float)($total/$act_price),1,'.','');
            }

        }else{
            $total=(float)$credits2;

            $remaining=(float)$remains;
            $used=(float)$total - $remaining;
            // if($act_price == 0)
            //                     {
            //                         $credit=0;
            //                     }
            //                     else{
            //                          $credit=number_format((float)($total/$act_price),1,'.','');
            //                     }

            //  //$credit=(float)($total/$act_price);
            //  //$credit=number_format((float)($total/$act_price),1,'.','');
        }

        // } else{
        //                      $totalcredit=(float)$credits2;
        //                     $remaining=(float)$remains;
        //                     $used=(float)$totalcredit - $remaining;
        //                     }
    }else{


        $credit = new Credits1();
        $credit->company_id = $company_id;
        $credit->client_id = $client_id;
        //$credit->service_id = $service_id1;
        $credit->credits = 0;
        $credit->paid_amount = 0;
        $credit->old_amount = 0;
        $credit->date_of_payment = null;
        $credit->last_check = $ab;
        $credit->remaining=0;
        $credit->save();
        $credit->creditnew_id = (int) $credit->creditnew_id;

        $totalcredit=0;
        $remaining=0;
        $used=0;
    }




    $creditData=array(
        'company_id' => $company_id,
        'client_id' => $client_id,
        // 'service_name' => $service_name,
        // 'service_id' =>$service_id1,
        'Total' => $total,
        'Used' =>  $used,
        'remaining' => $remaining,
        // 'credit' => $credit,
        // 'Walks' => $walks,
        // 'Additional_walks' => $additional_walks,
        // 'Extra_visits' => $extra_visits
    );
//}

    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Credits retrive successfully.';
    $response['data']=$creditData;


    echoResponse(200, $response);
});

/*
*
* credit calculation based on appointments.
*/

$app->post('/:id/creditcalc', function($id) use ($app)
{
    verifyFields(array('client_id','pet_id'));

    $response['error_code'] = 1;
    $response['message'] = 'No Credit List found';
    $response['status'] = false;
    $response['data']=0;

    $client_id=$app->request->post('client_id');
    $pet_id=$app->request->post('pet_id');
    $company_id=$id;
    $creditData=[];


    $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
    $ab=date('Y-m-d');

    $total=0;
    foreach ($aa as $val)
    {

        $service_id1=$val->service_id;
        $test1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

        if(count($test1) == 0)
        {
            $credit = new Credits();
            $credit->company_id = $company_id;
            $credit->client_id = $client_id;
            $credit->pet_id = $pet_id;
            $credit->service_id = $service_id1;
            //$credit->credits = 0;
            $credit->paid_amount = 0;
            $credit->old_amount = 0;
            $credit->date_of_payment = null;
            $credit->last_check = $ab;
            $credit->remaining=0;
            $credit->save();
            $credit->credit_id = (int) $credit->credit_id;
        }
        // $test = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

        //       $check_date=date('Y-m-d H:i:s',strtotime($test->check_date));  /*chenge strat_date to check_date*/

        //       $p_amt=$test->paid_amount;
        //       $rem=$test->remaining;
        //       $cd=date('Y-m-d H:i:s');

        //       if(($p_amt == 0 && $rem == 0 && $check_date == $cd) || ($p_amt == 0 && $rem == 0 && $check_date < $cd))
        //           {
        //               $walks = 0;
        //               $additional_walks = 0;
        //                       $extra_visits = 0;
        //           }else{
        //       $appoint = Appointment::find_by_sql("SELECT visits AS v, visit_hours AS vh FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$check_date' and '$cd'");

        //                      $walks = 0;
        //                       $additional_walks = 0;
        //                       $extra_visits = 0;

        //            foreach ($appoint as  $value1)
        //                   {

        //                       $walks += count($value1);


        //                       if($walks == 0.5)
        //                       {
        //                           $additional_walks += 1-1;
        //                       }else{
        //                           $additional_walks += (float)($value1->vh)-1;
        //                       }
        //                       $extra_visits += (float)($value1->v)-1;

        //                   }

        //               }


        $service=Service::find(array("conditions" => "service_id = $service_id1"));
        $service_name=$service->service_name;

        $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

        if (count($creditCheck)>0)

        {
            foreach ($creditCheck as  $valu)
            {
                $last_date=$valu->last_check;
                $credits2=(float)$valu->paid_amount;
                $remains=$valu->remaining;

            }


            $last_check=date('Y-m-d',strtotime($last_date));


            //     $net=Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));
            // if(isset($net))
            // {
            //         $act_price=$net->full_hour_price;
            //         if($act_price == 0)
            //             {
            //                 $credit=0;
            //             }
            //             else{
            //                     $credit=(float)($credits2/$act_price);
            //                 }

            // }else{
            //         $response['error_code'] = 1;
            //         $response['message'] = 'you have not set price for this service';
            //         $response['status'] = false;
            //      }


            if($last_check != $ab)
            {

                $datetime = new DateTime($last_check);
                $datetime->modify('+1 day');
                $l_check=$datetime->format('Y-m-d');

                $appoint = Appointment::find_by_sql("SELECT price as p , date as d FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and pet_id=$pet_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab'");


                if(count($appoint)>0)
                {
                    $t_price=0;

                    foreach ($appoint as  $value1)
                    {
                        $t_price += $value1->p;


                        //$total=(float)$credits2;

                        // if($act_price == 0)
                        // {
                        //     $credit=0;
                        // }
                        // else{
                        //      $credit=number_format((float)($total/$act_price),1,'.','');
                        // }


                        $remaining=$remains;

                        $remaining-=(float)$t_price;
                        //$used=(float)$total - $remaining;

                        /*added extra for make all field zero when remaining is 0*/
                        if($remaining == 0)
                        {

                            $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                            //$creditCheck4->check_date=date('Y-m-d H:i:s');
                            $creditCheck4->paid_amount=0;
                            //$creditCheck4->credits=0;

                            $creditCheck4->save();

                            //$credit =0;
                            //$used =0;
                            $total =0;
                            // $walks =0;
                            // $additional_walks =0;
                            // $extra_visits =0;
                        }


                        // $check_amt = $credit - $walks;

                        // /*For log status dynamically*/
                        // $type='';

                        // if($remaining <= 0)
                        // {
                        //      $type = 'Completed';
                        // }else{
                        //      $type = 'Active';
                        // }
                        // /*end*/


                        $pet=Pet::find($pet_id);
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id1;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s',strtotime($value1->d));
                        $log->type = "Charge";
                        $log->amount = $value1->p;
                        $log->l_flag = "Deducted";
                        $log->old_value = $remains;
                        $log->new_value = $remaining;
                        $log->save();
                        $log->log_id = (int) $log->log_id;

                        // $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        // $pet_name = $pet->pet_name;
                        // $log = new Log();
                        // $log->company_id = $company_id;
                        // $log->client_id = $client_id;
                        // $log->pet_name = $pet_name;
                        // $log->date_of_transaction = date('Y-m-d H:i:s');
                        // $log->l_status = $status;
                        // $log->amount = $value1->p;
                        // $log->l_flag = "Deducted";
                        // $log->save();
                        // $log->log_id = (int) $log->log_id;

                    }
                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));
                    if(count($creditCheck1) >0)
                    {
                        $creditCheck1->last_check=date('Y-m-d');
                        //$creditCheck1->credits=$credit;
                        $creditCheck1->remaining=$remaining;
                        $creditCheck1->save();
                    }
                }else{
                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                    if(count($creditCheck1) >0)
                    {
                        $creditCheck1->last_check=$ab;
                        if($creditCheck1->remaining == 0)
                        {
                            //$creditCheck1->credits=0;
                            $creditCheck1->paid_amount=0;

                            $creditCheck1->date_of_payment=null;

                        }


                        $creditCheck1->save();
                    }




                    //$total=(float)$credits2;
                    $remaining=(float)$remains;
                    //$used=(float)$total - $remaining;

                    // if($act_price == 0)
                    //                     {
                    //                         $credit=0;
                    //                     }
                    //                     else{
                    //                          $credit=number_format((float)($total/$act_price),1,'.','');
                    //                     }

                }

            }else{
                //$total=(float)$credits2;

                $remaining=(float)$remains;
                //$used=(float)$total - $remaining;
                // if($act_price == 0)
                //                     {
                //                         $credit=0;
                //                     }
                //                     else{
                //                          $credit=number_format((float)($total/$act_price),1,'.','');
                //                     }


            }


        }else{


            $credit = new Credits();
            $credit->company_id = $company_id;
            $credit->client_id = $client_id;
            $credit->pet_id = $pet_id;
            $credit->service_id = $service_id1;
            //$credit->credits = 0;
            $credit->paid_amount = 0;
            $credit->old_amount = 0;
            $credit->date_of_payment = null;
            $credit->last_check = $ab;
            $credit->remaining=0;
            $credit->save();
            $credit->credit_id = (int) $credit->credit_id;

            //$totalcredit=0;
            $remaining=0;
            //$used=0;
        }




        $service_data[]=array(

            'service_id' =>$service_id1,
            'service_name' =>$service_name,
            'remaining' => $remaining,

            // 'Used' =>  $used,
            //'credit' => $credit
            // 'Walks' => $walks,
            // 'Additional_walks' => $additional_walks,
            // 'Extra_visits' => $extra_visits
        );
        $total+=$remaining;
    }

    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Credits retrive successfully.';
    $response['data']=array(
        'company_id' => $company_id,
        'client_id' => $client_id,
        'pet_id' =>$pet_id,
        'service' => $service_data,
        'Total' => $total);


    echoResponse(200, $response);
});



// /*
// *
// * credit calculation based on appointments.
// */

// $app->post('/:id/creditcalc', function($id) use ($app)
// {
//         verifyFields(array('client_id'));

//                 $response['error_code'] = 1;
//                 $response['message'] = 'No Credit List found';
//                 $response['status'] = false;
//                 $response['data']=0;

//     $client_id=$app->request->post('client_id');
//     $company_id=$id;
//     $creditData=[];


//      $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
//    $ab=date('Y-m-d');


// foreach ($aa as $val)
// {

//     $service_id1=$val->service_id;
//     $test1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1}"));

//             if(count($test1) == 0)
//               {

//                 $credit = new Credits();
//                 $credit->company_id = $company_id;
//                 $credit->client_id = $client_id;
//                 $credit->service_id = $service_id1;
//                 $credit->credits = 0;
//                 $credit->paid_amount = 0;
//                 $credit->old_amount = 0;
//                 $credit->date_of_payment = null;
//                 $credit->last_check = $ab;
//                 $credit->remaining=0;
//                 $credit->save();
//                 $credit->credit_id = (int) $credit->credit_id;
//               }
//               $test = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1}"));

//                     $check_date=date('Y-m-d H:i:s',strtotime($test->check_date));  /*chenge strat_date to check_date*/

//                     $p_amt=$test->paid_amount;
//                     $rem=$test->remaining;
//                     $cd=date('Y-m-d H:i:s');

//                     if(($p_amt == 0 && $rem == 0 && $check_date == $cd) || ($p_amt == 0 && $rem == 0 && $check_date < $cd))
//                         {
//                             $walks = 0;
//                             $additional_walks = 0;
//                                     $extra_visits = 0;
//                         }else{
//                     $appoint = Appointment::find_by_sql("SELECT visits AS v, visit_hours AS vh FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$check_date' and '$cd'");

//                                    $walks = 0;
//                                     $additional_walks = 0;
//                                     $extra_visits = 0;

//                          foreach ($appoint as  $value1)
//                                 {

//                                     $walks += count($value1);


//                                     if($walks == 0.5)
//                                     {
//                                     	$additional_walks += 1-1;
//                                     }else{
//                                     	$additional_walks += (float)($value1->vh)-1;
//                                 	}
//                                     $extra_visits += (float)($value1->v)-1;

//                                 }

//                             }


//                         $service=Service::find(array("conditions" => "service_id = $service_id1"));
//                         $service_name=$service->service_name;

//                         $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1}"));

//                         if (count($creditCheck)>0)

//                         {
//                                      foreach ($creditCheck as  $valu)
//                                      {
//                                           $last_date=$valu->last_check;
//                                           $credits2=(float)$valu->paid_amount;
//                                           $remains=$valu->remaining;

//                                     }


//                                 $last_check=date('Y-m-d',strtotime($last_date));


//                                     $net=Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id1}"));
//                                 if(isset($net))
//                                 {
//                                         $act_price=$net->full_hour_price;
//                                         if($act_price == 0)
//                                             {
//                                                 $credit=0;
//                                             }
//                                             else{
//                                                     $credit=(float)($credits2/$act_price);
//                                                 }

//                                 }else{
//                                         $response['error_code'] = 1;
//                                         $response['message'] = 'you have not set price for this service';
//                                         $response['status'] = false;
//                                      }


//                    if($last_check != $ab)
//                     {

//                         $datetime = new DateTime($last_check);
//                         $datetime->modify('+1 day');
//                         $l_check=$datetime->format('Y-m-d');

//                         $appoint = Appointment::find_by_sql("SELECT price as p FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab'");


//                         if(count($appoint)>0)
//                             {
//                             	$t_price=0;

//                                 foreach ($appoint as  $value1)
//                                 {
//                                     $t_price += $value1->p;


//                                     $total=(float)$credits2;

//                                                 if($act_price == 0)
//                                                 {
//                                                     $credit=0;
//                                                 }
//                                                 else{
//                                                      $credit=number_format((float)($total/$act_price),1,'.','');
//                                                 }


//                                     $remaining=$remains;

//                                     $remaining-=(float)$t_price;
//                                      $used=(float)$total - $remaining;

//                                     added extra for make all field zero when remaining is 0
//                                     if($remaining == 0)
//                                    {

//                             $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1}"));


//                                     $creditCheck4->check_date=date('Y-m-d H:i:s');
//                                     $creditCheck4->paid_amount=0;
//                                     $creditCheck4->credits=0;

//                                     $creditCheck4->save();

//                                     $credit =0;
//                                     $used =0;
//                                     $total =0;
//                                     $walks =0;
//                                     $additional_walks =0;
//                                     $extra_visits =0;
//                                    }


//                                    $check_amt = $credit - $walks;

//                                    /*For log status dynamically*/
//                                    $status='';

//                                    if($remaining == 0)
//                                    {
//                                         $status = 'Completed';
//                                    }else{
//                                         $status = 'Active';
//                                    }
//                                    /*end*/

//                                     $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
//                                     $pet_name = $pet->pet_name;
//                                     $log = new Log();
//                                     $log->company_id = $company_id;
//                                     $log->client_id = $client_id;
//                                     $log->pet_name = $pet_name;
//                                     $log->date_of_transaction = date('Y-m-d H:i:s');
//                                     $log->l_status = $status;
//                                     $log->amount = $value1->p;
//                                     $log->l_flag = "Deducted";
//                                     $log->save();
//                                     $log->log_id = (int) $log->log_id;

//                                 }
//                                 $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1}"));
//                                 if(count($creditCheck1) >0)
//                                 {
//                                     $creditCheck1->last_check=date('Y-m-d');
//                                     $creditCheck1->credits=$credit;
//                                     $creditCheck1->remaining=$remaining;
//                                     $creditCheck1->save();
//                                 }
//                         }else{
//                              $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1}"));


// 								if(count($creditCheck1) >0)
//                                 {
//                                     $creditCheck1->last_check=$ab;
//                                      if($creditCheck1->remaining == 0)
//                                     {
//                                      $creditCheck1->credits=0;
//                                      $creditCheck1->paid_amount=0;

//                                      $creditCheck1->date_of_payment=null;

//                                     }


//                                     $creditCheck1->save();
//                                 }




//                              $total=(float)$credits2;
//                             $remaining=(float)$remains;
//                             $used=(float)$total - $remaining;

//                             if($act_price == 0)
//                                                 {
//                                                     $credit=0;
//                                                 }
//                                                 else{
//                                                      $credit=number_format((float)($total/$act_price),1,'.','');
//                                                 }

//                             }

//                 }else{
//                              $total=(float)$credits2;

//                             $remaining=(float)$remains;
//                             $used=(float)$total - $remaining;
//                             if($act_price == 0)
//                                                 {
//                                                     $credit=0;
//                                                 }
//                                                 else{
//                                                      $credit=number_format((float)($total/$act_price),1,'.','');
//                                                 }


//                             }


//             }else{


//                             $credit = new Credits();
//                             $credit->company_id = $company_id;
//                             $credit->client_id = $client_id;
//                             $credit->service_id = $service_id1;
//                             $credit->credits = 0;
//                             $credit->paid_amount = 0;
//                             $credit->old_amount = 0;
//                             $credit->date_of_payment = null;
//                             $credit->last_check = $ab;
//                             $credit->remaining=0;
//                             $credit->save();
//                             $credit->credit_id = (int) $credit->credit_id;

//                             $totalcredit=0;
//                             $remaining=0;
//                             $used=0;
//             }




//             $creditData[]=array(
//                           'company_id' => $company_id,
//                           'client_id' => $client_id,
//                           'service_name' => $service_name,
//                           'service_id' =>$service_id1,
//                           'Total' => $total,
//                           'Used' =>  $used,
//                           'remaining' => $remaining,
//                           'credit' => $credit,
//                           'Walks' => $walks,
//                           'Additional_walks' => $additional_walks,
//                           'Extra_visits' => $extra_visits
//                                 );
// }

//             $response['error_code'] = 0;
//             $response['status'] = true;
//             $response['message'] = 'Credits retrive successfully.';
//             $response['data']=$creditData;



//     echoResponse(200, $response);
// });



/*
* credits add api
*/

$app->post('/:id/creditsnew',function($id) use ($app)
{
    verifyFields(array('client_id','paid_amount'));

    $response['error_code'] = 1;
    $response['message'] = 'something went wrong..pls try again';
    $response['status'] = false;

    $client_id = $app->request->post('client_id');
    $paid_amount = $app->request->post('paid_amount');
//$service_id=$app->request->post('service_id');
    $date_of_payment=date('Y-m-d');
    $company_id=$id;

// $net=Price::find(array("conditions" => "company_id={$id} AND client_id={$client_id} AND service_id={$service_id}"));

//               if(isset($net)){
//                 $act_price=$net->full_hour_price;

//                 $credit1=number_format((float)($paid_amount/$act_price),1,'.','');
//                 //number_format((float)$a,2,'.','');
//                   }
//                   else{
//                 $response['error_code'] = 1;
//                 $response['message'] = 'you have not set price for this service';
//                 $response['status'] = false;
//                   }


    //Credits::transaction(function() use($app, $company_id,$client_id, $credit1, $paid_amount, $date_of_payment) {
    $creditCheck = Credits1::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id}"));






    if (count($creditCheck) > 0) {

        //$precredit=$creditCheck->credits;
        $remaining=$creditCheck->remaining;
        $preamount=$creditCheck->paid_amount;

        $creditCheck->company_id = $company_id;
        $creditCheck->client_id = $client_id;
        //$creditCheck->credits = (float)$precredit+$credit1;
        $creditCheck->paid_amount = $preamount+$paid_amount;
        $creditCheck->old_amount = $preamount;
        $creditCheck->date_of_payment = $date_of_payment;
        //$creditCheck->service_id = $service_id;
        $creditCheck->r_flag = 0;


        $creditCheck->remaining =  (float)$remaining+$paid_amount;
        if($creditCheck->remaining == 0)
        {
            //$creditCheck->credits =0;
            $creditCheck->paid_amount=0;
            $used=0;
            //$creditCheck->date_of_payment=NULL;
        }
        $creditCheck->save();
        $creditCheck->creditnew_id = (int) $creditCheck->creditnew_id;

        if ($creditCheck->creditnew_id > 0) {
            $aa=$creditCheck->date_of_payment;

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Credits Successfully added.';
            $response['data'] = array(
                'credit_id' => $creditCheck->creditnew_id,
                'company_id' =>  $creditCheck->company_id,
                'client_id' =>  $creditCheck->client_id,
                //'service_id' => $creditCheck->service_id,
                //'credits' => $creditCheck->credits,
                'paid_amount' => $creditCheck->paid_amount,
                'date_of_payment' => $aa->format('Y-m-d'),
                'remaining' => $creditCheck->remaining,
            );
        }

        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
        $pet_name = $pet->pet_name;
        $log = new Log();
        $log->company_id = $company_id;
        $log->client_id = $client_id;
        $log->pet_name = $pet_name;
        $log->date_of_transaction = date('Y-m-d H:i:s');
        $log->l_status = "Active";
        $log->amount = $paid_amount;
        $log->l_flag = "Added";
        //$log->date=date('Y-m-d');
        $log->save();
        $log->log_id = (int) $log->log_id;
        //echoResponse(200, $response);
    } else {
        /*
         *  Stroing to table
         */

        $credit = new Credits1();
        $credit->company_id = $company_id;
        $credit->client_id = $client_id;
        //$credit->service_id = $service_id;
        // $credit->credits = (float)$credit1;
        $credit->paid_amount = $paid_amount;
        $credit->old_amount = 0;
        $credit->date_of_payment = $date_of_payment;
        $credit->r_flag = 0;
        $credit->save();
        $credit->creditnew_id = (int) $credit->creditnew_id;




        if ($credit->creditnew_id > 0) {
            $ab=$credit->date_of_payment;

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'credits succesfully added.';
            $response['data'] = array(
                'credit_id' => $credit->creditnew_id,
                'company_id' => $credit->company_id,
                'client_id' => $credit->client_id,
                //'service_id' => $credit->service_id,
                //'credits' =>$credit->credits,
                'paid_amount' => $credit->paid_amount,
                'date_of_payment' => $ab->format('Y-m-d'),
                //'remaining' => $creditCheck->remaining,
            );

        }
        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
        $pet_name = $pet->pet_name;
        $log = new Log();
        $log->company_id = $company_id;
        $log->client_id = $client_id;
        $log->pet_name = $pet_name;
        $log->date_of_transaction = date('Y-m-d H:i:s');
        $log->l_status = "Active";
        $log->amount = $paid_amount;
        $log->l_flag = "Added";
        $log->save();
        $log->log_id = (int) $log->log_id;
    }

    echoResponse(200, $response);
    // });

});



/*
* credits add api
*/

$app->post('/:id/credits',function($id) use ($app)
{
    verifyFields(array('client_id','paid_amount','service_id','pet_id'));

    $response['error_code'] = 1;
    $response['message'] = 'something went wrong..pls try again';
    $response['status'] = false;

    $client_id = $app->request->post('client_id');
    $paid_amount = $app->request->post('paid_amount');
    $service_id = $app->request->post('service_id');
    $pet_id = $app->request->post('pet_id');
    $date_of_payment=date ('Y-m-d H:i:s');
    $company_id=$id;

// $net=Pricenew::find(array("conditions" => "company_id={$id} AND client_id={$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

//               if(isset($net)){
//                 $act_price=$net->full_hour_price;

//                 $credit1=number_format((float)($paid_amount/$act_price),1,'.','');

//                   }
//                   else{
//                     $response['error_code'] = 1;
//                 $response['message'] = 'you have not set price for this service';
//                 $response['status'] = false;
//                   }

    $creditCheck = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

    if (count($creditCheck) > 0) {

        //$precredit=$creditCheck->credits;
        $remaining=$creditCheck->remaining;
        $preamount=$creditCheck->paid_amount;

        $creditCheck->company_id = $company_id;
        $creditCheck->client_id = $client_id;
        $creditCheck->pet_id = $pet_id;
        //$creditCheck->credits = (float)$precredit+$credit1;
        $creditCheck->paid_amount = $preamount+$paid_amount;
        $creditCheck->old_amount = $preamount;
        $creditCheck->date_of_payment = $date_of_payment;
        $creditCheck->service_id = $service_id;
        $creditCheck->r_flag = 0;


        $creditCheck->remaining =  (float)$remaining+$paid_amount;
        // echo $creditCheck->remaining;
        // die;
        if($creditCheck->remaining == 0)
        {
            //$creditCheck->credits = 0;
            $creditCheck->paid_amount = 0;
            //$creditCheck->check_date = $date_of_payment;
        }
        $creditCheck->save();
        $creditCheck->credit_id = (int) $creditCheck->credit_id;

        $creditCheck1 = Credits::find('all',array("conditions" => "company_id = {$id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

        if($creditCheck1)
        {
            $total=0;
            foreach ($creditCheck1 as $val) {
                $total+=$val->remaining;
            }
        }

        if ($creditCheck->credit_id > 0) {
            $aa=$creditCheck->date_of_payment;

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Credits Successfully added.';
            $response['data'] = array(
                'credit_id' => $creditCheck->credit_id,
                'company_id' =>  $creditCheck->company_id,
                'client_id' =>  $creditCheck->client_id,
                'pet_id' => $creditCheck->pet_id,
                'service_id' => $creditCheck->service_id,
                //'credits' => $creditCheck->credits,
                'paid_amount' => $creditCheck->paid_amount,
                'date_of_payment' => $aa->format('Y-m-d'),
                'remaining' => $creditCheck->remaining,
                'total' => $total
            );
        }

        $pet=Pet::find($pet_id);
        $pet_name = $pet->pet_name;
        $log = new transactionlog();
        $log->company_id = $company_id;
        $log->client_id = $client_id;
        $log->pet_id = $pet_id;
        $log->service_id = $service_id;
        $log->pet_name = $pet_name;
        $log->date_of_transaction = date('Y-m-d H:i:s');
        $log->type = "Topup";
        $log->amount = $paid_amount;
        $log->l_flag = "Added";
        $log->old_value = $remaining;
        $log->new_value = $creditCheck->remaining;
        $log->save();
        $log->log_id = (int) $log->log_id;

        // $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
        //                              $pet_name = $pet->pet_name;
        //                              $log = new Log();
        //                              $log->company_id = $company_id;
        //                              $log->client_id = $client_id;
        //                              $log->pet_name = $pet_name;
        //                              $log->date_of_transaction = date('Y-m-d H:i:s');
        //                              $log->l_status = "Active";
        //                              $log->amount = $paid_amount;
        //                              $log->l_flag = "Added";
        //                              $log->save();
        //                              $log->log_id = (int) $log->log_id;

    } else {

        /*
         *  Stroing to table
         */

        $credit = new Credits();
        $credit->company_id = $company_id;
        $credit->client_id = $client_id;
        $credit->pet_id = $pet_id;
        $credit->service_id = $service_id;
        //$credit->credits = (float)$credit1;
        $credit->paid_amount = $paid_amount;
        $credit->old_amount = 0;
        $credit->remaining = $paid_amount;
        $credit->date_of_payment = $date_of_payment;
        $credit->save();
        $credit->credit_id = (int) $credit->credit_id;

        $creditCheck1 = Credits::find('all',array("conditions" => "company_id = {$id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

        if($creditCheck1)
        {
            $total=0;
            foreach ($creditCheck1 as $val) {
                $total+=$val->remaining;
            }
        }


        if ($credit->credit_id > 0) {
            $ab=$credit->date_of_payment;

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'credits succesfully added.';
            $response['data'] = array(
                'credit_id' => $credit->credit_id,
                'company_id' => $credit->company_id,
                'client_id' => $credit->client_id,
                'pet_id' => $credit->pet_id,
                'service_id' => $credit->service_id,
                //'credits' =>$credit->credits,
                'paid_amount' => $credit->paid_amount,
                'date_of_payment' => $ab->format('Y-m-d'),
                'remaining' => $credit->remaining,
                'total' => $total
            );

        }

        $pet=Pet::find($pet_id);
        $pet_name = $pet->pet_name;
        $log = new transactionlog();
        $log->company_id = $company_id;
        $log->client_id = $client_id;
        $log->pet_id = $pet_id;
        $log->service_id = $service_id;
        $log->pet_name = $pet_name;
        $log->date_of_transaction = date('Y-m-d H:i:s');
        $log->type = "Topup";
        $log->amount = $paid_amount;
        $log->l_flag = "Added";
        $log->old_value = 0;
        $log->new_value = $paid_amount;
        $log->save();
        $log->log_id = (int) $log->log_id;
        // $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
        //                              $pet_name = $pet->pet_name;
        //                              $log = new Log();
        //                              $log->company_id = $company_id;
        //                              $log->client_id = $client_id;
        //                              $log->pet_name = $pet_name;
        //                              $log->date_of_transaction = date('Y-m-d H:i:s');
        //                              $log->l_status = "Active";
        //                              $log->amount = $paid_amount;
        //                              $log->l_flag = "Added";
        //                              $log->save();
        //                              $log->log_id = (int) $log->log_id;
    }

    echoResponse(200, $response);


});

/*
Edit credit ...
*/
$app->post('/:id/editcredit',function($id) use ($app)
{
    verifyFields(array('client_id','pet_id','amount','service_id'));

    $client_id= $app->request->post('client_id');
    $pet_id = $app->request->post('pet_id');
    $amount = $app->request->post('amount');
    $service_id = $app->request->post('service_id');
    //$ab=date()
    $edit=credits::find(array("conditions" => "company_id={$id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));

    $pre_amount = $edit->paid_amount;
    $edit->paid_amount = $amount;
    $edit->remaining = $amount;
    $edit->save();

    $creditCheck1 = Credits::find('all',array("conditions" => "company_id = {$id} AND client_id = {$client_id} AND pet_id={$pet_id}  AND service_id={$service_id} "));

    if($creditCheck1)
    {
        $total=0;
        foreach ($creditCheck1 as $val) {
            $total+=$val->remaining;
        }
    }

    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'credits succesfully edited.';
    $response['data'] = array(
        'company_id' => $id,
        'client_id' => $client_id,
        'pet_id' => $pet_id,
        'service_id' => $service_id,
        //'credits' =>$credit->credits,
        'paid_amount' => $amount,
        'date_of_payment' => date('Y-m-d'),
        'remaining' => $amount,
        'total' => $total
    );


//$edit=credits::find('all',array("conditions" => "company_id={$id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
    $l_flag='';
    if($pre_amount > $amount)
    {
        $l_flag = "Deducted";
        $old_value1 = $amount;
        $new_value1 = $amount-$total;

    }else{
        $old_value1 = $amount;
        $new_value1 = $amount+$total;
        $l_flag = "Added";
    }

    $pet=Pet::find($pet_id);
    $pet_name = $pet->pet_name;
    $log = new transactionlog();
    $log->company_id = $id;
    $log->client_id = $client_id;
    $log->pet_id = $pet_id;
    $log->service_id = $service_id;
    $log->pet_name = $pet_name;
    $log->date_of_transaction = date('Y-m-d H:i:s');
    $log->type = "Correction";
    $log->amount = $amount;
    $log->l_flag = $l_flag;
    $log->old_value = $old_value1;
    $log->new_value = $new_value1;
    $log->save();
    $log->log_id = (int) $log->log_id;

    echoResponse(200, $response);
});


/*
Update credits first time only..

*/
$app->post('/:id/updatecreditnew',function($id) use ($app)
{
    verifyFields(array('client_id','paid_amount','remaining'));
    $client_id = $app->request->post('client_id');
    //$service_id = $app->request->post('service_id');
    $paid_amount = $app->request->post('paid_amount');
    $remaining = $app->request->post('remaining');

// $net=Price::find(array("conditions" => "company_id={$id} AND service_id={$service_id}"));
//               if(isset($net)){
//                 $act_price=$net->full_hour_price;

//                 $credit=number_format((float)($paid_amount/$act_price),1,'.','');

//                   }
//                   else{
//                     $response['error_code'] = 1;
//                 $response['message'] = 'you have not set price for this service';
//                 $response['status'] = false;
//                   }
    $used=$paid_amount - $remaining;

    $creditCheck = Credits1::find(array("conditions" => "company_id = {$id} AND client_id = {$client_id}"));

    $flag = $creditCheck->f_flag;
    $p_amount = $creditCheck->paid_amount;
    $remain = $creditCheck->remaining;

    if($creditCheck)
    {

        $precredit=$creditCheck->credits;
        if($flag == 0 && $p_amount == 0 && $remain == 0)
        {
            $creditCheck->company_id = $id;
            $creditCheck->client_id = $client_id;
            //$creditCheck->service_id = $service_id;
            $creditCheck->paid_amount = $paid_amount;
            $creditCheck->last_check = date('Y-m-d');
            $creditCheck->remaining =  (float)$remaining;
            //$creditCheck->credits = $precredit + $credit;
            $creditCheck->f_flag = 1;
            $creditCheck->r_flag = 1;
            $creditCheck->save();
            $creditCheck->creditnew_id = (int) $creditCheck->creditnew_id;



            if ($creditCheck->creditnew_id > 0)
            {


                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Amount Successfully added.';
                $response['data'] = array(
                    'credit_id' => $creditCheck->creditnew_id,
                    'company_id' =>  $creditCheck->company_id,
                    'client_id' =>  $creditCheck->client_id,
                    //'service_id' => $creditCheck->service_id,
                    //'credits' => $creditCheck->credits,
                    'paid_amount' => $creditCheck->paid_amount,
                    'used' => $used,
                    'remaining' => $creditCheck->remaining,
                );

            }

        }else{
            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'You can update only once.';

        }
    }
    else{

        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Something went wrong please try again.';
        $response['data'] =0;
    }
    echoResponse(200, $response);

});


/*
Update credits first time only..

*/
$app->post('/:id/updatecredit',function($id) use ($app)
{
    verifyFields(array('client_id','service_id','paid_amount','remaining','pet_id'));
    $client_id = $app->request->post('client_id');
    $service_id = $app->request->post('service_id');
    $paid_amount = $app->request->post('paid_amount');
    $remaining = $app->request->post('remaining');
    $pet_id = $app->request->post('pet_id');

// $net=Price::find(array("conditions" => "company_id={$id} AND service_id={$service_id}"));
//               if(isset($net)){
//                 $act_price=$net->full_hour_price;

//                 $credit=number_format((float)($paid_amount/$act_price),1,'.','');

//                   }
//                   else{
//                     $response['error_code'] = 1;
//                 $response['message'] = 'you have not set price for this service';
//                 $response['status'] = false;
//                   }
//                   $used=$paid_amount - $remaining;

    $creditCheck = Credits::find(array("conditions" => "company_id = {$id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

    $flag = $creditCheck->flag;
    $p_amount = $creditCheck->paid_amount;
    $remain = $creditCheck->remaining;

    if($creditCheck)
    {
        //$precredit=$creditCheck->credits;
        if($flag == 0 && $p_amount == 0 && $remain == 0)
        {
            $creditCheck->company_id = $id;
            $creditCheck->client_id = $client_id;
            $creditCheck->service_id = $service_id;
            $creditCheck->paid_amount = $paid_amount;
            $creditCheck->last_check = date('Y-m-d');
            $creditCheck->remaining =  (float)$remaining;
            //$creditCheck->credits = $precredit + $credit;
            $creditCheck->flag = 1;
            $creditCheck->r_flag = 1;
            $creditCheck->save();
            $creditCheck->credit_id = (int) $creditCheck->credit_id;



            if ($creditCheck->credit_id > 0)
            {


                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Amount Successfully added.';
                $response['data'] = array(
                    'credit_id' => $creditCheck->credit_id,
                    'company_id' =>  $creditCheck->company_id,
                    'client_id' =>  $creditCheck->client_id,
                    'pet_id' => $creditCheck->pet_id,
                    'service_id' => $creditCheck->service_id,
                    //'credits' => $creditCheck->credits,
                    'paid_amount' => $creditCheck->paid_amount,
                    //'used' => $used,
                    'remaining' => $creditCheck->remaining,
                );

            }

        }else{
            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'You can update only once.';

        }
    }
    else{

        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Something went wrong please try again.';
        $response['data'] =0;
    }
    echoResponse(200, $response);

});

$app->post('/:id/transactionlognew',function($id) use ($app)
{

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Log not found.';

    verifyFields(array('client_id','pet_id'));
    $client_id = $app->request->post('client_id');
    //$service_id = $app->request->post('service_id');
    $pet_id = $app->request->post('pet_id');

// $service=CompanyService::find('all',array('conditions' => "company_id='{$id}'"));
// foreach ($service as $value1)
//         {


    //cnaging oder from date_of_transaction to log_id
    $log = transactionlog::find_by_sql("SELECT * FROM `tbl_newtransaction_log` where company_id=$id AND client_id=$client_id AND pet_id=$pet_id order by log_id DESC limit 30");



    if(count($log)>0)
    {
        foreach ($log as $key => $value)
        {
            //var_dump($value->old_value);
            //var_dump($value->new_value); die;

            $aa=Service::find($value->service_id);
            $log_id=$value->log_id;
            $company_id=$value->company_id;
            $client_id=$value->client_id;
            $pet_id = $value->pet_id;
            $service_id = $value->service_id;
            $service_name = $aa->service_name;
            $pet_name=$value->pet_name;
            $date_of_transaction=date('d-m-Y',strtotime($value->date_of_transaction));
            $type=$value->type;
            $amount=$value->amount;
            $l_flag=$value->l_flag;
            $old_value=$value->old_value;
            $new_value=$value->new_value;

            $logData[]=array(
                'log_id' => $log_id,
                'company_id' => $company_id,
                'client_id' => $client_id,
                'pet_id' => $pet_id,
                'service_id' => $service_id,
                'service_name' => $service_name,
                'pet_name' => $pet_name,
                'date_of_transaction' =>$date_of_transaction,
                'type' => $type,
                'amount' =>  $amount,
                'old_value' =>  $old_value,
                'new_value' =>  $new_value,
                'l_flag' => $l_flag

            );


        }
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Log retrive successfully.';
        $response['data']=$logData;


    }
//}
    echoResponse(200, $response);
});

$app->post('/:id/transactionlogrange', function($id) use ($app)
{
    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Log not found.';

    verifyFields(array('start_date', 'end_date', 'client_id', 'pet_id'));

    $client_id = $app->request->post('client_id');
    $start_date = date('Y-m-d',strtotime($app->request->post('start_date')));
    $end_date = date('Y-m-d',strtotime($app->request->post('end_date')));
    //$service_id = $app->request->post('service_id');
    $pet_id = $app->request->post('pet_id');

    $service=CompanyService::find('all',array('conditions' => "company_id='{$id}'"));
    foreach ($service as $value1)
    {

        $log = transactionlog::find_by_sql("SELECT * FROM `tbl_newtransaction_log` where company_id=$id AND client_id=$client_id AND pet_id=$pet_id AND service_id={$value1->service_id} AND date_of_transaction BETWEEN '$start_date' AND '$end_date' order by date_of_transaction ");

        if(count($log)>0)
        {
            foreach ($log as $key => $value)
            {

                $aa=Service::find($value->service_id);
                $log_id=$value->log_id;
                $company_id=$value->company_id;
                $client_id=$value->client_id;
                $pet_id = $value->pet_id;
                $service_id = $value->service_id;
                $service_name = $aa->service_name;
                $pet_name=$value->pet_name;
                $date_of_transaction=date('d-m-Y',strtotime($value->date_of_transaction));
                $type=$value->type;
                $amount=$value->amount;
                $l_flag=$value->l_flag;
                $old_value=$value->old_value;
                $new_value=$value->new_value;

                $logData[]=array(
                    'log_id' => $log_id,
                    'company_id' => $company_id,
                    'client_id' => $client_id,
                    'pet_id' => $pet_id,
                    'service_id' => $service_id,
                    'service_name' => $service_name,
                    'pet_name' => $pet_name,
                    'date_of_transaction' =>$date_of_transaction,
                    'type' => $type,
                    'amount' =>  $amount,
                    'old_value' =>  $old_value,
                    'new_value' =>  $new_value,
                    'l_flag' => $l_flag

                );


            }
            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Log retrive successfully.';
            $response['data']=$logData;


        }
    }
    echoResponse(200, $response);


});


/*api for log*/
$app->post('/:id/:status/log',function($id,$status) use($app)
{
    verifyFields(array('client_id'));

    $client_id = $app->request->post('client_id');
    $log=[];
    if($status == 'active')
    {
        $log = Log::find_by_sql("SELECT *  FROM `tbl_transaction_log` WHERE `company_id` = $id and client_id= $client_id and date_of_transaction > '2018-02-03' and `l_status`='$status' and `l_flag`='Added' order by date_of_transaction DESC");
    }else{
        $log = Log::find_by_sql("SELECT *  FROM `tbl_transaction_log` WHERE `company_id` = $id and client_id= $client_id and date_of_transaction > '2018-02-03' and `l_status`='$status' order by date_of_transaction DESC");
    }


    if(count($log)>0)
    {
        foreach ($log as $key => $value)
        {
            $log_id=$value->log_id;
            $company_id=$value->company_id;
            $client_id=$value->client_id;
            $pet_name=$value->pet_name;
            $date_of_transaction=date('d-m-Y',strtotime($value->date_of_transaction));
            $l_status=$value->l_status;
            $amount=$value->amount;
            $l_flag=$value->l_flag;

            $logData[]=array(
                'log_id' => $log_id,
                'company_id' => $company_id,
                'client_id' => $client_id,
                'pet_name' => $pet_name,
                'date_of_transaction' =>$date_of_transaction,
                'l_status' => $l_status,
                'amount' =>  $amount,
                'l_flag' => $l_flag

            );


        }
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Credits retrive successfully.';
        $response['data']=$logData;
    }else{
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Log not found.';

    }
    echoResponse(200, $response);
});

/*api for revert amount*/

$app->post('/:id/revert',function($id) use ($app)
{
    verifyFields(array('client_id','service_id','pet_id'));
    $client_id = $app->request->post('client_id');
    $service_id = $app->request->post('service_id');
    $pet_id = $app->request->post('pet_id');






    $creditCheck = Credits::find(array("conditions" => "company_id = {$id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


    if(count($creditCheck)>0)
    {
        if($creditCheck->r_flag == 0)
        {
            $new_amount = $creditCheck->paid_amount;
            $old_amount = $creditCheck->old_amount;

            // $net=Price::find(array("conditions" => "company_id={$id} AND service_id={$service_id}"));
            //   if(isset($net)){
            //     $act_price=$net->full_hour_price;

            //     $credit=number_format((float)($old_amount/$act_price),1,'.','');

            //       }
            //       else{
            //         $response['error_code'] = 1;
            //     $response['message'] = 'you have not set price for this service';
            //     $response['status'] = false;
            //       }

            $r_amt = $new_amount > $old_amount? $new_amount - $old_amount : $old_amount - $new_amount;
            $old_remain = $creditCheck->remaining;
            $new_remain = $old_remain - $r_amt;
            $used = $old_amount - $new_remain;

            $creditCheck->paid_amount = $old_amount;
            $creditCheck->remaining = $new_remain;
            // if($new_remain == 0){
            //    // $creditCheck->check_date=date('Y-m-d');
            // }

            //$creditCheck->credits = $credit;
            $creditCheck->r_flag = 1;

            $creditCheck->save();
            $creditCheck->credit_id = (int) $creditCheck->credit_id;


            $pet=Pet::find($pet_id);
            $pet_name = $pet->pet_name;
            $log1 = new transactionlog();
            $log1->company_id = $id;
            $log1->client_id = $client_id;
            $log1->pet_id = $pet_id;
            $log1->service_id = $service_id;
            $log1->pet_name = $pet_name;
            $log1->date_of_transaction = date('Y-m-d H:i:s');
            $log1->type = "Revert";
            $log1->amount = $r_amt;
            $log1->l_flag = "Deducted";
            $log1->old_value = $old_amount;
            $log1->new_value = $new_amount;
            $log1->save();
            $log1->log_id = (int) $log1->log_id;

            // $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
            //         $pet_name = $pet->pet_name;
            //         $log = new Log();
            //         $log->company_id = $id;
            //         $log->client_id = $client_id;
            //         $log->pet_name = $pet_name;
            //         $log->date_of_transaction = date('Y-m-d H:i:s');
            //         $log->l_status = "Cancelled";
            //         $log->amount = $r_amt;
            //         $log->l_flag = "Deducted";
            //         $log->save();
            //         $log->log_id = (int) $log->log_id;

            $creditCheck1 = Credits::find('all',array("conditions" => "company_id = {$id} AND client_id = {$client_id} AND pet_id={$pet_id}"));

            if($creditCheck1)
            {
                $total=0;
                foreach ($creditCheck1 as $val) {
                    $total+=$val->remaining;
                }
            }

            $service=Service::find($service_id);

            if ($creditCheck->credit_id > 0)
            {


                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Amount Successfully revert.';
                $response['data'] = array(
                    'credit_id' => $creditCheck->credit_id,
                    'company_id' => $creditCheck->company_id,
                    'client_id' => $creditCheck->client_id,
                    'pet_id' => $creditCheck->pet_id,
                    'service_id' =>$creditCheck->service_id,
                    'service_name' => $service->service_name,
                    'remaining' => $new_remain,
                    'total' => $total,
                    //'Used' =>  $used,
                    //'credit' => $credit

                );
            }
        }else{
            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'You can undo last transaction only once.';
        }

    }
    else{
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Record not found.';
    }

    echoResponse(200, $response);
});


/*api for revert amount*/

$app->post('/:id/revertnew',function($id) use ($app)
{
    verifyFields(array('client_id'));
    $client_id = $app->request->post('client_id');
    //$service_id = $app->request->post('service_id');


    $creditCheck = Credits1::find(array("conditions" => "company_id = {$id} AND client_id = {$client_id}"));


    if(count($creditCheck)>0)
    {
        if($creditCheck->r_flag == 0)
        {
            $new_amount = $creditCheck->paid_amount;
            $old_amount = $creditCheck->old_amount;

            // $net=Price::find(array("conditions" => "company_id={$id} AND service_id={$service_id}"));
            //   if(isset($net)){
            //     $act_price=$net->full_hour_price;

            //     $credit=number_format((float)($old_amount/$act_price),1,'.','');

            //       }
            //       else{
            //         $response['error_code'] = 1;
            //     $response['message'] = 'you have not set price for this service';
            //     $response['status'] = false;
            //       }

            $r_amt = $new_amount > $old_amount? $new_amount - $old_amount : $old_amount - $new_amount;
            $old_remain = $creditCheck->remaining;
            $new_remain = $old_remain - $r_amt;
            $used = $old_amount - $new_remain;

            $creditCheck->paid_amount = $old_amount;
            $creditCheck->remaining = $new_remain;
            if($new_remain == 0){
                $creditCheck->check_date=date('Y-m-d');
            }

            //$creditCheck->credits = $credit;
            $creditCheck->r_flag = 1;

            $creditCheck->save();
            $creditCheck->creditnew_id = (int) $creditCheck->creditnew_id;

            $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
            $pet_name = $pet->pet_name;
            $log = new Log();
            $log->company_id = $id;
            $log->client_id = $client_id;
            $log->pet_name = $pet_name;
            $log->date_of_transaction = date('Y-m-d H:i:s');
            $log->l_status = "Cancelled";
            $log->amount = $r_amt;
            $log->l_flag = "Deducted";
            $log->save();
            $log->log_id = (int) $log->log_id;

            if ($creditCheck->creditnew_id > 0)
            {


                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Amount Successfully revert.';
                $response['data'] = array(
                    'credit_id' => $creditCheck->creditnew_id,
                    'company_id' => $creditCheck->company_id,
                    'client_id' => $creditCheck->client_id,
                    // 'service_id' =>$creditCheck->service_id,
                    'Total' => $old_amount,
                    'Used' =>  $used,
                    'remaining' => $new_remain,
                    //'credit' => $credit

                );
            }
        }else{
            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'You can undo last transaction only once.';
        }

    }
    else{
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Record not found.';
    }

    echoResponse(200, $response);
});

/*
*
*  Service count list according to company_id and pet_id..


$app->post('/:id/servicelist', function($id) use ($app)
{
verifyFields(array('pet_id'));


$response['error_code'] = 1;
    $response['message'] = 'No Service List found';
    $response['status'] = false;

    $pet_id=$app->request->post('pet_id');
    $company_id=$id;
    // echo $company_id;
    $appointment_exist=Appointment::exists($company_id);
  //  var_dump($appointment_exist);
  // die;

    $service_detail=array();

             $aa=CompanyService::find('all',array('conditions' => "company_id='{$id}'"));
                        // print_r($aa);
                        // die;
                 foreach ($aa as $value) {
                            $total=Service::find('all',array('conditions' => "service_id='{$value->service_id}'"));
                                foreach ($total as $value1) {

                                    $service_name = $value1->service_name;
                                    $service_ids = $value1->service_id;

//print_r($total);
                                //$appointment=Appointment::find('all',array('conditions' => "company_id='{$id}' and pet_id={$pet_id} and service_id={$service_ids}"));
                                    $appointment=Appointment::find_by_sql("select count(*) as totl from tbl_appointments where company_id=$id and pet_id=$pet_id and service_id={$value1->service_id} and (status='accepted' or status='assign staff')");

                                    foreach ($appointment as $ke => $val) {
                                        // $total=count($val->appointment_id);
                                        $total_appointment=$val->totl;
                                       // echo $total_appointment;
                                    }
                                    // $dt=date();
                                    //
                                    $ab=date('Y/m/d');
                                    //die();
                                    $appointment1=Appointment::find_by_sql("select count(*) as ttl1 from tbl_appointments where company_id=$id and pet_id=$pet_id and service_id={$value1->service_id} and (status='accepted' or status='assign staff') and date between '(select * from tbl_appointments oerder by date asc limit 1)' and '$ab'");

                                    foreach ($appointment1 as $ke => $val1) {
                                        // $total=count($val->appointment_id);
                                       $used_appointment=$val1->ttl1;
                                //echo $used_appointment;
                                    }
                                    $ab=date('Y/m/d');
                                    //die();
                                    $appointment2=Appointment::find_by_sql("select count(*) as ttl2 from tbl_appointments where company_id=$id and pet_id=$pet_id and service_id={$value1->service_id} and (status='accepted' or status='assign staff') and date > '$ab'");

                                    foreach ($appointment2 as $ke => $val2) {
                                        // $total=count($val->appointment_id);
                                      $unused_appointment=$val2->ttl2;
                                       // echo $used_appointment;
                                    }
                                    //die;
                                }
                                $service_detail=array(
                                    'total_appointment'=>$total_appointment,
                                    'used_appointment' =>$used_appointment,
                                    'unused_appointment'=>$unused_appointment
                                );

                                 if($appointment_exist)
            {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Service list retrive successfully.';
        $response['data'][]=array(
            'company_id' => $company_id,
            'pet_id' => $pet_id,
            'service_name' => $service_name,
            'service_id' => $service_ids,
            'service_detail' =>$service_detail,
            );

        }


                        }







       // die;


    echoResponse(200, $response);
});
*/

/*
 * payment history accoridng to pet id
 */
$app->get('/:id/appointpaymentpet', function($id) use ($app) {


    $response['error_code'] = 1;
    $response['message'] = 'No Payment for this pet.';
    $response['status'] = false;



    $appointment = Appointment::find('all', array('conditions' => "pet_id = {$id} and status='accepted' or status='assign staff' "));
    //die();


    if (count($appointment) > 0) {


        $appointmentData = array();

        foreach ($appointment as $key => $value) {
            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $payment = Payment::find(array('conditions' => "appointment_id = {$value->appointment_id}"));
            if (count($payment) > 0)
            {
                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Peyment history list retrived successfully.';

                $appointmentData[] = array(
                    'appointment_id' => $value->appointment_id,
                    'status' => $value->status,
                    'company_detail'=> array(
                        'company_id' => $value->company->company_id,
                        'company_name' => $value->company->company_name,
                        'emailid' => $value->company->emailid,
                        'contact_number' => $value->company->contact_number,
                        'company_image' => $value->company->company_image != null ? COMPANY_PIC_PATH .$value->company->company_image :NULL,
                        'website' => $value->company->website,
                        'address'=>$value->company->address,
                        'about' => $value->company->about
                    ),
                    'client_detail'=> array(
                        'client_id' => $value->client->client_id,
                        'firstname' => $value->client->firstname,
                        'lastname' => $value->client->lastname,
                        'emailid'=>$value->client->emailid,
                        'profile_image'=> $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
                        'contact'=> $value->client->contact_number,
                        'address'=>$value->client->client_address,
                        'client_notes'=>$value->client->client_notes
                    ),
                    'service_name'=> $service->service_name,
                    'visits' => $value->visits,
                    'visit_hours' => $value->visit_hours,
                    'date' => date('d-m-Y',strtotime($value->date)),
                    'pet_detail' => array(
                        'pet_id' => $value->pet->pet_id,
                        'pet_name' => $value->pet->pet_name,
                        'pet_birth' => $value->pet->pet_birth,

                        'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                        'pet_age' => $value->pet->age,
                        'gender' => $value->pet->gender,
                        'pet_type' => $value->pet->pet_type,
                        'breed' => $value->pet->breed,
                        'neutered' => $value->pet->neutered,
                        'spayed' => $value->pet->spayed,
                        'injuries' => $value->pet->injuries,
                        'medical_detail' => $value->pet->medical_detail,
                        'pet_notes' => $value->pet->pet_notes
                    ),
                    'payment_detail' => array(
                        'id' => $payment->id,
                        'payment_date'=>date('d-m-Y',strtotime($payment->created_at)),
                        'price' => $value->price
                    ),
                );
            }
        }

        $response['data'] = $appointmentData;
    }
//die;
    echoResponse(200, $response);
});



/*
 * Appoinment Edit
 */
$app->post('/appointment/:id/edit', function($id) use ($app) {
//echo $id;
//    die;
    $exist = Appointment::exists($id);


    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];

    if ($exist) {

        $appointment = Appointment::find($id);
        $old_amount= $appointment->price;

        $company_id = empty($app->request->post('company_id')) ? $appointment->company_id : $app->request->post('company_id');
        $service_id = empty($app->request->post('service_id')) ? $appointment->service_id : $app->request->post('service_id');
        $date = empty($app->request->post('date')) ? $appointment->date : date('Y-m-d', strtotime($app->request->post('date')));
        $org_date=date('Y-m-d', strtotime($appointment->date));//$date = date('Y-m-d', $stringdate);
        //  $date2 = date('d-m-Y', $stringdate);
        $visits = empty($app->request->post('visits')) ? $appointment->visits : $app->request->post('visits');
        $visit_hours = empty($app->request->post('visit_hours')) ? $appointment->visit_hours : $app->request->post('visit_hours');
//$price = $app->request->post('price');
//$status = $app->request->post('status');
        $pet_id = empty($app->request->post('pet_id')) ? $appointment->pet_id : $app->request->post('pet_id');
        $message = empty($app->request->post('message')) ? $appointment->message : $app->request->post('message');
        $client_id = empty($app->request->post('client_id')) ? $appointment->client_id : (int) $app->request->post('client_id');
        $client = Client::find($client_id);
        $client_name = $client->firstname . ' ' . $client->lastname;
        $rf_company_id = $client->company_id;

        $clientCheck = Client::find($client_id);
        $flag = $clientCheck->company_id != NULL ? true : NULL;

        $today = date('Y-m-d');

        if($org_date > $today)
        {

            $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
            // print_r($price);

            $whole_visit= floor($visits);
            $whole = floor($visit_hours);

            // echo $whole_visit."</br>";
            // echo $whole;

            // whole number from
            $fraction = $visit_hours - $whole; // getting part after decimal point
// echo $fraction;
// die;
            /*
         * Price calculating
         */
            if ($fraction)
            {

                if($whole>1 && $whole_visit > 1)
                {

                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                }
                else if($whole > 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }
                else if($whole < 1 && $whole_visit == 1)
                {

                    $total = $price->half_hour_price;

                }else if($whole < 1 && $whole_visit > 1)
                {
                    $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }

            } else {
                //if($whole>1)
                //$total = $whole * $price->full_hour_price;
                if($whole>1 && $whole_visit>1)
                {
                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                }
                else if($whole>1 && $whole_visit == 1)
                {
                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                }
                else if($whole ==1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                }
                else
                {
                    $total = $price->full_hour_price;

                }
            }

            /*
         *  Stroing to table
         */

            //$appointment = new Appointment();
            $appointment->company_id = $company_id;
            $appointment->client_id = $client_id;
            $appointment->service_id = $service_id;
            $appointment->date = $date;
            $appointment->visits = $visits;
            $appointment->visit_hours = $visit_hours;
            $appointment->price = $total;

            //$appointment->status = 'accepted';					//status stored in db
            $appointment->pet_id = $pet_id;
            $appointment->message = $message;
            if (empty($rf_company_id)) {
                $appointment->created_by = 'client';
            } else {
                $appointment->created_by = 'company';
            }
            $appointment->created_at = date('Y-m-d H:i:s');
            $appointment->save();
            $appointment->appointment_id = (int) $appointment->appointment_id;


            $service = Service::find($service_id);
            $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
            /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
            if ($appointment->appointment_id > 0) {


                $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                $companyServices = array();
                foreach ($services as $companyService) {
                    $companyServices[] = array(
                        'id' => $companyService->service_id,
                        'name' => $companyService->service_name
                    );
                }
                $ownerDetail = array(
                    'client_id' => $appointment->client->client_id,
                    'firstname' => $appointment->client->firstname,
                    'lastname' => $appointment->client->lastname,
                    'emailid' => $appointment->client->emailid,
                    'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                    'contact_number' => $appointment->client->contact_number,
                    'client_address' => $appointment->client->client_address,
                    'client_notes' => $appointment->client->client_notes,
                    'player_id' => $appointment->client->player_id,
                );

                $companyDetail = array(
                    'company_id' => $appointment->company->company_id,
                    'account_id' => $appointment->company->account_id,
                    'company_name' => $appointment->company->company_name,
                    'emailid' => $appointment->company->emailid,
                    'contact_number' => $appointment->company->contact_number,
                    'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                    'website' => $appointment->company->website,
                    'address' => $appointment->company->address,
                    'about' => $appointment->company->about,
                    'services' => $companyServices,
                );

                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Appointment Successfully updated.';
                $response['data'] = array(
                    'appointment_id' => $appointment->appointment_id,
                    'company_detail' => $companyDetail,
                    'owner_detail' => $ownerDetail,
                    'isManualClient' => $flag,
                    //'compnay_id' => $appointment->company_id,
                    // 'client_id' => $client_id,
                    // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                    //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                    'service_id' => $appointment->service_id,
                    'service_name' => $service->service_name,
                    'pet_id' => $appointment->pet_id,
                    'date' => $appointment->date,
                    'visits' => $visits,
                    'visit_hours' => $visit_hours,
                    //  'price' => $appointment->price,
                    'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                    'pet_detail' => array(
                        'pet_id' => $appointment->pet->pet_id,
                        'pet_name' => $appointment->pet->pet_name,
                        'pet_birth' => $appointment->pet->pet_birth,

                        'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                        'pet_age' => $appointment->pet->age,
                        'gender' => $appointment->pet->gender,
                        'pet_type' => $appointment->pet->pet_type,
                        'breed' => $appointment->pet->breed,
                        'neutered' => $appointment->pet->neutered,
                        'spayed' => $appointment->pet->spayed,
                        'injuries' => $appointment->pet->injuries,
                        'medical_detail' => $appointment->pet->medical_detail,
                        'pet_notes' => $appointment->pet->pet_notes,
                        'latitude' => $appointment->pet->latitude,
                        'longitude' => $appointment->pet->longitude,
                    ),
                    'message' => $message,
                    'notification_flag' => 'appointment_booking'
                );

            }


            /*------------------------------------------------------------------------------------------------------------------------------------------------------*/

        }elseif($org_date == $today)
        {


            if(date('Y-m-d',strtotime($date)) == $today)
            {

                $credit_check1= Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                $lastc=date('Y-m-d',strtotime($credit_check1->last_check));
                $precredit=$credit_check1->remaining;


                if($org_date == $lastc)
                {

                    $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                    $whole_visit= floor($visits);
                    $whole = floor($visit_hours);




                    $fraction = $visit_hours - $whole; // getting part after decimal point

                    /*
							 * Price calculating
							 */
                    if ($fraction)
                    {

                        if($whole>1 && $whole_visit > 1)
                        {

                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                        }
                        else if($whole > 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }
                        else if($whole < 1 && $whole_visit == 1)
                        {

                            $total = $price->half_hour_price;

                        }else if($whole < 1 && $whole_visit > 1)
                        {
                            $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }

                    } else {

                        if($whole>1 && $whole_visit>1)
                        {
                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                        }
                        else if($whole>1 && $whole_visit == 1)
                        {
                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                        }
                        else if($whole ==1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                        }
                        else
                        {
                            $total = $price->full_hour_price;

                        }
                    }

                    // echo $total;
                    // echo $old_amount;
                    // die;
                    $credit_check = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                    $rem=$credit_check->remaining;
                    //echo $rem;
                    //die;
                    $diff=0;
                    if($old_amount > $total)
                    {

                        $diff= $old_amount - $total;
                        $credit_check->remaining=$rem+$diff;
                        $credit_check->save();

                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Alteration";
                        $log->amount = $diff;
                        $log->l_flag = "Added";
                        $log->old_value = $rem;
                        $log->new_value = $rem+$diff;
                        $log->save();
                        $log->log_id = (int) $log->log_id;
                    }else{

                        $diff= $total - $old_amount;

                        $credit_check->remaining=$rem-$diff;

                        $credit_check->save();
                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Alteration";
                        $log->amount = $diff;
                        $log->l_flag = "Deducted";
                        $log->old_value = $rem;
                        $log->new_value = $rem-$diff;
                        $log->save();
                        $log->log_id = (int) $log->log_id;
                    }
                    /*
							 *  Stroing to table
							 */

                    //$appointment = new Appointment();
                    $appointment->company_id = $company_id;
                    $appointment->client_id = $client_id;
                    $appointment->service_id = $service_id;
                    $appointment->date = $date;
                    $appointment->visits = $visits;
                    $appointment->visit_hours = $visit_hours;
                    $appointment->price = $total;

                    //$appointment->status = 'accepted';					//status stored in db
                    $appointment->pet_id = $pet_id;
                    $appointment->message = $message;
                    if (empty($rf_company_id)) {
                        $appointment->created_by = 'client';
                    } else {
                        $appointment->created_by = 'company';
                    }
                    $appointment->created_at = date('Y-m-d H:i:s');
                    $appointment->save();
                    $appointment->appointment_id = (int) $appointment->appointment_id;


                    $service = Service::find($service_id);
                    $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                    /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                    if ($appointment->appointment_id > 0)
                    {


                        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                        $companyServices = array();
                        foreach ($services as $companyService)
                        {
                            $companyServices[] = array(
                                'id' => $companyService->service_id,
                                'name' => $companyService->service_name
                            );
                        }
                        $ownerDetail = array(
                            'client_id' => $appointment->client->client_id,
                            'firstname' => $appointment->client->firstname,
                            'lastname' => $appointment->client->lastname,
                            'emailid' => $appointment->client->emailid,
                            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            'contact_number' => $appointment->client->contact_number,
                            'client_address' => $appointment->client->client_address,
                            'client_notes' => $appointment->client->client_notes,
                            'player_id' => $appointment->client->player_id,
                        );

                        $companyDetail = array(
                            'company_id' => $appointment->company->company_id,
                            'account_id' => $appointment->company->account_id,
                            'company_name' => $appointment->company->company_name,
                            'emailid' => $appointment->company->emailid,
                            'contact_number' => $appointment->company->contact_number,
                            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                            'website' => $appointment->company->website,
                            'address' => $appointment->company->address,
                            'about' => $appointment->company->about,
                            'services' => $companyServices,
                        );

                        $response['error_code'] = 0;
                        $response['status'] = true;
                        $response['message'] = 'Appointment Successfully updated.';
                        $response['data'] = array(
                            'appointment_id' => $appointment->appointment_id,
                            'company_detail' => $companyDetail,
                            'owner_detail' => $ownerDetail,
                            'isManualClient' => $flag,
                            //'compnay_id' => $appointment->company_id,
                            // 'client_id' => $client_id,
                            // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                            'service_id' => $appointment->service_id,
                            'service_name' => $service->service_name,
                            'pet_id' => $appointment->pet_id,
                            'date' => $appointment->date,
                            'visits' => $visits,
                            'visit_hours' => $visit_hours,
                            //  'price' => $appointment->price,
                            'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                            'pet_detail' => array(
                                'pet_id' => $appointment->pet->pet_id,
                                'pet_name' => $appointment->pet->pet_name,
                                'pet_birth' => $appointment->pet->pet_birth,

                                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                                'pet_age' => $appointment->pet->age,
                                'gender' => $appointment->pet->gender,
                                'pet_type' => $appointment->pet->pet_type,
                                'breed' => $appointment->pet->breed,
                                'neutered' => $appointment->pet->neutered,
                                'spayed' => $appointment->pet->spayed,
                                'injuries' => $appointment->pet->injuries,
                                'medical_detail' => $appointment->pet->medical_detail,
                                'pet_notes' => $appointment->pet->pet_notes,
                                'latitude' => $appointment->pet->latitude,
                                'longitude' => $appointment->pet->longitude,
                            ),
                            'message' => $message,
                            'notification_flag' => 'appointment_booking'
                        );

                    }

                }else{

                    $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
                    $ab=date('Y-m-d');

                    $total=0;
                    foreach ($aa as $val)
                    {

                        $service_id1=$val->service_id;
                        $test1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                        if(count($test1) == 0)
                        {

                            $credit = new Credits();
                            $credit->company_id = $company_id;
                            $credit->client_id = $client_id;
                            $credit->pet_id = $pet_id;
                            $credit->service_id = $service_id1;
                            //$credit->credits = 0;
                            $credit->paid_amount = 0;
                            $credit->old_amount = 0;
                            $credit->date_of_payment = null;
                            $credit->last_check = $ab;
                            $credit->remaining=0;
                            $credit->save();
                            $credit->credit_id = (int) $credit->credit_id;
                        }




                        $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                        if (count($creditCheck)>0)

                        {
                            foreach ($creditCheck as  $valu)
                            {
                                $last_date=$valu->last_check;
                                $credits2=(float)$valu->paid_amount;
                                $remains=$valu->remaining;

                            }


                            $last_check1=date('Y-m-d',strtotime($last_date));



                            if($last_check1 != $ab)
                            {

                                $datetime = new DateTime($last_check1);
                                $datetime->modify('+1 day');
                                $l_check=$datetime->format('Y-m-d');

                                $appoint = Appointment::find_by_sql("SELECT price as p , date as d FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and pet_id=$pet_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab'");


                                if(count($appoint)>0)
                                {
                                    $t_price=0;

                                    foreach ($appoint as  $value1)
                                    {
                                        $t_price += $value1->p;
                                        $remaining=$remains;

                                        $remaining-=(float)$t_price;


                                        /*added extra for make all field zero when remaining is 0*/
                                        if($remaining == 0)
                                        {

                                            $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                            $creditCheck4->paid_amount=0;

                                            $creditCheck4->save();

                                            $total =0;

                                        }

                                        $pet=Pet::find($pet_id);
                                        $pet_name = $pet->pet_name;
                                        $log = new transactionlog();
                                        $log->company_id = $company_id;
                                        $log->client_id = $client_id;
                                        $log->pet_id = $pet_id;
                                        $log->service_id = $service_id1;
                                        $log->pet_name = $pet_name;
                                        $log->date_of_transaction = date('Y-m-d H:i:s',strtotime($value1->d));
                                        $log->type = "Charge";
                                        $log->amount = $value1->p;
                                        $log->l_flag = "Deducted";
                                        $log->old_value = $remains;
                                        $log->new_value = $remaining;
                                        //$log->old_amount = $old_amount;
                                        //$log->new_amount = $rem+$diff;
                                        $log->save();
                                        $log->log_id = (int) $log->log_id;

                                    }
                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));
                                    if(count($creditCheck1) >0)
                                    {
                                        $creditCheck1->last_check=date('Y-m-d');

                                        $creditCheck1->remaining=$remaining;
                                        $creditCheck1->save();
                                    }
                                }else{
                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                    if(count($creditCheck1) >0)
                                    {
                                        $creditCheck1->last_check=$ab;
                                        if($creditCheck1->remaining == 0)
                                        {

                                            $creditCheck1->paid_amount=0;

                                            $creditCheck1->date_of_payment=null;

                                        }

                                        $creditCheck1->save();
                                    }

                                    $remaining=(float)$remains;


                                }

                            }else{

                                $remaining=(float)$remains;
                            }


                        }else{


                            $credit = new Credits();
                            $credit->company_id = $company_id;
                            $credit->client_id = $client_id;
                            $credit->pet_id = $pet_id;
                            $credit->service_id = $service_id1;
                            //$credit->credits = 0;
                            $credit->paid_amount = 0;
                            $credit->old_amount = 0;
                            $credit->date_of_payment = null;
                            $credit->last_check = $ab;
                            $credit->remaining=0;
                            $credit->save();
                            $credit->credit_id = (int) $credit->credit_id;


                            $remaining=0;

                        }

                    }

                    $creditt = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

                    $creditt->remaining += $old_amount;
                    $creditt->save();

                    $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                    $whole_visit= floor($visits);
                    $whole = floor($visit_hours);




                    $fraction = $visit_hours - $whole; // getting part after decimal point

                    /*
							 * Price calculating
							 */
                    if ($fraction)
                    {

                        if($whole>1 && $whole_visit > 1)
                        {

                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                        }
                        else if($whole > 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }
                        else if($whole < 1 && $whole_visit == 1)
                        {

                            $total = $price->half_hour_price;

                        }else if($whole < 1 && $whole_visit > 1)
                        {
                            $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }

                    } else {

                        if($whole>1 && $whole_visit>1)
                        {
                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                        }
                        else if($whole>1 && $whole_visit == 1)
                        {
                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                        }
                        else if($whole ==1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                        }
                        else
                        {
                            $total = $price->full_hour_price;

                        }
                    }
                    // $credit_check = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                    // $rem=$credit_check->remaining;
                    // //echo $rem;
                    // //die;
                    // $diff=0;
                    // if($old_amount > $total)
                    // {

                    // 	$diff= $old_amount - $total;
                    // 	$credit_check->remaining=$rem+$diff;
                    // 	$credit_check->save();

                    // 				$pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                    // 					$pet_name = $pet->pet_name;
                    // 					$log = new transactionlog();
                    // 					$log->company_id = $company_id;
                    // 					$log->client_id = $client_id;
                    // 					$log->pet_id = $pet_id;
                    // 					$log->service_id = $service_id;
                    // 					$log->pet_name = $pet_name;
                    // 					$log->date_of_transaction = date('Y-m-d H:i:s');
                    // 					$log->type = "Alteration";
                    // 					$log->amount = $diff;
                    // 					$log->l_flag = "Added";
                    // 					$log->save();
                    // 					$log->log_id = (int) $log->log_id;
                    // }else{

                    // 	$diff= $total - $old_amount;

                    // 	$credit_check->remaining=$rem-$diff;

                    // 	$credit_check->save();
                    // 					$pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                    // 					$pet_name = $pet->pet_name;
                    // 					$log = new transactionlog();
                    // 					$log->company_id = $company_id;
                    // 					$log->client_id = $client_id;
                    // 					$log->pet_id = $pet_id;
                    // 					$log->service_id = $service_id;
                    // 					$log->pet_name = $pet_name;
                    // 					$log->date_of_transaction = date('Y-m-d H:i:s');
                    // 					$log->type = "Alteration";
                    // 					$log->amount = $diff;
                    // 					$log->l_flag = "Deducted";
                    // 					$log->save();
                    // 					$log->log_id = (int) $log->log_id;
                    // 	}
                    /*
							 *  Stroing to table
							 */

                    //$appointment = new Appointment();
                    $appointment->company_id = $company_id;
                    $appointment->client_id = $client_id;
                    $appointment->service_id = $service_id;
                    $appointment->date = $date;
                    $appointment->visits = $visits;
                    $appointment->visit_hours = $visit_hours;
                    $appointment->price = $total;

                    //$appointment->status = 'accepted';					//status stored in db
                    $appointment->pet_id = $pet_id;
                    $appointment->message = $message;
                    if (empty($rf_company_id)) {
                        $appointment->created_by = 'client';
                    } else {
                        $appointment->created_by = 'company';
                    }
                    $appointment->created_at = date('Y-m-d H:i:s');
                    $appointment->save();
                    $appointment->appointment_id = (int) $appointment->appointment_id;


                    $service = Service::find($service_id);
                    $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                    /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                    if ($appointment->appointment_id > 0)
                    {


                        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                        $companyServices = array();
                        foreach ($services as $companyService)
                        {
                            $companyServices[] = array(
                                'id' => $companyService->service_id,
                                'name' => $companyService->service_name
                            );
                        }
                        $ownerDetail = array(
                            'client_id' => $appointment->client->client_id,
                            'firstname' => $appointment->client->firstname,
                            'lastname' => $appointment->client->lastname,
                            'emailid' => $appointment->client->emailid,
                            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            'contact_number' => $appointment->client->contact_number,
                            'client_address' => $appointment->client->client_address,
                            'client_notes' => $appointment->client->client_notes,
                            'player_id' => $appointment->client->player_id,
                        );

                        $companyDetail = array(
                            'company_id' => $appointment->company->company_id,
                            'account_id' => $appointment->company->account_id,
                            'company_name' => $appointment->company->company_name,
                            'emailid' => $appointment->company->emailid,
                            'contact_number' => $appointment->company->contact_number,
                            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                            'website' => $appointment->company->website,
                            'address' => $appointment->company->address,
                            'about' => $appointment->company->about,
                            'services' => $companyServices,
                        );

                        $response['error_code'] = 0;
                        $response['status'] = true;
                        $response['message'] = 'Appointment Successfully updated.';
                        $response['data'] = array(
                            'appointment_id' => $appointment->appointment_id,
                            'company_detail' => $companyDetail,
                            'owner_detail' => $ownerDetail,
                            'isManualClient' => $flag,
                            //'compnay_id' => $appointment->company_id,
                            // 'client_id' => $client_id,
                            // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                            'service_id' => $appointment->service_id,
                            'service_name' => $service->service_name,
                            'pet_id' => $appointment->pet_id,
                            'date' => $appointment->date,
                            'visits' => $visits,
                            'visit_hours' => $visit_hours,
                            //  'price' => $appointment->price,
                            'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                            'pet_detail' => array(
                                'pet_id' => $appointment->pet->pet_id,
                                'pet_name' => $appointment->pet->pet_name,
                                'pet_birth' => $appointment->pet->pet_birth,

                                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                                'pet_age' => $appointment->pet->age,
                                'gender' => $appointment->pet->gender,
                                'pet_type' => $appointment->pet->pet_type,
                                'breed' => $appointment->pet->breed,
                                'neutered' => $appointment->pet->neutered,
                                'spayed' => $appointment->pet->spayed,
                                'injuries' => $appointment->pet->injuries,
                                'medical_detail' => $appointment->pet->medical_detail,
                                'pet_notes' => $appointment->pet->pet_notes,
                                'latitude' => $appointment->pet->latitude,
                                'longitude' => $appointment->pet->longitude,
                            ),
                            'message' => $message,
                            'notification_flag' => 'appointment_booking'
                        );

                    }


                }
                /*else completed of 1st if($org_date == $lastc)*/

            }else{

                $credit_check1= Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                $lastc=date('Y-m-d',strtotime($credit_check1->last_check));
                $precredit=$credit_check1->remaining;

                if($org_date == $lastc)
                {
                    $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                    $whole_visit= floor($visits);
                    $whole = floor($visit_hours);




                    $fraction = $visit_hours - $whole; // getting part after decimal point

                    /*
							 * Price calculating
							 */
                    if ($fraction)
                    {

                        if($whole>1 && $whole_visit > 1)
                        {

                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                        }
                        else if($whole > 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }
                        else if($whole < 1 && $whole_visit == 1)
                        {

                            $total = $price->half_hour_price;

                        }else if($whole < 1 && $whole_visit > 1)
                        {
                            $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }

                    } else {

                        if($whole>1 && $whole_visit>1)
                        {
                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                        }
                        else if($whole>1 && $whole_visit == 1)
                        {
                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                        }
                        else if($whole ==1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                        }
                        else
                        {
                            $total = $price->full_hour_price;

                        }
                    }
                    $credit_check = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                    $rem=$credit_check->remaining;
                    //echo $rem;
                    //die;
                    $diff=0;
                    if($old_amount > $total)
                    {

                        $diff= $old_amount - $total;
                        $credit_check->remaining=$rem+$diff;
                        $credit_check->save();

                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Alteration";
                        $log->amount = $diff;
                        $log->l_flag = "Added";
                        $log->old_value = $rem;
                        $log->new_value = $rem+$diff;
                        $log->save();
                        $log->log_id = (int) $log->log_id;
                    }else{

                        $diff= $total - $old_amount;

                        $credit_check->remaining=$rem-$diff;

                        $credit_check->save();
                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Alteration";
                        $log->amount = $diff;
                        $log->l_flag = "Deducted";
                        $log->old_value = $rem;
                        $log->new_value = $rem-$diff;
                        $log->save();
                        $log->log_id = (int) $log->log_id;
                    }
                    /*
							 *  Stroing to table
							 */

                    //$appointment = new Appointment();
                    $appointment->company_id = $company_id;
                    $appointment->client_id = $client_id;
                    $appointment->service_id = $service_id;
                    $appointment->date = $date;
                    $appointment->visits = $visits;
                    $appointment->visit_hours = $visit_hours;
                    $appointment->price = $total;

                    //$appointment->status = 'accepted';					//status stored in db
                    $appointment->pet_id = $pet_id;
                    $appointment->message = $message;
                    if (empty($rf_company_id)) {
                        $appointment->created_by = 'client';
                    } else {
                        $appointment->created_by = 'company';
                    }
                    $appointment->created_at = date('Y-m-d H:i:s');
                    $appointment->save();
                    $appointment->appointment_id = (int) $appointment->appointment_id;


                    $service = Service::find($service_id);
                    $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                    /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                    if ($appointment->appointment_id > 0)
                    {


                        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                        $companyServices = array();
                        foreach ($services as $companyService)
                        {
                            $companyServices[] = array(
                                'id' => $companyService->service_id,
                                'name' => $companyService->service_name
                            );
                        }
                        $ownerDetail = array(
                            'client_id' => $appointment->client->client_id,
                            'firstname' => $appointment->client->firstname,
                            'lastname' => $appointment->client->lastname,
                            'emailid' => $appointment->client->emailid,
                            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            'contact_number' => $appointment->client->contact_number,
                            'client_address' => $appointment->client->client_address,
                            'client_notes' => $appointment->client->client_notes,
                            'player_id' => $appointment->client->player_id,
                        );

                        $companyDetail = array(
                            'company_id' => $appointment->company->company_id,
                            'account_id' => $appointment->company->account_id,
                            'company_name' => $appointment->company->company_name,
                            'emailid' => $appointment->company->emailid,
                            'contact_number' => $appointment->company->contact_number,
                            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                            'website' => $appointment->company->website,
                            'address' => $appointment->company->address,
                            'about' => $appointment->company->about,
                            'services' => $companyServices,
                        );

                        $response['error_code'] = 0;
                        $response['status'] = true;
                        $response['message'] = 'Appointment Successfully updated.';
                        $response['data'] = array(
                            'appointment_id' => $appointment->appointment_id,
                            'company_detail' => $companyDetail,
                            'owner_detail' => $ownerDetail,
                            'isManualClient' => $flag,
                            //'compnay_id' => $appointment->company_id,
                            // 'client_id' => $client_id,
                            // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                            'service_id' => $appointment->service_id,
                            'service_name' => $service->service_name,
                            'pet_id' => $appointment->pet_id,
                            'date' => $appointment->date,
                            'visits' => $visits,
                            'visit_hours' => $visit_hours,
                            //  'price' => $appointment->price,
                            'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                            'pet_detail' => array(
                                'pet_id' => $appointment->pet->pet_id,
                                'pet_name' => $appointment->pet->pet_name,
                                'pet_birth' => $appointment->pet->pet_birth,

                                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                                'pet_age' => $appointment->pet->age,
                                'gender' => $appointment->pet->gender,
                                'pet_type' => $appointment->pet->pet_type,
                                'breed' => $appointment->pet->breed,
                                'neutered' => $appointment->pet->neutered,
                                'spayed' => $appointment->pet->spayed,
                                'injuries' => $appointment->pet->injuries,
                                'medical_detail' => $appointment->pet->medical_detail,
                                'pet_notes' => $appointment->pet->pet_notes,
                                'latitude' => $appointment->pet->latitude,
                                'longitude' => $appointment->pet->longitude,
                            ),
                            'message' => $message,
                            'notification_flag' => 'appointment_booking'
                        );

                    }

                }else{
                    $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
                    $ab=date('Y-m-d');

                    $total=0;
                    foreach ($aa as $val)
                    {

                        $service_id1=$val->service_id;
                        $test1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                        if(count($test1) == 0)
                        {

                            $credit = new Credits();
                            $credit->company_id = $company_id;
                            $credit->client_id = $client_id;
                            $credit->pet_id = $pet_id;
                            $credit->service_id = $service_id1;
                            //$credit->credits = 0;
                            $credit->paid_amount = 0;
                            $credit->old_amount = 0;
                            $credit->date_of_payment = null;
                            $credit->last_check = $ab;
                            $credit->remaining=0;
                            $credit->save();
                            $credit->credit_id = (int) $credit->credit_id;
                        }




                        $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                        if (count($creditCheck)>0)

                        {
                            foreach ($creditCheck as  $valu)
                            {
                                $last_date=$valu->last_check;
                                $credits2=(float)$valu->paid_amount;
                                $remains=$valu->remaining;

                            }


                            $last_check1=date('Y-m-d',strtotime($last_date));



                            if($last_check1 != $ab)
                            {

                                $datetime = new DateTime($last_check1);
                                $datetime->modify('+1 day');
                                $l_check=$datetime->format('Y-m-d');

                                $appoint = Appointment::find_by_sql("SELECT price as p , date as d FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and pet_id=$pet_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab'");


                                if(count($appoint)>0)
                                {
                                    $t_price=0;

                                    foreach ($appoint as  $value1)
                                    {
                                        $t_price += $value1->p;
                                        $remaining=$remains;

                                        $remaining-=(float)$t_price;


                                        /*added extra for make all field zero when remaining is 0*/
                                        if($remaining == 0)
                                        {

                                            $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                            $creditCheck4->paid_amount=0;

                                            $creditCheck4->save();

                                            $total =0;

                                        }

                                        $pet=Pet::find($pet_id);
                                        $pet_name = $pet->pet_name;
                                        $log = new transactionlog();
                                        $log->company_id = $company_id;
                                        $log->client_id = $client_id;
                                        $log->pet_id = $pet_id;
                                        $log->service_id = $service_id1;
                                        $log->pet_name = $pet_name;
                                        $log->date_of_transaction = date('Y-m-d H:i:s',strtotime($value1->d));
                                        $log->type = "Charge";
                                        $log->amount = $value1->p;
                                        $log->l_flag = "Deducted";
                                        $log->old_value = $remains;
                                        $log->new_value = $remaining;
                                        $log->save();
                                        $log->log_id = (int) $log->log_id;

                                    }
                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));
                                    if(count($creditCheck1) >0)
                                    {
                                        $creditCheck1->last_check=date('Y-m-d');

                                        $creditCheck1->remaining=$remaining;
                                        $creditCheck1->save();
                                    }
                                }else{
                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                    if(count($creditCheck1) >0)
                                    {
                                        $creditCheck1->last_check=$ab;
                                        if($creditCheck1->remaining == 0)
                                        {

                                            $creditCheck1->paid_amount=0;

                                            $creditCheck1->date_of_payment=null;

                                        }

                                        $creditCheck1->save();
                                    }

                                    $remaining=(float)$remains;


                                }

                            }else{

                                $remaining=(float)$remains;
                            }


                        }else{


                            $credit = new Credits();
                            $credit->company_id = $company_id;
                            $credit->client_id = $client_id;
                            $credit->pet_id = $pet_id;
                            $credit->service_id = $service_id1;
                            //$credit->credits = 0;
                            $credit->paid_amount = 0;
                            $credit->old_amount = 0;
                            $credit->date_of_payment = null;
                            $credit->last_check = $ab;
                            $credit->remaining=0;
                            $credit->save();
                            $credit->credit_id = (int) $credit->credit_id;


                            $remaining=0;

                        }

                    }

                    $creditt = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

                    $creditt->remaining += $old_amount;
                    $creditt->save();

                    $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                    $whole_visit= floor($visits);
                    $whole = floor($visit_hours);




                    $fraction = $visit_hours - $whole; // getting part after decimal point

                    /*
							 * Price calculating
							 */
                    if ($fraction)
                    {

                        if($whole>1 && $whole_visit > 1)
                        {

                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                        }
                        else if($whole > 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }
                        else if($whole < 1 && $whole_visit == 1)
                        {

                            $total = $price->half_hour_price;

                        }else if($whole < 1 && $whole_visit > 1)
                        {
                            $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }

                    } else {

                        if($whole>1 && $whole_visit>1)
                        {
                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                        }
                        else if($whole>1 && $whole_visit == 1)
                        {
                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                        }
                        else if($whole ==1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                        }
                        else
                        {
                            $total = $price->full_hour_price;

                        }
                    }

                    /*
							 *  Stroing to table
							 */

                    //$appointment = new Appointment();
                    $appointment->company_id = $company_id;
                    $appointment->client_id = $client_id;
                    $appointment->service_id = $service_id;
                    $appointment->date = $date;
                    $appointment->visits = $visits;
                    $appointment->visit_hours = $visit_hours;
                    $appointment->price = $total;

                    //$appointment->status = 'accepted';					//status stored in db
                    $appointment->pet_id = $pet_id;
                    $appointment->message = $message;
                    if (empty($rf_company_id)) {
                        $appointment->created_by = 'client';
                    } else {
                        $appointment->created_by = 'company';
                    }
                    $appointment->created_at = date('Y-m-d H:i:s');
                    $appointment->save();
                    $appointment->appointment_id = (int) $appointment->appointment_id;


                    $service = Service::find($service_id);
                    $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                    /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                    if ($appointment->appointment_id > 0)
                    {


                        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                        $companyServices = array();
                        foreach ($services as $companyService)
                        {
                            $companyServices[] = array(
                                'id' => $companyService->service_id,
                                'name' => $companyService->service_name
                            );
                        }
                        $ownerDetail = array(
                            'client_id' => $appointment->client->client_id,
                            'firstname' => $appointment->client->firstname,
                            'lastname' => $appointment->client->lastname,
                            'emailid' => $appointment->client->emailid,
                            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            'contact_number' => $appointment->client->contact_number,
                            'client_address' => $appointment->client->client_address,
                            'client_notes' => $appointment->client->client_notes,
                            'player_id' => $appointment->client->player_id,
                        );

                        $companyDetail = array(
                            'company_id' => $appointment->company->company_id,
                            'account_id' => $appointment->company->account_id,
                            'company_name' => $appointment->company->company_name,
                            'emailid' => $appointment->company->emailid,
                            'contact_number' => $appointment->company->contact_number,
                            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                            'website' => $appointment->company->website,
                            'address' => $appointment->company->address,
                            'about' => $appointment->company->about,
                            'services' => $companyServices,
                        );

                        $response['error_code'] = 0;
                        $response['status'] = true;
                        $response['message'] = 'Appointment Successfully updated.';
                        $response['data'] = array(
                            'appointment_id' => $appointment->appointment_id,
                            'company_detail' => $companyDetail,
                            'owner_detail' => $ownerDetail,
                            'isManualClient' => $flag,
                            //'compnay_id' => $appointment->company_id,
                            // 'client_id' => $client_id,
                            // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                            'service_id' => $appointment->service_id,
                            'service_name' => $service->service_name,
                            'pet_id' => $appointment->pet_id,
                            'date' => $appointment->date,
                            'visits' => $visits,
                            'visit_hours' => $visit_hours,
                            //  'price' => $appointment->price,
                            'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                            'pet_detail' => array(
                                'pet_id' => $appointment->pet->pet_id,
                                'pet_name' => $appointment->pet->pet_name,
                                'pet_birth' => $appointment->pet->pet_birth,

                                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                                'pet_age' => $appointment->pet->age,
                                'gender' => $appointment->pet->gender,
                                'pet_type' => $appointment->pet->pet_type,
                                'breed' => $appointment->pet->breed,
                                'neutered' => $appointment->pet->neutered,
                                'spayed' => $appointment->pet->spayed,
                                'injuries' => $appointment->pet->injuries,
                                'medical_detail' => $appointment->pet->medical_detail,
                                'pet_notes' => $appointment->pet->pet_notes,
                                'latitude' => $appointment->pet->latitude,
                                'longitude' => $appointment->pet->longitude,
                            ),
                            'message' => $message,
                            'notification_flag' => 'appointment_booking'
                        );

                    }


                }


            }
            /*else completed of 1st if($date == $today)*/

            /*----------------------------------------------------------------------------------------------------------------------------------------------------*/
        }elseif($org_date != $today){

            if(date('Y-m-d',strtotime($date)) == $today)
            {
                $credit_check1= Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                $lastc=date('Y-m-d',strtotime($credit_check1->last_check));
                $precredit=$credit_check1->remaining;

                if($org_date == $lastc)
                {
                    $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                    $whole_visit= floor($visits);
                    $whole = floor($visit_hours);




                    $fraction = $visit_hours - $whole; // getting part after decimal point

                    /*
							 * Price calculating
							 */
                    if ($fraction)
                    {

                        if($whole>1 && $whole_visit > 1)
                        {

                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                        }
                        else if($whole > 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }
                        else if($whole < 1 && $whole_visit == 1)
                        {

                            $total = $price->half_hour_price;

                        }else if($whole < 1 && $whole_visit > 1)
                        {
                            $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }

                    } else {

                        if($whole>1 && $whole_visit>1)
                        {
                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                        }
                        else if($whole>1 && $whole_visit == 1)
                        {
                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                        }
                        else if($whole ==1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                        }
                        else
                        {
                            $total = $price->full_hour_price;

                        }
                    }
                    $credit_check = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                    $rem=$credit_check->remaining;
                    //echo $rem;
                    //die;
                    $diff=0;
                    if($old_amount > $total)
                    {

                        $diff= $old_amount - $total;
                        $credit_check->remaining=$rem+$diff;
                        $credit_check->save();

                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Alteration";
                        $log->amount = $diff;
                        $log->l_flag = "Added";
                        $log->old_value = $rem;
                        $log->new_value = $rem+$diff;
                        $log->save();
                        $log->log_id = (int) $log->log_id;
                    }else{

                        $diff= $total - $old_amount;

                        $credit_check->remaining=$rem-$diff;

                        $credit_check->save();
                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Alteration";
                        $log->amount = $diff;
                        $log->l_flag = "Deducted";
                        $log->old_value = $rem;
                        $log->new_value = $rem-$diff;
                        $log->save();
                        $log->log_id = (int) $log->log_id;
                    }
                    /*
							 *  Stroing to table
							 */

                    //$appointment = new Appointment();
                    $appointment->company_id = $company_id;
                    $appointment->client_id = $client_id;
                    $appointment->service_id = $service_id;
                    $appointment->date = $date;
                    $appointment->visits = $visits;
                    $appointment->visit_hours = $visit_hours;
                    $appointment->price = $total;

                    //$appointment->status = 'accepted';					//status stored in db
                    $appointment->pet_id = $pet_id;
                    $appointment->message = $message;
                    if (empty($rf_company_id)) {
                        $appointment->created_by = 'client';
                    } else {
                        $appointment->created_by = 'company';
                    }
                    $appointment->created_at = date('Y-m-d H:i:s');
                    $appointment->save();
                    $appointment->appointment_id = (int) $appointment->appointment_id;


                    $service = Service::find($service_id);
                    $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                    /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                    if ($appointment->appointment_id > 0)
                    {


                        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                        $companyServices = array();
                        foreach ($services as $companyService)
                        {
                            $companyServices[] = array(
                                'id' => $companyService->service_id,
                                'name' => $companyService->service_name
                            );
                        }
                        $ownerDetail = array(
                            'client_id' => $appointment->client->client_id,
                            'firstname' => $appointment->client->firstname,
                            'lastname' => $appointment->client->lastname,
                            'emailid' => $appointment->client->emailid,
                            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            'contact_number' => $appointment->client->contact_number,
                            'client_address' => $appointment->client->client_address,
                            'client_notes' => $appointment->client->client_notes,
                            'player_id' => $appointment->client->player_id,
                        );

                        $companyDetail = array(
                            'company_id' => $appointment->company->company_id,
                            'account_id' => $appointment->company->account_id,
                            'company_name' => $appointment->company->company_name,
                            'emailid' => $appointment->company->emailid,
                            'contact_number' => $appointment->company->contact_number,
                            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                            'website' => $appointment->company->website,
                            'address' => $appointment->company->address,
                            'about' => $appointment->company->about,
                            'services' => $companyServices,
                        );

                        $response['error_code'] = 0;
                        $response['status'] = true;
                        $response['message'] = 'Appointment Successfully updated.';
                        $response['data'] = array(
                            'appointment_id' => $appointment->appointment_id,
                            'company_detail' => $companyDetail,
                            'owner_detail' => $ownerDetail,
                            'isManualClient' => $flag,
                            //'compnay_id' => $appointment->company_id,
                            // 'client_id' => $client_id,
                            // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                            'service_id' => $appointment->service_id,
                            'service_name' => $service->service_name,
                            'pet_id' => $appointment->pet_id,
                            'date' => $appointment->date,
                            'visits' => $visits,
                            'visit_hours' => $visit_hours,
                            //  'price' => $appointment->price,
                            'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                            'pet_detail' => array(
                                'pet_id' => $appointment->pet->pet_id,
                                'pet_name' => $appointment->pet->pet_name,
                                'pet_birth' => $appointment->pet->pet_birth,

                                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                                'pet_age' => $appointment->pet->age,
                                'gender' => $appointment->pet->gender,
                                'pet_type' => $appointment->pet->pet_type,
                                'breed' => $appointment->pet->breed,
                                'neutered' => $appointment->pet->neutered,
                                'spayed' => $appointment->pet->spayed,
                                'injuries' => $appointment->pet->injuries,
                                'medical_detail' => $appointment->pet->medical_detail,
                                'pet_notes' => $appointment->pet->pet_notes,
                                'latitude' => $appointment->pet->latitude,
                                'longitude' => $appointment->pet->longitude,
                            ),
                            'message' => $message,
                            'notification_flag' => 'appointment_booking'
                        );

                    }

                }else{
                    $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
                    $ab=date('Y-m-d');

                    $total=0;
                    foreach ($aa as $val)
                    {

                        $service_id1=$val->service_id;
                        $test1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                        if(count($test1) == 0)
                        {

                            $credit = new Credits();
                            $credit->company_id = $company_id;
                            $credit->client_id = $client_id;
                            $credit->pet_id = $pet_id;
                            $credit->service_id = $service_id1;
                            //$credit->credits = 0;
                            $credit->paid_amount = 0;
                            $credit->old_amount = 0;
                            $credit->date_of_payment = null;
                            $credit->last_check = $ab;
                            $credit->remaining=0;
                            $credit->save();
                            $credit->credit_id = (int) $credit->credit_id;
                        }




                        $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                        if (count($creditCheck)>0)

                        {
                            foreach ($creditCheck as  $valu)
                            {
                                $last_date=$valu->last_check;
                                $credits2=(float)$valu->paid_amount;
                                $remains=$valu->remaining;

                            }


                            $last_check1=date('Y-m-d',strtotime($last_date));



                            if($last_check1 != $ab)
                            {

                                $datetime = new DateTime($last_check1);
                                $datetime->modify('+1 day');
                                $l_check=$datetime->format('Y-m-d');

                                $appoint = Appointment::find_by_sql("SELECT price as p , date as d FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and pet_id=$pet_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab'");


                                if(count($appoint)>0)
                                {
                                    $t_price=0;

                                    foreach ($appoint as  $value1)
                                    {
                                        $t_price += $value1->p;
                                        $remaining=$remains;

                                        $remaining-=(float)$t_price;


                                        /*added extra for make all field zero when remaining is 0*/
                                        if($remaining == 0)
                                        {

                                            $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                            $creditCheck4->paid_amount=0;

                                            $creditCheck4->save();

                                            $total =0;

                                        }

                                        $pet=Pet::find($pet_id);
                                        $pet_name = $pet->pet_name;
                                        $log = new transactionlog();
                                        $log->company_id = $company_id;
                                        $log->client_id = $client_id;
                                        $log->pet_id = $pet_id;
                                        $log->service_id = $service_id1;
                                        $log->pet_name = $pet_name;
                                        $log->date_of_transaction = date('Y-m-d H:i:s',strtotime($value1->d));
                                        $log->type = "Charge";
                                        $log->amount = $value1->p;
                                        $log->l_flag = "Deducted";
                                        $log->old_value = $remains;
                                        $log->new_value = $remaining;
                                        $log->save();
                                        $log->log_id = (int) $log->log_id;

                                    }
                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));
                                    if(count($creditCheck1) >0)
                                    {
                                        $creditCheck1->last_check=date('Y-m-d');

                                        $creditCheck1->remaining=$remaining;
                                        $creditCheck1->save();
                                    }
                                }else{
                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                    if(count($creditCheck1) >0)
                                    {
                                        $creditCheck1->last_check=$ab;
                                        if($creditCheck1->remaining == 0)
                                        {

                                            $creditCheck1->paid_amount=0;

                                            $creditCheck1->date_of_payment=null;

                                        }

                                        $creditCheck1->save();
                                    }

                                    $remaining=(float)$remains;


                                }

                            }else{

                                $remaining=(float)$remains;
                            }


                        }else{


                            $credit = new Credits();
                            $credit->company_id = $company_id;
                            $credit->client_id = $client_id;
                            $credit->pet_id = $pet_id;
                            $credit->service_id = $service_id1;
                            //$credit->credits = 0;
                            $credit->paid_amount = 0;
                            $credit->old_amount = 0;
                            $credit->date_of_payment = null;
                            $credit->last_check = $ab;
                            $credit->remaining=0;
                            $credit->save();
                            $credit->credit_id = (int) $credit->credit_id;


                            $remaining=0;

                        }

                    }

                    $creditt = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

                    $creditt->remaining += $old_amount;
                    $creditt->save();

                    $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                    $whole_visit= floor($visits);
                    $whole = floor($visit_hours);




                    $fraction = $visit_hours - $whole; // getting part after decimal point

                    /*
							 * Price calculating
							 */
                    if ($fraction)
                    {

                        if($whole>1 && $whole_visit > 1)
                        {

                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                        }
                        else if($whole > 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }
                        else if($whole < 1 && $whole_visit == 1)
                        {

                            $total = $price->half_hour_price;

                        }else if($whole < 1 && $whole_visit > 1)
                        {
                            $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }

                    } else {

                        if($whole>1 && $whole_visit>1)
                        {
                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                        }
                        else if($whole>1 && $whole_visit == 1)
                        {
                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                        }
                        else if($whole ==1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                        }
                        else
                        {
                            $total = $price->full_hour_price;

                        }
                    }

                    /*
							 *  Stroing to table
							 */

                    //$appointment = new Appointment();
                    $appointment->company_id = $company_id;
                    $appointment->client_id = $client_id;
                    $appointment->service_id = $service_id;
                    $appointment->date = $date;
                    $appointment->visits = $visits;
                    $appointment->visit_hours = $visit_hours;
                    $appointment->price = $total;

                    //$appointment->status = 'accepted';					//status stored in db
                    $appointment->pet_id = $pet_id;
                    $appointment->message = $message;
                    if (empty($rf_company_id)) {
                        $appointment->created_by = 'client';
                    } else {
                        $appointment->created_by = 'company';
                    }
                    $appointment->created_at = date('Y-m-d H:i:s');
                    $appointment->save();
                    $appointment->appointment_id = (int) $appointment->appointment_id;


                    $service = Service::find($service_id);
                    $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                    /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                    if ($appointment->appointment_id > 0)
                    {


                        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                        $companyServices = array();
                        foreach ($services as $companyService)
                        {
                            $companyServices[] = array(
                                'id' => $companyService->service_id,
                                'name' => $companyService->service_name
                            );
                        }
                        $ownerDetail = array(
                            'client_id' => $appointment->client->client_id,
                            'firstname' => $appointment->client->firstname,
                            'lastname' => $appointment->client->lastname,
                            'emailid' => $appointment->client->emailid,
                            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            'contact_number' => $appointment->client->contact_number,
                            'client_address' => $appointment->client->client_address,
                            'client_notes' => $appointment->client->client_notes,
                            'player_id' => $appointment->client->player_id,
                        );

                        $companyDetail = array(
                            'company_id' => $appointment->company->company_id,
                            'account_id' => $appointment->company->account_id,
                            'company_name' => $appointment->company->company_name,
                            'emailid' => $appointment->company->emailid,
                            'contact_number' => $appointment->company->contact_number,
                            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                            'website' => $appointment->company->website,
                            'address' => $appointment->company->address,
                            'about' => $appointment->company->about,
                            'services' => $companyServices,
                        );

                        $response['error_code'] = 0;
                        $response['status'] = true;
                        $response['message'] = 'Appointment Successfully updated.';
                        $response['data'] = array(
                            'appointment_id' => $appointment->appointment_id,
                            'company_detail' => $companyDetail,
                            'owner_detail' => $ownerDetail,
                            'isManualClient' => $flag,
                            //'compnay_id' => $appointment->company_id,
                            // 'client_id' => $client_id,
                            // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                            'service_id' => $appointment->service_id,
                            'service_name' => $service->service_name,
                            'pet_id' => $appointment->pet_id,
                            'date' => $appointment->date,
                            'visits' => $visits,
                            'visit_hours' => $visit_hours,
                            //  'price' => $appointment->price,
                            'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                            'pet_detail' => array(
                                'pet_id' => $appointment->pet->pet_id,
                                'pet_name' => $appointment->pet->pet_name,
                                'pet_birth' => $appointment->pet->pet_birth,

                                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                                'pet_age' => $appointment->pet->age,
                                'gender' => $appointment->pet->gender,
                                'pet_type' => $appointment->pet->pet_type,
                                'breed' => $appointment->pet->breed,
                                'neutered' => $appointment->pet->neutered,
                                'spayed' => $appointment->pet->spayed,
                                'injuries' => $appointment->pet->injuries,
                                'medical_detail' => $appointment->pet->medical_detail,
                                'pet_notes' => $appointment->pet->pet_notes,
                                'latitude' => $appointment->pet->latitude,
                                'longitude' => $appointment->pet->longitude,
                            ),
                            'message' => $message,
                            'notification_flag' => 'appointment_booking'
                        );

                    }


                }

                /*else completed of 2st if($org_date == $lastc)*/
            }else{

                $credit_check1= Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                $lastc=date('Y-m-d',strtotime($credit_check1->last_check));
                $precredit=$credit_check1->remaining;

                if($org_date == $lastc)
                {
                    $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                    $whole_visit= floor($visits);
                    $whole = floor($visit_hours);




                    $fraction = $visit_hours - $whole; // getting part after decimal point

                    /*
							 * Price calculating
							 */
                    if ($fraction)
                    {

                        if($whole>1 && $whole_visit > 1)
                        {

                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                        }
                        else if($whole > 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }
                        else if($whole < 1 && $whole_visit == 1)
                        {

                            $total = $price->half_hour_price;

                        }else if($whole < 1 && $whole_visit > 1)
                        {
                            $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }

                    } else {

                        if($whole>1 && $whole_visit>1)
                        {
                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                        }
                        else if($whole>1 && $whole_visit == 1)
                        {
                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                        }
                        else if($whole ==1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                        }
                        else
                        {
                            $total = $price->full_hour_price;

                        }
                    }
                    $credit_check = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                    $rem=$credit_check->remaining;
                    //echo $rem;
                    //die;
                    $diff=0;
                    if($old_amount > $total)
                    {

                        $diff= $old_amount - $total;
                        $credit_check->remaining=$rem+$diff;
                        $credit_check->save();

                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Alteration";
                        $log->amount = $diff;
                        $log->l_flag = "Added";
                        $log->old_value = $rem;
                        $log->new_value = $rem+$diff;
                        $log->save();
                        $log->log_id = (int) $log->log_id;
                    }else{

                        $diff= $total - $old_amount;

                        $credit_check->remaining=$rem-$diff;

                        $credit_check->save();
                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Alteration";
                        $log->amount = $diff;
                        $log->l_flag = "Deducted";
                        $log->old_value = $rem;
                        $log->new_value = $rem-$diff;
                        $log->save();
                        $log->log_id = (int) $log->log_id;
                    }
                    /*
							 *  Stroing to table
							 */

                    //$appointment = new Appointment();
                    $appointment->company_id = $company_id;
                    $appointment->client_id = $client_id;
                    $appointment->service_id = $service_id;
                    $appointment->date = $date;
                    $appointment->visits = $visits;
                    $appointment->visit_hours = $visit_hours;
                    $appointment->price = $total;

                    //$appointment->status = 'accepted';					//status stored in db
                    $appointment->pet_id = $pet_id;
                    $appointment->message = $message;
                    if (empty($rf_company_id)) {
                        $appointment->created_by = 'client';
                    } else {
                        $appointment->created_by = 'company';
                    }
                    $appointment->created_at = date('Y-m-d H:i:s');
                    $appointment->save();
                    $appointment->appointment_id = (int) $appointment->appointment_id;


                    $service = Service::find($service_id);
                    $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                    /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                    if ($appointment->appointment_id > 0)
                    {


                        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                        $companyServices = array();
                        foreach ($services as $companyService)
                        {
                            $companyServices[] = array(
                                'id' => $companyService->service_id,
                                'name' => $companyService->service_name
                            );
                        }
                        $ownerDetail = array(
                            'client_id' => $appointment->client->client_id,
                            'firstname' => $appointment->client->firstname,
                            'lastname' => $appointment->client->lastname,
                            'emailid' => $appointment->client->emailid,
                            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            'contact_number' => $appointment->client->contact_number,
                            'client_address' => $appointment->client->client_address,
                            'client_notes' => $appointment->client->client_notes,
                            'player_id' => $appointment->client->player_id,
                        );

                        $companyDetail = array(
                            'company_id' => $appointment->company->company_id,
                            'account_id' => $appointment->company->account_id,
                            'company_name' => $appointment->company->company_name,
                            'emailid' => $appointment->company->emailid,
                            'contact_number' => $appointment->company->contact_number,
                            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                            'website' => $appointment->company->website,
                            'address' => $appointment->company->address,
                            'about' => $appointment->company->about,
                            'services' => $companyServices,
                        );

                        $response['error_code'] = 0;
                        $response['status'] = true;
                        $response['message'] = 'Appointment Successfully updated.';
                        $response['data'] = array(
                            'appointment_id' => $appointment->appointment_id,
                            'company_detail' => $companyDetail,
                            'owner_detail' => $ownerDetail,
                            'isManualClient' => $flag,
                            //'compnay_id' => $appointment->company_id,
                            // 'client_id' => $client_id,
                            // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                            'service_id' => $appointment->service_id,
                            'service_name' => $service->service_name,
                            'pet_id' => $appointment->pet_id,
                            'date' => $appointment->date,
                            'visits' => $visits,
                            'visit_hours' => $visit_hours,
                            //  'price' => $appointment->price,
                            'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                            'pet_detail' => array(
                                'pet_id' => $appointment->pet->pet_id,
                                'pet_name' => $appointment->pet->pet_name,
                                'pet_birth' => $appointment->pet->pet_birth,

                                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                                'pet_age' => $appointment->pet->age,
                                'gender' => $appointment->pet->gender,
                                'pet_type' => $appointment->pet->pet_type,
                                'breed' => $appointment->pet->breed,
                                'neutered' => $appointment->pet->neutered,
                                'spayed' => $appointment->pet->spayed,
                                'injuries' => $appointment->pet->injuries,
                                'medical_detail' => $appointment->pet->medical_detail,
                                'pet_notes' => $appointment->pet->pet_notes,
                                'latitude' => $appointment->pet->latitude,
                                'longitude' => $appointment->pet->longitude,
                            ),
                            'message' => $message,
                            'notification_flag' => 'appointment_booking'
                        );

                    }

                }else{
                    $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
                    $ab=date('Y-m-d');

                    $total=0;
                    foreach ($aa as $val)
                    {

                        $service_id1=$val->service_id;
                        $test1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                        if(count($test1) == 0)
                        {

                            $credit = new Credits();
                            $credit->company_id = $company_id;
                            $credit->client_id = $client_id;
                            $credit->pet_id = $pet_id;
                            $credit->service_id = $service_id1;
                            //$credit->credits = 0;
                            $credit->paid_amount = 0;
                            $credit->old_amount = 0;
                            $credit->date_of_payment = null;
                            $credit->last_check = $ab;
                            $credit->remaining=0;
                            $credit->save();
                            $credit->credit_id = (int) $credit->credit_id;
                        }




                        $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                        if (count($creditCheck)>0)

                        {
                            foreach ($creditCheck as  $valu)
                            {
                                $last_date=$valu->last_check;
                                $credits2=(float)$valu->paid_amount;
                                $remains=$valu->remaining;

                            }


                            $last_check1=date('Y-m-d',strtotime($last_date));



                            if($last_check1 != $ab)
                            {

                                $datetime = new DateTime($last_check1);
                                $datetime->modify('+1 day');
                                $l_check=$datetime->format('Y-m-d');

                                $appoint = Appointment::find_by_sql("SELECT price as p , date as d FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and pet_id=$pet_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab'");


                                if(count($appoint)>0)
                                {
                                    $t_price=0;

                                    foreach ($appoint as  $value1)
                                    {
                                        $t_price += $value1->p;
                                        $remaining=$remains;

                                        $remaining-=(float)$t_price;


                                        /*added extra for make all field zero when remaining is 0*/
                                        if($remaining == 0)
                                        {

                                            $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                            $creditCheck4->paid_amount=0;

                                            $creditCheck4->save();

                                            $total =0;

                                        }

                                        $pet=Pet::find($pet_id);
                                        $pet_name = $pet->pet_name;
                                        $log = new transactionlog();
                                        $log->company_id = $company_id;
                                        $log->client_id = $client_id;
                                        $log->pet_id = $pet_id;
                                        $log->service_id = $service_id1;
                                        $log->pet_name = $pet_name;
                                        $log->date_of_transaction = date('Y-m-d H:i:s',strtotime($value1->d));
                                        $log->type = "Charge";
                                        $log->amount = $value1->p;
                                        $log->l_flag = "Deducted";
                                        $log->old_value = $remains;
                                        $log->new_value = $remaining;
                                        $log->save();
                                        $log->log_id = (int) $log->log_id;

                                    }
                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));
                                    if(count($creditCheck1) >0)
                                    {
                                        $creditCheck1->last_check=date('Y-m-d');

                                        $creditCheck1->remaining=$remaining;
                                        $creditCheck1->save();
                                    }
                                }else{
                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                    if(count($creditCheck1) >0)
                                    {
                                        $creditCheck1->last_check=$ab;
                                        if($creditCheck1->remaining == 0)
                                        {

                                            $creditCheck1->paid_amount=0;

                                            $creditCheck1->date_of_payment=null;

                                        }

                                        $creditCheck1->save();
                                    }

                                    $remaining=(float)$remains;


                                }

                            }else{

                                $remaining=(float)$remains;
                            }


                        }else{


                            $credit = new Credits();
                            $credit->company_id = $company_id;
                            $credit->client_id = $client_id;
                            $credit->pet_id = $pet_id;
                            $credit->service_id = $service_id1;
                            //$credit->credits = 0;
                            $credit->paid_amount = 0;
                            $credit->old_amount = 0;
                            $credit->date_of_payment = null;
                            $credit->last_check = $ab;
                            $credit->remaining=0;
                            $credit->save();
                            $credit->credit_id = (int) $credit->credit_id;


                            $remaining=0;

                        }

                    }

                    $creditt = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

                    $creditt->remaining += $old_amount;
                    $creditt->save();

                    $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                    $whole_visit= floor($visits);
                    $whole = floor($visit_hours);




                    $fraction = $visit_hours - $whole; // getting part after decimal point

                    /*
							 * Price calculating
							 */
                    if ($fraction)
                    {

                        if($whole>1 && $whole_visit > 1)
                        {

                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                        }
                        else if($whole > 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }
                        else if($whole < 1 && $whole_visit == 1)
                        {

                            $total = $price->half_hour_price;

                        }else if($whole < 1 && $whole_visit > 1)
                        {
                            $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }

                    } else {

                        if($whole>1 && $whole_visit>1)
                        {
                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                        }
                        else if($whole>1 && $whole_visit == 1)
                        {
                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                        }
                        else if($whole ==1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                        }
                        else
                        {
                            $total = $price->full_hour_price;

                        }
                    }

                    /*
							 *  Stroing to table
							 */

                    //$appointment = new Appointment();
                    $appointment->company_id = $company_id;
                    $appointment->client_id = $client_id;
                    $appointment->service_id = $service_id;
                    $appointment->date = $date;
                    $appointment->visits = $visits;
                    $appointment->visit_hours = $visit_hours;
                    $appointment->price = $total;

                    //$appointment->status = 'accepted';					//status stored in db
                    $appointment->pet_id = $pet_id;
                    $appointment->message = $message;
                    if (empty($rf_company_id)) {
                        $appointment->created_by = 'client';
                    } else {
                        $appointment->created_by = 'company';
                    }
                    $appointment->created_at = date('Y-m-d H:i:s');
                    $appointment->save();
                    $appointment->appointment_id = (int) $appointment->appointment_id;


                    $service = Service::find($service_id);
                    $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                    /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                    if ($appointment->appointment_id > 0)
                    {


                        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                        $companyServices = array();
                        foreach ($services as $companyService)
                        {
                            $companyServices[] = array(
                                'id' => $companyService->service_id,
                                'name' => $companyService->service_name
                            );
                        }
                        $ownerDetail = array(
                            'client_id' => $appointment->client->client_id,
                            'firstname' => $appointment->client->firstname,
                            'lastname' => $appointment->client->lastname,
                            'emailid' => $appointment->client->emailid,
                            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            'contact_number' => $appointment->client->contact_number,
                            'client_address' => $appointment->client->client_address,
                            'client_notes' => $appointment->client->client_notes,
                            'player_id' => $appointment->client->player_id,
                        );

                        $companyDetail = array(
                            'company_id' => $appointment->company->company_id,
                            'account_id' => $appointment->company->account_id,
                            'company_name' => $appointment->company->company_name,
                            'emailid' => $appointment->company->emailid,
                            'contact_number' => $appointment->company->contact_number,
                            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                            'website' => $appointment->company->website,
                            'address' => $appointment->company->address,
                            'about' => $appointment->company->about,
                            'services' => $companyServices,
                        );

                        $response['error_code'] = 0;
                        $response['status'] = true;
                        $response['message'] = 'Appointment Successfully updated.';
                        $response['data'] = array(
                            'appointment_id' => $appointment->appointment_id,
                            'company_detail' => $companyDetail,
                            'owner_detail' => $ownerDetail,
                            'isManualClient' => $flag,
                            //'compnay_id' => $appointment->company_id,
                            // 'client_id' => $client_id,
                            // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                            'service_id' => $appointment->service_id,
                            'service_name' => $service->service_name,
                            'pet_id' => $appointment->pet_id,
                            'date' => $appointment->date,
                            'visits' => $visits,
                            'visit_hours' => $visit_hours,
                            //  'price' => $appointment->price,
                            'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                            'pet_detail' => array(
                                'pet_id' => $appointment->pet->pet_id,
                                'pet_name' => $appointment->pet->pet_name,
                                'pet_birth' => $appointment->pet->pet_birth,

                                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                                'pet_age' => $appointment->pet->age,
                                'gender' => $appointment->pet->gender,
                                'pet_type' => $appointment->pet->pet_type,
                                'breed' => $appointment->pet->breed,
                                'neutered' => $appointment->pet->neutered,
                                'spayed' => $appointment->pet->spayed,
                                'injuries' => $appointment->pet->injuries,
                                'medical_detail' => $appointment->pet->medical_detail,
                                'pet_notes' => $appointment->pet->pet_notes,
                                'latitude' => $appointment->pet->latitude,
                                'longitude' => $appointment->pet->longitude,
                            ),
                            'message' => $message,
                            'notification_flag' => 'appointment_booking'
                        );

                    }


                }

            }
            /*else completed */
            /*----------------------------------------------------------------------------------------------------------------------------------------------*/
        }else{
            if($org_date == $date && $org_date < $today && date('Y-m-d',strtotime($date)) < $today)
            {
                $credit_check1= Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                $lastc=date('Y-m-d',strtotime($credit_check1->last_check));
                $precredit=$credit_check1->remaining;

                if($today == $lastc)
                {
                    $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                    $whole_visit= floor($visits);
                    $whole = floor($visit_hours);




                    $fraction = $visit_hours - $whole; // getting part after decimal point

                    /*
							 * Price calculating
							 */
                    if ($fraction)
                    {

                        if($whole>1 && $whole_visit > 1)
                        {

                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                        }
                        else if($whole > 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }
                        else if($whole < 1 && $whole_visit == 1)
                        {

                            $total = $price->half_hour_price;

                        }else if($whole < 1 && $whole_visit > 1)
                        {
                            $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }

                    } else {

                        if($whole>1 && $whole_visit>1)
                        {
                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                        }
                        else if($whole>1 && $whole_visit == 1)
                        {
                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                        }
                        else if($whole ==1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                        }
                        else
                        {
                            $total = $price->full_hour_price;

                        }
                    }
                    $credit_check = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                    $rem=$credit_check->remaining;
                    //echo $rem;
                    //die;
                    $diff=0;
                    if($old_amount > $total)
                    {

                        $diff= $old_amount - $total;
                        $credit_check->remaining=$rem+$diff;
                        $credit_check->save();

                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Alteration";
                        $log->amount = $diff;
                        $log->l_flag = "Added";
                        $log->old_value = $rem;
                        $log->new_value = $rem+$diff;
                        $log->save();
                        $log->log_id = (int) $log->log_id;
                    }else{

                        $diff= $total - $old_amount;

                        $credit_check->remaining=$rem-$diff;

                        $credit_check->save();
                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                        $pet_name = $pet->pet_name;
                        $log = new transactionlog();
                        $log->company_id = $company_id;
                        $log->client_id = $client_id;
                        $log->pet_id = $pet_id;
                        $log->service_id = $service_id;
                        $log->pet_name = $pet_name;
                        $log->date_of_transaction = date('Y-m-d H:i:s');
                        $log->type = "Alteration";
                        $log->amount = $diff;
                        $log->l_flag = "Deducted";
                        $log->old_value = $rem;
                        $log->new_value = $rem-$diff;
                        $log->save();
                        $log->log_id = (int) $log->log_id;
                    }
                    /*
							 *  Stroing to table
							 */

                    //$appointment = new Appointment();
                    $appointment->company_id = $company_id;
                    $appointment->client_id = $client_id;
                    $appointment->service_id = $service_id;
                    $appointment->date = $date;
                    $appointment->visits = $visits;
                    $appointment->visit_hours = $visit_hours;
                    $appointment->price = $total;

                    //$appointment->status = 'accepted';					//status stored in db
                    $appointment->pet_id = $pet_id;
                    $appointment->message = $message;
                    if (empty($rf_company_id)) {
                        $appointment->created_by = 'client';
                    } else {
                        $appointment->created_by = 'company';
                    }
                    $appointment->created_at = date('Y-m-d H:i:s');
                    $appointment->save();
                    $appointment->appointment_id = (int) $appointment->appointment_id;


                    $service = Service::find($service_id);
                    $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                    /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                    if ($appointment->appointment_id > 0)
                    {


                        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                        $companyServices = array();
                        foreach ($services as $companyService)
                        {
                            $companyServices[] = array(
                                'id' => $companyService->service_id,
                                'name' => $companyService->service_name
                            );
                        }
                        $ownerDetail = array(
                            'client_id' => $appointment->client->client_id,
                            'firstname' => $appointment->client->firstname,
                            'lastname' => $appointment->client->lastname,
                            'emailid' => $appointment->client->emailid,
                            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            'contact_number' => $appointment->client->contact_number,
                            'client_address' => $appointment->client->client_address,
                            'client_notes' => $appointment->client->client_notes,
                            'player_id' => $appointment->client->player_id,
                        );

                        $companyDetail = array(
                            'company_id' => $appointment->company->company_id,
                            'account_id' => $appointment->company->account_id,
                            'company_name' => $appointment->company->company_name,
                            'emailid' => $appointment->company->emailid,
                            'contact_number' => $appointment->company->contact_number,
                            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                            'website' => $appointment->company->website,
                            'address' => $appointment->company->address,
                            'about' => $appointment->company->about,
                            'services' => $companyServices,
                        );

                        $response['error_code'] = 0;
                        $response['status'] = true;
                        $response['message'] = 'Appointment Successfully updated.';
                        $response['data'] = array(
                            'appointment_id' => $appointment->appointment_id,
                            'company_detail' => $companyDetail,
                            'owner_detail' => $ownerDetail,
                            'isManualClient' => $flag,
                            //'compnay_id' => $appointment->company_id,
                            // 'client_id' => $client_id,
                            // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                            'service_id' => $appointment->service_id,
                            'service_name' => $service->service_name,
                            'pet_id' => $appointment->pet_id,
                            'date' => $appointment->date,
                            'visits' => $visits,
                            'visit_hours' => $visit_hours,
                            //  'price' => $appointment->price,
                            'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                            'pet_detail' => array(
                                'pet_id' => $appointment->pet->pet_id,
                                'pet_name' => $appointment->pet->pet_name,
                                'pet_birth' => $appointment->pet->pet_birth,

                                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                                'pet_age' => $appointment->pet->age,
                                'gender' => $appointment->pet->gender,
                                'pet_type' => $appointment->pet->pet_type,
                                'breed' => $appointment->pet->breed,
                                'neutered' => $appointment->pet->neutered,
                                'spayed' => $appointment->pet->spayed,
                                'injuries' => $appointment->pet->injuries,
                                'medical_detail' => $appointment->pet->medical_detail,
                                'pet_notes' => $appointment->pet->pet_notes,
                                'latitude' => $appointment->pet->latitude,
                                'longitude' => $appointment->pet->longitude,
                            ),
                            'message' => $message,
                            'notification_flag' => 'appointment_booking'
                        );

                    }

                }else{
                    $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
                    $ab=date('Y-m-d');

                    $total=0;
                    foreach ($aa as $val)
                    {

                        $service_id1=$val->service_id;
                        $test1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                        if(count($test1) == 0)
                        {

                            $credit = new Credits();
                            $credit->company_id = $company_id;
                            $credit->client_id = $client_id;
                            $credit->pet_id = $pet_id;
                            $credit->service_id = $service_id1;
                            //$credit->credits = 0;
                            $credit->paid_amount = 0;
                            $credit->old_amount = 0;
                            $credit->date_of_payment = null;
                            $credit->last_check = $ab;
                            $credit->remaining=0;
                            $credit->save();
                            $credit->credit_id = (int) $credit->credit_id;
                        }




                        $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                        if (count($creditCheck)>0)

                        {
                            foreach ($creditCheck as  $valu)
                            {
                                $last_date=$valu->last_check;
                                $credits2=(float)$valu->paid_amount;
                                $remains=$valu->remaining;

                            }


                            $last_check1=date('Y-m-d',strtotime($last_date));



                            if($last_check1 != $ab)
                            {

                                $datetime = new DateTime($last_check1);
                                $datetime->modify('+1 day');
                                $l_check=$datetime->format('Y-m-d');

                                $appoint = Appointment::find_by_sql("SELECT price as p , date as d FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and pet_id=$pet_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab'");


                                if(count($appoint)>0)
                                {
                                    $t_price=0;

                                    foreach ($appoint as  $value1)
                                    {
                                        $t_price += $value1->p;
                                        $remaining=$remains;

                                        $remaining-=(float)$t_price;


                                        /*added extra for make all field zero when remaining is 0*/
                                        if($remaining == 0)
                                        {

                                            $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                            $creditCheck4->paid_amount=0;

                                            $creditCheck4->save();

                                            $total =0;

                                        }

                                        $pet=Pet::find($pet_id);
                                        $pet_name = $pet->pet_name;
                                        $log = new transactionlog();
                                        $log->company_id = $company_id;
                                        $log->client_id = $client_id;
                                        $log->pet_id = $pet_id;
                                        $log->service_id = $service_id1;
                                        $log->pet_name = $pet_name;
                                        $log->date_of_transaction = date('Y-m-d H:i:s',strtotime($value1->d));
                                        $log->type = "Charge";
                                        $log->amount = $value1->p;
                                        $log->l_flag = "Deducted";
                                        $log->old_value = $remains;
                                        $log->new_value = $remaining;
                                        $log->save();
                                        $log->log_id = (int) $log->log_id;

                                    }
                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));
                                    if(count($creditCheck1) >0)
                                    {
                                        $creditCheck1->last_check=date('Y-m-d');

                                        $creditCheck1->remaining=$remaining;
                                        $creditCheck1->save();
                                    }
                                }else{
                                    $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                    if(count($creditCheck1) >0)
                                    {
                                        $creditCheck1->last_check=$ab;
                                        if($creditCheck1->remaining == 0)
                                        {

                                            $creditCheck1->paid_amount=0;

                                            $creditCheck1->date_of_payment=null;

                                        }

                                        $creditCheck1->save();
                                    }

                                    $remaining=(float)$remains;


                                }

                            }else{

                                $remaining=(float)$remains;
                            }


                        }else{


                            $credit = new Credits();
                            $credit->company_id = $company_id;
                            $credit->client_id = $client_id;
                            $credit->pet_id = $pet_id;
                            $credit->service_id = $service_id1;
                            //$credit->credits = 0;
                            $credit->paid_amount = 0;
                            $credit->old_amount = 0;
                            $credit->date_of_payment = null;
                            $credit->last_check = $ab;
                            $credit->remaining=0;
                            $credit->save();
                            $credit->credit_id = (int) $credit->credit_id;


                            $remaining=0;

                        }

                    }

                    $creditt = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

                    $creditt->remaining += $old_amount;
                    $creditt->save();

                    $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                    $whole_visit= floor($visits);
                    $whole = floor($visit_hours);




                    $fraction = $visit_hours - $whole; // getting part after decimal point

                    /*
							 * Price calculating
							 */
                    if ($fraction)
                    {

                        if($whole>1 && $whole_visit > 1)
                        {

                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                        }
                        else if($whole > 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit == 1)
                        {

                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                        }
                        else if($whole == 1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }
                        else if($whole < 1 && $whole_visit == 1)
                        {

                            $total = $price->half_hour_price;

                        }else if($whole < 1 && $whole_visit > 1)
                        {
                            $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                        }

                    } else {

                        if($whole>1 && $whole_visit>1)
                        {
                            $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                        }
                        else if($whole>1 && $whole_visit == 1)
                        {
                            $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                        }
                        else if($whole ==1 && $whole_visit>1)
                        {
                            $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                        }
                        else
                        {
                            $total = $price->full_hour_price;

                        }
                    }

                    /*
							 *  Stroing to table
							 */

                    //$appointment = new Appointment();
                    $appointment->company_id = $company_id;
                    $appointment->client_id = $client_id;
                    $appointment->service_id = $service_id;
                    $appointment->date = $date;
                    $appointment->visits = $visits;
                    $appointment->visit_hours = $visit_hours;
                    $appointment->price = $total;

                    //$appointment->status = 'accepted';					//status stored in db
                    $appointment->pet_id = $pet_id;
                    $appointment->message = $message;
                    if (empty($rf_company_id)) {
                        $appointment->created_by = 'client';
                    } else {
                        $appointment->created_by = 'company';
                    }
                    $appointment->created_at = date('Y-m-d H:i:s');
                    $appointment->save();
                    $appointment->appointment_id = (int) $appointment->appointment_id;


                    $service = Service::find($service_id);
                    $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                    /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                    if ($appointment->appointment_id > 0)
                    {


                        $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                        $companyServices = array();
                        foreach ($services as $companyService)
                        {
                            $companyServices[] = array(
                                'id' => $companyService->service_id,
                                'name' => $companyService->service_name
                            );
                        }
                        $ownerDetail = array(
                            'client_id' => $appointment->client->client_id,
                            'firstname' => $appointment->client->firstname,
                            'lastname' => $appointment->client->lastname,
                            'emailid' => $appointment->client->emailid,
                            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            'contact_number' => $appointment->client->contact_number,
                            'client_address' => $appointment->client->client_address,
                            'client_notes' => $appointment->client->client_notes,
                            'player_id' => $appointment->client->player_id,
                        );

                        $companyDetail = array(
                            'company_id' => $appointment->company->company_id,
                            'account_id' => $appointment->company->account_id,
                            'company_name' => $appointment->company->company_name,
                            'emailid' => $appointment->company->emailid,
                            'contact_number' => $appointment->company->contact_number,
                            'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                            'website' => $appointment->company->website,
                            'address' => $appointment->company->address,
                            'about' => $appointment->company->about,
                            'services' => $companyServices,
                        );

                        $response['error_code'] = 0;
                        $response['status'] = true;
                        $response['message'] = 'Appointment Successfully updated.';
                        $response['data'] = array(
                            'appointment_id' => $appointment->appointment_id,
                            'company_detail' => $companyDetail,
                            'owner_detail' => $ownerDetail,
                            'isManualClient' => $flag,
                            //'compnay_id' => $appointment->company_id,
                            // 'client_id' => $client_id,
                            // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                            //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                            'service_id' => $appointment->service_id,
                            'service_name' => $service->service_name,
                            'pet_id' => $appointment->pet_id,
                            'date' => $appointment->date,
                            'visits' => $visits,
                            'visit_hours' => $visit_hours,
                            //  'price' => $appointment->price,
                            'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                            'pet_detail' => array(
                                'pet_id' => $appointment->pet->pet_id,
                                'pet_name' => $appointment->pet->pet_name,
                                'pet_birth' => $appointment->pet->pet_birth,

                                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                                'pet_age' => $appointment->pet->age,
                                'gender' => $appointment->pet->gender,
                                'pet_type' => $appointment->pet->pet_type,
                                'breed' => $appointment->pet->breed,
                                'neutered' => $appointment->pet->neutered,
                                'spayed' => $appointment->pet->spayed,
                                'injuries' => $appointment->pet->injuries,
                                'medical_detail' => $appointment->pet->medical_detail,
                                'pet_notes' => $appointment->pet->pet_notes,
                                'latitude' => $appointment->pet->latitude,
                                'longitude' => $appointment->pet->longitude,
                            ),
                            'message' => $message,
                            'notification_flag' => 'appointment_booking'
                        );

                    }


                }

            }
        }


    }
    echoResponse(200, $response);
    // });
});

$app->post('/appointment/:id/editnew', function($id) use ($app) {
//echo $id;
//    die;
    $exist = Appointment::exists($id);


    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];

    if ($exist) {

        $appointment = Appointment::find($id);
        $old_amount= $appointment->price;

        $company_id = empty($app->request->post('company_id')) ? $appointment->company_id : $app->request->post('company_id');
        $service_id = empty($app->request->post('service_id')) ? $appointment->service_id : $app->request->post('service_id');
        $date = empty($app->request->post('date')) ? $appointment->date : date('Y-m-d', strtotime($app->request->post('date')));
        $org_date=date('Y-m-d', strtotime($appointment->date));//$date = date('Y-m-d', $stringdate);
        //  $date2 = date('d-m-Y', $stringdate);
        $visits = empty($app->request->post('visits')) ? $appointment->visits : $app->request->post('visits');
        $visit_hours = empty($app->request->post('visit_hours')) ? $appointment->visit_hours : $app->request->post('visit_hours');
        //$price = $app->request->post('price');
        //$status = $app->request->post('status');
        $pet_id = empty($app->request->post('pet_id')) ? $appointment->pet_id : $app->request->post('pet_id');
        $message = empty($app->request->post('message')) ? $appointment->message : $app->request->post('message');
        $client_id = empty($app->request->post('client_id')) ? $appointment->client_id : (int) $app->request->post('client_id');
        $client = Client::find($client_id);
        $client_name = $client->firstname . ' ' . $client->lastname;
        $rf_company_id = $client->company_id;

        $clientCheck = Client::find($client_id);
        $flag = $clientCheck->company_id != NULL ? true : NULL;

        $today = date('Y-m-d');


        if(date('Y-m-d',strtotime($date)) > $today)
        {
            if($org_date <= $today)
            {
                /*start for refund*/
                $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
                $ab=date('Y-m-d');

                $total=0;
                foreach ($aa as $val)
                {

                    $service_id1=$val->service_id;
                    $test1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                    if(count($test1) == 0)
                    {

                        $credit = new Credits();
                        $credit->company_id = $company_id;
                        $credit->client_id = $client_id;
                        $credit->pet_id = $pet_id;
                        $credit->service_id = $service_id1;
                        //$credit->credits = 0;
                        $credit->paid_amount = 0;
                        $credit->old_amount = 0;
                        $credit->date_of_payment = null;
                        $credit->last_check = $ab;
                        $credit->remaining=0;
                        $credit->save();
                        $credit->credit_id = (int) $credit->credit_id;
                    }




                    $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                    if (count($creditCheck)>0)

                    {
                        foreach ($creditCheck as  $valu)
                        {
                            $last_date=$valu->last_check;
                            $credits2=(float)$valu->paid_amount;
                            $remains=$valu->remaining;

                        }


                        $last_check1=date('Y-m-d',strtotime($last_date));



                        if($last_check1 != $ab)
                        {

                            $datetime = new DateTime($last_check1);
                            $datetime->modify('+1 day');
                            $l_check=$datetime->format('Y-m-d');

                            $appoint = Appointment::find_by_sql("SELECT price as p , date as d FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and pet_id=$pet_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab'");


                            if(count($appoint)>0)
                            {
                                $t_price=0;

                                foreach ($appoint as  $value1)
                                {
                                    $t_price += $value1->p;
                                    $remaining=$remains;

                                    $remaining-=(float)$t_price;


                                    /*added extra for make all field zero when remaining is 0*/
                                    if($remaining == 0)
                                    {

                                        $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                        $creditCheck4->paid_amount=0;

                                        $creditCheck4->save();

                                        $total =0;

                                    }

                                    $pet=Pet::find($pet_id);
                                    $pet_name = $pet->pet_name;
                                    $log = new transactionlog();
                                    $log->company_id = $company_id;
                                    $log->client_id = $client_id;
                                    $log->pet_id = $pet_id;
                                    $log->service_id = $service_id1;
                                    $log->pet_name = $pet_name;
                                    $log->date_of_transaction = date('Y-m-d H:i:s',strtotime($value1->d));
                                    $log->type = "Charge";
                                    $log->amount = $value1->p;
                                    $log->l_flag = "Deducted";
                                    $log->old_value = $remains;
                                    $log->new_value = $remaining;
                                    $log->save();
                                    $log->log_id = (int) $log->log_id;

                                }
                                $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));
                                if(count($creditCheck1) >0)
                                {
                                    $creditCheck1->last_check=date('Y-m-d');

                                    $creditCheck1->remaining=$remaining;
                                    $creditCheck1->save();
                                }
                            }else{
                                $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                if(count($creditCheck1) >0)
                                {
                                    $creditCheck1->last_check=$ab;
                                    if($creditCheck1->remaining == 0)
                                    {

                                        $creditCheck1->paid_amount=0;

                                        $creditCheck1->date_of_payment=null;

                                    }

                                    $creditCheck1->save();
                                }

                                $remaining=(float)$remains;


                            }

                        }else{

                            $remaining=(float)$remains;
                        }


                    }else{


                        $credit = new Credits();
                        $credit->company_id = $company_id;
                        $credit->client_id = $client_id;
                        $credit->pet_id = $pet_id;
                        $credit->service_id = $service_id1;
                        //$credit->credits = 0;
                        $credit->paid_amount = 0;
                        $credit->old_amount = 0;
                        $credit->date_of_payment = null;
                        $credit->last_check = $ab;
                        $credit->remaining=0;
                        $credit->save();
                        $credit->credit_id = (int) $credit->credit_id;


                        $remaining=0;

                    }

                }

                $creditt = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

                $creditt->remaining += $old_amount;
                $creditt->save();

                $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


                $whole_visit= floor($visits);
                $whole = floor($visit_hours);




                $fraction = $visit_hours - $whole; // getting part after decimal point

                /*
							 * Price calculating
							 */
                if ($fraction)
                {

                    if($whole>1 && $whole_visit > 1)
                    {

                        $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                    }
                    else if($whole > 1 && $whole_visit == 1)
                    {

                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                    }
                    else if($whole == 1 && $whole_visit == 1)
                    {

                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                    }
                    else if($whole == 1 && $whole_visit>1)
                    {
                        $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                    }
                    else if($whole < 1 && $whole_visit == 1)
                    {

                        $total = $price->half_hour_price;

                    }else if($whole < 1 && $whole_visit > 1)
                    {
                        $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                    }

                } else {

                    if($whole>1 && $whole_visit>1)
                    {
                        $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                    }
                    else if($whole>1 && $whole_visit == 1)
                    {
                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                    }
                    else if($whole ==1 && $whole_visit>1)
                    {
                        $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                    }
                    else
                    {
                        $total = $price->full_hour_price;

                    }
                }

                /*
							 *  Stroing to table
							 */

                //$appointment = new Appointment();
                $appointment->company_id = $company_id;
                $appointment->client_id = $client_id;
                $appointment->service_id = $service_id;
                $appointment->date = $date;
                $appointment->visits = $visits;
                $appointment->visit_hours = $visit_hours;
                $appointment->price = $total;

                //$appointment->status = 'accepted';					//status stored in db
                $appointment->pet_id = $pet_id;
                $appointment->message = $message;
                if (empty($rf_company_id)) {
                    $appointment->created_by = 'client';
                } else {
                    $appointment->created_by = 'company';
                }
                $appointment->created_at = date('Y-m-d H:i:s');
                $appointment->save();
                $appointment->appointment_id = (int) $appointment->appointment_id;


                $service = Service::find($service_id);
                $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                if ($appointment->appointment_id > 0)
                {


                    $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                    $companyServices = array();
                    foreach ($services as $companyService)
                    {
                        $companyServices[] = array(
                            'id' => $companyService->service_id,
                            'name' => $companyService->service_name
                        );
                    }
                    $ownerDetail = array(
                        'client_id' => $appointment->client->client_id,
                        'firstname' => $appointment->client->firstname,
                        'lastname' => $appointment->client->lastname,
                        'emailid' => $appointment->client->emailid,
                        'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                        'contact_number' => $appointment->client->contact_number,
                        'client_address' => $appointment->client->client_address,
                        'client_notes' => $appointment->client->client_notes,
                        'player_id' => $appointment->client->player_id,
                    );

                    $companyDetail = array(
                        'company_id' => $appointment->company->company_id,
                        'account_id' => $appointment->company->account_id,
                        'company_name' => $appointment->company->company_name,
                        'emailid' => $appointment->company->emailid,
                        'contact_number' => $appointment->company->contact_number,
                        'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                        'website' => $appointment->company->website,
                        'address' => $appointment->company->address,
                        'about' => $appointment->company->about,
                        'services' => $companyServices,
                    );

                    $response['error_code'] = 0;
                    $response['status'] = true;
                    $response['message'] = 'Appointment Successfully updated.';
                    $response['data'] = array(
                        'appointment_id' => $appointment->appointment_id,
                        'company_detail' => $companyDetail,
                        'owner_detail' => $ownerDetail,
                        'isManualClient' => $flag,
                        //'compnay_id' => $appointment->company_id,
                        // 'client_id' => $client_id,
                        // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                        //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                        'service_id' => $appointment->service_id,
                        'service_name' => $service->service_name,
                        'pet_id' => $appointment->pet_id,
                        'date' => $appointment->date,
                        'visits' => $visits,
                        'visit_hours' => $visit_hours,
                        //  'price' => $appointment->price,
                        'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                        'pet_detail' => array(
                            'pet_id' => $appointment->pet->pet_id,
                            'pet_name' => $appointment->pet->pet_name,
                            'pet_birth' => $appointment->pet->pet_birth,

                            'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                            'pet_age' => $appointment->pet->age,
                            'gender' => $appointment->pet->gender,
                            'pet_type' => $appointment->pet->pet_type,
                            'breed' => $appointment->pet->breed,
                            'neutered' => $appointment->pet->neutered,
                            'spayed' => $appointment->pet->spayed,
                            'injuries' => $appointment->pet->injuries,
                            'medical_detail' => $appointment->pet->medical_detail,
                            'pet_notes' => $appointment->pet->pet_notes,
                            'latitude' => $appointment->pet->latitude,
                            'longitude' => $appointment->pet->longitude,
                        ),
                        'message' => $message,
                        'notification_flag' => 'appointment_booking'
                    );

                }

                /* end for refund*/
            }
            else{
                /*start no change*/

                $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
                // print_r($price);

                $whole_visit= floor($visits);
                $whole = floor($visit_hours);

                // echo $whole_visit."</br>";
                // echo $whole;

                // whole number from
                $fraction = $visit_hours - $whole; // getting part after decimal point
                // echo $fraction;
                // die;
                /*
							         * Price calculating
							         */
                if ($fraction)
                {

                    if($whole>1 && $whole_visit > 1)
                    {

                        $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                    }
                    else if($whole > 1 && $whole_visit == 1)
                    {

                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                    }
                    else if($whole == 1 && $whole_visit == 1)
                    {

                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                    }
                    else if($whole == 1 && $whole_visit>1)
                    {
                        $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                    }
                    else if($whole < 1 && $whole_visit == 1)
                    {

                        $total = $price->half_hour_price;

                    }else if($whole < 1 && $whole_visit > 1)
                    {
                        $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                    }

                } else {
                    //if($whole>1)
                    //$total = $whole * $price->full_hour_price;
                    if($whole>1 && $whole_visit>1)
                    {
                        $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                    }
                    else if($whole>1 && $whole_visit == 1)
                    {
                        $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                    }
                    else if($whole ==1 && $whole_visit>1)
                    {
                        $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                    }
                    else
                    {
                        $total = $price->full_hour_price;

                    }
                }

                /*
							         *  Stroing to table
							         */

                //$appointment = new Appointment();
                $appointment->company_id = $company_id;
                $appointment->client_id = $client_id;
                $appointment->service_id = $service_id;
                $appointment->date = $date;
                $appointment->visits = $visits;
                $appointment->visit_hours = $visit_hours;
                $appointment->price = $total;

                //$appointment->status = 'accepted';					//status stored in db
                $appointment->pet_id = $pet_id;
                $appointment->message = $message;
                if (empty($rf_company_id)) {
                    $appointment->created_by = 'client';
                } else {
                    $appointment->created_by = 'company';
                }
                $appointment->created_at = date('Y-m-d H:i:s');
                $appointment->save();
                $appointment->appointment_id = (int) $appointment->appointment_id;


                $service = Service::find($service_id);
                $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
                /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
                if ($appointment->appointment_id > 0) {


                    $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                    $companyServices = array();
                    foreach ($services as $companyService) {
                        $companyServices[] = array(
                            'id' => $companyService->service_id,
                            'name' => $companyService->service_name
                        );
                    }
                    $ownerDetail = array(
                        'client_id' => $appointment->client->client_id,
                        'firstname' => $appointment->client->firstname,
                        'lastname' => $appointment->client->lastname,
                        'emailid' => $appointment->client->emailid,
                        'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                        'contact_number' => $appointment->client->contact_number,
                        'client_address' => $appointment->client->client_address,
                        'client_notes' => $appointment->client->client_notes,
                        'player_id' => $appointment->client->player_id,
                    );

                    $companyDetail = array(
                        'company_id' => $appointment->company->company_id,
                        'account_id' => $appointment->company->account_id,
                        'company_name' => $appointment->company->company_name,
                        'emailid' => $appointment->company->emailid,
                        'contact_number' => $appointment->company->contact_number,
                        'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                        'website' => $appointment->company->website,
                        'address' => $appointment->company->address,
                        'about' => $appointment->company->about,
                        'services' => $companyServices,
                    );

                    $response['error_code'] = 0;
                    $response['status'] = true;
                    $response['message'] = 'Appointment Successfully updated.';
                    $response['data'] = array(
                        'appointment_id' => $appointment->appointment_id,
                        'company_detail' => $companyDetail,
                        'owner_detail' => $ownerDetail,
                        'isManualClient' => $flag,
                        //'compnay_id' => $appointment->company_id,
                        // 'client_id' => $client_id,
                        // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                        //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                        'service_id' => $appointment->service_id,
                        'service_name' => $service->service_name,
                        'pet_id' => $appointment->pet_id,
                        'date' => $appointment->date,
                        'visits' => $visits,
                        'visit_hours' => $visit_hours,
                        //  'price' => $appointment->price,
                        'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                        'pet_detail' => array(
                            'pet_id' => $appointment->pet->pet_id,
                            'pet_name' => $appointment->pet->pet_name,
                            'pet_birth' => $appointment->pet->pet_birth,

                            'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                            'pet_age' => $appointment->pet->age,
                            'gender' => $appointment->pet->gender,
                            'pet_type' => $appointment->pet->pet_type,
                            'breed' => $appointment->pet->breed,
                            'neutered' => $appointment->pet->neutered,
                            'spayed' => $appointment->pet->spayed,
                            'injuries' => $appointment->pet->injuries,
                            'medical_detail' => $appointment->pet->medical_detail,
                            'pet_notes' => $appointment->pet->pet_notes,
                            'latitude' => $appointment->pet->latitude,
                            'longitude' => $appointment->pet->longitude,
                        ),
                        'message' => $message,
                        'notification_flag' => 'appointment_booking'
                    );

                }
                /*end no change*/

            }
        }
        else{
            /*start alteration*/
            $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));


            $whole_visit= floor($visits);
            $whole = floor($visit_hours);




            $fraction = $visit_hours - $whole; // getting part after decimal point

            /*
							 * Price calculating
							 */
            if ($fraction)
            {

                if($whole>1 && $whole_visit > 1)
                {

                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


                }
                else if($whole > 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit == 1)
                {

                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

                }
                else if($whole == 1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }
                else if($whole < 1 && $whole_visit == 1)
                {

                    $total = $price->half_hour_price;

                }else if($whole < 1 && $whole_visit > 1)
                {
                    $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

                }

            } else {

                if($whole>1 && $whole_visit>1)
                {
                    $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

                }
                else if($whole>1 && $whole_visit == 1)
                {
                    $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

                }
                else if($whole ==1 && $whole_visit>1)
                {
                    $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

                }
                else
                {
                    $total = $price->full_hour_price;

                }
            }
            $credit_check = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id} AND pet_id={$pet_id}"));
            $rem=$credit_check->remaining;
            //echo $rem;
            //die;
            $diff=0;
            if($old_amount > $total)
            {

                $diff= $old_amount - $total;
                $credit_check->remaining=$rem+$diff;
                $credit_check->save();

                $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                $pet_name = $pet->pet_name;
                $log = new transactionlog();
                $log->company_id = $company_id;
                $log->client_id = $client_id;
                $log->pet_id = $pet_id;
                $log->service_id = $service_id;
                $log->pet_name = $pet_name;
                $log->date_of_transaction = date('Y-m-d H:i:s');
                $log->type = "Alteration";
                $log->amount = $diff;
                $log->l_flag = "Added";
                $log->old_value = $rem;
                $log->new_value = $rem+$diff;
                $log->save();
                $log->log_id = (int) $log->log_id;
            }else{

                $diff= $total - $old_amount;

                $credit_check->remaining=$rem-$diff;

                $credit_check->save();
                $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                $pet_name = $pet->pet_name;
                $log = new transactionlog();
                $log->company_id = $company_id;
                $log->client_id = $client_id;
                $log->pet_id = $pet_id;
                $log->service_id = $service_id;
                $log->pet_name = $pet_name;
                $log->date_of_transaction = date('Y-m-d H:i:s');
                $log->type = "Alteration";
                $log->amount = $diff;
                $log->l_flag = "Deducted";
                $log->old_value = $rem;
                $log->new_value = $rem-$diff;
                $log->save();
                $log->log_id = (int) $log->log_id;
            }
            /*
							 *  Stroing to table
							 */

            //$appointment = new Appointment();
            $appointment->company_id = $company_id;
            $appointment->client_id = $client_id;
            $appointment->service_id = $service_id;
            $appointment->date = $date;
            $appointment->visits = $visits;
            $appointment->visit_hours = $visit_hours;
            $appointment->price = $total;

            //$appointment->status = 'accepted';					//status stored in db
            $appointment->pet_id = $pet_id;
            $appointment->message = $message;
            if (empty($rf_company_id)) {
                $appointment->created_by = 'client';
            } else {
                $appointment->created_by = 'company';
            }
            $appointment->created_at = date('Y-m-d H:i:s');
            $appointment->save();
            $appointment->appointment_id = (int) $appointment->appointment_id;


            $service = Service::find($service_id);
            $username = $appointment->client->firstname . ' ' . $appointment->client->lastname;
            /* $username .' wants to request '.$service->service_name.' for '.$appointment->pet->pet_name.' '. $appointment->client->lastname.' on '.$contract->created_at */
            if ($appointment->appointment_id > 0)
            {


                $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$appointment->company->company_id}");
                $companyServices = array();
                foreach ($services as $companyService)
                {
                    $companyServices[] = array(
                        'id' => $companyService->service_id,
                        'name' => $companyService->service_name
                    );
                }
                $ownerDetail = array(
                    'client_id' => $appointment->client->client_id,
                    'firstname' => $appointment->client->firstname,
                    'lastname' => $appointment->client->lastname,
                    'emailid' => $appointment->client->emailid,
                    'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                    'contact_number' => $appointment->client->contact_number,
                    'client_address' => $appointment->client->client_address,
                    'client_notes' => $appointment->client->client_notes,
                    'player_id' => $appointment->client->player_id,
                );

                $companyDetail = array(
                    'company_id' => $appointment->company->company_id,
                    'account_id' => $appointment->company->account_id,
                    'company_name' => $appointment->company->company_name,
                    'emailid' => $appointment->company->emailid,
                    'contact_number' => $appointment->company->contact_number,
                    'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                    'website' => $appointment->company->website,
                    'address' => $appointment->company->address,
                    'about' => $appointment->company->about,
                    'services' => $companyServices,
                );

                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Appointment Successfully updated.';
                $response['data'] = array(
                    'appointment_id' => $appointment->appointment_id,
                    'company_detail' => $companyDetail,
                    'owner_detail' => $ownerDetail,
                    'isManualClient' => $flag,
                    //'compnay_id' => $appointment->company_id,
                    // 'client_id' => $client_id,
                    // 'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                    //'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
                    'service_id' => $appointment->service_id,
                    'service_name' => $service->service_name,
                    'pet_id' => $appointment->pet_id,
                    'date' => $appointment->date,
                    'visits' => $visits,
                    'visit_hours' => $visit_hours,
                    //  'price' => $appointment->price,
                    'status' => $appointment->status != NULL ? 'accepted' : $appointment->status,
                    'pet_detail' => array(
                        'pet_id' => $appointment->pet->pet_id,
                        'pet_name' => $appointment->pet->pet_name,
                        'pet_birth' => $appointment->pet->pet_birth,

                        'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                        'pet_age' => $appointment->pet->age,
                        'gender' => $appointment->pet->gender,
                        'pet_type' => $appointment->pet->pet_type,
                        'breed' => $appointment->pet->breed,
                        'neutered' => $appointment->pet->neutered,
                        'spayed' => $appointment->pet->spayed,
                        'injuries' => $appointment->pet->injuries,
                        'medical_detail' => $appointment->pet->medical_detail,
                        'pet_notes' => $appointment->pet->pet_notes,
                        'latitude' => $appointment->pet->latitude,
                        'longitude' => $appointment->pet->longitude,
                    ),
                    'message' => $message,
                    'notification_flag' => 'appointment_booking'
                );

            }

            /*end alteration*/
        }


    }
    echoResponse(200, $response);
    // });
});

/*
 * Appointment listing according to staff_id and company_id.
 */
$app->post('/:id/staffappointmentlist', function($id) use ($app) {

    verifyFields(array('staff_id','date'));

    $staff_id=$app->request->post('staff_id');
    $date=date('Y-m-d', strtotime($app->request->post('date')));

    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;

    $appointment = Appointment::find_by_sql("select * from tbl_appointments where company_id=$id AND staff_id=$staff_id AND date='$date'");

    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointment list retrive successfully according to staff.';

        $appointmentData = [];


        foreach ($appointment as $key => $value) {

            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$value->company->company_id}");
            $companyServices = array();
            foreach ($services as $companyService) {
                $companyServices[] = array(
                    'id' => $companyService->service_id,
                    'name' => $companyService->service_name
                );
            }
            // $new_k=$appointment[$key] + 1;
            // //print_r($new_k);
            // //die;
            // foreach($appointment as $new_k => $value_k)
            // {
            //     if($value_k->pet->pet_name == $value->pet->pet_name)
            //     {
            //         $appointmentData['pet_name'] = $value->pet->pet_name .' '. $value->client->lastname;
            //     }
            //     else{
            //         $appointmentData['pet_name'] = $value->pet->pet_name;
            //     }

            // }
            if ($value->status == 'accepted') {
                $staff = Staff::find($value->staff_id);
                $sfirstname = isset($staff->firstname) ? $staff->firstname : '';
                $slatname = isset($staff->lastname) ? $staff->lastname : NULL;
                $staff_image = isset($staff->profile_image) ? $staff->profile_image : '';

            } else {
                $sfirstname = '';
                $slatname = '';
                $staff_image = "";
            }
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
                'status' => $value->client->status == 1 ? 'active' : 'inactive',
            );
//if($type == 'compnay')
            $companyDetail = array(
                'company_id' => $value->company->company_id,
                'account_id' => $value->company->account_id,
                'company_name' => $value->company->company_name,
                'emailid' => $value->company->emailid,
                'contact_number' => $value->company->contact_number,
                'company_image' => $value->company->company_image != NULL ? COMPANY_PIC_PATH . $value->company->company_image : NULL,
                'website' => $value->company->website,
                'address' => $value->company->address,
                'about' => $value->company->about,
                'services' => $companyServices,
            );
            $price = Price::find(array('conditions' => "service_id = $value->service_id"));
//var_dump($price);
// die;

            $whole = floor($value->visit_hours);      // whole number from
            $fraction = $value->visit_hours - $whole; // getting part after decimal point

            /*
             * Price calculating
             */
            if (!empty($price)) {

                if ($fraction) {

                    $minutes = ($fraction * 100);   // gettong minutes from
                    if ($minutes == 30 && empty($whole)) {
                        $total = ($price->half_hour_price);
                    } else {
                        $total = $whole * $price->full_hour_price + round($minutes * ($price->full_hour_price / 60));
                    }
                } else {
                    $total = $whole * $price->full_hour_price;
                }
            } else {
                $total = NULL;
            }
            $flag = $value->created_by == 'company' ? TRUE : FALSE;
            $pet1 = Appointment::find('all',array("conditions" => "pet_id= '{$value->pet_id}'"));
            //$pet_nm = Pet::find_by_sql("select pet_name from tbl_pets where pet_id='{$value->pet_id}'");
            //$temp = $pet_nm + 1;
            //print_r($temp);
            //die;
            $petnm_detail = array(
                'pet_name' => $value->pet->pet_name,);
            // print_r($petnm_detail);
            //die;
            $appointmentData[] = array(
                'appointment_id' => $value->appointment_id,
                'company_detail' => $companyDetail,
                'owner_detail' => $ownerDetail,
                'isManualClient' => $flag,
                'service_id' => $value->service_id,
                'service_name' => $serviceName,
                'date' => $value->date,
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'base_price' => $value->visits * $total,
                'additional_visit' => NULL,
                'additional_visit_price' => empty($price) ? NULL : (NULL * $price->additional_visits_price),
                'additional_hour' => NULL,
                'additional_hour_price' => empty($price) ? NULL : (NULL * $price->additional_hours_price),
                'price' => $value->price,
                'status' => $value->status,
                'accepted' =>$value->accepted,
                'staff_firstname' => $sfirstname,
                'staff_lastname' => $slatname,
                'staff_image' => $staff_image,
                'pet_detail' => array(
                    'pet_id' => $value->pet->pet_id,
                    'pet_name' =>  count($pet1)>1 ? $value->pet->pet_name.' '.$value->client->lastname : $value->pet->pet_name,
                    'pet_birth' => $value->pet->pet_birth,

                    'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                    'pet_age' => $value->pet->age,
                    'gender' => $value->pet->gender,
                    'pet_type' => $value->pet->pet_type,
                    'breed' => $value->pet->breed,
                    'neutered' => $value->pet->neutered,
                    'spayed' => $value->pet->spayed,
                    'injuries' => $value->pet->injuries,
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
 * Appointment listing according to type(company/client) ,id(companyid/clientid) and status[optional]
 */
$app->post('/:type/:id(/:status)/appointments(/:today)', function($type, $id, $status = NULL,$today=NULL) use ($app) {

    $date= date('Y-m-d',strtotime($app->request->post('date')));


    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;
    $condition = '';
    if ($type == 'company') {
        $condition = "company_id";

        $contract=Contract::find('all',array("conditions" => "company_id = {$id}"));
        foreach ($contract as $ke => $val) {
            $clientid=$val->client_id;

            $pet= Pet::find('all',array("conditions" => "client_id = {$clientid}"));
            foreach ($pet as $key1 => $value1) {
                $petname[]=$value1->pet_name;

            }
        }
    } else {
        $condition = "client_id";
        $pet= Pet::find('all',array("conditions" => "client_id = {$id}"));
        foreach ($pet as $key1 => $value1) {
            $petname[]=$value1->pet_name;

        }
    }


// $contract=Contract::find('all',array("conditions" => "company_id = {$id}"));
//     foreach ($contract as $ke => $val) {
//         $clientid=$val->client_id;

//         $pet= Pet::find('all',array("conditions" => "client_id = {$clientid}"));
//         foreach ($pet as $key1 => $value1) {
//             $petname[]=$value1->pet_name;

//         }
//     }


    /*$limit = 10;
    if($page == 1)
    {
        $newoffset = 0;
    }else{
        $newoffset = ($page-1)*$limit;
    }*/

    if ($status != NULL && $today == NULL) {
        if ($status == 'pending') {
            $appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id} AND status in('pending','payment pending')", 'order' => 'date desc'));
        } else if ($status == 'rejected') {
            $appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id} AND status = '{$status}' ",'order' => 'date desc'));
        } else {
            $appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id} AND status in('accepted','assign staff') ", 'order' => 'date desc'));
        }
    } else if ($today != NULL) {

        if ($status != NULL) {
            if ($status == 'assign') {
                $appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id} AND status in('assign staff') AND date =  '$date'" ,'order' => 'date desc'));
            } else if ($status == 'rejected') {
                $appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id} AND status = '{$status}' AND date =  '$date' ", 'order' => 'date desc'));
            }
        } else {

            $appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id} AND status  in('accepted','assign staff')  AND date =  '$date'", 'order' => 'date desc'));
        }
    } else {

        $appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id}", 'order' => 'date desc'));
    }

//     foreach ($appointment as $key => $value) {
//     $pet1[]=$value->pet->pet_name;

// }
//var_dump($id);
//die;

    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointment list retrive successfully.';

        $appointmentData = [];

        foreach ($appointment as $key => $value) {

            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$value->company->company_id}");
            $companyServices = array();
            foreach ($services as $companyService) {
                $companyServices[] = array(
                    'id' => $companyService->service_id,
                    'name' => $companyService->service_name
                );
            }

            if ($value->status == 'accepted' && $value->staff_id != NULL) {
                $staff = Staff::find($value->staff_id);
                $sfirstname = isset($staff->firstname) ? $staff->firstname : '';
                $slatname = isset($staff->lastname)?$staff->lastname:'';
                $sid = isset($staff->staff_id)?$staff->staff_id:'';
                $staff_image = isset($staff->profile_image) ? $staff->profile_image : '';
            } else {
                $sfirstname = '';
                $slatname = '';
                $sid = '';
                $staff_image = "";
            }


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
                'profile_status' => $value->client->status ==1 ? 'active' : 'inactive',
            );
            //if($type == 'compnay')
            $companyDetail = array(
                'company_id' => $value->company->company_id,
                'account_id' => $value->company->account_id,
                'company_name' => $value->company->company_name,
                'emailid' => $value->company->emailid,
                'contact_number' => $value->company->contact_number,
                'company_image' => $value->company->company_image != NULL ? COMPANY_PIC_PATH . $value->company->company_image : NULL,
                'website' => $value->company->website,
                'address' => $value->company->address,
                'about' => $value->company->about,
                'services' => $companyServices,
            );
            $price = Price::find(array('conditions' => "service_id = $value->service_id"));
            //var_dump($price);
            // die;

            $whole = floor($value->visit_hours);      // whole number from
            $fraction = $value->visit_hours - $whole; // getting part after decimal point

            /*
             * Price calculating
             */
            if (!empty($price)) {

                if ($fraction) {

                    $minutes = ($fraction * 100);   // gettong minutes from
                    if ($minutes == 30 && empty($whole)) {
                        $total = ($price->half_hour_price);
                    } else {
                        $total = $whole * $price->full_hour_price + round($minutes * ($price->full_hour_price / 60));
                    }
                } else {
                    $total = $whole * $price->full_hour_price;
                }
            } else {
                $total = NULL;
            }
            $flag = $value->created_by == 'company' ? TRUE : FALSE;
            $last_name = $value->client->lastname;
            $lst =  $value->client->client_id;
            /*append last name if first pet name is repeate*/
            $status1='';
            $maincoin=[];
            for($i=0;$i<count($petname);$i++)
            {
                // echo $haha."</br>";

                // print_r($maincoin);


                if(strcmp($petname[$i],$value->pet->pet_name)===0)
                {
                    $maincoin[]='a';
                    $status1='';
                    // echo "=========================$pet_names[$i]        ".$value2->pet_name.'==========</br>';
                }
                if(count($maincoin)>1)
                {
                    $status1='abcd';
                    // echo "$pet_names[$i]        ".$value2->pet_name.'</br>';
                    break;
                }



            }


            if($status1=='abcd')
            {
                $petfull = $value->pet->pet_name." ".$value->client->lastname;
            }
            else
            {
                $petfull = $value->pet->pet_name;
            }

            $backup_contact=[];
            $contact_check=Contact_backup::find(array("conditions"=>"client_id={$value->client->client_id} AND pet_id={$value->pet->pet_id}"));
            if($contact_check != NULL)
            {
                $backup_contact=array(
                    'name' => $contact_check->name,
                    'address' => $contact_check->address,
                    'number' => $contact_check->contact_number,
                );
            }else{
                $backup_contact= new stdClass();
            }
            $petDetail=array(
                'pet_id' => $value->pet->pet_id,
                'pet_name' => $petfull,
                'pet_birth' => $value->pet->pet_birth,

                'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                'pet_age' => $value->pet->age,
                'gender' => $value->pet->gender,
                'pet_type' => $value->pet->pet_type,
                'breed' => $value->pet->breed,
                'neutered' => $value->pet->neutered,
                'spayed' => $value->pet->spayed,
                'injuries' => $value->pet->injuries,
                'medical_detail' => $value->pet->medical_detail,
                'pet_notes' => $value->pet->pet_notes,
                'latitude' => $value->pet->latitude,
                'longitude' => $value->pet->longitude,
                'backupcontact' => $backup_contact,
            );

            //$pet1 = Appointment::find('all',array("conditions" => "pet_id = '{$value->pet->pet_id}'"));
            //$pet1 = Pet::find($value->pet->pet_name);
            $appointmentData[] = array(
                'appointment_id' => $value->appointment_id,
                'company_detail' => $companyDetail,
                'owner_detail' => $ownerDetail,
                'isManualClient' => $flag,
                'service_id' => $value->service_id,
                'service_name' => $serviceName,
                'date' => $value->date,
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'base_price' => $value->visits * $total,
                'additional_visit' => NULL,
                'additional_visit_price' => empty($price) ? NULL : (NULL * $price->additional_visits_price),
                'additional_hour' => NULL,
                'additional_hour_price' => empty($price) ? NULL : (NULL * $price->additional_hours_price),
                'price' => $value->price,
                'status' => $value->status,
                'staff_firstname' => $sfirstname,
                'staff_lastname' => $slatname,
                'staff_image' => $staff_image,
                'pet_detail' => $petDetail,
                'message' => $value->message,
            );
        }
        $response['data'] = $appointmentData;
    }
    echoResponse(200, $response);
});

$app->post('/:id/appointmentedit(/:cancel)',function($id,$cancel=NULL) use ($app)
{
    verifyFields(array('company_id','client_id','service_id'));
    $response['error_code'] = 1;
    $response['message'] = 'No appointment found.';
    $response['status'] = false;

    $company_id = $app->request->post('company_id');
    $client_id = $app->request->post('client_id');
    $service_id = $app->request->post('service_id');
    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');

    if($cancel!=NULL)
    {
        $appointment=Appointment::find(array("conditions" => "appointment_id={$id}"));

        $price = $appointment->price;

        $credit = Crednits1::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id}"));

        $remain = $credit->remaining;

        $credit->remaining=$remain + $price;
        if($credit->remaining == 0)
        {
            $credit->credits=0;
            $credit->paid_amount=0;
            //$credit->check_date=date('Y-m-d');
        }
        $credit->save();

        $appointment->delete();

        $response['error_code'] = 0;
        $response['message'] = 'Appointment deleted successfully.';
        $response['status'] = true;
    }
    else{
        $price = Price::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND service_id={$service_id}"));

        $whole_visit= floor($visits);
        $whole = floor($visit_hours);      // whole number from
        $fraction = $visit_hours - $whole; // getting part after decimal point
// echo $fraction;
// die;
        /*
         * Price calculating
         */
        if ($fraction) {

            if($whole>1 && $whole_visit>1){
                $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;
            }else if($whole>1 && $whole_visit == 1){
                $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;
            }else if($whole ==1 && $whole_visit>1){
                $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;
            }else{
                $total = $price->full_hour_price + $price->half_hour_price;
            }
            // $minutes = ($fraction * 100);   // gettong minutes from
            // if ($minutes == 30 && empty($whole)) {
            //     $total = ($price->half_hour_price);
            // } else {
            //     $total = $whole * $price->full_hour_price + round($minutes * ($price->full_hour_price / 60));
            // }
        } else {
            //if($whole>1)
            //$total = $whole * $price->full_hour_price;
            if($whole>1 && $whole_visit>1){
                $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;
            }else if($whole>1 && $whole_visit == 1){
                $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));
            }else if($whole ==1 && $whole_visit>1){
                $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));
            }else{
                $total = $price->full_hour_price;
            }
        }

        $appointment=Appointment::find(array("conditions" => "appointment_id={$id}"));
        $preprice = $appointment->price;

        /*edit appointment*/
        $appointment->visits = $visits;
        $appointment->visit_hours = $visit_hours;
        $appointment->price = $total;
        $appointment->save();
        $newprice = $appointment->price;



        $credit = Credits1::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id}"));

        $remain = $credit->remaining;

        $diff=0;
        if($newprice>$preprice)
        {
            $diff=$newprice - $preprice;
            $credit->remaining=$remain - $diff;
            $credit->save();
        }else{
            $diff=$preprice - $newprice;
            $credit->remaining=$remain + $diff;
            $credit->save();
        }


        $response['error_code'] = 0;
        $response['message'] = 'Appointment edit successfully.';
        $response['status'] = true;
    }
    echoResponse(200, $response);
});


$app->post('/:id/appointmenteditnew(/:cancel)',function($id,$cancel=NULL) use ($app)
{
    verifyFields(array('company_id','client_id','service_id','pet_id'));
    $response['error_code'] = 1;
    $response['message'] = 'No appointment found.';
    $response['status'] = false;

    $company_id = $app->request->post('company_id');
    $client_id = $app->request->post('client_id');
    $service_id = $app->request->post('service_id');
    $visits = $app->request->post('visits');
    $visit_hours = $app->request->post('visit_hours');
    $pet_id = $app->request->post('pet_id');

    if($cancel!=NULL)
    {
        $appointment=Appointment::find(array("conditions" => "appointment_id={$id}"));

        $company_id=$appointment->company_id;
        $client_id=$appointment->client_id;
        $service_id=$appointment->service_id;
        $staff_id = $appointment->staff_id;
        $date = date('Y-m-d',strtotime($appointment->date));
        $visits =$appointment->visits;
        $visit_hours = $appointment->visit_hours;
        $price = $appointment->price;
        $status = $appointment->status;
        //$acknowledge = $appointment->acknowledge;
        $accepted = $appointment->accepted;
        $complete = $appointment->completed;
        $pet_id = $appointment->pet_id;
        $message = $appointment->message;
        $created_by = $appointment->created_by;
        $created_at = $appointment->created_at;

        //$price = $appointment->price;
///////naveen
        $today = date('Y-m-d');
///////naveen
        if($date > $today) {
            $appointment->delete();
///////naveen

        }else{


            ///////naveen



            ///////////////////////////

            $credit = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

            $remain = $credit->remaining;

            $credit->remaining=$remain + $price;
            if($credit->remaining == 0)
            {
                //$credit->credits=0;
                $credit->paid_amount=0;
                //$credit->check_date=date('Y-m-d');
            }
            $credit->save();


            $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
            $pet_name = $pet->pet_name;
            $log = new transactionlog();
            $log->company_id = $company_id;
            $log->client_id = $client_id;
            $log->pet_id = $pet_id;
            $log->service_id = $service_id;
            $log->pet_name = $pet_name;
            $log->date_of_transaction = date('Y-m-d H:i:s');
            $log->type = "Refund1";
            $log->amount = isset($price)?$price:0;
            $log->l_flag = "Added";
            $log->old_value = $remain;
            $log->new_value = $remain+$price;
            $log->save();
            $log->log_id = (int) $log->log_id;

            $app_cancel = new Appointment_cancel();
            $app_cancel->company_id = $company_id;
            $app_cancel->client_id = $client_id;
            $app_cancel->service_id = $service_id;
            $app_cancel->staff_id = $staff_id;
            $app_cancel->date  = $date;
            $app_cancel->visits = $visits;
            $app_cancel->visit_hours = $visit_hours;
            $app_cancel->price = $price;
            $app_cancel->status = $status;
            //$app_cancel->acknowledge = $acknowledge;
            $app_cancel->accepted = $accepted;
            $app_cancel->completed  = $complete;
            $app_cancel->pet_id = $pet_id;
            $app_cancel->message = $message;
            $app_cancel->created_by = $created_by;
            $app_cancel->created_at = $created_at;
            $app_cancel->save();
            $app_cancel->appointment_id = (int) $id;

            $appointment->delete();


        }
        ///////naveen


        $response['error_code'] = 0;
        $response['message'] = 'Appointment deleted successfully.';
        $response['status'] = true;


        ////////////
    }
    else{
        $price = Pricenew::find(array("conditions" => "company_id={$company_id} AND client_id={$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

        $whole_visit= floor($visits);
        $whole = floor($visit_hours);      // whole number from
        $fraction = $visit_hours - $whole; // getting part after decimal point

        /*
         * Price calculating
         */
        if ($fraction)
        {

            if($whole>1 && $whole_visit > 1)
            {

                $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1))+ $price->half_hour_price ;


            }
            else if($whole > 1 && $whole_visit == 1)
            {

                $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

            }
            else if($whole == 1 && $whole_visit == 1)
            {

                $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1)) + $price->half_hour_price ;

            }
            else if($whole == 1 && $whole_visit>1)
            {
                $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

            }
            else if($whole < 1 && $whole_visit == 1)
            {

                $total = $price->half_hour_price;

            }else if($whole < 1 && $whole_visit > 1)
            {
                $total = ($price->additional_visits_price * ($whole_visit-1)) + $price->half_hour_price;

            }

        } else {

            if($whole>1 && $whole_visit>1)
            {
                $total =$price->full_hour_price +($price->additional_hours_price * ($whole-1))+($price->additional_visits_price * ($whole_visit-1)) ;

            }
            else if($whole>1 && $whole_visit == 1)
            {
                $total = $price->full_hour_price +($price->additional_hours_price * ($whole-1));

            }
            else if($whole ==1 && $whole_visit>1)
            {
                $total = $price->full_hour_price +($price->additional_visits_price * ($whole_visit-1));

            }
            else
            {
                $total = $price->full_hour_price;

            }
        }


        $appointment=Appointment::find(array("conditions" => "appointment_id={$id}"));
        $preprice = $appointment->price;

        /*edit appointment*/
        $appointment->visits = $visits;
        $appointment->visit_hours = $visit_hours;
        $appointment->price = $total;
        $appointment->save();
        $newprice = $appointment->price;

        $org_date=date('Y-m-d', strtotime($appointment->date));//$date = date('Y-m-d', $stringdate);

        $credit = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

        $remain = $credit->remaining;

        $diff=0;
        $today = date('Y-m-d');
        if($org_date==$today){

            if($newprice>$preprice)
            {
                $diff=$newprice - $preprice;
                $credit->remaining=$remain - $diff;
                $credit->save();

                $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                $pet_name = $pet->pet_name;
                $log = new transactionlog();
                $log->company_id = $company_id;
                $log->client_id = $client_id;
                $log->pet_id = $pet_id;
                $log->service_id = $service_id;
                $log->pet_name = $pet_name;
                $log->date_of_transaction = date('Y-m-d H:i:s');
                $log->type = "Alteration";
                $log->amount = $diff;
                $log->l_flag = "Deducted";
                $log->old_value = $remain;
                $log->new_value = $remain-$diff;
                $log->save();
                $log->log_id = (int) $log->log_id;

            }else{
                $diff=$preprice - $newprice;
                $credit->remaining=$remain + $diff;
                $credit->save();

                $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                $pet_name = $pet->pet_name;
                $log = new transactionlog();
                $log->company_id = $company_id;
                $log->client_id = $client_id;
                $log->pet_id = $pet_id;
                $log->service_id = $service_id;
                $log->pet_name = $pet_name;
                $log->date_of_transaction = date('Y-m-d H:i:s');
                $log->type = "Alteration";
                $log->amount = $diff;
                $log->l_flag = "Added";
                $log->old_value = $remain;
                $log->new_value = $remain+$diff;
                $log->save();
                $log->log_id = (int) $log->log_id;
            }

        }




        $response['error_code'] = 0;
        $response['message'] = 'Appointment edit successfully.';
        $response['status'] = true;
    }
    echoResponse(200, $response);
});



/*API for change status of client*/

$app->get('/:id/changestatus',function($id) use ($app){


    $client = Client::find(array("conditions" => "client_id = {$id}"));
    $client->status = 1;
    $client->save();

    $response['error_code'] = 0;
    $response['message'] = 'Client is active.';
    $response['status'] = true;

    echoResponse(200, $response);
});



/*
 * Appoinment Cancle
 */

$app->get("/appointment/:id/canclenew", function($id,$manual=null) use ($app) {
//echo $id;
//die;

    $exist = Appointment::exists($id);

    if ($exist) {
        //$appoinmnet = Appointment::find($id);

        if($manual != NULL)
        {
            $condition = "appointment_id = $id AND created_by = 'company'";
        }
        else
        {
            $condition = "appointment_id = $id";
        }

        $appointment=Appointment::find(array("conditions" => "{$condition}"));

        $company_id=$appointment->company_id;
        $client_id=$appointment->client_id;
        $service_id=$appointment->service_id;
        $staff_id = $appointment->staff_id;
        $date = date('Y-m-d',strtotime($appointment->date));
        $visits =$appointment->visits;
        $visit_hours = $appointment->visit_hours;
        $price = $appointment->price;
        $status = $appointment->status;
        //$acknowledge = $appointment->acknowledge;
        $accepted = $appointment->accepted;
        $complete = $appointment->completed;
        $pet_id = $appointment->pet_id;
        $message = $appointment->message;
        $created_by = $appointment->created_by;
        $created_at = $appointment->created_at;


        $credit = Credits1::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id}"));

        $remain = $credit->remaining;

        $credit->remaining=$remain + $price;
        $credit->save();

        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
        $pet_name = $pet->pet_name;
        $log = new Log();
        $log->company_id = $company_id;
        $log->client_id = $client_id;
        $log->pet_name = $pet_name;
        $log->date_of_transaction = date('Y-m-d H:i:s');
        $log->l_status = "Cancelled";
        $log->amount = isset($price)?$price:0;
        $log->l_flag = "Added";
        $log->save();
        $log->log_id = (int) $log->log_id;

        $app_cancel = new Appointment_cancel();
        $app_cancel->company_id = $company_id;
        $app_cancel->client_id = $client_id;
        $app_cancel->service_id = $service_id;
        $app_cancel->staff_id = $staff_id;
        $app_cancel->date  = $date;
        $app_cancel->visits = $visits;
        $app_cancel->visit_hours = $visit_hours;
        $app_cancel->price = $price;
        $app_cancel->status = $status;
        //$app_cancel->acknowledge = $acknowledge;
        $app_cancel->accepted = $accepted;
        $app_cancel->completed  = $complete;
        $app_cancel->pet_id = $pet_id;
        $app_cancel->message = $message;
        $app_cancel->created_by = $created_by;
        $app_cancel->created_at = $created_at;
        $app_cancel->save();
        $app_cancel->appointment_id = (int) $id;


        $appointment->delete();
        $response['error_code'] = 0;
        $response['message'] = 'Appointment cancled successfully!';
        $response['status'] = true;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No Appoinment found';
        $response['status'] = false;
    }

    echoResponse(200, $response);
});



/*
 * Appoinment Cancle
 */

$app->get("/appointment/:id/cancle", function($id,$manual=null) use ($app) {
//echo $id;
//die;

    $exist = Appointment::exists($id);

    if ($exist) {
        //$appoinmnet = Appointment::find($id);

        if($manual != NULL)
        {
            $condition = "appointment_id = $id AND created_by = 'company'";
        }
        else
        {
            $condition = "appointment_id = $id";
        }

        $appointment=Appointment::find(array("conditions" => "{$condition}"));

        $company_id=$appointment->company_id;
        $client_id=$appointment->client_id;
        $service_id=$appointment->service_id;
        $staff_id = $appointment->staff_id;
        $date = date('Y-m-d',strtotime($appointment->date));
        $visits =$appointment->visits;
        $visit_hours = $appointment->visit_hours;
        $price = $appointment->price;
        $status = $appointment->status;
        //$acknowledge = $appointment->acknowledge;
        $accepted = $appointment->accepted;
        $complete = $appointment->completed;
        $pet_id = $appointment->pet_id;
        $message = $appointment->message;
        $created_by = $appointment->created_by;
        $created_at = $appointment->created_at;

        $today = date('Y-m-d');

        if($date > $today)
        {
            $app_cancel = new Appointment_cancel();
            $app_cancel->company_id = $company_id;
            $app_cancel->client_id = $client_id;
            $app_cancel->service_id = $service_id;
            $app_cancel->staff_id = $staff_id;
            $app_cancel->date  = $date;
            $app_cancel->visits = $visits;
            $app_cancel->visit_hours = $visit_hours;
            $app_cancel->price = $price;
            $app_cancel->status = $status;
            //$app_cancel->acknowledge = $acknowledge;
            $app_cancel->accepted = $accepted;
            $app_cancel->completed  = $complete;
            $app_cancel->pet_id = $pet_id;
            $app_cancel->message = $message;
            $app_cancel->created_by = $created_by;
            $app_cancel->created_at = $created_at;
            $app_cancel->save();
            $app_cancel->appointment_id = (int) $id;


            $appointment->delete();
            $response['error_code'] = 0;
            $response['message'] = 'Appointment cancled successfully!';
            $response['status'] = true;
        }else{
            $credit = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));
            $remain = $credit->remaining;
            $last_check = date('Y-m-d',strtotime($credit->last_check));
            if($last_check == $today)
            {
                $credit->remaining=$remain + $price;
                $credit->save();

                $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                $pet_name = $pet->pet_name;
                $log = new transactionlog();
                $log->company_id = $company_id;
                $log->client_id = $client_id;
                $log->pet_id = $pet_id;
                $log->service_id = $service_id;
                $log->pet_name = $pet_name;
                $log->date_of_transaction = $date;
                $log->type = "Refund2";
                $log->amount = isset($price)?$price:0;
                $log->l_flag = "Added";
                $log->old_value = $remain;
                $log->new_value = $remain+$price;
                $log->save();
                $log->log_id = (int) $log->log_id;

                $app_cancel = new Appointment_cancel();
                $app_cancel->company_id = $company_id;
                $app_cancel->client_id = $client_id;
                $app_cancel->service_id = $service_id;
                $app_cancel->staff_id = $staff_id;
                $app_cancel->date  = $date;
                $app_cancel->visits = $visits;
                $app_cancel->visit_hours = $visit_hours;
                $app_cancel->price = $price;
                $app_cancel->status = $status;
                //$app_cancel->acknowledge = $acknowledge;
                $app_cancel->accepted = $accepted;
                $app_cancel->completed  = $complete;
                $app_cancel->pet_id = $pet_id;
                $app_cancel->message = $message;
                $app_cancel->created_by = $created_by;
                $app_cancel->created_at = $created_at;
                $app_cancel->save();
                $app_cancel->appointment_id = (int) $id;


                $appointment->delete();
                $response['error_code'] = 0;
                $response['message'] = 'Appointment cancled successfully!';
                $response['status'] = true;
            }else{



                $aa=CompanyService::find('all',array('conditions' => "company_id='{$company_id}'"));
                $ab=date('Y-m-d');

                $total=0;
                foreach ($aa as $val)
                {

                    $service_id1=$val->service_id;
                    $test1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                    if(count($test1) == 0)
                    {

                        $credit = new Credits();
                        $credit->company_id = $company_id;
                        $credit->client_id = $client_id;
                        $credit->pet_id = $pet_id;
                        $credit->service_id = $service_id1;
                        //$credit->credits = 0;
                        $credit->paid_amount = 0;
                        $credit->old_amount = 0;
                        $credit->date_of_payment = null;
                        $credit->last_check = $ab;
                        $credit->remaining=0;
                        $credit->save();
                        $credit->credit_id = (int) $credit->credit_id;
                    }



                    //$service=Service::find(array("conditions" => "service_id = $service_id1"));
                    // $service_name=$service->service_name;

                    $creditCheck = Credits::find('all',array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));

                    if (count($creditCheck)>0)

                    {
                        foreach ($creditCheck as  $valu)
                        {
                            $last_date=$valu->last_check;
                            $credits2=(float)$valu->paid_amount;
                            $remains=$valu->remaining;

                        }


                        $last_check1=date('Y-m-d',strtotime($last_date));



                        if($last_check1 != $ab)
                        {

                            $datetime = new DateTime($last_check1);
                            $datetime->modify('+1 day');
                            $l_check=$datetime->format('Y-m-d');

                            $appoint = Appointment::find_by_sql("SELECT price as p , date as d FROM `tbl_appointments` where company_id=$company_id and client_id=$client_id and pet_id=$pet_id and service_id=$service_id1 and (status='accepted' or status='assign staff') and date BETWEEN '$l_check' and '$ab'");


                            if(count($appoint)>0)
                            {
                                $t_price=0;

                                foreach ($appoint as  $value1)
                                {
                                    $t_price += $value1->p;
                                    $remaining=$remains;

                                    $remaining-=(float)$t_price;


                                    /*added extra for make all field zero when remaining is 0*/
                                    if($remaining == 0)
                                    {

                                        $creditCheck4 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                        $creditCheck4->paid_amount=0;

                                        $creditCheck4->save();


                                        $total =0;

                                    }




                                    $pet=Pet::find($pet_id);
                                    $pet_name = $pet->pet_name;
                                    $log = new transactionlog();
                                    $log->company_id = $company_id;
                                    $log->client_id = $client_id;
                                    $log->pet_id = $pet_id;
                                    $log->service_id = $service_id1;
                                    $log->pet_name = $pet_name;
                                    $log->date_of_transaction = date('Y-m-d H:i:s',strtotime($value1->d));
                                    $log->type = "Charge";
                                    $log->amount = $value1->p;
                                    $log->l_flag = "Deducted";
                                    $log->old_value = $remains;
                                    $log->new_value = $remaining;
                                    $log->save();
                                    $log->log_id = (int) $log->log_id;



                                }
                                $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));
                                if(count($creditCheck1) >0)
                                {
                                    $creditCheck1->last_check=date('Y-m-d');

                                    $creditCheck1->remaining=$remaining;
                                    $creditCheck1->save();
                                }
                            }else{
                                $creditCheck1 = Credits::find(array("conditions" => "company_id = {$company_id} AND client_id = {$client_id} AND service_id={$service_id1} AND pet_id={$pet_id}"));


                                if(count($creditCheck1) >0)
                                {
                                    $creditCheck1->last_check=$ab;
                                    if($creditCheck1->remaining == 0)
                                    {

                                        $creditCheck1->paid_amount=0;

                                        $creditCheck1->date_of_payment=null;

                                    }


                                    $creditCheck1->save();
                                }

                                $remaining=(float)$remains;


                            }

                        }else{


                            $remaining=(float)$remains;


                        }


                    }else{


                        $credit = new Credits();
                        $credit->company_id = $company_id;
                        $credit->client_id = $client_id;
                        $credit->pet_id = $pet_id;
                        $credit->service_id = $service_id1;
                        //$credit->credits = 0;
                        $credit->paid_amount = 0;
                        $credit->old_amount = 0;
                        $credit->date_of_payment = null;
                        $credit->last_check = $ab;
                        $credit->remaining=0;
                        $credit->save();
                        $credit->credit_id = (int) $credit->credit_id;


                        $remaining=0;

                    }

                }

                $creditt = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));

                $creditt->remaining=$remain + $price;
                $creditt->save();

                $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
                $pet_name = $pet->pet_name;
                $log = new transactionlog();
                $log->company_id = $company_id;
                $log->client_id = $client_id;
                $log->pet_id = $pet_id;
                $log->service_id = $service_id;
                $log->pet_name = $pet_name;
                $log->date_of_transaction = date('Y-m-d H:i:s');
                $log->type = "Refund3";
                $log->amount = isset($price)?$price:0;
                $log->l_flag = "Added";
                $log->old_value = $remain;
                $log->new_value = $remain + $price;
                // $log->new_value = $remain;

                $log->save();
                $log->log_id = (int) $log->log_id;

                $app_cancel = new Appointment_cancel();
                $app_cancel->company_id = $company_id;
                $app_cancel->client_id = $client_id;
                $app_cancel->service_id = $service_id;
                $app_cancel->staff_id = $staff_id;
                $app_cancel->date  = $date;
                $app_cancel->visits = $visits;
                $app_cancel->visit_hours = $visit_hours;
                $app_cancel->price = $price;
                $app_cancel->status = $status;
                //$app_cancel->acknowledge = $acknowledge;
                $app_cancel->accepted = $accepted;
                $app_cancel->completed  = $complete;
                $app_cancel->pet_id = $pet_id;
                $app_cancel->message = $message;
                $app_cancel->created_by = $created_by;
                $app_cancel->created_at = $created_at;
                $app_cancel->save();
                $app_cancel->appointment_id = (int) $id;


                $appointment->delete();
                $response['error_code'] = 0;
                $response['message'] = 'Appointment cancled successfully!';
                $response['status'] = true;
            }
        }

        // $credit = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND pet_id={$pet_id} AND service_id={$service_id}"));
        //        $remain = $credit->remaining;
        //        $last_check = date('Y-m-d',strtotime($credit->last_check);
        //        // if($last_check == $today)
        //        // {
        //        $credit->remaining=$remain + $price;
        //        $credit->save();

        //                        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
        //                                $pet_name = $pet->pet_name;
        //                                $log = new transactionlog();
        //                                $log->company_id = $company_id;
        //                                $log->client_id = $client_id;
        //                                $log->pet_id = $pet_id;
        //                                $log->service_id = $service_id;
        //                                $log->pet_name = $pet_name;
        //                                $log->date_of_transaction = date('Y-m-d H:i:s');
        //                                $log->type = "Refund";
        //                                $log->amount = isset($price)?$price:0;
        //                                $log->l_flag = "Added";
        //                                $log->save();
        //                                $log->log_id = (int) $log->log_id;

        //                              $app_cancel = new Appointment_cancel();
        //                              $app_cancel->company_id = $company_id;
        //                              $app_cancel->client_id = $client_id;
        //                              $app_cancel->service_id = $service_id;
        //                              $app_cancel->staff_id = $staff_id;
        //                              $app_cancel->date  = $date;
        //                              $app_cancel->visits = $visits;
        //                              $app_cancel->visit_hours = $visit_hours;
        //                              $app_cancel->price = $price;
        //                              $app_cancel->status = $status;
        //                              //$app_cancel->acknowledge = $acknowledge;
        //                              $app_cancel->accepted = $accepted;
        //                              $app_cancel->completed  = $complete;
        //                              $app_cancel->pet_id = $pet_id;
        //                              $app_cancel->message = $message;
        //                              $app_cancel->created_by = $created_by;
        //                              $app_cancel->created_at = $created_at;
        //                              $app_cancel->save();
        //                              $app_cancel->appointment_id = (int) $id;


        //    $appointment->delete();
        //    $response['error_code'] = 0;
        //    $response['message'] = 'Appointment cancled successfully!';
        //    $response['status'] = true;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No Appoinment found';
        $response['status'] = false;
    }

    echoResponse(200, $response);
});



/*
*
*  Appointment list according to company_id and dates
*/


$app->post('/:type/:id/appointmentslist', function($type, $id) use ($app) {

    verifyFields(array('date'));

    $stringdate = $app->request->post('date');
    $date = date('Y-m-d', strtotime($stringdate));

    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;
    $condition = '';
    if ($type == 'company') {
        $condition = "company_id";
    } else {
        $condition = "client_id";
    }


    $appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id} AND  (status='accepted' OR status='assign staff')  AND date = '$date'"));

// foreach ($appointment as $key => $value) {
//     $pet1[]=$value->pet->pet_name;

// }

    $contract=Contract::find('all',array("conditions" => "company_id = {$id}"));
    foreach ($contract as $ke => $val) {
        $clientid=$val->client_id;

        $pet= Pet::find('all',array("conditions" => "client_id = {$clientid}"));
        foreach ($pet as $key1 => $value1) {
            $petname[]=$value1->pet_name;

        }
    }
//var_dump($id);
//die;

    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointment list retrive successfully.';
        $appointmentData = [];



        foreach ($appointment as $key => $value) {

            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$value->company->company_id}");
            $companyServices = array();
            foreach ($services as $companyService) {
                $companyServices[] = array(
                    'id' => $companyService->service_id,
                    'name' => $companyService->service_name
                );
            }
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
//if($type == 'compnay')
            $companyDetail = array(
                'company_id' => $value->company->company_id,
                'account_id' => $value->company->account_id,
                'company_name' => $value->company->company_name,
                'emailid' => $value->company->emailid,
                'contact_number' => $value->company->contact_number,
                'company_image' => $value->company->company_image != NULL ? COMPANY_PIC_PATH . $value->company->company_image : NULL,
                'website' => $value->company->website,
                'address' => $value->company->address,
                'about' => $value->company->about,
                'services' => $companyServices,
            );


            if ($value->status == 'accepted' && $value->staff_id != NULL) {
                $staff = Staff::find($value->staff_id);
                $sfirstname = isset($staff->firstname) ? $staff->firstname : '';
                $slatname = isset($staff->lastname)?$staff->lastname:'';
                $sid = isset($staff->staff_id)?$staff->staff_id:'';
                $staff_image =  isset($staff->profile_image) ? $staff->profile_image : '';
            } elseif($value->status == 'assign staff'){
                $staff = Staff::find($value->staff_id);
                $sfirstname = isset($staff->firstname) ? $staff->firstname : '';
                $slatname = isset($staff->lastname)?$staff->lastname:'';
                $sid = isset($staff->staff_id)?$staff->staff_id:'';
                $staff_image =  isset($staff->profile_image) ? $staff->profile_image : '';
            
            } else {
                $sfirstname = '';
                $slatname = '';
                $sid = '';
                $staff_image = "";
            }

            $price = Price::find(array('conditions' => "service_id = $value->service_id"));
//var_dump($price);
// die;

            $whole = floor($value->visit_hours);      // whole number from
            $fraction = $value->visit_hours - $whole; // getting part after decimal point
            $flag = $value->created_by == 'company' ? TRUE : FALSE;
            /*
             * Price calculating
             */
            if (!empty($price)) {

                if ($fraction) {

                    $minutes = ($fraction * 100);   // gettong minutes from
                    if ($minutes == 30 && empty($whole)) {
                        $total = ($price->half_hour_price);
                    } else {
                        $total = $whole * $price->full_hour_price + round($minutes * ($price->full_hour_price / 60));
                    }
                } else {
                    $total = $whole * $price->full_hour_price;
                }
            } else {
                $total = NULL;
            }
//$status=$value->status;
            //    if($status =='accepted')
            //    {
            //     $appointmentData[] = array(
            //        'appointment_id' => $value->appointment_id,
            //        'company_detail' => $companyDetail,
            //        'owner_detail' => $ownerDetail,
            //        'service_id' => $value->service_id,
            //        'service_name' => $serviceName,
            //        'date' => $value->date,
            //        'visits' => $value->visits,
            //        'visit_hours' => $value->visit_hours,
            //        'base_price' => $value->visits * $total,
            //        'additional_visit' => NULL,
            //        'additional_visit_price' => empty($price) ? NULL : (NULL * $price->additional_visits_price),
            //        'additional_hour' => NULL,
            //        'additional_hour_price' => empty($price) ? NULL : (NULL * $price->additional_hours_price),
            //        'price' => $value->price,
            //        'status' => $value->status,
            //        'staff_firstname' => $value->firstname,
            //        'staff_lastname' => $value->staff->lastname,

            //        //  'staff' => array(
            //        //             'staff_id' => $value->staff->staff_id,
            //        //             'firstname' => $value->staff->firstname,
            //        //             'lastname' => $value->staff->lastname,
            //        //             'emailid' => $value->staff->emailid,
            //        //             'proflie_image' => $value->staff->profile_image != NULL ? STAFF_PIC_PATH . $value->staff->profile_image : NULL,
            //        //             'contact_number' => $value->staff->contact_number,
            //        // ),
            //        'pet_detail' => array(
            //            'pet_id' => $value->pet->pet_id,
            //            'pet_name' => $value->pet->pet_name,
            //            'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
            //            'pet_age' => $value->pet->age,
            //            'medical_detail' => $value->pet->medical_detail,
            //            'pet_notes' => $value->pet->pet_notes,
            //            'latitude' => $value->pet->latitude,
            //            'longitude' => $value->pet->longitude,
            //        ),
            //        'message' => $value->message,
            //    );
            // }else{

            //   // $x=new stdClass();
            //    $appointmentData[] = array(
            //        'appointment_id' => $value->appointment_id,
            //        'company_detail' => $companyDetail,
            //        'owner_detail' => $ownerDetail,
            //        'service_id' => $value->service_id,
            //        'service_name' => $serviceName,
            //        'date' => $value->date,
            //        'visits' => $value->visits,
            //        'visit_hours' => $value->visit_hours,
            //        'base_price' => $value->visits * $total,
            //        'additional_visit' => NULL,
            //        'additional_visit_price' => empty($price) ? NULL : (NULL * $price->additional_visits_price),
            //        'additional_hour' => NULL,
            //        'additional_hour_price' => empty($price) ? NULL : (NULL * $price->additional_hours_price),
            //        'price' => $value->price,
            //        'status' => $value->status,
            //        'staff' => $x,
            //        'pet_detail' => array(
            //            'pet_id' => $value->pet->pet_id,
            //            'pet_name' => $value->pet->pet_name,
            //            'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
            //            'pet_age' => $value->pet->age,
            //            'medical_detail' => $value->pet->medical_detail,
            //            'pet_notes' => $value->pet->pet_notes,
            //            'latitude' => $value->pet->latitude,
            //            'longitude' => $value->pet->longitude,
            //        ),
            //        'message' => $value->message,
            //    );
            //    }

            //$pet1 = Pet::find(array("conditions" => "pet_id= '{$value->pet_id}'"));



            //print_r(array_count_values($pet1));
// $pet3[]=array_count_values($pet1);
// //print_r($pet3);
            $status='';
            $maincoin=[];
            for($i=0;$i<count($petname);$i++)
            {
                // echo $haha."</br>";

                // print_r($maincoin);


                if(strcmp($petname[$i],$value->pet->pet_name)===0)
                {
                    $maincoin[]='a';
                    $status='';
                    // echo "=========================$pet_names[$i]        ".$value2->pet_name.'==========</br>';
                }
                if(count($maincoin)>1)
                {
                    $status='abcd';
                    // echo "$pet_names[$i]        ".$value2->pet_name.'</br>';
                    break;
                }



            }


            if($status=='abcd')
            {
                $petfull = $value->pet->pet_name." ".$value->client->lastname;
            }
            else
            {
                $petfull = $value->pet->pet_name;
            }

            $backup_contact=[];
            $contact_check=Contact_backup::find(array("conditions"=>"client_id={$value->client->client_id} AND pet_id={$value->pet->pet_id}"));
            if($contact_check != NULL)
            {
                $backup_contact=array(
                    'name' => $contact_check->name,
                    'address' => $contact_check->address,
                    'number' => $contact_check->contact_number,
                );
            }else{
                $backup_contact= new stdClass();
            }

            $petDetail=array(
                'pet_id' => $value->pet->pet_id,
                'pet_name' => $petfull,
                'pet_birth' => $value->pet->pet_birth,
                'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                'pet_age' => $value->pet->age,
                'gender' => $value->pet->gender,
                'pet_type' => $value->pet->pet_type,
                'breed' => $value->pet->breed,
                'neutered' => $value->pet->neutered,
                'spayed' => $value->pet->spayed,
                'injuries' => $value->pet->injuries,
                'medical_detail' => $value->pet->medical_detail,
                'pet_notes' => $value->pet->pet_notes,
                'latitude' => $value->pet->latitude,
                'longitude' => $value->pet->longitude,
                'backupcontact' => $backup_contact,
            );

            //$last_name = $value->client->lastname;
            //$lst =  $value->client->client_id;
            //$pet1 = Appointment::find('all',array("conditions" => "pet_id = '{$value->pet->pet_id}'"));

            $appointmentData[] = array(
                'appointment_id' => $value->appointment_id,
                'company_detail' => $companyDetail,
                'owner_detail' => $ownerDetail,
                'isManualClient' => $flag,
                'service_id' => $value->service_id,
                'service_name' => $serviceName,
                'date' => $value->date,
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'base_price' => $value->visits * $total,
                'additional_visit' => NULL,
                'additional_visit_price' => empty($price) ? NULL : (NULL * $price->additional_visits_price),
                'additional_hour' => NULL,
                'additional_hour_price' => empty($price) ? NULL : (NULL * $price->additional_hours_price),
                'price' => $value->price,
                'status' => $value->status,
                'staff_firstname' => $sfirstname,
                'staff_lastname' => $slatname,
                'staff_image' => $staff_image,
                'pet_detail' => $petDetail,
                'message' => $value->message,
            );
        }
        $response['data'] = $appointmentData;
    }
    echoResponse(200, $response);
});

/*New report for staff*/
$app->post('/:id/reportstaff',function($id) use($app)
{
    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'No staff report found.';

    // $starttime = strtotime($app->request->post('startdate'));
    // $startdate = date('Y-m-d', strtotime($app->request->post('startdate')));
    // $enddate = date('Y-m-d', strtotime($app->request->post('enddate')));
    $staff_option = $app->request->post('staff_option');
    $date_option = $app->request->post('date_option');
    $staff_start = date('Y-m-d', strtotime($app->request->post('staff_start')));
    $staff_end = date('Y-m-d', strtotime($app->request->post('staff_end')));
    // $client_id = $app->request->post('client_id');
    // $pet_id = $app->request->post('pet_id');
    $staff_id = $app->request->post('staff_id');

    if($staff_option == 'single')
    {
        if($date_option == 'single')
        {
            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Staff report retrive successfully.';
            $reportdata=[];
            $ttl_book=0;

            $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date='$staff_start' and staff_id=$staff_id and company_id=$id");

            foreach ($total_bk as $bk)
            {
                $ttl_book = $bk->ttl;
            }
            $s_app = Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl, a.staff_id as staff_id,a.date as date FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and a.staff_id=$staff_id and a.date='$staff_start' AND status ='accepted' group by date");

            //$s_app = Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and a.client_id=$client_id and a.pet_id=$pet_id and a.date='$staff_start' AND status ='accepted' group by a.staff_id");
            //$staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
            // $staff_id=array();
            //   foreach ($staff as  $s) {
            //     $staff_id[]=$s->staff_id;
            //     $staff_name[]=$s->firstname;
            //     }

            // foreach ($staff_name as $value) {

            //     $staff_detail[]=array('name' => $value,'appointment' => 0);
            // }

            $reportDetail=[];
            if($s_app)
            {
                foreach ($s_app as $value1) {

                    $date = date('Y-m-d',strtotime($value1->date));
                    $reportDetail[]=array('appointment' => $value1->dtotl,
                        'date' => $date);

                    $staff_detail[]=array('name' =>$value1->name,
                        'staff_id' =>$value1->staff_id,
                        'staff_total' =>$value1->dtotl,
                        'report_detail' =>$reportDetail
                    );
                }
                // foreach ($staff_id as $key => $value2) {

                //     if($value1->staff_id === $value2)
                //     {
                //         $staff_detail[$key]['appointment']=$value1->dtotl;
                //     }
                // }
                $reportdata[]= array(
                    'staff_detail' => $staff_detail
                );

            }else{
                $staff=Staff::find(array("conditions" => "staff_id={$staff_id}"));

                $reportDetail[]=array('appointment' => 0,
                    'date' => $staff_start);
                $staff_detail[]=array('name' =>$staff->firstname,
                    'staff_id' =>$staff->staff_id,
                    'staff_total' =>0,
                    'report_detail' =>$reportDetail
                );
                $reportdata[]= array(
                    'staff_detail' => $staff_detail
                );
            }


            $response['data'] = $reportdata;

        }else{

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Staff report retrive successfully.';
            $reportdata=[];
            $ttl_book=0;
            $dates =getDatesFromRange($staff_start,$staff_end,'d-m-Y');

            $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date BETWEEN '$staff_start' and '$staff_end' and staff_id=$staff_id and company_id=$id");

            foreach ($total_bk as $bk)
            {
                $ttl_book = $bk->ttl;
            }


            $reportDetail=[];
            //$list=[];
            $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where staff_id=$staff_id");

            //         $staff_id=array();
            foreach ($dates as $d)
            {

                $reportDetail[]=array('appointment' => 0, 'date' => $d);

            }

            //$k = 0;

            foreach ($staff as  $s) {

                // $s_app =  Appointment::find_by_sql("SELECT count(staff_id) as atotal, date as date,staff_id as staff_id from tbl_appointments where staff_id=$s->staff_id and company_id=$id and date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by staff_id,date");



                $staff_name=$s->firstname;
                $staff_id=$s->staff_id;

                $staff_detail[]=array('name' => $staff_name,
                    'staff_id' => $staff_id,
                    'staff_total' => 0,
                    'report_detail' => $reportDetail);
            }


            $s_app =  Appointment::find_by_sql("SELECT count(staff_id) as atotal, date as date,staff_id as staff_id from tbl_appointments where company_id=$id and staff_id = $staff_id and date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by staff_id, date");
// print_r($s_app);
// die;
            foreach ($staff_detail as $key => $value)
            {
                $reportDetail=[];
                foreach ($dates as $d)
                {

                    $reportDetail[]=array('appointment' => 0, 'date' => $d);

                }

                $sid=$value['staff_id'];
                //foreach ($s_app as $key1 => $value1)
                $ttl=0;
                for($i=0;$i<count($s_app);$i++)
                {
                    $ttl+=$s_app[$i]->atotal;
                    //echo $s_app[$i]->atotal;

                    if($sid == $s_app[$i]->staff_id)
                    {
                        //echo $i;

                        $rdata=$value['report_detail'];
                        $date = date('d-m-Y',strtotime($s_app[$i]->date));
                        //echo $date;
                        //print_r($rdata);
                        //$i=0;
                        foreach ($rdata as $key2 => $value2)
                        {

                            $r_date=$value2['date'];
                            if($r_date == $date)
                            {
                                //echo $r_date."===".$date."</br>";
                                //$i++;

                                $reportDetail[$key2]['appointment']=$s_app[$i]->atotal;
                                $staff_detail[$key]['report_detail']=$reportDetail;
                                $staff_detail[$key]['staff_total']=$ttl;

                                //break;
                            }
                            //$reportDetail=setReportdetailtoZero($key2,$reportDetail);
                        }//break;
                    }
                }//echo $i;

            }



            // $s_app =  Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl, a.staff_id as staff_id, a.date as date FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and s.company_id=$id and a.staff_id=$staff_id and a.date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by date");

            // // $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
            // //         $staff_id=array();
            // //           foreach ($staff as  $s) {
            // //             $staff_id[]=$s->staff_id;
            // //             $staff_name[]=$s->firstname;
            // //             }

            // //             foreach ($staff_name as $value) {

            // //                 $staff_detail[]=array('name' => $value,'appointment' => 0);
            // //             }
            //                 $reportDetail=[];
            //                 if($s_app)
            //                 {

            //                foreach ($s_app as $value1) {

            //                 $date = date('Y-m-d',strtotime($value1->date));
            //                 $reportDetail[]=array('appointment' => $value1->dtotl,
            //                                         'date' => $date);
            //                 $name=$value1->name;
            //                     }

            //                    $staff_detail[]=array('name' =>$name,
            //                                         'reportDetail' =>$reportDetail
            //                                         );



            //                     // foreach ($staff_id as $key => $value2) {

            //                     //     if($value1->staff_id === $value2)
            //                     //     {
            //                     //         $staff_detail[$key]['appointment']=$value1->dtotl;
            //                     //     }
            //                     // }
            //                     $reportdata[]= array('staff_total' => $ttl_book,
            //                 'staff_detail' => $staff_detail
            //                 );
            //                 }else{
            //                          $staff=Staff::find(array("conditions" => "staff_id={$staff_id}"));

            //                     $reportDetail[]=array('appointment' => 0,
            //                                             'date' => $staff_start);
            //                         $staff_detail[]=array('name' =>$staff->firstname,
            //                                         'reportDetail' =>$reportDetail
            //                                         );
            //                         $reportdata[]= array('staff_total' => 0,
            //                 'staff_detail' => $staff_detail
            //                 );
            //                 }
            $reportdata[]= array(
                'staff_detail' => $staff_detail
            );

            $response['data'] = $reportdata;


        }
    }else{

        if($date_option == 'single')
        {
            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Staff report retrive successfully.';
            $reportdata=[];
            $ttl_book=0;

            $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date='$staff_start' and company_id=$id");

            foreach ($total_bk as $bk)
            {
                $ttl_book = $bk->ttl;
            }
            // $s_app = Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl, a.staff_id as staff_id,a.date as date FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and a.date='$staff_start' AND status ='accepted' group by a.staff_id");



            $reportDetail=[];
            //$s_app = Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and a.client_id=$client_id and a.pet_id=$pet_id and a.date='$staff_start' AND status ='accepted' group by a.staff_id");
            $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
            //         $staff_id=array();




            foreach ($staff as  $s)
            {
                $reportDetail=[];
                $s_app =  Appointment::find_by_sql("SELECT count(staff_id) as atotal, date as date,staff_id as staff_id from tbl_appointments where staff_id=$s->staff_id and company_id=$id and date = '$staff_start' AND status ='accepted' group by staff_id, date");
                //$total=0;
                $ttl=0;
                if($s_app)
                {

                    foreach ($s_app as $key1 => $value1)
                    {


                        if($s->staff_id==$value1->staff_id)
                        {
                            $reportDetail[]=array('appointment' =>$value1->atotal,
                                'date' =>$staff_start );
                            $ttl+=$value1->atotal;
                        }

                    }
                }else{
                    $reportDetail[]=array('appointment' =>0,
                        'date' =>$staff_start );
                }

                $staff_detail[]=array('name' => $s->firstname,
                    'staff_id' => $s->staff_id,
                    'staff_total' => $ttl,
                    'report_detail' => $reportDetail);

            }



            //     $reportDetail=[];
            // foreach ($s_app as $key => $value) {
            //     foreach ($staff_id as $key => $value1) {
            //         $reportDetail[]=array('appointment' =>$value->dtotl,
            //                                 'date' =>$staff_start );
            //         $staff_detail[]=array()
            //     }

            // }





            $reportdata[]= array(
                'staff_detail' => $staff_detail
            );
            $response['data'] = $reportdata;

        }else{
            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Staff report retrive successfully.';
            $reportdata=[];
            $ttl_book=0;

            $dates =getDatesFromRange($staff_start,$staff_end,'d-m-Y');


            $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date BETWEEN '$staff_start' and '$staff_end' and company_id=$id");

            foreach ($total_bk as $bk)
            {
                $ttl_book = $bk->ttl;
            }
            // $s_app =  Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl, a.staff_id as staff_id,a.date as date FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and s.company_id=$id and a.date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by a.staff_id,date");


            // $s_app =  Appointment::find_by_sql("SELECT s.staff_id,s.firstname,count(a.staff_id) as atotal, a.date from tbl_staffs s LEFT join tbl_appointments a ON s.staff_id=a.staff_id and a.company_id=$id and a.date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by a.staff_id,date");
            // print_r($s_app);
            // die;
            //$temp_arr=[];
            $reportDetail=[];
            $list=[];
            $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");

            //         $staff_id=array();
            foreach ($dates as $d)
            {

                $reportDetail[]=array('appointment' => 0, 'date' => $d);

            }

            //$k = 0;

            foreach ($staff as  $s) {

                // $s_app =  Appointment::find_by_sql("SELECT count(staff_id) as atotal, date as date,staff_id as staff_id from tbl_appointments where staff_id=$s->staff_id and company_id=$id and date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by staff_id,date");



                $staff_name=$s->firstname;
                $staff_id=$s->staff_id;

                $staff_detail[]=array('name' => $staff_name,
                    'staff_id' => $staff_id,
                    'staff_total' =>0,
                    'report_detail' => $reportDetail);
            }


            $s_app =  Appointment::find_by_sql("SELECT count(staff_id) as atotal, date as date,staff_id as staff_id from tbl_appointments where company_id=$id and date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by staff_id, date");
// print_r($s_app);
// die;
            foreach ($staff_detail as $key => $value)
            {
                $reportDetail=[];
                foreach ($dates as $d)
                {

                    $reportDetail[]=array('appointment' => 0, 'date' => $d);

                }

                $sid=$value['staff_id'];
                //foreach ($s_app as $key1 => $value1)
                $ttl=0;
                for($i=0;$i<count($s_app);$i++)
                {

                    //echo $s_app[$i]->atotal;

                    if($sid == $s_app[$i]->staff_id)
                    {
                        $ttl+=$s_app[$i]->atotal;
                        //echo $i;

                        $rdata=$value['report_detail'];
                        $date = date('d-m-Y',strtotime($s_app[$i]->date));
                        //echo $date;
                        //print_r($rdata);
                        //$i=0;
                        foreach ($rdata as $key2 => $value2)
                        {

                            $r_date=$value2['date'];
                            if($r_date == $date)
                            {
                                //echo $r_date."===".$date."</br>";
                                //$i++;

                                $reportDetail[$key2]['appointment']=$s_app[$i]->atotal;
                                $staff_detail[$key]['report_detail']=$reportDetail;
                                $staff_detail[$key]['staff_total']=$ttl;


                                //break;
                            }
                            //$reportDetail=setReportdetailtoZero($key2,$reportDetail);
                        }//break;
                    }
                }//echo $i;

            }







//                                  //array_push($temp_arr,$s_app);
//                                // $staff_id[]=$s->staff_id;
//                                  $staff_name[]=$s->firstname;
//                                  foreach($s_app as $sapps){
// 									if(count($sapps)>0){
// 									$adate[] = date('d-m-Y',strtotime($sapps->date));
// 									$aid[] = $sapps->staff_id;
// 									// $data['id'] = $sapps->staff_id;
// 									// if(in_array($sapps->date, $dates)){
// 									// 	echo $dates[$k];
// 									// }else{
// 									// 		$data['date']= $sapps->staff_id;

// 									// 	$data['id'] = date('d-m-Y',strtotime($sapps->date));;
// 									// }
// 									// print_r($data);
// 								}


// }
// $k++;
// 	}

// print_r($aid);


// 			for($k=0;$k<count($staff_name);$k++){
// 				for($d1=0;$d1<count($list);$d1++){

// 				}
// 			}


// 			die;

// 			foreach ($list as $key => $value) {
// 						# code...
// 					}
//                             $reportDetail=[];

//                               // foreach ($dates as $d)
//                               //       {

//                               //           $reportDetail[]=array('appointment' => 0, 'date' => $d);

//                               //       }
//                            // $ddd[]=$list['date'];
//                             // $dateCount = count($list);
//                             // echo $dateCount;
//                             for ($i=0; $i<count($list);$i++) {
//                             	if($i<40){
//                             	$date2=$list[$i]['date'];
//                             	//echo $date2;
//                             }

//                             	// print_r($value)
//                             	//print_r($date2);
//                             	if($s_app)
//                             	{

//                             		foreach ($s_app as $value1) {
//                             			// echo $value1->staff_id;
//                             			// die;
//                             			$date = date('d-m-Y',strtotime($value1->date));


//                             				if( $date === $date2 && $s->staff_id === $value1->staff_id)
//                             				{
//                             					$list[$i]['appointment']=$value1->atotal;
//                             				}

//                             		}
//                             	 }


//                             }

//                             //die;
//                             //$list=$value;
//                             $reportDetail=array_values($list);

//  						$staff_detail[]=array('name' => $staff_name,
//                                                    'report_detail' => $reportDetail);







// 								// if($s_app)
// 								// 	{
//         //                                  foreach($s_app as $value1)
//         //                                  {

// 								// 			// if(in_array($date, $dates))
//         //    //                                              {
//         //                                                 	 foreach ($dates as $d)
// 		      //                               						{
// 		      //                               							$date = date('d-m-Y',strtotime($value1->date));
// 		      //                               							//$x=date('d',strtotime($d));
// 		      //                               							if($date === $d)
// 		      //                                       					{
// 		      //                                   						$reportDetail[]=array('appointment' => $value1->atotal, 'date' => $d);
// 		      //                                           				}
// 		      //                                           				else{
// 		      //                                           				$reportDetail[]=array('appointment' => 0, 'date' => $d);
// 		      //                                           				}
// 		      //                              							}



//         //                                  				// }

//         //                        			            $staff_detail[]=array('name' => $staff_name,
//         //                                             'report_detail' => $reportDetail);
//         //                                              break;
//         //                     			  }

//         //                     		}
//                             	// 	else{
//                             	// 			foreach ($dates as $d)
//                             	// 			{

// 		                           //  		$reportDetail[]=array('appointment' => 0, 'date' => $d);

// 		                        			// }
// 		                        			// 		$staff_detail[]=array('name' => $staff_name,
//                              //                          'report_detail' => $reportDetail);
//                             	// 		  }
//                             $k++;


//                                     die;  // foreach ($staff_name as $key4 => $value)
            //     {
            //         $reportDetail=[];

            //         foreach ($dates as $d)
            //         {

            //           $reportDetail[]=array('appointment' => 0, 'date' => $d);

            //         }


            //             foreach ($temp_arr as $key => $value2)
            //             {

            //             if(count($value2) != 0)
            //                 {
            //                     foreach ($value2 as $key3 => $val) {


            //                 $date = date('d-m-Y',strtotime($val->date));

            //                     if(in_array($date, $dates))
            //                     {
            //                         $reportDetail[]['appointment']=$val->atotal;

            //                     }else{


            //                         $reportDetail[$key3]['appointment']=0;

            //                     }


            //                 }
            //                         $staff_detail[]=array('name' => $value,
            //                                     'report_detail' => $reportDetail);
            //                         break;
            //             }else{

            //              $staff_detail[]=array('name' => $value,
            //                                     'report_detail' => $reportDetail);
            //         }


            //     }


            //     }

            // foreach ($staff_id as $key => $value2)
            // {
            //     $reportDetail=[];
            //     foreach ($dates as $key3 => $d) {
            //       $reportDetail[]=array('appointment' =>0,
            //                             'date' =>$d);


            //     if($s_app)
            //     {
            //         foreach ($s_app as $value1)
            //         {

            //         $date = date('Y-m-d',strtotime($value1->date));
            //             if($value1->staff_id === $value2 && $date === $d)
            //                     {



            //                     $reportDetail[$key3]['appointment'] =$value1->dtotl;

            //                     $staff_detail[$key]['reportDetail']=$reportDetail;


            //                     }


            //         }
            //     }else{
            //         $reportDetail[]=array('appointment' =>0,
            //                                             'date' =>$d );
            //                     //print_r($reportDetail);

            //                     //array_push($temp_arr,$reportDetail);
            //                     $staff_detail[$key]['reportDetail']=$reportDetail;
            //     }
            //    }
            // }//die;
            //     // print_r($temp_arr);
            //     // die;

            // // foreach ($s_app as $value1) {

            // //                 $date = date('Y-m-d',strtotime($value1->date));
            // //                 $reportDetail=array('appointment' => $value1->dtotl,
            // //                                         'date' => $date);
            // //                 $name[]=$value1->name;
            // //                     }
            // //                   foreach ($name as $key => $value) {

            // //                         $staff_detail[]=array('name' =>$value,
            // //                                         'reportDetail' =>$reportDetail
            // //                                         );
            // //                     }


            $reportdata[]= array(
                'staff_detail' => $staff_detail
            );
            $response['data'] = $reportdata;


        }

    }

    echoResponse(200, $response);

});

function setReportdetailtoZero($index,$reportDetail)
{
    $reportDetail[$index]['appointment']=0;
    return $reportDetail;
}
/*Appointment report according to company id*/

$app->post('/:id/reportpet',function($id,$manual=null) use ($app)
{
    //verifyFields(array())
    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'No pet report found.';

    $starttime = strtotime($app->request->post('startdate'));
    $startdate = date('Y-m-d', strtotime($app->request->post('startdate')));
    $enddate = date('Y-m-d', strtotime($app->request->post('enddate')));
    $pet_option = $app->request->post('pet_option');
    $date_option = $app->request->post('date_option');
    // $staff_option = $app->request->post('staff_option');
    // $staff_start = date('Y-m-d', strtotime($app->request->post('staff_start')));
    // $staff_end = date('Y-m-d', strtotime($app->request->post('staff_end')));
    $client_id = $app->request->post('client_id');
    $pet_id = $app->request->post('pet_id');


    $smonth = date('m', $starttime);
    $syear = date('Y', $starttime);

    if($pet_option == 'single')
    {
        if($date_option == 'single')
        {
            $appointment=Appointment::find_by_sql("SELECT date as date,count(appointment_id) as total, visits as visits, visit_hours as visit_hours  FROM `tbl_appointments` WHERE `company_id` = $id AND `client_id` = $client_id and `pet_id` = $pet_id AND (status = 'accepted' OR status ='assign staff') and date='$startdate'");


            if(count($appointment) > 0)
            {
                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Appointments report retrive successfully.';
                $reportData = [];

                foreach ($appointment as $key => $value) {

                    $pet=Pet::find(array("conditions" => "pet_id={$pet_id}"));
                    //$credit=Credits::find(array("conditions" => "company_id={$id} and client_id={$client_id}"));

                    $cmp_detail1 = [];
                    $reportData = [];
                    // $staff_detail=[];
                    // $ttl_book_accepted=0;
                    // $ttl_book_assign=0;
                    // $ttl_book=0;
                    $temp_ttl=0;
                    $temp_visits=0;
                    $temp_visit_hour=0;

                    $temp_ttl +=$value->total;
                    $temp_visits +=$value->visits;
                    $temp_visit_hour +=$value->visit_hours;
                    $cmp_detail1[]=array(
                        //'date' => $startdate,
                        'profile_image'=>$pet->pet_image != NULL ? PET_PIC_PATH . $pet->pet_image : NULL,
                        'pet_name'=>$pet->pet_name,
                        'pet_birth' => $pet->pet_birth,
                        'pet_id' => $pet_id,
                        'client_id'=>$client_id,
                        'total' => $temp_ttl,
                        'visits' => $temp_visits,
                        'duration' => $temp_visit_hour,
                        //'deposite' =>$credit->remaining,
                    );


                    // if($staff_option == 'single')
                    // {
                    //  $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date='$staff_start' and company_id=$id");

                    //  foreach ($total_bk as $bk)
                    //          {
                    //                $ttl_book = $bk->ttl;
                    //          }

                    //      $total_bk_assign= Appointment::find_by_sql("SELECT count(appointment_id) as ttl2 FROM `tbl_appointments` WHERE `company_id` =$id and  date='$staff_start' AND status = 'assign staff' group by date");

                    //  foreach ($total_bk_assign as $bk2)
                    //          {
                    //                $ttl_book_assign += $bk2->ttl2;
                    //          }

                    //      $s_app = Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl, a.staff_id as staff_id FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and a.client_id=$client_id and a.pet_id=$pet_id and a.date='$staff_start' AND status ='accepted' group by a.staff_id");

                    //      //$s_app = Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and a.client_id=$client_id and a.pet_id=$pet_id and a.date='$staff_start' AND status ='accepted' group by a.staff_id");
                    //      $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
                    //              $staff_id=array();
                    //                foreach ($staff as  $s) {
                    //                  $staff_id[]=$s->staff_id;
                    //                  $staff_name[]=$s->firstname;
                    //                  }

                    //                  foreach ($staff_name as $value) {

                    //                      $staff_detail[]=array('name' => $value,'appointment' => 0);
                    //                  }

                    //                      foreach ($s_app as $value1) {

                    //                          foreach ($staff_id as $key => $value2) {

                    //                              if($value1->staff_id === $value2)
                    //                              {
                    //                                  $staff_detail[$key]['appointment']=$value1->dtotl;
                    //                              }
                    //                          }

                    //                      }


                    // }elseif($staff_option == 'range'){

                    //      $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date BETWEEN '$staff_start' and '$staff_end' and company_id=$id");

                    //  foreach ($total_bk as $bk)
                    //          {
                    //                $ttl_book = $bk->ttl;
                    //          }

                    //      $total_bk_assign= Appointment::find_by_sql("SELECT count(appointment_id) as ttl2 FROM `tbl_appointments` WHERE `company_id` =$id and  date BETWEEN '$staff_start' and '$staff_end' AND status = 'assign staff' group by date");

                    //  foreach ($total_bk_assign as $bk2)
                    //          {
                    //                $ttl_book_assign += $bk2->ttl2;
                    //          }

                    //      $s_app =  Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl, a.staff_id as staff_id FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and s.company_id=$id and a.date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by a.staff_id");

                    //      $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
                    //              $staff_id=array();
                    //                foreach ($staff as  $s) {
                    //                  $staff_id[]=$s->staff_id;
                    //                  $staff_name[]=$s->firstname;
                    //                  }

                    //                  foreach ($staff_name as $value) {

                    //                      $staff_detail[]=array('name' => $value,'appointment' => 0);
                    //                  }

                    //                      foreach ($s_app as $value1) {

                    //                          foreach ($staff_id as $key => $value2) {

                    //                              if($value1->staff_id === $value2)
                    //                              {
                    //                                  $staff_detail[$key]['appointment']=$value1->dtotl;
                    //                              }
                    //                          }

                    //                      }


                    // }else{
                    //  $staff_detail[]=null;
                    // }
                }


                $reportData[] = array(
                    // 'Month' => date('M Y', $starttime),
                    //  //'timeperiod' => $timeperiod,
                    'total_booking' => $temp_ttl,
                    //'staff_total' => $ttl_book,
                    'total_visits' => $temp_visits,
                    'total_duration' => $temp_visit_hour,
                    'booking' => $cmp_detail1,
                    //'staff' => $staff_detail,
                );
            }

            $response['data'] = $reportData;

        }else{
            /*code for date range*/

            // $start=date('d-m-Y',strtotime($startdate));
            // $end=date('d-m-Y',strtotime($enddate));

            // $days=getDatesFromRange($start,$end);

            //     foreach ($days as $da)
            //     {
            //         $list[$da]=array('date' =>$da,
            //                     'total' => 0
            //                 );
            //     }

            $total_book=0;
            $total_visits=0;
            $total_duration=0;
            $appointment=Appointment::find_by_sql("SELECT date as date,count(appointment_id) as total, visits as visits, visit_hours as visit_hours FROM `tbl_appointments` WHERE `company_id` = $id AND `client_id` = $client_id and `pet_id` = $pet_id and (status = 'accepted' OR status ='assign staff') and date BETWEEN '$startdate' and '$enddate' GROUP BY date ");


            if(count($appointment) > 0)
            {
                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Appointments  report retrive successfully.';
                $reportData = [];

                $temp_ttl=0;
                $temp_visits=0;
                $temp_visit_hour=0;
                foreach ($appointment as $key => $value)
                {

                    $pet=Pet::find(array("conditions" => "pet_id={$pet_id}"));

                    // $ttl_book_accepted=0;
                    // $ttl_book_assign=0;
                    // $ttl_book=0;
                    // $reportData = [];
                    $cmp_detail1 = [];
                    // $staff_detail=[];


                    $temp_ttl +=$value->total;
                    $temp_visits +=$value->visits;
                    $temp_visit_hour += (float)$value->visit_hours;

                    $cmp_detail1[]=array(
                        //'date' => $startdate,
                        'profile_image'=>$pet->pet_image != NULL ? PET_PIC_PATH . $pet->pet_image : NULL,
                        'pet_name'=>$pet->pet_name,
                        'pet_birth' => $pet->pet_birth,
                        'pet_id' => $pet_id,
                        'client_id'=>$client_id,
                        'total' => $temp_ttl,
                        'visits' => $temp_visits,
                        'duration' => $temp_visit_hour,
                        //'deposite' =>$credit->remaining,
                    );



                    // $app_date=date('d-m-Y',strtotime($value->date));


                    // $list[$app_date]['date'] = $app_date;
                    // $list[$app_date]['total'] = $value->total;

                    // $cmp_detail1 = array_values($list);


                    // if($staff_option == 'single')
                    //    {

                    //     $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date='$staff_start' and company_id=$id and client_id=$client_id");

                    //     foreach ($total_bk as $bk)
                    //             {
                    //                   $ttl_book = $bk->ttl;
                    //             }

                    //         $total_bk_assign= Appointment::find_by_sql("SELECT count(appointment_id) as ttl2 FROM `tbl_appointments` WHERE `company_id` =$id and  date='$staff_start' AND status = 'assign staff' group by date");

                    //     foreach ($total_bk_assign as $bk2)
                    //             {
                    //                   $ttl_book_assign += $bk2->ttl2;
                    //             }

                    //         $s_app = Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl, a.staff_id as staff_id FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and a.client_id=$client_id and a.pet_id=$pet_id and a.date='$staff_start' AND status ='accepted' group by a.staff_id");


                    //             $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
                    //                 $staff_id=array();
                    //                   foreach ($staff as  $s) {
                    //                     $staff_id[]=$s->staff_id;
                    //                     $staff_name[]=$s->firstname;
                    //                     }

                    //                     foreach ($staff_name as $value) {

                    //                         $staff_detail[]=array('name' => $value,'appointment' => 0);
                    //                     }

                    //                         foreach ($s_app as $value1) {

                    //                             foreach ($staff_id as $key => $value2) {

                    //                                 if($value1->staff_id === $value2)
                    //                                 {
                    //                                     $staff_detail[$key]['appointment']=$value1->dtotl;
                    //                                 }
                    //                             }

                    //                         }


                    //    }elseif($staff_option == 'range'){

                    //         $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date BETWEEN '$staff_start' and '$staff_end' and company_id=$id and client_id=$client_id and pet_id=$pet_id" );

                    //     foreach ($total_bk as $bk)
                    //             {
                    //                   $ttl_book = $bk->ttl;
                    //             }

                    //         $total_bk_assign= Appointment::find_by_sql("SELECT count(appointment_id) as ttl2 FROM `tbl_appointments` WHERE `company_id` =$id and  date BETWEEN '$staff_start' and '$staff_end' AND status = 'assign staff' and client_id=$client_id and pet_id=$pet_id group by date");

                    //     foreach ($total_bk_assign as $bk2)
                    //             {
                    //                   $ttl_book_assign += $bk2->ttl2;
                    //             }

                    //         $s_app =  Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl ,a.staff as staff_id FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and s.company_id=$id and a.date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by a.staff_id");

                    //         $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
                    //                 $staff_id=array();
                    //                   foreach ($staff as  $s) {
                    //                     $staff_id[]=$s->staff_id;
                    //                     $staff_name[]=$s->firstname;
                    //                     }

                    //                     foreach ($staff_name as $value) {

                    //                         $staff_detail[]=array('name' => $value,'appointment' => 0);
                    //                     }

                    //                         foreach ($s_app as $value1) {

                    //                             foreach ($staff_id as $key => $value2) {

                    //                                 if($value1->staff_id === $value2)
                    //                                 {
                    //                                     $staff_detail[$key]['appointment']=$value1->dtotl;
                    //                                 }
                    //                             }

                    //                         }


                    //    } else{
                    //      $staff_detail[] = null;
                    //    }

                }
                $total_book+=$temp_ttl;
                $total_visits+=$temp_visits;
                $total_duration+=$temp_visit_hour;
                $reportData[] = array(
                    // 'Month' => date('M Y', $starttime),
                    //  //'timeperiod' => $timeperiod,
                    'total_booking' => $total_book,
                    //'staff_total' => $ttl_book,
                    'total_visits' => $total_visits,
                    'total_duration' => $total_duration,
                    'booking' => $cmp_detail1,
                    // 'staff' => $staff_detail,
                );

            }//die;
            $response['data'] = $reportData;
        }

        /**For ALL*/

    }else{

        if($date_option == 'single')
        {
            $contract=contract::find('all',array("conditions" => "company_id={$id}"));

            foreach ($contract as $value)
            {

                $client=Client::find_by_sql("select client_id as client_id from tbl_clients where client_id=$value->client_id and status='1'");
                foreach ($client as $value1)
                {
                    $c_id[]=$value1->client_id;

                }
                //$xyz[]=$appointment;
            }

            $temp=[];
            $total_book=0;
            $total_visits=0;
            $total_duration=0;
            foreach ($c_id as $val)
            {


                $appointment=Appointment::find_by_sql("SELECT date as date, pet_id as pet_id ,count(appointment_id) as total, client_id as client_id, visits as visits, visit_hours as visit_hours FROM `tbl_appointments` WHERE `company_id` = $id and `client_id` = $val  and (status = 'accepted' OR status ='assign staff') and date = '$startdate' GROUP BY date");

                //print_r($appointment);
                //die;
                $pet=Pet::find(array("conditions" => "client_id={$val}"));
                //print_r($pet);
                if($appointment)
                {

                    //die;
                    $response['error_code'] = 0;
                    $response['status'] = true;
                    $response['message'] = 'Appointments  report retrive successfully.';
                    $reportData = [];
                    $temp_ttl=0;
                    $temp_visit=0;
                    $temp_hour=0;
                    //  $ttl_book_accepted=0;
                    // $ttl_book_assign=0;
                    // $ttl_book=0;
                    $reportData = [];

                    // $staff_detail=[];
                    $cmp_detail1 = [];
                    foreach ($appointment as $value2)
                    {

                        //print_r($value2);

                        $temp_ttl +=$value2->total;
                        $temp_visit += $value2->visits;
                        $temp_hour += $value2->visit_hours;

                        $cmp_detail1=array(
                            //'date' => $startdate,
                            'profile_image'=>$pet->pet_image != NULL ? PET_PIC_PATH . $pet->pet_image : NULL,
                            'pet_name'=>$pet->pet_name,
                            'pet_birth' => $pet->pet_birth,
                            'pet_id' => $pet->pet_id,
                            'client_id'=>$val,
                            'total' => $temp_ttl,
                            'visits' => $temp_visit,
                            'duration' => $temp_hour,
                            //'deposite' =>$credit->remaining,
                        );


                    }

                    $total_book+=$temp_ttl;
                    $total_visits+=$temp_visit;
                    $total_duration+=$temp_hour;


                }else{
// echo "higk </n>";

                    $cmp_detail1=array(
                        //'date' => $startdate,
                        'profile_image'=>$pet->pet_image != NULL ? PET_PIC_PATH . $pet->pet_image : NULL,
                        'pet_name'=>$pet->pet_name,
                        'pet_birth' => $pet->pet_birth,
                        'pet_id' => $pet->pet_id,
                        'client_id'=>$val,
                        'total' => 0,
                        'visits' => 0,
                        'duration' => 0,
                        //'deposite' =>$credit->remaining,
                    );





                }
                array_push($temp,$cmp_detail1);
            }
//print_r($temp);die;
// // die;
//                if($staff_option == 'single')
//                {
//                 $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date='$staff_start' and company_id=$id");

//                 foreach ($total_bk as $bk)
//                         {
//                               $ttl_book = $bk->ttl;
//                         }

//                     $total_bk_assign= Appointment::find_by_sql("SELECT count(appointment_id) as ttl2 FROM `tbl_appointments` WHERE `company_id` =$id and  date='$staff_start' AND status = 'assign staff' group by date");

//                 foreach ($total_bk_assign as $bk2)
//                         {
//                               $ttl_book_assign += $bk2->ttl2;
//                         }

//                     $s_app = Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl ,a.staff_id as staff_id FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and s.company_id=$id and a.date='$staff_start' AND status ='accepted' group by a.staff_id");

//                            $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
//                             $staff_id=array();
//                               foreach ($staff as  $s) {
//                                 $staff_id[]=$s->staff_id;
//                                 $staff_name[]=$s->firstname;
//                                 }

//                                 foreach ($staff_name as $value) {

//                                     $staff_detail[]=array('name' => $value,'appointment' => 0);
//                                 }

//                                     foreach ($s_app as $value1) {

//                                         foreach ($staff_id as $key => $value2) {

//                                             if($value1->staff_id === $value2)
//                                             {
//                                                 $staff_detail[$key]['appointment']=$value1->dtotl;
//                                             }
//                                         }

//                                     }



//                }elseif($staff_option == 'range'){

//                     $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date BETWEEN '$staff_start' and '$staff_end' and company_id=$id");

//                 foreach ($total_bk as $bk)
//                         {
//                               $ttl_book = $bk->ttl;
//                         }

//                     $total_bk_assign= Appointment::find_by_sql("SELECT count(appointment_id) as ttl2 FROM `tbl_appointments` WHERE `company_id` =$id and  date BETWEEN '$staff_start' and '$staff_end' AND status = 'assign staff' group by date");

//                 foreach ($total_bk_assign as $bk2)
//                         {
//                               $ttl_book_assign += $bk2->ttl2;
//                         }

//                     $s_app =  Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl ,a.staff_id as staff_id FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and s.company_id=$id and a.date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by a.staff_id");

//                    $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
//                             $staff_id=array();
//                               foreach ($staff as  $s) {
//                                 $staff_id[]=$s->staff_id;
//                                 $staff_name[]=$s->firstname;
//                                 }

//                                 foreach ($staff_name as $value) {

//                                     $staff_detail[]=array('name' => $value,'appointment' => 0);
//                                 }

//                                     foreach ($s_app as $value1) {

//                                         foreach ($staff_id as $key => $value2) {

//                                             if($value1->staff_id === $value2)
//                                             {
//                                                 $staff_detail[$key]['appointment']=$value1->dtotl;
//                                             }
//                                         }

//                                     }


//                }else{
//                 $staff_detail=[];
//             }

            $reportData[] = array(
                // 'Month' => date('M Y', $starttime),
                //  //'timeperiod' => $timeperiod,
                'total_booking' => $total_book,
                //'staff_total' => $ttl_book,
                'total_visits' => $total_visits,
                'total_duration' =>$total_duration ,
                'booking' => $temp,
                //'staff' => $staff_detail
            );


            $response['data'] = $reportData;

        }else{


            /*code for date range*/

            // $start=date('d-m-Y',strtotime($startdate));
            //         $end=date('d-m-Y',strtotime($enddate));

            //         $days=getDatesFromRange($start,$end);

            //             foreach ($days as $da)
            //             {
            //                 $list[$da]=array('date' =>$da,
            //                             'total' => 0
            //                         );
            //             }

            $contract=contract::find('all',array("conditions" => "company_id={$id}"));

            foreach ($contract as $value)
            {

                $client=Client::find_by_sql("select client_id as client_id from tbl_clients where client_id=$value->client_id and status='1'");
                foreach ($client as $value1)
                {
                    $c_id[]=$value1->client_id;

                }
                //$xyz[]=$appointment;
            }
            // print_r($c_id);
            // die;
            $temp=[];
            $total_book=0;
            $total_visits=0;
            $total_duration=0;
            foreach ($c_id as $val)
            {


                $appointment=Appointment::find_by_sql("SELECT date as date, pet_id as pet_id ,count(appointment_id) as total, client_id as client_id, visits as visits, visit_hours as visit_hours FROM `tbl_appointments` WHERE `company_id` = $id and `client_id` = $val  and (status = 'accepted' OR status ='assign staff') and date BETWEEN '$startdate' and '$enddate' GROUP BY date");

                //print_r($appointment);
                $pet=Pet::find(array("conditions" => "client_id={$val}"));
                if(count($appointment) > 0)
                {

                    $response['error_code'] = 0;
                    $response['status'] = true;
                    $response['message'] = 'Appointments  report retrive successfully.';
                    $reportData = [];
                    $temp_ttl=0;
                    $temp_visit=0;
                    $temp_hour=0;
                    //  $ttl_book_accepted=0;
                    // $ttl_book_assign=0;
                    // $ttl_book=0;
                    $reportData = [];

                    //$staff_detail=[];
                    $cmp_detail1 = [];
                    foreach ($appointment as $value2)
                    {



                        $temp_ttl +=$value2->total;
                        $temp_visit += $value2->visits;
                        $temp_hour += $value2->visit_hours;

                        $cmp_detail1=array(
                            //'date' => $startdate,
                            'profile_image'=>$pet->pet_image != NULL ? PET_PIC_PATH . $pet->pet_image : NULL,
                            'pet_name'=>$pet->pet_name,
                            'pet_birth' => $pet->pet_birth,
                            'pet_id' => $pet->pet_id,
                            'client_id'=>$val,
                            'total' => $temp_ttl,
                            'visits' => $temp_visit,
                            'duration' => $temp_hour,
                            //'deposite' =>$credit->remaining,
                        );



                    }

                    $total_book+=$temp_ttl;
                    $total_visits+=$temp_visit;
                    $total_duration+=$temp_hour;


                    // $total_visits=0;
                    // $total_duration=0;


                }else{
                    $cmp_detail1=array(
                        //'date' => $startdate,
                        'profile_image'=>$pet->pet_image != NULL ? PET_PIC_PATH . $pet->pet_image : NULL,
                        'pet_name'=>$pet->pet_name,
                        'pet_birth' => $pet->pet_birth,
                        'pet_id' => $pet->pet_id,
                        'client_id'=>$val,
                        'total' => 0,
                        'visits' => 0,
                        'duration' => 0,
                        //'deposite' =>$credit->remaining,
                    );
                }
                array_push($temp,$cmp_detail1);

            }


            // if($staff_option == 'single')
            //            {
            //             $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date='$staff_start' and company_id=$id");

            //             foreach ($total_bk as $bk)
            //                     {
            //                           $ttl_book = $bk->ttl;
            //                     }

            //                 $total_bk_assign= Appointment::find_by_sql("SELECT count(appointment_id) as ttl2 FROM `tbl_appointments` WHERE `company_id` =$id and  date='$staff_start' AND status = 'assign staff' group by date");

            //             foreach ($total_bk_assign as $bk2)
            //                     {
            //                           $ttl_book_assign += $bk2->ttl2;
            //                     }

            //                 $s_app = Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl,a.staff_id as staff_id FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and s.company_id=$id and a.date='$staff_start' AND status ='accepted' group by a.staff_id");

            //                         $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
            //                         $staff_id=array();
            //                           foreach ($staff as  $s) {
            //                             $staff_id[]=$s->staff_id;
            //                             $staff_name[]=$s->firstname;
            //                             }

            //                             foreach ($staff_name as $value) {

            //                                 $staff_detail[]=array('name' => $value,'appointment' => 0);
            //                             }

            //                                 foreach ($s_app as $value1) {

            //                                     foreach ($staff_id as $key => $value2) {

            //                                         if($value1->staff_id === $value2)
            //                                         {
            //                                             $staff_detail[$key]['appointment']=$value1->dtotl;
            //                                         }
            //                                     }

            //                                 }


            //     if($s_app){
            //             foreach ($s_app as $value) {


            //     $staff_detail = array();
            //         foreach ($staff as $snm)
            //         {

            //             if($value->name === $snm->firstname)
            //             {
            //                  $staff_detail[] = array(
            //                 'name' => $snm->firstname,
            //                 'appointments' =>$value->dtotl,
            //                 );
            //                  echo $value->dtotl;
            //             }else{

            //                 $staff_detail[] = array(
            //                 'name' => $snm->firstname,
            //                 'appointments' =>0,
            //                 );
            //                 echo $value->dtotl;

            //             }


            //             }
            //             $ttl_book_accepted += $value->dtotl;
            //     }

            // }else{

            //     $staff_detail = array();
            //         foreach ($staff as $snm)
            //         {

            //                  $staff_detail[] = array(
            //                 'name' => $snm->firstname,
            //                 'appointments' =>0,
            //                 );
            //             }
            //             //$ttl_book_accepted += $value->dtotl;
            //     }

            // }elseif($staff_option == 'range'){

            //      $total_bk= Appointment::find_by_sql("SELECT count(appointment_id) as ttl FROM `tbl_appointments` WHERE status = 'accepted' and date BETWEEN '$staff_start' and '$staff_end' and company_id=$id");

            //  foreach ($total_bk as $bk)
            //          {
            //                $ttl_book = $bk->ttl;
            //          }

            //      $total_bk_assign= Appointment::find_by_sql("SELECT count(appointment_id) as ttl2 FROM `tbl_appointments` WHERE `company_id` =$id and  date BETWEEN '$staff_start' and '$staff_end' AND status = 'assign staff' group by date");

            //  foreach ($total_bk_assign as $bk2)
            //          {
            //                $ttl_book_assign += $bk2->ttl2;
            //          }

            //      $s_app =  Appointment::find_by_sql("SELECT s.firstname as name ,count(s.staff_id) as dtotl ,a.staff_id as staff_id FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and s.company_id=$id and a.date BETWEEN '$staff_start' and '$staff_end' AND status ='accepted' group by a.staff_id");


            //       $staff=Staff::find_by_sql("select staff_id, firstname from tbl_staffs where company_id=$id");
            //              $staff_id=array();
            //                foreach ($staff as  $s) {
            //                  $staff_id[]=$s->staff_id;
            //                  $staff_name[]=$s->firstname;
            //                  }

            //                  foreach ($staff_name as $value) {

            //                      $staff_detail[]=array('name' => $value,'appointment' => 0);
            //                  }

            //                      foreach ($s_app as $value1) {

            //                          foreach ($staff_id as $key => $value2) {

            //                              if($value1->staff_id === $value2)
            //                              {
            //                                  $staff_detail[$key]['appointment']=$value1->dtotl;
            //                              }
            //                          }

            //                      }


            // }else{
            //  $staff_detail = [];
            // }


            $reportData[] = array(
                // 'Month' => date('M Y', $starttime),
                //'timeperiod' => $timeperiod,
                'total_booking' => $total_book,
                //'staff_total' => $ttl_book,
                'total_visits' => $total_visits,
                'total_duration' => $total_duration,
                'booking' => $temp,
                //'staff' => $staff_detail,
            );

            $response['data'] = $reportData;
        }

    }

    echoResponse(200, $response);

});




/*
 * Appointment Status Update
 */
$app->post('/appointmentstatusupdate', function() use($app) {

    verifyFields(array('appointment_id', 'status'));

    $appointment_id = $app->request->post('appointment_id');
    $status = $app->request->post('status');


    $appointment = Appointment::find(array('appointment_id' => $appointment_id));


    if (empty($appointment)) {

        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Error! Something went wrong. please try again later.';
        $response['data'] = [];
        echoResponse(200, $response);
        $app->stop();
    } else {
        if ($status == 'rejected') {
            $appointment->status = $status;
        } else {

            $price = Price::find_by_service_id($appointment->service_id);
            $whole = floor($appointment->visit_hours);      // whole number from
            $fraction = $appointment->visit_hours - $whole; // getting part after decimal point

            /*
             * Price calculating
             */
            if ($fraction) {

                $minutes = ($fraction * 100);   // gettong minutes from
                if ($minutes == 30 && empty($whole)) {
                    $total = ($price->half_hour_price);
                } else {
                    $total = $whole * $price->full_hour_price + round($minutes * ($price->full_hour_price / 60));
                }
            } else {
                $total = $whole * $price->full_hour_price;
            }
            $appointment->price = $total * $appointment->visits;
            $appointment->status = 'payment pending';
        }
        $appointment->save();
    }

    if ($appointment) {
        $response['error_code'] = 0;
        $response['message'] = 'Appointment request Successfully ' . ($status == 'rejected' ? $status : 'accepted');
        $response['status'] = true;
        $response['data'] = array(
            'appointment_id' => $appointment->appointment_id,
            'company_id' => $appointment->company->company_id,
            'company_name' => $appointment->company->company_name,
            'client_name' => $appointment->client->firstname . ' ' . $appointment->client->lastname,
            'firstname' => $appointment->client->firstname,
            'lastname' => $appointment->client->lastname,
            'company_image' => $appointment->company->company_image ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
            'client_image' => $appointment->client->profile_image ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
            'status' => $appointment->status,
            'price' => $appointment->price,
            'service_id' => $appointment->service_id,
            //'service_name' => $serviceName,
            'date' => $appointment->date,
            'visits' => $appointment->visits,
            'visit_hours' => $appointment->visit_hours,
            'status' => $appointment->status,
            'notification_flag' => "appointment_request_status",
        );


        $notification = array('message' => $appointment->company->company_name . ' ' . ($status == 'rejected' ? $status : 'accepted ') . 'your appointment request',
            'player_ids' => array($appointment->client->player_id),
            'data' => $response['data'],
        );

//    var_dump($notification);
//    die;
        //sendMessage($notification);
    }

    echoResponse(200, $response);
});



/*
 * Appointment Resend
 */
$app->post('/:id/appointments/resend', function($id) use ($app) {

    verifyFields(array('date'));
    $stringdate = $app->request->post('date');
    $date = date('Y-m-d', strtotime($stringdate));
    $appointment = Appointment::find(array('appointment_id' => $id, 'status' => 'rejected'));

    if (empty($appointment)) {

        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Error! Something went wrong. please try again later.';
        $response['data'] = [];
        echoResponse(200, $response);
        $app->stop();
    } else {

        $client = Client::find($appointment->client_id);
        $rf_company_id = $client->company_id;
        if (empty($rf_company_id)) {
            $appointment->created_by = 'client';
        } else {
            $appointment->created_by = 'company';
        }
        $appointment->status = 'pending';
        $appointment->date = $date;
        $appointment->created_at = date('Y-m-d H:i:s');
        $appointment->save();
        $appointmentData = [];
        $client_name = $appointment->client->firstname . ' ' . $appointment->client->lastname;
        if ($appointment->save()) {
            $response['error_code'] = 0;
            $response['message'] = 'Appointment request Successfully sent';
            $response['status'] = true;
            $service = Service::find(array('conditions' => "service_id = {$appointment->service_id}"));
            $serviceName = empty($service) ? NULL : $service->service_name;
            $ownerDetail = array(
                'client_id' => $appointment->client->client_id,
                'firstname' => $appointment->client->firstname,
                'lastname' => $appointment->client->lastname,
                'emailid' => $appointment->client->emailid,
                'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
                'contact_number' => $appointment->client->contact_number,
                'client_address' => $appointment->client->client_address,
                'client_notes' => $appointment->client->client_notes,
                'player_id' => $appointment->client->player_id,
            );
            //if($type == 'compnay')
            $companyDetail = array(
                'company_id' => $appointment->company->company_id,
                'account_id' => $appointment->company->account_id,
                'company_name' => $appointment->company->company_name,
                'emailid' => $appointment->company->emailid,
                'contact_number' => $appointment->company->contact_number,
                'company_image' => $appointment->company->company_image != NULL ? COMPANY_PIC_PATH . $appointment->company->company_image : NULL,
                'website' => $appointment->company->website,
                'address' => $appointment->company->address,
                'about' => $appointment->company->about,
            );


            $price = Price::find(array('conditions' => "service_id = $appointment->service_id"));
            //var_dump($price);
            // die;

            $whole = floor($appointment->visit_hours);      // whole number from
            $fraction = $appointment->visit_hours - $whole; // getting part after decimal point

            /*
             * Price calculating
             */
            if (!empty($price)) {

                if ($fraction) {

                    $minutes = ($fraction * 100);   // gettong minutes from
                    if ($minutes == 30 && empty($whole)) {
                        $total = ($price->half_hour_price);
                    } else {
                        $total = $whole * $price->full_hour_price + round($minutes * ($price->full_hour_price / 60));
                    }
                } else {
                    $total = $whole * $price->full_hour_price;
                }
            } else {
                $total = NULL;
            }
            $pet1 = Appointment::find('all',array("conditions" => "pet_id = '{$value->pet->pet_id}'"));
            $appointmentData[] = array(
                'appointment_id' => $appointment->appointment_id,
                'company_detail' => $companyDetail,
                'owner_detail' => $ownerDetail,
                'service_id' => $appointment->service_id,
                'service_name' => $serviceName,
                'date' => $appointment->date,
                'visits' => $appointment->visits,
                'visit_hours' => $appointment->visit_hours,
                'base_price' => $appointment->visits * $total,
                'additional_visit' => NULL,
                'additional_visit_price' => empty($price) ? NULL : (NULL * $price->additional_visits_price),
                'additional_hour' => NULL,
                'additional_hour_price' => empty($price) ? NULL : (NULL * $price->additional_hours_price),
                'status' => $appointment->status,
                'price' => $appointment->price,
                'pet_detail' => array(
                    'pet_id' => $appointment->pet->pet_id,
                    'pet_name' => count($pet1)>1?$appointment->pet->pet_name.' '.$appointment->client->lastname:$appointment->pet->pet_name,
                    'pet_birth' => $appointment->pet->pet_birth,
                    'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                    'pet_age' => $appointment->pet->age,
                    'gender' => $appointment->pet->gender,
                    'pet_type' => $appointment->pet->pet_type,
                    'breed' => $appointment->pet->breed,
                    'neutered' => $appointment->pet->neutered,
                    'spayed' => $appointment->pet->spayed,
                    'injuries' => $appointment->pet->injuries,
                    'medical_detail' => $appointment->pet->medical_detail,
                    'pet_notes' => $appointment->pet->pet_notes,
                    'latitude' => $appointment->pet->latitude,
                    'longitude' => $appointment->pet->longitude,
                ),
                'message' => $appointment->message,
                'notification_flag' => "appointment_request_resend",
            );
        }
        $response['data'] = $appointmentData;
        $notification = array('message' => $client_name . ' has booked an appointment on ' . $date,
            'player_ids' => array($appointment->company->player_id),
            'data' => $response['data'],
        );

        sendMessage($notification);
    }
    echoResponse(200, $response);
});







/*
 * Rejected appointment listing
 */
$app->get('/:id/appointmentrejected', function($id) use($app) {
    $response['error_code'] = 1;
    $response['message'] = 'No rejected appointment found';
    $response['status'] = false;



    // $appointment = Appointment::find('all', array('conditions' =>"company_id={$id} and status = 'rejected'"));
    $appointment = Appointment::find('all', array('conditions' => "company_id={$id}  AND status = 'rejected' ",'order' => 'date desc'));
    //$appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id} AND status = '{$status}' AND date =  '$date' ", 'order' => 'date desc'));
//    var_dump($appointment);
//    die;
    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Rejected appoinment list retrived successfully.';

        $appointmentData = [];

        foreach ($appointment as $key => $value) {

            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $services = CompanyService::find_by_sql("SELECT ts.service_id, ts.service_name FROM tbl_services ts, tbl_company_services tcs where tcs.service_id = ts.service_id AND tcs.company_id = {$value->company->company_id}");
            $companyServices = array();
            foreach ($services as $companyService) {
                $companyServices[] = array(
                    'id' => $companyService->service_id,
                    'name' => $companyService->service_name
                );
            }

            if ($value->status == 'rejected') {
                $staff = Staff::find($value->staff_id);
                $sfirstname = isset($staff->firstname) ? $staff->firstname : '';
                $slatname = isset($staff->lastname)?$staff->lastname:'';
                $sid = isset($staff->staff_id)?$staff->staff_id:'';
            } else {
                $sfirstname = '';
                $slatname = '';
                $sid = '';
            }


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
                'profile_status' => $value->client->status ==1 ? 'active' : 'inactive',
            );
            //if($type == 'compnay')
            $companyDetail = array(
                'company_id' => $value->company->company_id,
                'account_id' => $value->company->account_id,
                'company_name' => $value->company->company_name,
                'emailid' => $value->company->emailid,
                'contact_number' => $value->company->contact_number,
                'company_image' => $value->company->company_image != NULL ? COMPANY_PIC_PATH . $value->company->company_image : NULL,
                'website' => $value->company->website,
                'address' => $value->company->address,
                'about' => $value->company->about,
                'services' => $companyServices,
            );
            $price = Price::find(array('conditions' => "service_id = $value->service_id"));
            //var_dump($price);
            // die;

            $whole = floor($value->visit_hours);      // whole number from
            $fraction = $value->visit_hours - $whole; // getting part after decimal point

            /*
             * Price calculating
             */
            if (!empty($price)) {

                if ($fraction) {

                    $minutes = ($fraction * 100);   // gettong minutes from
                    if ($minutes == 30 && empty($whole)) {
                        $total = ($price->half_hour_price);
                    } else {
                        $total = $whole * $price->full_hour_price + round($minutes * ($price->full_hour_price / 60));
                    }
                } else {
                    $total = $whole * $price->full_hour_price;
                }
            } else {
                $total = NULL;
            }
            $flag = $value->created_by == 'company' ? TRUE : FALSE;
            $last_name = $value->client->lastname;
            $lst =  $value->client->client_id;
            $pet1 = Appointment::find('all',array("conditions" => "pet_id = '{$value->pet->pet_id}'"));
            //$pet1 = Pet::find($value->pet->pet_name);
            $appointmentData[] = array(
                'appointment_id' => $value->appointment_id,
                'company_detail' => $companyDetail,
                'owner_detail' => $ownerDetail,
                'isManualClient' => $flag,
                'service_id' => $value->service_id,
                'service_name' => $serviceName,
                'date' => $value->date,
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'base_price' => $value->visits * $total,
                'additional_visit' => NULL,
                'additional_visit_price' => empty($price) ? NULL : (NULL * $price->additional_visits_price),
                'additional_hour' => NULL,
                'additional_hour_price' => empty($price) ? NULL : (NULL * $price->additional_hours_price),
                'price' => $value->price,
                'status' => $value->status,
                'staff_firstname' => $sfirstname,
                'staff_lastname' => $slatname,
                'pet_detail' => array(
                    'pet_id' => $value->pet->pet_id,
                    'pet_name' => count($pet1)> 1 ? $value->pet->pet_name .' '. $last_name : $value->pet->pet_name,
                    'pet_birth' => $value->pet->pet_birth,
                    'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                    'pet_age' => $value->pet->age,
                    'gender' => $value->pet->gender,
                    'pet_type' => $value->pet->pet_type,
                    'breed' => $value->pet->breed,
                    'neutered' => $value->pet->neutered,
                    'spayed' => $value->pet->spayed,
                    'injuries' => $value->pet->injuries,
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
    //     foreach ($appointment as $key => $value) {

    //         $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
    //         $serviceName = empty($service) ? NULL : $service->service_name;
    //         $ownerDetail = array(
    //             'client_id' => $value->client->client_id,
    //             'firstname' => $value->client->firstname,
    //             'lastname' => $value->client->lastname,
    //             'emailid' => $value->client->emailid,
    //             'profile_image' => $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
    //             'contact_number' => $value->client->contact_number,
    //             'client_address' => $value->client->client_address,
    //             'client_notes' => $value->client->client_notes,
    //             'player_id' => $value->client->player_id,
    //         );

    //         $appointmentData[] = array(
    //             'appointment_id' => $value->appointment_id,
    //             'company_id' => $value->company_id,
    //             'owner_detail' => $ownerDetail,
    //             'service_id' => $value->service_id,
    //             'service_name' => $serviceName,
    //             'date' => date('d-m-Y',strtotime($value->date)),
    //             'visits' => $value->visits,
    //             'visit_hours' => $value->visit_hours,
    //             'status' => $value->status,
    //             'pet_detail' => array(
    //                 'pet_id' => $value->pet->pet_id,
    //                 'pet_name' => $value->pet->pet_name,
    //                 'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
    //                 'pet_age' => $value->pet->age,
    //                 'medical_detail' => $value->pet->medical_detail,
    //                 'pet_notes' => $value->pet->pet_notes,
    //                 'latitude' => $value->pet->latitude,
    //                 'longitude' => $value->pet->longitude,
    //             ),
    //             'message' => $value->message,
    //         );
    //     }

    //     $response['data'] = $appointmentData;
    // }
    echoResponse(200, $response);
});

/*
 * Appointment history accoridng to pet id
 */
$app->get('/:id/appointmenthistorypet', function($id) use ($app) {


    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;

    $date=date('Y-m-d', strtotime("-3 Months"));

//$appointment = Appointment::find('all', array('conditions' => "pet_id = {$id}"));
    $appointment = Appointment::find_by_sql("SELECT *  FROM `tbl_appointments` WHERE pet_id = $id and date >= '$date' ORDER BY date DESC");
    //die();


    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointments history list retrived successfully.';
        $appointmentData = array();



        foreach ($appointment as $key => $value) {
            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $pet1 = Appointment::find('all',array("conditions" => "pet_id = '{$value->pet->pet_id}'"));

            $backup_contact=[];
            $contact_check=Contact_backup::find(array("conditions"=>"client_id={$value->client->client_id} AND pet_id={$value->pet->pet_id}"));
            if($contact_check != NULL)
            {
                $backup_contact=array(
                    'name' => $contact_check->name,
                    'address' => $contact_check->address,
                    'number' => $contact_check->contact_number,
                );
            }else{
                $backup_contact= new stdClass();
            }
            $appointmentData[] = array(
                'appointment_id' => $value->appointment_id,
                'status' => $value->status,
                'company_detail'=> array(
                    'company_id' => $value->company->company_id,
                    'company_name' => $value->company->company_name,
                    'emailid' => $value->company->emailid,
                    'contact_number' => $value->company->contact_number,
                    'company_image' => $value->company->company_image != null ? COMPANY_PIC_PATH .$value->company->company_image :NULL,
                    'website' => $value->company->website,
                    'address'=>$value->company->address,
                    'about' => $value->company->about
                ),
                'client_detail'=> array(
                    'client_id' => $value->client->client_id,
                    'firstname' => $value->client->firstname,
                    'lastname' => $value->client->lastname,
                    'emailid'=>$value->client->emailid,
                    'profile_image'=> $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
                    'contact'=> $value->client->contact_number,
                    'address'=>$value->client->client_address,
                    'client_notes'=>$value->client->client_notes
                ),
                'service_name'=> $service->service_name,
                'service_id' => $service->service_id,
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'message' => $value->message,
                'date' => date('d-m-Y',strtotime($value->date)),
                'pet_detail' => array(
                    'pet_id' => $value->pet->pet_id,
                    'pet_name' => count($pet1)>1 ? $value->pet->pet_name.' '.$value->client->lastname:$value->pet->pet_name,
                    'pet_birth' => $value->pet->pet_birth,
                    
                    'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                    'pet_age' => $value->pet->age,
                    'gender' => $value->pet->gender,
                    'pet_type' => $value->pet->pet_type,
                    'breed' => $value->pet->breed,
                    'neutered' => $value->pet->neutered,
                    'spayed' => $value->pet->spayed,
                    'injuries' => $value->pet->injuries,
                    'medical_detail' => $value->pet->medical_detail,
                    'pet_notes' => $value->pet->pet_notes,
                    'backupcontact' =>$backup_contact
                ),
            );
        }

        $response['data'] = $appointmentData;
    }
//die;
    echoResponse(200, $response);
});


/*
 * Appointment history accoridng to pet id
 */
$app->get('/:id/:limit/futurebookhistorypet', function($id,$limit) use ($app) {


    $response['error_code'] = 1;
    $response['message'] = 'No future appointments found.';
    $response['status'] = false;

    $date=date('Y-m-d');

//$appointment = Appointment::find('all', array('conditions' => "pet_id = {$id}"));
    $appointment = Appointment::find_by_sql("SELECT *  FROM `tbl_appointments` WHERE pet_id = $id and date > '$date' ORDER BY date ASC LIMIT $limit");
    //die();


    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointments history list retrived successfully.';
        $appointmentData = array();



        foreach ($appointment as $key => $value) {
            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $pet1 = Appointment::find('all',array("conditions" => "pet_id = '{$value->pet->pet_id}'"));

            $appointmentData[] = array(
                'appointment_id' => $value->appointment_id,
                'status' => $value->status,
                'company_detail'=> array(
                    'company_id' => $value->company->company_id,
                    'company_name' => $value->company->company_name,
                    'emailid' => $value->company->emailid,
                    'contact_number' => $value->company->contact_number,
                    'company_image' => $value->company->company_image != null ? COMPANY_PIC_PATH .$value->company->company_image :NULL,
                    'website' => $value->company->website,
                    'address'=>$value->company->address,
                    'about' => $value->company->about
                ),
                'client_detail'=> array(
                    'client_id' => $value->client->client_id,
                    'firstname' => $value->client->firstname,
                    'lastname' => $value->client->lastname,
                    'emailid'=>$value->client->emailid,
                    'profile_image'=> $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
                    'contact'=> $value->client->contact_number,
                    'address'=>$value->client->client_address,
                    'client_notes'=>$value->client->client_notes
                ),
                'service_name'=> $service->service_name,
                'service_id' => $service->service_id,
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'message' => $value->message,
                'date' => date('d-m-Y',strtotime($value->date)),
                'pet_detail' => array(
                    'pet_id' => $value->pet->pet_id,
                    'pet_name' => count($pet1)>1 ? $value->pet->pet_name.' '.$value->client->lastname:$value->pet->pet_name,
                    'pet_birth' => $value->pet->pet_birth,
                    'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                    'pet_age' => $value->pet->age,
                    'gender' => $value->pet->gender,
                    'pet_type' => $value->pet->pet_type,
                    'breed' => $value->pet->breed,
                    'neutered' => $value->pet->neutered,
                    'spayed' => $value->pet->spayed,
                    'injuries' => $value->pet->injuries,
                    'medical_detail' => $value->pet->medical_detail,
                    'pet_notes' => $value->pet->pet_notes
                ),
            );
        }

        $response['data'] = $appointmentData;
    }
//die;
    echoResponse(200, $response);
});



/*
 * Code for simple pagination
 */
$app->get('/pagination(/:page)', function($page = 0) use ($app) {

    $response['error_code'] = 1;
    $response['message'] = 'No services found';
    $response['status'] = false;

    // Total number of record
    $total = Company::find('all');
    // limit for pagination
    $limit = 5;
    // Number of total page according to total record
    $total_page = round(count($total) / $limit);

    $count = count($total);

    // offset for finding perticular length record
    if (ctype_digit($page)) {
        $offset = $limit * ($page - 1);
    } else if ($page == 'last') {
        $offset = ($limit * ( $total_page)) - $limit;
    } else {
        $offset = 0;
    }

//print(count($total)/$total_page);
//die;
// gettting lastpage
    $lastpage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
    $companies = Company::find('all', array('limit' => $limit, 'offset' => $offset));
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
            //'client_id' => $company->client_id,
            // 'status' => $company->status
            'pagination' => [
                'needed' => $count > $limit,
                'count' => $count,
                'page' => $page,
                'next' => $page + 1 > $lastpage ? NULL : $page + 1,
                'previous' => $page - 1 <= 0 ? NULL : $page - 1,
                'lastpage' => $lastpage,
                'limit' => $limit,
            ],
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

$app->post('/priceadd', function() use ($app) {

    verifyFields(array('service_id', 'company_id','full_hour_price', 'half_hour_price', 'additional_hours_price', 'additional_visits_price','price_per_walk','additional_pets','payment_option'));

    $company_id = $app->request->post('company_id');
    $service_id = $app->request->post('service_id');
    $full_hour_price = $app->request->post('full_hour_price');
    $half_hour_price = $app->request->post('half_hour_price');
    $additional_hours_price = $app->request->post('additional_hours_price');
    $additional_visits_price = $app->request->post('additional_visits_price');
    $price_per_walk = $app->request->post('price_per_walk');
    $additional_pets = $app->request->post('additional_pets');
    $payment_option= $app->request->post('payment_option');


    $priceCheck = Price::find(array("conditions" => "company_id = {$company_id} AND service_id = {$service_id} AND p_flag = '0'"));

    if (count($priceCheck) > 0) {
        $priceCheck->company_id = $company_id;
        $priceCheck->service_id = $service_id;
        $priceCheck->full_hour_price = $full_hour_price;
        $priceCheck->half_hour_price = $half_hour_price;
        $priceCheck->additional_hours_price = $additional_hours_price;
        $priceCheck->additional_visits_price = $additional_visits_price;
        $priceCheck->price_per_walk = $price_per_walk;
        $priceCheck->additional_pets = $additional_pets;
        $priceCheck->payment_option = $payment_option;
        $priceCheck->p_flag = 0;
        $priceCheck->save();
        $priceCheck->price_id = (int) $priceCheck->price_id;

        if ($priceCheck->price_id > 0) {

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Price Successfully updated.';
            $response['data'] = array(
                'price_id' => $priceCheck->price_id,
                'service_id' => $priceCheck->service_id,
                'full_hour_price' => $priceCheck->full_hour_price,
                'half_hour_price' => $priceCheck->half_hour_price,
                'additional_hours_price' => $priceCheck->additional_hours_price,
                'additional_visits_price' => $priceCheck->additional_visits_price,
                'price_per_walk' => $priceCheck->price_per_walk,
                'additional_pets' => $priceCheck->additional_pets,
                'payment_option' => $priceCheck->payment_option,
            );
        }

        echoResponse(200, $response);
    } else {


        Price::transaction(function() use($app, $service_id, $company_id,$full_hour_price, $half_hour_price, $additional_hours_price, $additional_visits_price,$price_per_walk,$additional_pets,$payment_option) {
            $price = new Price();
            $price->company_id = $company_id;
            $price->service_id = $service_id;
            $price->full_hour_price = $full_hour_price;
            $price->half_hour_price = $half_hour_price;
            $price->additional_hours_price = $additional_hours_price;
            $price->additional_visits_price = $additional_visits_price;
            $price->price_per_walk = $price_per_walk;
            $price->additional_pets = $additional_pets;
            $price->payment_option = $payment_option;
            $price->p_flag = 0;
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
                    'company_id' => $price->company_id,
                    'service_id' => $price->service_id,
                    'full_hour_price' => $full_hour_price,
                    'half_hour_price' => $half_hour_price,
                    'additional_hours_price' => $additional_hours_price,
                    'additional_visits_price' => $additional_visits_price,
                    'price_per_walk' => $price_per_walk,
                    'additional_pets' => $additional_pets,
                    'payment_option' => $payment_option,
                );
            }


            echoResponse(200, $response);
        });
    }
});


$app->post('/priceaddnew', function() use ($app) {

    verifyFields(array('company_id','service_id', 'full_hour_price', 'full_day_price', 'half_hour_price', 'additional_hours_price', 'additional_visits_price','price_per_walk','additional_pets','payment_option'));

    $company_id = $app->request->post('company_id');
    $service_id = $app->request->post('service_id');
    $full_hour_price = $app->request->post('full_hour_price');
    $full_day_price = $app->request->post('full_day_price');

    $half_hour_price = $app->request->post('half_hour_price');
    $additional_hours_price = $app->request->post('additional_hours_price');
    $additional_visits_price = $app->request->post('additional_visits_price');
    $price_per_walk = $app->request->post('price_per_walk');
    $additional_pets = $app->request->post('additional_pets');
    $payment_option= $app->request->post('payment_option');


    $priceCheck = Pricenew::find(array("conditions" => "company_id = {$company_id} AND service_id = {$service_id} AND p_flag = '0'"));

    if (count($priceCheck) > 0) {
        $priceCheck->company_id = $company_id;
        $priceCheck->service_id = $service_id;
        $priceCheck->full_hour_price = $full_hour_price;
        $priceCheck->full_day_price = $full_day_price;

        $priceCheck->half_hour_price = $half_hour_price;
        $priceCheck->additional_hours_price = $additional_hours_price;
        $priceCheck->additional_visits_price = $additional_visits_price;
        $priceCheck->price_per_walk = $price_per_walk;
        $priceCheck->additional_pets = $additional_pets;
        $priceCheck->payment_option = $payment_option;
        $priceCheck->p_flag = 0;
        $priceCheck->save();
        $priceCheck->price_id = (int) $priceCheck->price_id;

        if ($priceCheck->price_id > 0) {

            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Price Successfully updated.';
            $response['data'] = array(
                'price_id' => $priceCheck->price_id,
                'company_id' => $priceCheck->company_id,
                'service_id' => $priceCheck->service_id,
                'full_hour_price' => $priceCheck->full_hour_price,
                'full_day_price' => $priceCheck->full_day_price,

                'half_hour_price' => $priceCheck->half_hour_price,
                'additional_hours_price' => $priceCheck->additional_hours_price,
                'additional_visits_price' => $priceCheck->additional_visits_price,
                'price_per_walk' => $priceCheck->price_per_walk,
                'additional_pets' => $priceCheck->additional_pets,
                'payment_option' => $priceCheck->payment_option,
            );
        }

        echoResponse(200, $response);
    } else {


        Pricenew::transaction(function() use($app, $company_id, $client_id, $pet_id, $service_id, $company_id,$full_hour_price, $full_day_price, $half_hour_price, $additional_hours_price, $additional_visits_price,$price_per_walk,$additional_pets,$payment_option) {
            $price = new Pricenew();
            $price->company_id = $company_id;
            $price->client_id = NULL;
            $price->pet_id = NULL;
            $price->service_id = $service_id;
            $price->full_hour_price = $full_hour_price;
            $price->full_day_price = $full_day_price;

            $price->half_hour_price = $half_hour_price;
            $price->additional_hours_price = $additional_hours_price;
            $price->additional_visits_price = $additional_visits_price;
            $price->price_per_walk = $price_per_walk;
            $price->additional_pets = $additional_pets;
            $price->payment_option = $payment_option;
            $price->p_flag = 0;
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
                    'company_id' => $price->company_id,
                    'service_id' => $price->service_id,
                    'full_hour_price' => $full_hour_price,
                    'full_day_price' => $full_day_price,

                    'half_hour_price' => $half_hour_price,
                    'additional_hours_price' => $additional_hours_price,
                    'additional_visits_price' => $additional_visits_price,
                    'price_per_walk' => $price_per_walk,
                    'additional_pets' => $additional_pets,
                    'payment_option' => $payment_option,
                );
            }


            echoResponse(200, $response);
        });
    }
});



/*
 * Payment
 */
$app->post('/payment', function() use($app) {

    verifyFields(array('appointment_id', 'transaction_id'));


    $appointment_id = $app->request->post('appointment_id');
    $transaction_id = $app->request->post('transaction_id');
    // $company_id = $app->request->post('compnay_id');

    $appointment = Appointment::find($appointment_id);
    $company_id = $appointment->company_id;
    $client_id = $appointment->client_id;
    // $transaction_id = 'ch_1BFzUfHNsOwHydTvFGOMUbPp';
    //$a = PayMent();
    if ($appointment) {
        $appointment->status = 'assign staff';
        $appointment->save();
        //    Payment::transaction(function() use($app, $appointment_id, $company_id, $client_id, $transaction_id) {
        $payment = new Payment();
        $payment->company_id = $company_id;
        $payment->client_id = $client_id;
        $payment->transaction_id = $transaction_id;
        $payment->appointment_id = $appointment_id;

        $payment->save();
        $payment->id = (int) $payment->id;


        if ($payment->id > 0) {
            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Payment done!';
        } else {
            $response["error_code"] = 1;
            $response["status"] = false;
            $response["message"] = "Payment not stored!";
        }
        //  });
//echo $appointment->client->client_id;
//die;
        $appointmentData = [];
        $pet1 = Appointment::find('all',array("conditions" => "pet_id = '{$appointment->pet->pet_id}'"));
        $service = Service::find(array('conditions' => "service_id = {$appointment->service_id}"));
        $serviceName = empty($service) ? NULL : $service->service_name;
        $ownerDetail = array(
            'client_id' => $appointment->client->client_id,
            'firstname' => $appointment->client->firstname,
            'lastname' => $appointment->client->lastname,
            'emailid' => $appointment->client->emailid,
            'profile_image' => $appointment->client->profile_image != NULL ? USER_PIC_URL_PATH . $appointment->client->profile_image : NULL,
            'contact_number' => $appointment->client->contact_number,
            'client_address' => $appointment->client->client_address,
            'client_notes' => $appointment->client->client_notes,
            'player_id' => $appointment->client->player_id,
        );

        $appointmentData[] = array(
            'appointment_id' => $appointment->appointment_id,
            'company_id' => $appointment->company_id,
            'owner_detail' => $ownerDetail,
            'service_id' => $appointment->service_id,
            'service_name' => $serviceName,
            'date' => $appointment->date,
            'visits' => $appointment->visits,
            'visit_hours' => $appointment->visit_hours,
            'status' => $appointment->status,
            'pet_detail' => array(
                'pet_id' => $appointment->pet->pet_id,
                'pet_name' => count($pet1)>1? $appointment->pet->pet_name.' '.$appointment->client->lastname:$appointment->pet->pet_name,
                'pet_birth' => $appointment->pet->pet_birth,
                'pet_image' => $appointment->pet->pet_image != NULL ? PET_PIC_PATH . $appointment->pet->pet_image : NULL,
                'pet_age' => $appointment->pet->age,
                'gender' => $appointment->pet->gender,
                'pet_type' => $appointment->pet->pet_type,
                'breed' => $appointment->pet->breed,
                'neutered' => $appointment->pet->neutered,
                'spayed' => $appointment->pet->spayed,
                'injuries' => $appointment->pet->injuries,
                'medical_detail' => $appointment->pet->medical_detail,
                'pet_notes' => $appointment->pet->pet_notes,
                'latitude' => $appointment->pet->latitude,
                'longitude' => $appointment->pet->longitude,
            ),
            'message' => $appointment->message,
        );

        $response['data'] = $appointmentData;
    } else {
        $response["error_code"] = 1;
        $response["status"] = false;
        $response["message"] = "appointment not exist!";
    }
    echoResponse(200, $response);
});

/*
 * Shwoing price as per service according to service
 */

$app->get('/:id/price', function($id) use($app) {
    $company_id = $id;

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];

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

            $price = Price::find(array('conditions' => "company_id = {$id} AND service_id = {$value->service_id}"));
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
            }else{

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

                $price = new Price();
                $price->company_id = $company_id;
                $price->client_id = NULL;
                $price->service_id = $value->service_id;
                $price->full_hour_price = 0;
                $price->half_hour_price = 0;
                $price->additional_hours_price = 0;
                $price->additional_visits_price = 0;
                $price->price_per_walk = 0;
                $price->additional_pets = 0;
                $price->payment_option = 0;
                $price->save();
                $price->price_id = (int) $price->price_id;
            }
        }
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Price Successfully Retrive.';
        $response['data'] = $ServicePrice;
    }

    echoResponse(200, $response);
});

/*
 * Notification settings
 */

$app->post('/notification/:type/:id', function($type, $id) use($app) {

    verifyFields(array('status'));

    // $states = $app->request->post('state');
    $status = $app->request->post('status');

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];
    $notificationdata = [];
    if ($type == 'client') {

        $states = array('accepted', 'rejected', 'confirm');
    } else {
        $states = array('created', 'onpayment', 'reschedule');
    }
    $s = count($status);


    for ($i = 0; $i < $s; $i++) {
        $notificationCheck = Notification::find(array("conditions" => "id = {$id} AND type = '{$type}' AND state = '{$states[$i]}'"));
        if (count($notificationCheck) > 0) {
            $notificationCheck->id = $id;
            $notificationCheck->type = $type;
            $notificationCheck->state = $states[$i];
            $notificationCheck->is_active = $status[$i];
            $notificationCheck->save();
            $notificationdata[] = array(
                'notification_id' => $notificationCheck->notification_id,
                'id' => $notificationCheck->id,
                'type' => $notificationCheck->type,
                'state' => $notificationCheck->state,
                'is_active' => $notificationCheck->is_active,
            );
        } else {
            $notification = new Notification();
            $notification->id = $id;
            $notification->type = $type;
            $notification->state = $states[$i];
            $notification->is_active = $status[$i];
            $notification->save();
            $notificationdata[] = array(
                'notification_id' => $notification->notification_id,
                'id' => $notification->id,
                'type' => $notification->type,
                'state' => $notification->state,
                'is_active' => $notification->is_active,
            );
        }
    }

    /*    // $notificationCheck->notification_id = (int) $notificationCheck->notification_id;


      for ($i = 0; $i < $s; $i++) {
      $notification = new Notification();
      $notification->id = $id;
      $notification->type = $type;
      $notification->state = $states[$i];
      $notification->is_active = $status[$i];
      $notification->save();
      $notificationdata[] = array(
      'notification_id' => $notification->notification_id,
      'id' => $notification->id,
      'type' => $notification->type,
      'state' => $notification->state,
      'is_active' => $notification->is_active,
      );
      } */
    //$notification->notification_id = (int) $notification->notification_id;
    //$notificationdata = $notification->to_array();
    // }


    $response['error_code'] = 0;
    $response['status'] = true;
    $response['message'] = 'Successfully Updated.';
    $response['data'] = $notificationdata;
    echoResponse(200, $response);
});

/*
 * Notification settings displaying
 */

$app->get('/notification/:type/:id', function($type, $id) use($app) {

    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];
    $notificationdata = [];

    $notificationCheck = Notification::find('all', array("conditions" => "id = {$id} AND type = '{$type}' "));


    if (count($notificationCheck) > 0) {
        foreach ($notificationCheck as $key => $value) {

            $notificationdata[] = array(
                'notification_id' => $value->notification_id,
                'id' => $value->id,
                'type' => $value->type,
                'state' => $value->state,
                'is_active' => $value->is_active,
            );
        }
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Successfully Retrive.';
        $response['data'] = $notificationdata;
    } else {
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'No entry available.';
        $response['data'] = $notificationdata;
    }

    echoResponse(200, $response);
});



/*
 * Appoinment Cancle new
 */

$app->get("/appointment/:id/canclenew", function($id,$manual=null) use ($app) {
//echo $id;
//die;

    $exist = Appointment::exists($id);

    if ($exist) {
        //$appoinmnet = Appointment::find($id);

        if($manual != NULL)
        {
            $condition = "appointment_id = $id AND created_by = 'company'";
        }
        else
        {
            $condition = "appointment_id = $id";
        }

        $appointment=Appointment::find(array("conditions" => "{$condition}"));

        $company_id=$appointment->company_id;
        $client_id=$appointment->client_id;
        $service_id=$appointment->service_id;
        $staff_id = $appointment->staff_id;
        $date = date('Y-m-d',strtotime($appointment->date));
        $visits =$appointment->visits;
        $visit_hours = $appointment->visit_hours;
        $price = $appointment->price;
        $status = $appointment->status;
        $acknowledge = $appointment->acknowledge;
        $accepted = $appointment->accepted;
        $complete = $appointment->complete;
        $pet_id = $appointment->pet_id;
        $message = $appointment->message;
        $created_by = $appointment->created_by;
        $created_at = $appointment->created_at;


        $credit = Credits1::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id}"));

        $remain = $credit->remaining;

        $credit->remaining=$remain + $price;
        $credit->save();

        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
        $pet_name = $pet->pet_name;
        $log = new Log();
        $log->company_id = $company_id;
        $log->client_id = $client_id;
        $log->pet_name = $pet_name;
        $log->date_of_transaction = date('Y-m-d H:i:s');
        $log->l_status = "Cancelled";
        $log->amount = isset($price)?$price:0;
        $log->l_flag = "Added";
        $log->save();
        $log->log_id = (int) $log->log_id;

        $app_cancel = new Appointment_cancel();
        $app_cancel->company_id = $company_id;
        $app_cancel->client_id = $client_id;
        $app_cancel->service_id = $service_id;
        $app_cancel->staff_id = $staff_id;
        $app_cancel->date  = $date;
        $app_cancel->visits = $visits;
        $app_cancel->visit_hours = $visit_hours;
        $app_cancel->price = $price;
        $app_cancel->status = $status;
        $app_cancel->acknowledge = $acknowledge;
        $app_cancel->accepted = $accepted;
        $app_cancel->complete  = $complete;
        $app_cancel->pet_id = $pet_id;
        $app_cancel->message = $message;
        $app_cancel->created_by = $created_by;
        $app_cancel->created_at = $created_at;
        $app_cancel->save();
        $app_cancel->appointment_id = (int) $id;


        $appointment->delete();
        $response['error_code'] = 0;
        $response['message'] = 'Appointment cancled successfully!';
        $response['status'] = true;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No Appoinment found';
        $response['status'] = false;
    }

    echoResponse(200, $response);
});


/*
 * Appoinment Cancle
 */

$app->get("/appointment/:id/cancle", function($id,$manual=null) use ($app) {
//echo $id;
//die;

    $exist = Appointment::exists($id);

    if ($exist) {
        //$appoinmnet = Appointment::find($id);

        if($manual != NULL)
        {
            $condition = "appointment_id = $id AND created_by = 'company'";
        }
        else
        {
            $condition = "appointment_id = $id";
        }

        $appointment=Appointment::find(array("conditions" => "{$condition}"));

        $company_id=$appointment->company_id;
        $client_id=$appointment->client_id;
        $service_id=$appointment->service_id;
        $staff_id = $appointment->staff_id;
        $date = date('Y-m-d',strtotime($appointment->date));
        $visits =$appointment->visits;
        $visit_hours = $appointment->visit_hours;
        $price = $appointment->price;
        $status = $appointment->status;
        //$acknowledge = $appointment->acknowledge;
        $accepted = $appointment->accepted;
        $completed = $appointment->completed;
        $pet_id = $appointment->pet_id;
        $message = $appointment->message;
        $created_by = $appointment->created_by;
        $created_at = $appointment->created_at;

        $credit = Credits::find(array("conditions"=>"company_id = {$company_id} AND client_id = {$client_id} AND service_id = {$service_id}"));

        $remain = $credit->remaining;

        $credit->remaining=$remain + $price;
        if($credit->remaining == 0)
        {
            $credit->paid_amount=0;
            $credit->credits=0;
            $credit->check_date=$date;
        }
        $credit->save();

        $pet=Pet::find(array("conditions" => "client_id={$client_id}"));
        $pet_name = $pet->pet_name;
        $log = new Log();
        $log->company_id = $company_id;
        $log->client_id = $client_id;
        $log->pet_name = $pet_name;
        $log->date_of_transaction = date('Y-m-d H:i:s');
        $log->l_status = "Cancelled";
        $log->amount = $price;
        $log->l_flag = "Added";
        $log->save();
        $log->log_id = (int) $log->log_id;

        $app_cancel = new Appointment_cancel();
        $app_cancel->company_id = $company_id;
        $app_cancel->client_id = $client_id;
        $app_cancel->service_id = $service_id;
        $app_cancel->staff_id = $staff_id;
        $app_cancel->date  = $date;
        $app_cancel->visits = $visits;
        $app_cancel->visit_hours = $visit_hours;
        $app_cancel->price = $price;
        $app_cancel->status = $status;
        //$app_cancel->acknowledge = $acknowledge;
        $app_cancel->accepted = $accepted;
        $app_cancel->completed  = $completed;
        $app_cancel->pet_id = $pet_id;
        $app_cancel->message = $message;
        $app_cancel->created_by = $created_by;
        $app_cancel->created_at = $created_at;
        $app_cancel->save();
        $app_cancel->appointment_id = (int) $id;

        $appointment->delete();
        $response['error_code'] = 0;
        $response['message'] = 'Appointment cancled successfully!';
        $response['status'] = true;
    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No Appoinment found';
        $response['status'] = false;
    }

    echoResponse(200, $response);
});


$app->post('/:id/appointmenthistorybetween', function($id) use ($app) {

    verifyFields(array('start_date','end_date'));

    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;
    $start_date = date('Y-m-d', strtotime($app->request->post('start_date')));
    $end_date = date('Y-m-d', strtotime($app->request->post('end_date')));
//$date= date('Y-m-d');
    // if ($type == 'company') {
    //     $condition = "company_id";
    // } else {
    //     $condition = "client_id";
    // }

    // Getting Appoinment list according to given date to current date and id
    //$appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id} AND DATE(date) = '$date' "));
    $appointment = Appointment::find_by_sql("SELECT *  FROM `tbl_appointments` WHERE pet_id = $id AND DATE(date) BETWEEN '$start_date' AND '$end_date' ORDER BY date DESC");
//find('all', array('conditions' => "DATE(date) BETWEEN '$date' AND now()"));
//    var_dump($appointment);
//    die;

    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointments  list retrive successfully.';
        $appointmentData = [];

        foreach ($appointment as $key => $value) {

            $pet1 = Appointment::find('all',array("conditions" => "pet_id = '{$value->pet->pet_id}'"));
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
                'date' => date('d-m-Y',strtotime($value->date)),
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'status' => $value->status,
                'pet_detail' => array(
                    'pet_id' => $value->pet->pet_id,
                    'pet_name' => count($pet1)>1? $value->pet->pet_name.' '.$value->client->lastname:$value->pet->pet_name,
                    'pet_birth' => $value->pet->pet_birth,
                    'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                    'pet_age' => $value->pet->age,
                    'gender' => $value->pet->gender,
                    'pet_type' => $value->pet->pet_type,
                    'breed' => $value->pet->breed,
                    'neutered' => $value->pet->neutered,
                    'spayed' => $value->pet->spayed,
                    'injuries' => $value->pet->injuries,
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

$app->post('/:type/:id/appointmenthistory', function($type, $id) use ($app) {

    verifyFields(array('date'));

    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;
    $date = date('Y-m-d', strtotime($app->request->post('date')));
//$date= date('Y-m-d');
    if ($type == 'company') {
        $condition = "company_id";

        $contract=Contract::find('all',array("conditions" => "company_id = {$id}"));
        foreach ($contract as $ke => $val) {
            $clientid=$val->client_id;

            $pet= Pet::find('all',array("conditions" => "client_id = {$clientid}"));
            foreach ($pet as $key1 => $value1) {
                $petname[]=$value1->pet_name;

            }
        }
    } else {
        $condition = "client_id";

        $pet= Pet::find('all',array("conditions" => "client_id = {$id}"));
        foreach ($pet as $key1 => $value1) {
            $petname[]=$value1->pet_name;

        }

    }






    // Getting Appoinment list according to given date to current date and id
    //$appointment = Appointment::find('all', array('conditions' => "{$condition} = {$id} AND DATE(date) = '$date' "));
    $appointment = Appointment::find_by_sql("SELECT *  FROM `tbl_appointments` WHERE $condition = $id AND DATE(date) = '$date' ORDER BY date DESC");
//find('all', array('conditions' => "DATE(date) BETWEEN '$date' AND now()"));
//    var_dump($appointment);
//    die;

    if (count($appointment) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointments  list retrive successfully.';
        $appointmentData = [];

        foreach ($appointment as $key => $value) {
            //$petname[]=$value->pet->pet_name;

            $service = Service::find(array('conditions' => "service_id = {$value->service_id}"));
            $serviceName = empty($service) ? NULL : $service->service_name;
            $ownerDetail = array(
                'client_id' => $value->client->client_id,
                'firstname' => $value->client->firstname,
                'lastname' => $value->client->lastname,
                'emailid' => $value->client->emailid,
                'profile_image' => $value->client->profile_image != NULL ? USER_PIC_URL_PATH . $value->client->profile_image : NULL,
                'contact_number' => $value->client->contact_number,
                'profile_status' => $value->client->status==1?'active':'inactive',
                'client_address' => $value->client->client_address,
                'client_notes' => $value->client->client_notes,
                'player_id' => $value->client->player_id,
            );

            $staff_image = "";
            if($value->staff_id != NULL){
                $staff = Staff::find($value->staff_id);
                $staff_image = $staff->profile_image;
            }
            $haha='';
            $maincoin=[];
            for($i=0;$i<count($petname);$i++)
            {
                // echo $haha."</br>";

                // print_r($maincoin);


                if(strcasecmp($petname[$i],$value->pet->pet_name)===0)
                {
                    $maincoin[]='a';
                    $haha='';
                    // echo "=========================$pet_names[$i]        ".$value2->pet_name.'==========</br>';
                }
                if(count($maincoin)>1)
                {
                    $haha='abcd';
                    // echo "$pet_names[$i]        ".$value2->pet_name.'</br>';
                    break;
                }



            }


            if($haha=='abcd')
            {
                $petfull = $value->pet->pet_name." ".$value->client->lastname;
            }
            else
            {
                $petfull = $value->pet->pet_name;
            }
            $backup_contact=[];
            $contact_check=Contact_backup::find(array("conditions"=>"client_id={$value->client->client_id} AND pet_id={$value->pet->pet_id}"));
            if($contact_check != NULL)
            {
                $backup_contact=array(
                    'name' => $contact_check->name,
                    'address' => $contact_check->address,
                    'number' => $contact_check->contact_number,
                );
            }else{
                $backup_contact= new stdClass();
            }

            $petDetail=array(
                'pet_id' => $value->pet->pet_id,
                'pet_name' => $petfull,
                'pet_birth' => $value->pet->pet_birth,
                'pet_image' => $value->pet->pet_image != NULL ? PET_PIC_PATH . $value->pet->pet_image : NULL,
                'pet_age' => $value->pet->age,
                'gender' => $value->pet->gender,
                'pet_type' => $value->pet->pet_type,
                'breed' => $value->pet->breed,
                'neutered' => $value->pet->neutered,
                'spayed' => $value->pet->spayed,
                'injuries' => $value->pet->injuries,
                'medical_detail' => $value->pet->medical_detail,
                'pet_notes' => $value->pet->pet_notes,
                'latitude' => $value->pet->latitude,
                'longitude' => $value->pet->longitude,
                'backupcontact' =>$backup_contact
            );

            $companyDetail = array(
                'company_id' => $value->company->company_id,
                'account_id' => $value->company->account_id,
                'company_name' => $value->company->company_name,
                'emailid' => $value->company->emailid,
                'contact_number' => $value->company->contact_number,
                'company_image' => $value->company->company_image != NULL ? COMPANY_PIC_PATH . $value->company->company_image : NULL,
                'website' => $value->company->website,
                'address' => $value->company->address,
                'about' => $value->company->about,
            );

            $appointmentData[] = array(
                'appointment_id' => $value->appointment_id,
                'company_id' => $value->company_id,
                'company_detail' => $companyDetail,
                'owner_detail' => $ownerDetail,
                'service_id' => $value->service_id,
                'service_name' => $serviceName,
                'date' => $value->date,
                'visits' => $value->visits,
                'visit_hours' => $value->visit_hours,
                'status' => $value->status,
                'staff_image' => $staff_image,
                'pet_detail' => $petDetail,
                'message' => $value->message,
            );
        }
        // die;
        $response['data'] = $appointmentData;
    }
    echoResponse(200, $response);
});
/*
  AppoinmentHistory compare by Id-
*/
$app->post('/appointmenthistory/compare/:id', function($id) use ($app) {

    verifyFields(array('startdate', 'enddate', 'timeperiod'));

    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;
    $starttime = strtotime('01-' . $app->request->post('startdate'));
    $startdate = date('Y-m-d', strtotime('01-' . $app->request->post('startdate')));
    $enddate = date('Y-m-d', strtotime('01-' . $app->request->post('enddate')));
    $endtime = strtotime('01-' . $app->request->post('enddate'));
    $timeperiod = $app->request->post('timeperiod');
    $smonth = date('m', $starttime);
    $syear = date('Y', $starttime);
    $emonth = date('m', $endtime);
    $eyear = date('Y', $endtime);
    $new[] = array('service' => "",
        'Month_1' => [],
        'Month_2' => [],
        'Diff' => []);


    if ($timeperiod == 'daily') {

        $start = Appointment::find_by_sql("SELECT date as day , count(appointment_id) as total   FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $smonth AND YEAR(date) = $syear  GROUP BY date ");

        $end = Appointment::find_by_sql("SELECT date as day , count(appointment_id) as total   FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $emonth AND YEAR(date) = $eyear  GROUP BY date ");


    } else if ($timeperiod == 'weekly') {

        $start = Appointment::find_by_sql("SELECT WEEK(date) AS day, YEAR(date) as year , MONTH(date) as month ,count(appointment_id) as total , service_id as service_id  FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $smonth AND YEAR(date) = $syear  GROUP BY WEEK(date) ");

        $end = Appointment::find_by_sql("SELECT WEEK(date) AS day, YEAR(date) as year , MONTH(date) as month ,count(appointment_id) as total,service_id as service_id   FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $emonth AND YEAR(date) = $eyear  GROUP BY WEEK(date) ");


    } else if ($timeperiod == 'monthly') {

        $start = Appointment::find_by_sql("SELECT DATE_FORMAT(date, '%M %Y') AS day, count(appointment_id) as total    FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $smonth AND YEAR(date) = $syear  GROUP BY MONTH(date) ");


        $end = Appointment::find_by_sql("SELECT DATE_FORMAT(date, '%M %Y') AS day, count(appointment_id) as total    FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $emonth AND YEAR(date) = $eyear  GROUP BY MONTH(date) ");
    }
    $pet_detail = [];

    if ($smonth == $emonth) {
        $response['error_code'] = 1;
        $response['message'] = 'You can not compare same month';
        $response['status'] = false;
    } else {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointments comparision done successfully.';

        $appointmentData = [];
        $cmp_detail1 = [];
        $cmp_detail2 = [];
        $Stotalap = 0;
        $Etotalap = 0;
        $NStotal = 0;
        $NEtotal = 0;
        $SWtotal = 0;
        $EWtotal = 0;
        $old = '';
        $week = array('1', '2', '3', '4', '5', '6');
        $Stotalweeks = weeks_in_month($syear, $smonth, 0);
        $Etotalweeks = weeks_in_month($eyear, $emonth, 0);
        $week1t=array();
        $week2t=array();
        for ($w = 1; $w <= 6; $w++) {
            if ($w > $Stotalweeks) {
                $weeks1[$w] = array(
                    'week' => "No week",
                    'month' => $smonth,
                    'year' => $syear,
                    'total' => 0);
                $week1t[$w]=0;
            } else {

                $weeks1[$w] = array(
                    'week' => $w,
                    'month' => $smonth,
                    'year' => $syear,
                    'total' => 0);
                $week1t[$w]=0;
            }
            if ($w > $Etotalweeks) {
                $weeks2[$w] = array(
                    'week' => "No week",
                    'month' => $emonth,
                    'year' => $eyear,
                    'total' => 0);
                $week2t[$w]=0;
            } else {

                $weeks2[$w] = array(
                    'week' => $w,
                    'month' => $emonth,
                    'year' => $eyear,
                    'total' => 0);
                $week2t[$w]=0;
            }
        }


        $SmaxDay = date('t', $starttime);
        $EmaxDay = date('t', $endtime);

        for ($i = 1; $i <= 12; $i++) {

            $months1[$i] = array(
                'month' => $i,
                'year' => $syear,
                'total' => 0,
                'diff' => 0
            );
        }
        for ($l = 1; $l <= 12; $l++)
        {
            $months2[$l] = array(
                'month' => $l,
                'year' => $eyear,
                'total' => 0,
                'diff' => 0
            );
        }

        $sod=array();
        $eod=array();
        for ($d = 1; $d <= 31; $d++) {
            $time = mktime(12, 0, 0, $smonth, $d, $syear);
            $time = mktime(12, 0, 0, $smonth, $d, $syear);
            if ($d > $SmaxDay) {
                $list[$d] = array('date' => "No date",
                    'weekNO' => '',
                    'month' => $smonth,
                    'year' => $syear,
                    'total' => 0);
                $sod[$d]=0;
            } else {

                $list[$d] = array('date' => $d . '-' . $smonth . '-' . $syear,
                    'weekNO' => '',
                    'month' => $smonth,
                    'year' => $syear,
                    'total' => 0,
                    'diff' => 0);
                $sod[$d]=0;
            }
        }

        for ($d = 1; $d <= 31; $d++) {
            $time2 = mktime(12, 0, 0, $emonth, $d, $eyear);
            $time2 = mktime(12, 0, 0, $emonth, $d, $eyear);
            if ($d > $EmaxDay) {
                $list2[$d] = array('date' => "No date",
                    'weekNO' => '',
                    'month' => $smonth,
                    'year' => $syear,
                    'total' => 0);
                $eod[$d]=0;
            } else {

                $list2[$d] = array('date' => $d . '-' . $emonth . '-' . $eyear,
                    'weekNO' => '',
                    'month' => $smonth,
                    'year' => $syear,
                    'total' => 0,
                    'diff' => 0);
                $eod[$d]=0;
            }
        }
        $services = Appointment::find_by_sql("SELECT tbl_appointments.service_id as service_id, tbl_services.service_name as service_name FROM `tbl_appointments` join tbl_services on tbl_services.service_id = tbl_appointments.service_id WHERE company_id = $id AND date BETWEEN '$startdate' AND '$enddate'  group by service_id");

        foreach ($services as $s) {

            if ($timeperiod == "daily") {
                $start2 = Appointment::find_by_sql("SELECT WEEK(date) AS day, date as date, YEAR(date) as year , MONTH(date) as month ,count(appointment_id) as total , service_id as service_id  FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $smonth AND YEAR(date) = $syear AND service_id = $s->service_id  GROUP BY date ");
                foreach ($start2 as $st) {
                    $dm = date('d', strtotime($st->date));
                    $list[$dm]['date'] = date('d-m-Y', strtotime($st->date));
                    $list[$dm]['weekNO'] = weekOfMonth(strtotime($st->date));
                    $list[$dm]['month'] = $st->month;
                    $list[$dm]['year'] = $st->year;
                    $list[$dm]['total'] = $st->total;
                    $sod[$dm] = $list[$dm]['total'];
                    $cmp_detail1 = array_values($list);
                    $Stotalap +=$st->total;
                    $NStotal +=$st->total;
                    $SWtotal = $st->total;
                }
                $end2 = Appointment::find_by_sql("SELECT WEEK(date) AS day, date as date, YEAR(date) as year , MONTH(date) as month ,count(appointment_id) as total , service_id as service_id  FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $emonth AND YEAR(date) = $eyear AND service_id = $s->service_id  GROUP BY date ");


                foreach ($end2 as $ed) {

                    $dm = date('d', strtotime($ed->date));
                    $list2[$dm]['date'] = date('d-m-Y', strtotime($ed->date));
                    $list2[$dm]['weekNO'] = weekOfMonth(strtotime($ed->date));
                    $list2[$dm]['month'] = $ed->month;
                    $list2[$dm]['year'] = $ed->year;
                    $list2[$dm]['total'] = $ed->total;
                    $eod[$dm] = $list2[$dm]['total'];
                    $cmp_detail2 = array_values($list2);
                    $Etotalap +=$ed->total;
                    $EWtotal = $ed->total;
                    $NEtotal +=$ed->total;

                    $SWtotal = 0;

                }

                for ($d = 1; $d <= count($sod); $d++)
                {
                    $diff2[$d] = array('diff' => abs($sod[$d] - $eod[$d]), 'position' => $d);
                }



                $diff = abs($Stotalap - $Etotalap);
                $new[] = array(
                    'service' => $s->service_name,
                    'Month_1' => $cmp_detail1,
                    'Month_2' => $cmp_detail2,
                    'Diff' => array_values($diff2)
                );
            } else if ($timeperiod == "weekly") {
                $start2 = Appointment::find_by_sql("SELECT WEEK(date) AS day, date as date, YEAR(date) as year , MONTH(date) as month ,count(appointment_id) as total , service_id as service_id  FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $smonth AND YEAR(date) = $syear AND service_id = $s->service_id  GROUP BY WEEK(date) ");
                foreach ($start2 as $st) {

                    $wl = weekOfMonth(strtotime($st->date));
                    $weeks1[$wl]['week'] = weekOfMonth(strtotime($st->date));
                    $weeks1[$wl]['month'] = $st->month;
                    $weeks1[$wl]['year'] = $st->year;
                    $weeks1[$wl]['total'] = $st->total;

                    $cmp_detail1 = array_values($weeks1);

                    $SWtotal = $st->total;
                    $Stotalap +=$st->total;
                    $NStotal +=$st->total;
                    $week1t[$wl] = $weeks1[$wl]['total'];

                }
                $old = '';

                $end2 = Appointment::find_by_sql("SELECT WEEK(date) AS day, YEAR(date) as year ,date as date, MONTH(date) as month ,count(appointment_id) as total,service_id as service_id   FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $emonth AND YEAR(date) = $eyear AND service_id =$s->service_id  GROUP BY WEEK(date) ");
                foreach ($end2 as $ed) {


                    $wl = weekOfMonth(strtotime($ed->date));
                    $weeks2[$wl]['week'] = weekOfMonth(strtotime($ed->date));
                    $weeks2[$wl]['month'] = $ed->month;
                    $weeks2[$wl]['year'] = $ed->year;
                    $weeks2[$wl]['total'] = $ed->total;

                    $cmp_detail2 = array_values($weeks2);
                    $Etotalap +=$ed->total;
                    $EWtotal = $ed->total;
                    $NEtotal +=$ed->total;
                    $week2t[$wl] = $weeks2[$wl]['total'];

                    $SWtotal = 0;

                }

                for ($dl = 1; $dl <= count($week1t); $dl++) {
                    $diff2[$dl] = array('diff' => abs($week1t[$dl]-$week2t[$dl]), 'position' => $dl);

                }
                $new[] = array(
                    'service' => $s->service_name,
                    'Month_1' => $cmp_detail1,
                    'Month_2' => $cmp_detail2,
                    'Diff' => array_values($diff2),
                );
            } else if ($timeperiod == "monthly") {
                $start2 = Appointment::find_by_sql("SELECT WEEK(date) AS day, date as date, YEAR(date) as year , MONTH(date) as month ,count(appointment_id) as total , service_id as service_id  FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $smonth AND YEAR(date) = $syear AND service_id = $s->service_id  GROUP BY MONTH(date) ");
                foreach ($start2 as $st) {

                    $cmp_detail1[] = array(

                        'month' => date('F', strtotime($st->date)),
                        'year' => $st->year,
                        'total' => $st->total
                    );

                    $Stotalap +=$st->total;
                    $NStotal +=$st->total;
                }
                $end2 = Appointment::find_by_sql("SELECT WEEK(date) AS day, YEAR(date) as year ,date as date, MONTH(date) as month ,count(appointment_id) as total,service_id as service_id   FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $emonth AND YEAR(date) = $eyear AND service_id =$s->service_id  GROUP BY MONTH(date) ");
                foreach ($end2 as $ed) {
                    $cmp_detail2[] = array(
                        'month' => date('F', strtotime($ed->date)),
                        'year' => $ed->year,
                        'total' => $ed->total
                    );
                    $Etotalap +=$ed->total;
                    $NEtotal +=$ed->total;
                }

                //echo $NStotal;
                //echo $NEtotal;
                //echo "</br>";
                //echo abs($NStotal - $NEtotal);
                //die();
                $diff = abs($NStotal - $NEtotal);
                $new[] = array(
                    'service' => $s->service_name,
                    'Month_1' => $cmp_detail1,
                    'Month_2' => $cmp_detail2,
                    'Diff' => $diff
                );
            }
            $cmp_detail1 = [];
            $cmp_detail2 = [];
            $Stotalap = 0;
            $Etotalap = 0;
            $diff = 0;
            $diff2 = [];
        }

        $appointmentData[] = array(
            'Month_1' => date('M Y', $starttime),
            'Month_2' => date('M Y', $endtime),
            'Month_1_v' => $NStotal,
            'Month_2_v' => $NEtotal,
            'difference' => abs($NStotal - $NEtotal),
            'services' => $new
        );

        $response['data'] = $appointmentData;

    }
    echoResponse(200, $response);
});


/*
*Appointment history accoridng to type and id and return staff information
*/
$app->post('/:type/:id/reportstaffgraph', function($type, $id) use ($app) {


    //verifyFields(array('month');
    verifyFields(array('month','timeperiod'));

    $response['error_code'] = 1;
    $response['message'] = 'No graph data found.';
    $response['status'] = false;

    $starttime = strtotime('01-' . $app->request->post('month'));

    $startdate = date('Y-m-d', strtotime('01-' . $app->request->post('month')));
    $timeperiod = $app->request->post('timeperiod');

    $smonth = date('m', $starttime);
    $syear = date('Y', $starttime);




    //if($type == 'company')
    //{
    /* if($manual != NULL)
         {
            $condition = "company_id = $id AND created_by = 'company'";
         }
          else
          {*/
    $s_app = Appointment::find_by_sql("SELECT date,s.firstname,s.lastname,a.staff_id as s_id FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.company_id=$id and month(date)=$smonth and year(date)=$syear group by a.staff_id");

    $staff_name=array();


    foreach ($s_app as $snm)
    {

        if ($timeperiod == "daily")
        {
            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Successfully retrieve graph data.';
            $start2 = Appointment::find_by_sql("SELECT year(date) as years,month(date) as months,date as datess,s.firstname,s.lastname,count(a.appointment_id) as totale FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.staff_id=$snm->s_id and a.company_id=$id and month(date)=$smonth and year(date)=$syear group by date asc");

            $staff_detail=array();
            foreach ($start2 as $st)
            {
                $staff_detail[] = array(
                    'date' => $st->datess,
                    'month' => $st->months,
                    'year' =>  $st->years,
                    'total' => $st->totale,
                    'name' => $st->firstname." ".$st->lastname
                );
            }

            $staff_name[] = array(
                'name' => $snm->firstname." ".$snm->lastname,
                'staff_data' =>  $staff_detail
            );


        }

        else if ($timeperiod == "weekly")
        {
            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Successfully retrieve graph data.';
            $start2 = Appointment::find_by_sql("SELECT year(date) as years,month(date) as months,(SELECT WEEK(date, 3) -  WEEK(date - INTERVAL DAY(date)-1 DAY, 3) + 1) as datess,s.firstname,s.lastname,count(a.appointment_id) as totale FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.staff_id=$snm->s_id and a.company_id=$id and month(date)=$smonth and year(date)=$syear group by week(date) asc");

            $staff_detail=array();
            foreach ($start2 as $st)
            {
                $staff_detail[] = array(
                    'week' => $st->datess,
                    'month' => $st->months,
                    'year' =>  $st->years,
                    'total' => $st->totale,
                    'name' => $st->firstname." ".$st->lastname
                );
            }

            $staff_name[] = array(
                'name' => $snm->firstname." ".$snm->lastname,
                'staff_data' =>  $staff_detail
            );
        }

        else if ($timeperiod == "monthly")
        {
            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Successfully retrieve graph data.';
            $start2 = Appointment::find_by_sql("SELECT year(date) as years,month(date) as datess,s.firstname,s.lastname,count(a.appointment_id) as totale FROM tbl_appointments a,tbl_staffs s where a.staff_id=s.staff_id and a.staff_id=$snm->s_id and a.company_id=$id and month(date)=$smonth and year(date)=$syear group by month(date) asc");

            $staff_detail=array();
            foreach ($start2 as $st)
            {
                $staff_detail[] = array(
                    'month' => $st->datess,
                    'total' => $st->totale,
                    'year' => $st->years,
                    'name' => $st->firstname." ".$st->lastname
                );
            }

            $staff_name[] = array(
                'name' => $snm->firstname." ".$snm->lastname,
                'staff_data' =>  $staff_detail
            );
        }
    }
    $response['data'] = $staff_name;
    echoResponse(200, $response);
    //}
    //}

    /* else
    {
        $condition = "client_id = $id ";
    }*/

    /*
	if ($timeperiod == 'daily') {

        $start = Appointment::find_by_sql("SELECT date as day , count(appointment_id) as total  FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $smonth GROUP BY date ");

    } else if ($timeperiod == 'weekly') {

        $start = Appointment::find_by_sql("SELECT WEEK(date) AS day, YEAR(date) as year , MONTH(date) as month ,count(appointment_id) as total , staff_id as staff_id  FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $smonth GROUP BY WEEK(date) ");
    } else if ($timeperiod == 'monthly') {

        $start = Appointment::find_by_sql("SELECT DATE_FORMAT(date, '%M %Y') AS day, count(appointment_id) as total  FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $smonth GROUP BY MONTH(date) ");
	}*/

    /*if ($type == 'company') {

        verifyFields(array('timeperiod'));
        $timeperiod = $app->request->post('timeperiod');
        //$view = $app->request->post('view');
        //$staffs = $app->request->post('staffs');
//   $satff = $app->request->post('staff');
        if ($type == 'company')
		{
            GraphOfStaff($id, $timeperiod, $smonth,$syear);
            $app->stop();
        }
    }*/



});


/*
* Chart and graph for appointment
*/
function CahrtANDGraph($id, $timeperiod, $view, $startdate, $enddate, $pets) {
    $petid = implode(',', $pets);

    $response['error_code'] = 1;
    $response['message'] = 'No Appointments found';
    $response['status'] = false;
    /* if ($timeperiod == 'daily') {

        $appointment_compare = Appointment::find_by_sql("SELECT date,CONCAT(YEAR(date), '/', WEEK(date)) AS week_name,DATE_FORMAT(date,'%M') as month, DATE_FORMAT(date,'%Y') as year, WEEK(date) as W, COUNT(*) as ap FROM `tbl_appointments` WHERE company_id = $id AND date BETWEEN '$startdate' AND '$enddate'
AND pet_id IN ($petid)  GROUP BY  date , pet_id ORDER BY date  ASC ");
    } else if ($timeperiod == 'weekly') {
        $appointment_compare = Appointment::find_by_sql("SELECT date,CONCAT(YEAR(date), '/', WEEK(date)) AS week_name,DATE_FORMAT(date,'%M') as month, DATE_FORMAT(date,'%Y') as year, WEEK(date), COUNT(*) as total FROM `tbl_appointments` WHERE company_id = $id AND date BETWEEN '$startdate' AND '$enddate'
AND pet_id IN ($petid)  GROUP BY week_name  ORDER BY YEAR(DATE) ASC, WEEK(date) ASC");
    } else if ($timeperiod == 'monthly') {
        $appointment_compare = Appointment::find_by_sql("SELECT date,DATE_FORMAT(date, '%m') AS month, DATE_FORMAT(date,'%Y') as year, DATE_FORMAT(date,'%m %y') as ord,count(*) as total
    FROM `tbl_appointments` WHERE company_id = $id AND date BETWEEN '$startdate' AND '$enddate' AND pet_id IN ($petid)   GROUP BY ord ORDER BY year, month asc");
    }*/
    //echo $id . $startdate .$enddate . $timeperiod . $view;
    // die;
    $check = Appointment::find_by_sql("SELECT *  FROM `tbl_appointments` WHERE company_id = $id AND date BETWEEN '$startdate' AND '$enddate' AND pet_id IN ($petid)");
    $total_ap = 0;
    $pet_detail = [];
    $details = [];
    if (count($check) > 0) {

        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Appointments  list retrive successfully.';
        $appointmentData = [];

        /*foreach ($appointment_compare as $key => $value) {

            if ($timeperiod == 'weekly') {
                $nweek = date("W", strtotime($value->date));
                $nYEAR = date("Y", strtotime($value->date));
                $nmonth = date("m", strtotime($value->date));
                $pet_id = Appointment::find_by_sql("SELECT DISTINCT pet_id FROM `tbl_appointments` WHERE company_id = $id AND WEEK(date) = $nweek AND MONTH(date) = $nmonth AND YEAR(date) = $nYEAR AND pet_id in($petid)");
                foreach ($pet_id as $p) {
                    $pet = Pet::find(array('conditions' => "pet_id = $p->pet_id"));
                    $a = Appointment::find('all', array("conditions" => "pet_id = $p->pet_id AND company_id = $id AND WEEK(date) = $nweek AND YEAR(date) = $nYEAR"));
                    $pet_detail[] = array(
                        'pet_id' => $pet->pet_id,
                        'pet_name' => $pet->pet_name,
                        'pet_image' => $pet->pet_image != NULL ? PET_PIC_PATH . $pet->pet_image : NULL,
                        'pet_age' => $pet->age,
                        'medical_detail' => $pet->medical_detail,
                        'pet_notes' => $pet->pet_notes,
                        'latitude' => $pet->latitude,
                        'longitude' => $pet->longitude,
                        'total' => count($a),
                    );
                }


                $appointmentData[] = array(
                    'Week' => date("W", strtotime($value->date)),
                    'YEAR' => $value->year,
                    'MONTH' => $value->month,
                    'total' => $value->total,
                    'pet_detial' => array_values(array_map('unserialize', array_unique(array_map('serialize', $pet_detail)))),
                );
            } else if ($timeperiod == 'monthly') {
                $nmonth = date("m", strtotime($value->date));
                $nYEAR = date("Y", strtotime($value->date));
                $pet_id = Appointment::find_by_sql("SELECT  pet_id FROM `tbl_appointments` WHERE company_id = $id AND MONTH(date) = $nmonth AND YEAR(date) = $nYEAR AND pet_id in($petid)");
                if ($pet_id) {

                    foreach ($pet_id as $p) {
                        $a = Appointment::find('all', array("conditions" => "pet_id = $p->pet_id AND company_id = $id AND MONTH(date) = $nmonth AND YEAR(date) = $nYEAR "));
                        $pet = Pet::find(array('conditions' => "pet_id = $p->pet_id "));
                        $pet_detail[] = array(
                            'MONTH' => $value->month,
                            'YEAR' => $value->year,
                            'pet_id' => $pet->pet_id,
                            'pet_name' => $pet->pet_name,
                            'pet_image' => $pet->pet_image != NULL ? PET_PIC_PATH . $pet->pet_image : NULL,
                            'pet_age' => $pet->age,
                            'medical_detail' => $pet->medical_detail,
                            'pet_notes' => $pet->pet_notes,
                            'latitude' => $pet->latitude,
                            'longitude' => $pet->longitude,
                            'total' => count($a),
                        );
                    }

                    $appointmentData[] = array(
                        //   'total' => $value->total,
                        'pet_detial' => array_values(array_map('unserialize', array_unique(array_map('serialize', $pet_detail)))),
                    );
                    //$pet_detail = [];
                }
            } else if ($timeperiod == 'daily') {

                $nday = date("Y-m-d", strtotime($value->date));
                $nYEAR = date("Y", strtotime($value->date));

                $pet_id = Appointment::find_by_sql("SELECT  pet_id FROM `tbl_appointments` WHERE company_id = $id AND date = $value->date AND pet_id in($petid)");

                if ($pet_id) {

                    foreach ($pet_id as $p) {
                        $a = Appointment::find('all', array("conditions" => "pet_id = $p->pet_id AND company_id = $id AND date = '$nday' "));
                        $pet = Pet::find(array('conditions' => "pet_id = $p->pet_id"));
                        $pet_detail[] = array(
                            'pet_id' => $pet->pet_id,
                            'pet_name' => $pet->pet_name,
                            'pet_image' => $pet->pet_image != NULL ? PET_PIC_PATH . $pet->pet_image : NULL,
                            'pet_age' => $pet->age,
                            'medical_detail' => $pet->medical_detail,
                            'pet_notes' => $pet->pet_notes,
                            'latitude' => $pet->latitude,
                            'longitude' => $pet->longitude,
                            'total' => count($a),
                        );
                    }

                    $appointmentData[] = array(
                        'Day' => date("d-m-Y", strtotime($value->date)),
                        'YEAR' => $value->year,
                        //'total' => $value->total,
                        'pet_detial' => array_values(array_map('unserialize', array_unique(array_map('serialize', $pet_detail)))),
                    );
                    //$pet_detail = [];
                }
            } else {

                $appointmentData[] = array(
                    //   "WEEK" => $value->w,
                    // "MONTH" => $value->month,
                    "Year" => $value->year,
                    //"week_name" => $value->week_name,
                    "toatl_pets" => $value->ap,
                );
            }

            $pet_detail = [];
        }*/
////////////////////////////////////////////////////////////////////////////////////////

        $petsids = Pet::find("all", array("conditions" => array("pet_id in(?)", $pets)));
        foreach ($petsids as $index => $p) {
            if ($timeperiod == 'monthly') {

                $ap = Appointment::find_by_sql("SELECT date,DATE_FORMAT(date, '%m') AS month, DATE_FORMAT(date,'%Y') as year, DATE_FORMAT(date,'%m %y') as ord,count(*) as total
    FROM `tbl_appointments` WHERE company_id = $id AND date BETWEEN '$startdate' AND '$enddate' AND pet_id = $p->pet_id   GROUP BY ord ORDER BY year, month asc");

                foreach ($ap as $a) {
                    $details[] = array(
                        'month' => $a->month,
                        'year' => $a->year,
                        'pet_name' => $p->pet_name,
                        'total' => $a->total
                    );
                    $total_ap += $a->total;
                }
            } else if ($timeperiod == 'weekly') {
                $ap = Appointment::find_by_sql("SELECT date,CONCAT(YEAR(date), '/', WEEK(date)) AS week_name,DATE_FORMAT(date,'%M') as month, DATE_FORMAT(date,'%Y') as year, WEEK(date) as week, COUNT(*) as total FROM `tbl_appointments` WHERE company_id = $id AND date BETWEEN '$startdate' AND '$enddate' AND pet_id = $p->pet_id GROUP BY week_name  ORDER BY  WEEK(date), YEAR(DATE) ASC");

                foreach ($ap as $a) {
                    $details[] = array(
                        'week' => $a->week,
                        'weekNO' => weekOfMonth(strtotime($a->date)),
                        'month' => $a->month,
                        'year' => $a->year,
                        'pet_name' => $p->pet_name,
                        'total' => $a->total
                    );
                    $total_ap += $a->total;
                }

            } else if ($timeperiod == 'daily') {
                $ap = Appointment::find_by_sql("SELECT date,CONCAT(YEAR(date), '/', WEEK(date)) AS week_name,DATE_FORMAT(date,'%M') as month, DATE_FORMAT(date,'%Y') as year, WEEK(date) as W, COUNT(*) as total FROM `tbl_appointments` WHERE company_id = $id AND date BETWEEN '$startdate' AND '$enddate'
AND pet_id = $p->pet_id GROUP BY  date , pet_id ORDER BY date  ASC ");



                foreach ($ap as $a) {
                    $details[] = array(
                        'date' => date('d-m-Y',strtotime($a->date)),
                        'month' => $a->month,
                        'year' => $a->year,
                        'pet_name' => $p->pet_name,
                        'total' => $a->total
                    );
                    $total_ap += $a->total;
                }

            }

            $PETSNAME[] = array(
                'pet_name' => $p->pet_name,
                'total_appointment' => $total_ap,
                'pet_detail' => array_values(array_map('unserialize', array_unique(array_map('serialize', $details))))
            );
            $details = [];
            $total_ap = 0;
        }


        // $response['data'] = $appointmentData;

        $response['data'] = $PETSNAME;

        // $response['data'] = $appointmentData;
    }

    echoResponse(200, $response);
}

// these three API yet to push to staging 
// /client/bycontact
// /client/send_otp
// /client/verify_otp
$app->get('/client/bycontact',function() use($app)
{   
    $response =[];
    $contact = $app->request->get('contact');
    $client = Client::find(array('conditions' => "contact_number = '{$contact}'"));
    if($contact && $client){
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Client found.';
        $c = [];
        $c['firstname'] = $client->firstname;
        $c['client_id'] = $client->client_id;
        $c['company_id'] = $client->company_id;
        $response['data'] = $c;
    }else{
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Client not found.';
    }
    echoResponse(200, $response);
});
$app->post('/client/send_otp',function() use($app)
{
    $client_id = $app->request->post('client_id');
    $testing = $app->request->post('testing');
    $client = Client::find(array('conditions' => "client_id = '{$client_id}'")); 

    //client contact 
    $mobileNumber = $client->contact_number;// TODO: sanitize contact before sending SMS, remove space, +, leading zero etc..  
    //contact can not be: 
    // + 07700900000
    // + 0 0 09 00000
    // + 0 0(909) 09 00000
    // + 0 0(909)- (090) 0000
    //=========
    // testing contact : 07700900000
    // $mobileNumber = $testing ? "07700900000895" : $mobileNumber ;// remove this when 
    $otp = rand(111,999).rand(111,999);// need to store this otp in backend in db in any of the table for verification
    $message = "Your one time password for walk about apointment is :". $otp;
    $url = 'https://www.firetext.co.uk/api/sendsms?' . http_build_query([
        'apiKey' => 'UejZAosj6v58gNzHZTJ9y3fxf3K9AA',
        'to' => $mobileNumber,
        'from' => "WA-APT",
        'message' => $message
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $is_otp_sent = curl_exec($ch);
    $response =[];
    if($is_otp_sent=="0:1 SMS successfully queued" || $testing){
        $client->otp= md5($otp);// save only if delieverd
        $client->save();
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Otp sent to the contact provided please enter.';// we will remove this as this is for testing only we won't send otp
        // $response['message'] = 'Otp sent to the contact provided please enter.'.$otp;// we will remove this as this is for testing only we won't send otp
    }else{
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Otp not sent to the contact provided please try after some time.';
    }
    echoResponse(200, $response);
});
$app->post('/client/verify_otp',function() use($app)
{
    $otp = $app->request->post('otp');
    $client_id = $app->request->post('client_id');
    $client = Client::find(array('conditions' => "client_id = '{$client_id}'"));   
    if($client && md5($otp)==$client->otp){
        $client->otp = md5(rand(111,999));// update once verified as same otp can not verify again
        $client->save();
        $idss = $client->client_id;
        $pet_list = Pet::find_by_sql("SELECT * FROM `tbl_pets` where client_id=$idss order by pet_id");
        $pets = [];
        foreach ($pet_list as $key3 => $value3)
        {
            $pet = [];
            $pet['id'] = $value3->pet_id;
            $pet['name'] = $value3->pet_name;
            $pets[]=$pet;
        }
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Otp verified';
        $response['data'] = $pets;
    }else{
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'Otp not verified. please enter correct otp.';
    }
    echoResponse(200, $response);
});


$app->get('/:id/overview',function($id) use($app)
{
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

        foreach ($contract as $key => $value)
        {
            $flag = $value->client->company_id != NULL ? TRUE : FALSE;
            $idss=$value->client->client_id;
            $pet = Pet::find_by_sql("SELECT * FROM `tbl_pets` where client_id=$idss order by pet_id limit 1");
            foreach ($pet as $key3 => $value3)
            {
                $pet_names[] = $value3->pet_name;
            }

        }



        foreach ($contract as $key => $value)
        {
            $flag = $value->client->company_id != NULL ? TRUE : FALSE;
            $pet = Pet::find('all', array("conditions" => "client_id = {$value->client->client_id}"));


            $counti=[];
            $pet_detail = [];
            foreach ($pet as $key2 => $value2)
            {
                $counti[]='a';


                $acbc='';
                $firstValue = current($pet_names);
                foreach ($pet_names as $vals)
                {
                    if ($firstValue !== $vals)
                    {
                        $acbc=true;
                    }
                }
                $haha='';
                $maincoin=[];
                for($i=0;$i<count($pet_names);$i++)
                {
                    // echo $haha."</br>";

                    // print_r($maincoin);


                    if(strcasecmp($pet_names[$i],$value2->pet_name)===0)
                    {
                        $maincoin[]='a';
                        $haha='';
                        // echo "=========================$pet_names[$i]        ".$value2->pet_name.'==========</br>';
                    }
                    if(count($maincoin)>1)
                    {
                        $haha='abcd';
                        // echo "$pet_names[$i]        ".$value2->pet_name.'</br>';
                        break;
                    }



                }


                if(count($counti)==1 && $haha=='abcd')
                {
                    $petfull = $value2->pet_name." ".$value->client->lastname;
                }
                else
                {
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
                );

            }



            $client_id1=$value->client->client_id;

            /*For last Walk date*/
            $appoint_date = Appointment::find_by_sql("SELECT date FROM `tbl_appointments` where company_id = $id and client_id=$client_id1 ORDER BY appointment_id DESC LIMIT 1");
            foreach ($appoint_date as $ke => $val) {
                $a_date = date('d-m-Y',strtotime($val->date));
            }
            /*end last walk*/
            //=[];
            $client_id = $value->client_id;
            $credit = Credits::find('all',array("conditions"=>"company_id = {$id} AND client_id = {$client_id}"));
            $array = ["walking","sitting","pet_home_boarding","pet_transportation"];

            // for($i=0; $i<count($array); $i++)
            // {
            //    $array[$i]= $credit[$i];
            // }
            if($credit)
            {
                $remain=[];
                foreach ($credit as $key2 => $value2) {
                    $remain[]=$value2->remaining;
                    //print_r($remain);

                }
            }else{
                $remain=array("0" => "0",
                    "1" => "0",
                    "2" => "0",
                    "3" => "0"
                );
            }

            if(count($array) == count($remain))
            {
                $d=(object)array_combine($array, $remain);
            }



            // print_r($d);

            //print_r($d);
            /*For Last Transaction*/
            $log = Log::find_by_sql("SELECT * FROM `tbl_transaction_log` where company_id = $id AND client_id =$client_id ORDER BY log_id DESC LIMIT 1");
            if(count($log) > 0)
            {
                foreach ($log as $key1 => $value1) {

                    $log_id = $value1->log_id;
                    $company_id1 = $value1->company_id;
                    $client_id1 = $value1->client_id;
                    $pet_name5 = $value1->pet_name;
                    $date_of_transaction = date('d-m-Y',strtotime($value1->date_of_transaction));
                    $l_status = $value1->l_status;
                    $amount = $value1->amount;
                    $l_flag = $value1->l_flag;
                }

                $contractData[] = array(
                    'client_id' => $value->client->client_id,
                    'firstname' => $value->client->firstname,
                    'lastname' => $value->client->lastname,
                    'last_walk_date' => $a_date,
                    'log_id' => $log_id,
                    'company_id' => $company_id1,
                    'client_id' => $client_id1,
                    'pet_name' => $pet_name5,
                    'date_of_transaction' =>$date_of_transaction,
                    'l_status' => $l_status,
                    'amount' =>  $amount,
                    'l_flag' => $l_flag,
                    'remaining' => $d,
                    'pet_detail' => $pet_detail,
                );
            }
            else{
                $message = "No transaction";
                $contractData[] = array(
                    'client_id' => $value->client->client_id,
                    'firstname' => $value->client->firstname,
                    'lastname' => $value->client->lastname,
                    'last_walk_date' => $a_date,
                    'message' => $message,
                    'remaining' => $d,
                    'pet_detail' => $pet_detail,
                );
            }
            /*End Transaction*/

            // $contractData[] = array(
            //     'client_id' => $value->client->client_id,
            //     'firstname' => $value->client->firstname,
            //     'lastname' => $value->client->lastname,
            //     'last_walk_date' => $a_date,
            //     'log_id' => $log_id,
            //     'company_id' => $company_id1,
            //     'client_id' => $client_id1,
            //     'pet_name' => $pet_name5,
            //     'date_of_transaction' =>$date_of_transaction,
            //     'l_status' => $l_status,
            //     'amount' =>  $amount,
            //     'l_flag' => $l_flag,
            //     'pet_detail' => $pet_detail,
            // );

        }

        $response['data'] = $contractData;

    } else {
        $response['error_code'] = 1;
        $response['message'] = 'No client found.';
        $response['status'] = false;
        $response['data'] = [];
    }
    // die;

    echoResponse(200, $response);
});

$app->get('/:id/overviewnew',function($id) use ($app)
{
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
        $temp=[];
        $pet_detail = [];
        foreach ($contract as $key => $value) {
            $flag = $value->client->company_id != NULL ? TRUE : FALSE;
            $idss=$value->client->client_id;
            $pet = Pet::find_by_sql("SELECT * FROM `tbl_pets` where client_id=$idss order by pet_id ");
            foreach ($pet as $key3 => $value3)
            {
                $pet_names[] = $value3->pet_name;
            }



            $pet = Pet::find('all', array("conditions" => "client_id = {$value->client->client_id}"));


            $counti=[];

            //$backup_contact = [];
            foreach ($pet as $key2 => $value2)
            {
                $counti[]='a';

                $haha='';
                $maincoin=[];
                for($i=0;$i<count($pet_names);$i++)
                {

                    if(strcasecmp($pet_names[$i],$value2->pet_name)===0)
                    {
                        $maincoin[]='a';
                        $haha='';
                    }
                    if(count($maincoin)>1)
                    {
                        $haha='abcd';

                        break;
                    }

                }

                if($haha=='abcd')
                {
                    $petfull = $value2->pet_name." ".$value->client->lastname;
                }
                else
                {
                    $petfull = $value2->pet_name;
                }
                $a_date=NULL;

                $clientids=$value->client->client_id;
                /*For last Walk date*/
                $appoint_date = Appointment::find_by_sql("SELECT date FROM `tbl_appointments` where company_id = $id and client_id=$clientids and pet_id=$value2->pet_id ORDER BY appointment_id DESC LIMIT 1");
                foreach ($appoint_date as $ke => $val) {
                    $a_date = date('d-m-Y',strtotime($val->date));
                }

                /*end last walk*/
                //=[];
                //$client_id = $value->client_id;
                $credit = Credits::find('all',array("conditions"=>"company_id = {$id} AND client_id = {$clientids} AND pet_id={$value2->pet_id}"));
                $array = ["walking","sitting","pet_home_boarding","pet_transportation"];

                // for($i=0; $i<count($array); $i++)
                // {
                //    $array[$i]= $credit[$i];
                // }
                if($credit)
                {
                    $remain=[];
                    foreach ($credit as $value7) {
                        $remain[]=$value7->remaining;
                        //print_r($remain);

                    }
                }else{
                    $remain=array("0" => "0",
                        "1" => "0",
                        "2" => "0",
                        "3" => "0"
                    );
                }

                if(count($array) == count($remain))
                {
                    $d=(object)array_combine($array, $remain);
                }


                /*For Last Transaction*/
                $log = transactionlog::find_by_sql("SELECT * FROM `tbl_newtransaction_log` where company_id = $id AND client_id =$clientids AND pet_id=$value2->pet_id ORDER BY log_id DESC LIMIT 1");

                if(count($log) > 0)
                {
                    foreach ($log as $key1 => $lvalue1)
                    {

                        $date_of_transaction = date('d-m-Y',strtotime($lvalue1->date_of_transaction));
                        $type = $lvalue1->type;
                        $l_flag = $lvalue1->l_flag;
                    }

                }else{
                    $date_of_transaction=NULL;
                    $type = NULL;
                    $l_flag = NULL;
                }

                if($a_date == NULL)
                {
                    $message_a="No appointments";
                }else{
                    $message_a=NULL;
                }
                if($date_of_transaction == NULL)
                {
                    $message_t="No transaction";
                }else{
                    $message_t=NULL;
                }

                $contractData[] = array(
                    'company_id' => $value->company->company_id,
                    'client_id' => $value->client->client_id,
                    'pet_id' => $value2->pet_id,
                    'firstname' => $value->client->firstname,
                    'lastname' => $value->client->lastname,
                    'pet_name' => $petfull,
                    'pet_birth' => $value2->pet_birth,
                    'pet_image' => $value2->pet_image != NULL ? PET_PIC_PATH . $value2->pet_image : NULL,
                    'last_appointment_date' => $a_date,
                    'last_transaction_date' =>	$date_of_transaction,
                    'message_a' => $message_a,
                    'message_t' => $message_t,
                    'type' => $type,
                    'l_flag' => $l_flag,
                    'remaining' => $d
                );


                // array_push($temp,$contractData);

            }
        }//print_r($contractData);
        // die;
        //     $demo=[];
        // foreach ($temp as  $value6) {
        //     $demo=$value6;
        // }
        $response['data'] = $contractData;

    }else {
        $response['error_code'] = 1;
        $response['message'] = 'No client found.';
        $response['status'] = false;
        $response['data'] = [];
    }

    echoResponse(200, $response);
});

$app->post('/:id(/:client_id)/exelgeneration',function($id, $client_id=null) use($app)
{

    verifyFields(array('option'));

    //$client_id = $client_id;
    $pet_id = $app->request->post('pet_id');
    $option = $app->request->post('option');

    if($option == 'payment')
    {
        $log=[];

        if($client_id != null)
        {

            $log = log::find_by_sql("SELECT pet_name, date_of_transaction, l_status, amount, l_flag from tbl_transaction_log where company_id=$id and client_id=$client_id ");
        }else{

            $log = log::find_by_sql("SELECT pet_name, date_of_transaction, l_status, amount, l_flag from tbl_transaction_log where company_id=$id ");
        }

//echo "dddd";

        // $ = '';
        //  $columnHeader ="Pet Name". "\t" . "Date of Transaction" . "\t" . "Status" . "\t" . "Amount" . "\t" . "Flag" . "\t";
        //  $setData = '';
        //  $rowData = '';
        //This will become our top header row
        $headerRowArr = array(array('petname', 'date', 'status', 'amount', 'flag'));

        foreach ($log as $key => $value)
        {

            $date =date('d-m-Y',strtotime($value->date_of_transaction));

            //$val = '"' . $value->pet_name . '"' . "\t" . '"' . $date . '"' . "\t" . '"' . $value->l_status . '"' . "\t" . '"' . $value->amount . '"' . "\t" . '"' . $value->l_flag. '"' . "\n";
            //$dataArr=[];
            $dataArr[]=array($value->pet_name,$date,$value->l_status,$value->amount,$value->l_flag);

        }


        array_splice($dataArr,0,0,$headerRowArr);

        $excelDataArr = array();

        /*
 * The PHPExcel Library can designate a cell's location in a matrix as
 * (column, row) i.e. (0,1), (1,1), (2,1)...
 * e.g. A1 becomes (0,1), B1 becomes (1,1) and C1 becomes (2,1).. and so on..
 * Notice that the columns start from 0 and rows start from 1
 * So to achieve an array which will have keys matching this columns and rows format, we'll
 * apply the following logic in the foreach loop
 */
        $i = 1;
        foreach($dataArr as $key => $val1){

            foreach($val1 as $value){
                $excelDataArr[$i][] = $value;
            }
            $i++;
        }

        $objPHPExcel = new PHPExcel();

// Set properties
        $objPHPExcel->getProperties()->setCreator("pethub")
            ->setLastModifiedBy("etc")
            ->setTitle("Office 2007 XLSX Export Document")
            ->setSubject("Office 2007 XLSX Export Document")
            ->setDescription("Exported doc for Office 2007 XLSX, generated by PHPExcel.")
            ->setKeywords("office EXCEL 2007 PHPExcel XLSX php")
            ->setCategory("Exported file");
        $objPHPExcel->getActiveSheet()->setTitle('pethub Excel Export');

        foreach($excelDataArr as $row => $val){

            foreach($val as $column => $value){



                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $value);
            }
        }

//Style the first header row to be bold and have borders
        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
            )
        );


        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//$location = __DIR__.'/files/excel/'; //Make sure this location exists

        $objWriter->save('../files/excel/Payment/payment.xlsx');
        $path="Payment/payment.xlsx";
//echo 'File created: payment.xlsx';

        $response['error_code'] = 0;
        $response['message'] = 'File created.';
        $response['status'] = true;
        $response['data'] = array('path' => EXCEL_PIC_PATH.$path);

        echoResponse(200, $response);


    }
    elseif($option == 'booking' )
    {
        $appointment=[];
        if($client_id != NULL)
        {
            $appointment = Appointment::find('all',array("conditions" => "company_id=$id and client_id=$client_id "));
        }else{
            $appointment = Appointment::find('all',array("conditions" => "company_id=$id "));
        }

        $headerRowArr = array(array('Date','Client name', 'Company name','Visit', 'Visit hours', 'Service name', 'Message', 'Staff name'));

        foreach ($appointment as $key => $value)
        {

            $date =date('d-m-Y',strtotime($value->date));
            $visit = $value->visits;
            $visit_hours=$value->visit_hours;
            $message=$value->message;
            $company_name=$value->company->company_name;
            $client_name=$value->client->firstname;
            $service=Service::find_by_service_id($value->service_id);
            $service_name=$service->service_name;
            if($value->status == 'accepted')
            {
                $staff_name=$value->staff->firstname;


            }else{
                $staff_name='not assign';
            }

            //echo $staff_name.'</br>';

            $dataArr[]=array($date,$client_name,$company_name,$visit,$visit_hours,$service_name,$message,$staff_name);


        }

        array_splice($dataArr,0,0,$headerRowArr);

        $excelDataArr = array();


        $i = 1;
        foreach($dataArr as $key => $val1){

            foreach($val1 as $value){
                $excelDataArr[$i][] = $value;
            }
            $i++;
        }

        $objPHPExcel = new PHPExcel();

// Set properties
        $objPHPExcel->getProperties()->setCreator("pethub")
            ->setLastModifiedBy("etc")
            ->setTitle("Office 2007 XLSX Booking Document")
            ->setSubject("Office 2007 XLSX Booking Document")
            ->setDescription("Exported doc for Office 2007 XLSX, generated by PHPExcel.")
            ->setKeywords("office EXCEL 2007 PHPExcel XLSX php")
            ->setCategory("Exported file");
        $objPHPExcel->getActiveSheet()->setTitle('pethub Excel Export');

        foreach($excelDataArr as $row => $val){

            foreach($val as $column => $value){



                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $value);
            }
        }

//Style the first header row to be bold and have borders
        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
            )
        );


        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//$location = __DIR__.'/files/excel/'; //Make sure this location exists

        $objWriter->save('../files/excel/Booking/booking.xlsx');
        $path="Booking/booking.xlsx";

//echo 'File created: MyExcelSheet.xlsx';
        $response['error_code'] = 0;
        $response['message'] = 'File created.';
        $response['status'] = true;
        $response['data'] = array('path' => EXCEL_PIC_PATH.$path);

        echoResponse(200, $response);

    }elseif($option == 'cancel')
    {
        $appointment=[];
        if($client_id != NULL)
        {
            $appointment = Appointment_cancel::find('all',array("conditions" => "company_id=$id and client_id=$client_id "));
        }else{
            $appointment = Appointment_cancel::find('all',array("conditions" => "company_id=$id "));
        }

        $headerRowArr = array(array('Date', 'Client name', 'Company name', 'Visit', 'Visit hours', 'Service name', 'Message', 'Staff name'));
        if(count($appointment)>0)
        {
            foreach ($appointment as $key => $value)
            {

                $date =date('d-m-Y',strtotime($value->date));
                $visit = $value->visits;
                $visit_hours=$value->visit_hours;
                $message=$value->message;
                $company_name=$value->company->company_name;
                $client_name=$value->client->firstname;
                $service=Service::find_by_service_id($value->service_id);
                $service_name=$service->service_name;
                if($value->status == 'accepted')
                {
                    $staff_name=$value->staff->firstname;


                }else{
                    $staff_name='not assign';
                }

                //echo $staff_name.'</br>';

                $dataArr[]=array($date,$client_name,$company_name,$visit,$visit_hours,$service_name,$message,$staff_name);


            }

            array_splice($dataArr,0,0,$headerRowArr);

            $excelDataArr = array();


            $i = 1;
            foreach($dataArr as $key => $val1){

                foreach($val1 as $value){
                    $excelDataArr[$i][] = $value;
                }
                $i++;
            }

            $objPHPExcel = new PHPExcel();

// Set properties
            $objPHPExcel->getProperties()->setCreator("pethub")
                ->setLastModifiedBy("etc")
                ->setTitle("Office 2007 XLSX Cancel Document")
                ->setSubject("Office 2007 XLSX Cancel Document")
                ->setDescription("Exported doc for Office 2007 XLSX, generated by PHPExcel.")
                ->setKeywords("office EXCEL 2007 PHPExcel XLSX php")
                ->setCategory("Exported file");
            $objPHPExcel->getActiveSheet()->setTitle('pethub Excel Export');

            foreach($excelDataArr as $row => $val){

                foreach($val as $column => $value){



                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $value);
                }
            }

//Style the first header row to be bold and have borders
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    )
                )
            );


            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//$location = __DIR__.'/files/excel/'; //Make sure this location exists

            $objWriter->save('../files/excel/Cancel/cancel.xlsx');
            $path="Cancel/cancel.xlsx";

//echo 'File created: MyExcelSheet.xlsx';
            $response['error_code'] = 0;
            $response['message'] = 'File created.';
            $response['status'] = true;
            $response['data'] = array('path' => EXCEL_PIC_PATH.$path);
        }else{
            $response['error_code'] = 1;
            $response['message'] = 'No canceled Appointment found.';
            $response['status'] = true;
        }
        echoResponse(200, $response);
    }


});


$app->post('/:id(/:client_id)/exelgenerationnew',function($id, $client_id=null) use($app)
{
    ini_set('memory_limit', '-1');

    verifyFields(array('option'));

    //$client_id = $client_id;
    $pet_id = $app->request->post('pet_id');
    $option = $app->request->post('option');

    if($option == 'payment')
    {
        $log=[];

        if($client_id != null)
        {

            $log = transactionlog::find_by_sql("SELECT pet_name, date_of_transaction, type, amount, l_flag from tbl_newtransaction_log where company_id=$id and client_id=$client_id ");
        }else{

            $log = transactionlog::find_by_sql("SELECT pet_name, date_of_transaction, type, amount, l_flag from tbl_newtransaction_log where company_id=$id ");
        }

//echo "dddd";

        // $ = '';
        //  $columnHeader ="Pet Name". "\t" . "Date of Transaction" . "\t" . "Status" . "\t" . "Amount" . "\t" . "Flag" . "\t";
        //  $setData = '';
        //  $rowData = '';
        //This will become our top header row
        $headerRowArr = array(array('petname', 'date', 'type', 'amount', 'flag'));

        foreach ($log as $key => $value)
        {

            $date =date('d-m-Y',strtotime($value->date_of_transaction));

            //$val = '"' . $value->pet_name . '"' . "\t" . '"' . $date . '"' . "\t" . '"' . $value->l_status . '"' . "\t" . '"' . $value->amount . '"' . "\t" . '"' . $value->l_flag. '"' . "\n";
            //$dataArr=[];
            $dataArr[]=array($value->pet_name,$date,$value->type,$value->amount,$value->l_flag);

        }


        array_splice($dataArr,0,0,$headerRowArr);

        $excelDataArr = array();

        /*
 * The PHPExcel Library can designate a cell's location in a matrix as
 * (column, row) i.e. (0,1), (1,1), (2,1)...
 * e.g. A1 becomes (0,1), B1 becomes (1,1) and C1 becomes (2,1).. and so on..
 * Notice that the columns start from 0 and rows start from 1
 * So to achieve an array which will have keys matching this columns and rows format, we'll
 * apply the following logic in the foreach loop
 */
        $i = 1;
        foreach($dataArr as $key => $val1){

            foreach($val1 as $value){
                $excelDataArr[$i][] = $value;
            }
            $i++;
        }

        $objPHPExcel = new PHPExcel();

// Set properties
        $objPHPExcel->getProperties()->setCreator("pethub")
            ->setLastModifiedBy("etc")
            ->setTitle("Office 2007 XLSX Export Document")
            ->setSubject("Office 2007 XLSX Export Document")
            ->setDescription("Exported doc for Office 2007 XLSX, generated by PHPExcel.")
            ->setKeywords("office EXCEL 2007 PHPExcel XLSX php")
            ->setCategory("Exported file");
        $objPHPExcel->getActiveSheet()->setTitle('pethub Excel Export');

        foreach($excelDataArr as $row => $val){

            foreach($val as $column => $value){



                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $value);
            }
        }

//Style the first header row to be bold and have borders
        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
            )
        );


        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//$location = __DIR__.'/files/excel/'; //Make sure this location exists

        $objWriter->save('../files/excel/Payment/payment.xlsx');
        $path="Payment/payment.xlsx";
//echo 'File created: payment.xlsx';

        $response['error_code'] = 0;
        $response['message'] = 'File created.';
        $response['status'] = true;
        $response['data'] = array('path' => EXCEL_PIC_PATH.$path);

        echoResponse(200, $response);


    }
    elseif($option == 'booking' )
    {
        $appointment=[];
        if($client_id != NULL)
        {
            $appointment = Appointment::find('all',array("conditions" => "company_id=$id and client_id=$client_id "));
        }else{
            $appointment = Appointment::find('all',array("conditions" => "company_id=$id "));
        }

        $headerRowArr = array(array('Date','Client name', 'Company name','Visit', 'Visit hours', 'Service name', 'Message', 'Staff name'));

        foreach ($appointment as $key => $value)
        {

            $date =date('d-m-Y',strtotime($value->date));
            $visit = $value->visits;
            $visit_hours=$value->visit_hours;
            $message=$value->message;
            $company_name=$value->company->company_name;
            $client_name=$value->client->firstname;
            $service=Service::find_by_service_id($value->service_id);
            $service_name=$service->service_name;
            if($value->status == 'accepted')
            {

                if(!is_null($value->staff)){
                    $staff_name=$value->staff->firstname;
                }else{
                    $staff_name='not assign';
                }
                // $staff_name=$value->staff->firstname;


            }else{
                $staff_name='not assign';
            }

            //    echo $staff_name.'</br>';

            $dataArr[]=array($date,$client_name,$company_name,$visit,$visit_hours,$service_name,$message,$staff_name);


        }

        array_splice($dataArr,0,0,$headerRowArr);

        $excelDataArr = array();


        $i = 1;
        foreach($dataArr as $key => $val1){

            foreach($val1 as $value){
                $excelDataArr[$i][] = $value;
            }
            $i++;
        }

        $objPHPExcel = new PHPExcel();

// Set properties
        $objPHPExcel->getProperties()->setCreator("pethub")
            ->setLastModifiedBy("etc")
            ->setTitle("Office 2007 XLSX Booking Document")
            ->setSubject("Office 2007 XLSX Booking Document")
            ->setDescription("Exported doc for Office 2007 XLSX, generated by PHPExcel.")
            ->setKeywords("office EXCEL 2007 PHPExcel XLSX php")
            ->setCategory("Exported file");
        $objPHPExcel->getActiveSheet()->setTitle('pethub Excel Export');

        foreach($excelDataArr as $row => $val){

            foreach($val as $column => $value){



                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $value);
            }
        }

//Style the first header row to be bold and have borders
        $styleArray = array(
            'font' => array(
                'bold' => true,
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                )
            )
        );


        $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//$location = __DIR__.'/files/excel/'; //Make sure this location exists

        $objWriter->save('../files/excel/Booking/booking.xlsx');
        $path="Booking/booking.xlsx";

//echo 'File created: MyExcelSheet.xlsx';
        $response['error_code'] = 0;
        $response['message'] = 'File created.';
        $response['status'] = true;
        $response['data'] = array('path' => EXCEL_PIC_PATH.$path);

        echoResponse(200, $response);

    }elseif($option == 'cancel')
    {
        $appointment=[];
        if($client_id != NULL)
        {
            $appointment = Appointment_cancel::find('all',array("conditions" => "company_id=$id and client_id=$client_id "));
        }else{
            $appointment = Appointment_cancel::find('all',array("conditions" => "company_id=$id "));
        }

        $headerRowArr = array(array('Date', 'Client name', 'Company name', 'Visit', 'Visit hours', 'Service name', 'Message', 'Staff name'));
        if(count($appointment)>0)
        {
            foreach ($appointment as $key => $value)
            {

                $date =date('d-m-Y',strtotime($value->date));
                $visit = $value->visits;
                $visit_hours=$value->visit_hours;
                $message=$value->message;
                $company_name=$value->company->company_name;
                $client_name=$value->client->firstname;
                $service=Service::find_by_service_id($value->service_id);
                $service_name=$service->service_name;
                if($value->status == 'accepted')
                {
                    $staff_name=$value->staff->firstname;


                }else{
                    $staff_name='not assign';
                }

                //echo $staff_name.'</br>';

                $dataArr[]=array($date,$client_name,$company_name,$visit,$visit_hours,$service_name,$message,$staff_name);


            }

            array_splice($dataArr,0,0,$headerRowArr);

            $excelDataArr = array();


            $i = 1;
            foreach($dataArr as $key => $val1){

                foreach($val1 as $value){
                    $excelDataArr[$i][] = $value;
                }
                $i++;
            }

            $objPHPExcel = new PHPExcel();

// Set properties
            $objPHPExcel->getProperties()->setCreator("pethub")
                ->setLastModifiedBy("etc")
                ->setTitle("Office 2007 XLSX Cancel Document")
                ->setSubject("Office 2007 XLSX Cancel Document")
                ->setDescription("Exported doc for Office 2007 XLSX, generated by PHPExcel.")
                ->setKeywords("office EXCEL 2007 PHPExcel XLSX php")
                ->setCategory("Exported file");
            $objPHPExcel->getActiveSheet()->setTitle('pethub Excel Export');

            foreach($excelDataArr as $row => $val){

                foreach($val as $column => $value){



                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($column, $row, $value);
                }
            }

//Style the first header row to be bold and have borders
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    )
                )
            );


            $objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//$location = __DIR__.'/files/excel/'; //Make sure this location exists

            $objWriter->save('../files/excel/Cancel/cancel.xlsx');
            $path="Cancel/cancel.xlsx";

//echo 'File created: MyExcelSheet.xlsx';
            $response['error_code'] = 0;
            $response['message'] = 'File created.';
            $response['status'] = true;
            $response['data'] = array('path' => EXCEL_PIC_PATH.$path);
        }else{
            $response['error_code'] = 1;
            $response['message'] = 'No canceled Appointment found.';
            $response['status'] = true;
        }
        echoResponse(200, $response);
    }


});

$app->post('/:id/reportresult',function($id) use ($app){

    verifyFields(array('client_id','pet_id','option'));

    //$company_id = $id;
    $client_id = $app->request->post('client_id');
    $pet_id = $app->request->post('pet_id');
    $option = $app->request->post('option');
    $startdate = date('Y-m-d', strtotime($app->request->post('startdate')));
    $enddate = date('Y-m-d', strtotime($app->request->post('enddate')));

    if($option == 'single')
    {

        $appointment = Appointment::find_by_sql("SELECT date as date, count(appointment_id) as total, sum(visits) as visits, sum(visit_hours) as visit_hours, staff_id FROM `tbl_appointments` WHERE company_id=$id AND client_id=$client_id AND pet_id=$pet_id AND date='$startdate'");




        $resultData = [];

        foreach ($appointment as $value) {

            if(!empty($value->visits) && !empty($value->visit_hours))
            {
                $staffname='';
                if($value->staff_id != NULL)
                {
                    $staff=Staff::find_by_staff_id($value->staff_id);
                    $staffname=$staff->firstname;
                }else{
                    $staffname='not assigned';
                }
                $response['error_code'] = 0;
                $response['status'] = true;
                $response['message'] = 'Appointments  report retrive successfully.';
                $resultData[]=array(
                    'date' => date('d-m-Y',strtotime($value->date)),
                    'booking' => $value->total,
                    'visits' => $value->visits,
                    'duration' => $value->visit_hours,
                    'staff' => $staffname
                );
            }else{
                $response['error_code'] = 1;
                $response['status'] = false;
                $response['message'] = 'No report result found.';
            }
        }

        $response['data'] = $resultData;


        echoResponse(200, $response);

    }else{

        $appointment = Appointment::find_by_sql("SELECT date as date, count(appointment_id) as total, sum(visits) as visits, sum(visit_hours) as visit_hours, staff_id FROM `tbl_appointments` WHERE company_id=$id AND client_id=$client_id AND pet_id=$pet_id AND date BETWEEN '$startdate' AND '$enddate' GROUP BY date");

        if($appointment != NULL)
        {
            $response['error_code'] = 0;
            $response['status'] = true;
            $response['message'] = 'Report result retrive successfully.';
            $resultData = [];
            // $temp_ttl=0;
            //  $temp_visit=0;
            //  $temp_visit_hour=0;

            foreach ($appointment as $value) {

                //$resultData=[];
                // $temp_ttl+=$value->total;
                // $temp_visit+=$value->visits;
                // $temp_visit_hour+=$value->visit_hours;
                $staffname='';
                if($value->staff_id != NULL)
                {
                    $staff=Staff::find_by_staff_id($value->staff_id);
                    $staffname=$staff->firstname;
                }else{
                    $staffname='not assigned';
                }
                $resultData[]=array(
                    'date' => date('d-m-Y',strtotime($value->date)),
                    'booking' => $value->total,
                    'visits' => $value->visits,
                    'duration' => $value->visit_hours,
                    'staff' => $staffname
                );
            }

            $response['data'] = $resultData;

        }else{
            $response['error_code'] = 1;
            $response['status'] = false;
            $response['message'] = 'No report result found.';
        }

        echoResponse(200, $response);
    }

});

$app->post('/:id/futurebookcancel',function($id) use($app){

    verifyFields(array('client_id','pet_id'));

    $client_id=$app->request->post('client_id');
    $pet_id=$app->request->post('pet_id');
    $ab=date('Y-m-d');

    $appointment=Appointment::find_by_sql("SELECT * FROM `tbl_appointments` WHERE `company_id` = $id AND `client_id` = $client_id AND `pet_id` = $pet_id and date > '$ab'");

    if($appointment != NULL)
    {
        //$appointment_id=[];
        foreach ($appointment as $value)
        {

            $appointment_id=$value->appointment_id;
            $company_ids=$value->company_id;
            $client_ids=$value->client_id;
            $service_id=$value->service_id;
            $staff_id = $value->staff_id;
            $date = date('Y-m-d',strtotime($value->date));
            $visits =$value->visits;
            $visit_hours = $value->visit_hours;
            $price = $value->price;
            $status = $value->status;
            //$acknowledge = $appointment->acknowledge;
            $accepted = $value->accepted;
            $completed = $value->completed;
            $pet_id = $value->pet_id;
            $message = $value->message;
            $created_by = $value->created_by;
            $created_at = $value->created_at;


            //$appoint= new Appointment_cancel();
            $app_cancel = new Appointment_cancel();
            $app_cancel->company_id = $company_ids;
            $app_cancel->client_id = $client_ids;
            $app_cancel->service_id = $service_id;
            $app_cancel->staff_id = $staff_id;
            $app_cancel->date  = $date;
            $app_cancel->visits = $visits;
            $app_cancel->visit_hours = $visit_hours;
            $app_cancel->price = $price;
            $app_cancel->status = $status;
            //$app_cancel->acknowledge = $acknowledge;
            $app_cancel->accepted = $accepted;
            $app_cancel->completed  = $completed;
            $app_cancel->pet_id = $pet_id;
            $app_cancel->message = $message;
            $app_cancel->created_by = $created_by;
            $app_cancel->created_at = $created_at;
            $app_cancel->save();
            $app_cancel->appointment_id = (int) $id;

            $check=Appointment::find_by_appointment_id($appointment_id);
            $check->delete();



        }
        $response['error_code'] = 0;
        $response['status'] = true;
        $response['message'] = 'Future booking cancelled successfully.';
    }else{
        $response['error_code'] = 1;
        $response['status'] = false;
        $response['message'] = 'No future booking found.';
    }
    echoResponse(200, $response);
});


/*
 * Client wise Price adding for service
 */
$app->post('/:id/clientpriceaddnew', function($id) use ($app) {

    verifyFields(array('service_id', 'client_id', 'pet_id', 'full_hour_price', 'half_hour_price', 'additional_hours_price', 'additional_visits_price','price_per_walk','additional_pets','payment_option'));

    $company_id =$id;
    $client_id = $app->request->post('client_id');
    $pet_id = $app->request->post('pet_id');
    $service_id = $app->request->post('service_id');
    $full_hour_price = $app->request->post('full_hour_price');
    $full_day_price = $app->request->post('full_day_price');
    $half_hour_price = $app->request->post('half_hour_price');
    $additional_hours_price = $app->request->post('additional_hours_price');
    $additional_visits_price = $app->request->post('additional_visits_price');
    $price_per_walk = $app->request->post('price_per_walk');
    $additional_pets = $app->request->post('additional_pets');
    $payment_option= $app->request->post('payment_option');


    $priceCheck = Pricenew::find(array("conditions" => "company_id = {$company_id} AND client_id= {$client_id} AND service_id = {$service_id} AND pet_id={$pet_id}"));


    if ($priceCheck != NULL) {


        $priceCheck->company_id = $company_id;
        $priceCheck->client_id = $client_id;
        $priceCheck->pet_id = $pet_id;
        $priceCheck->service_id = $service_id;
        $priceCheck->full_hour_price = $full_hour_price;
        $priceCheck->full_day_price = $full_day_price;
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
                'pet_id' => $priceCheck->pet_id,
                'service_id' => $priceCheck->service_id,
                'full_hour_price' => $priceCheck->full_hour_price,
                'full_day_price' => $priceCheck->full_day_price,
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


        Pricenew::transaction(function() use($app, $company_id, $client_id, $pet_id, $service_id, $full_hour_price, $full_day_price, $half_hour_price, $additional_hours_price, $additional_visits_price, $price_per_walk, $additional_pets, $payment_option) {
            $price = new Pricenew();
            $price->company_id = $company_id;
            $price->client_id = $client_id;
            $price->pet_id = $pet_id;
            $price->service_id = $service_id;
            $price->full_hour_price = $full_hour_price;
            $price->full_day_price = $full_day_price;

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
                    'company_id' => $company_id,
                    'client_id' => $client_id,
                    'pet_id' => $pet_id,
                    'service_id' => $service_id,
                    'full_hour_price' => $full_hour_price,
                    'full_day_price' => $full_day_price,
                    'half_hour_price' => $half_hour_price,
                    'additional_hours_price' => $additional_hours_price,
                    'additional_visits_price' => $additional_visits_price,
                    'price_per_walk' => $price_per_walk,
                    'additional_pets' => $additional_pets,
                    'payment_option' => $payment_option,
                    'p_flag' => $price->p_flag
                );
            }

            echoResponse(200, $response);
        });
    }
});

/*
 * Shwoing price as per service according to service
 */

$app->post('/:id/clientpricenew', function($id) use($app) {


    $response['error_code'] = 1;
    $response['status'] = false;
    $response['message'] = 'Error! Something went wrong. please try again later.';
    $response['data'] = [];

    verifyFields(array('client_id','pet_id'));
    $client_id = $app->request->post('client_id');
    $pet_id = $app->request->post('pet_id');
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
            'full_day_price' => '',
            'half_hour_price' => '',
            'additional_hours_price' => '',
            'additional_visits_price' => '',
            'price_per_walk' => '',
            'additional_pets' => '',
            'payment_option' => '',
        );

        foreach ($services as $key => $value) {

            $price = Pricenew::find(array('conditions' => "company_id = {$id} AND client_id={$client_id} AND service_id = {$value->service_id} AND pet_id={$pet_id}"));
            if (count($price) > 0) {

                $ServicePrice[] = array(
                    'price_id' => $price->price_id,
                    'service_id' => $price->service_id,
                    'service_name' => $price->service->service_name,
                    'full_hour_price' => $price->full_hour_price,
                    'full_day_price' => $price->full_day_price,
                    'half_hour_price' => $price->half_hour_price,
                    'additional_hours_price' => $price->additional_hours_price,
                    'additional_visits_price' => $price->additional_visits_price,
                    'price_per_walk' => $price->price_per_walk,
                    'additional_pets' => $price->additional_pets,
                    'payment_option' => $price->payment_option,
                );
            }else{

                $s = Service::find($value->service_id);
                $ServicePrice[] = array(
                    'price_id' => 0,
                    'service_id' => $value->service_id,
                    'service_name' => $s->service_name,
                    'full_hour_price' => 0,
                    'full_day_price' => 0,
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

//temporary api to put data on new price table.

// $app->get('/:id/price1', function($id) use ($app)
// {
//     $service=CompanyService::find('all',array('conditions' => "company_id='{$id}'"));
//     $client=Contract::find_by_sql("SELECT client_id from tbl_contracts where company_id=$id");
//     foreach ($client as $cli)
//     {

//        $pet=Pet::find_by_sql("SELECT pet_id from tbl_pets where client_id=$cli->client_id");

//        foreach ($pet as $p)
//         {

//             foreach ($service as $s)
//             {
//                     $pricecheck=Price::find(array("conditions" => "company_id=$id and client_id=$cli->client_id and service_id=$s->service_id"));


//                         if($pricecheck)
//                         {

//                             $com_id=$pricecheck->company_id;
//                             $cli_id=$pricecheck->client_id;
//                             $pe_id=$p->pet_id;
//                             $ser_id=$pricecheck->service_id;
//                             $full=$pricecheck->full_hour_price;
//                             $half=$pricecheck->half_hour_price;
//                             $add_hour=$pricecheck->additional_hours_price;
//                             $add_visit=$pricecheck->additional_visits_price;
//                             $walk=$pricecheck->price_per_walk;
//                             $additional_pets=$pricecheck->additional_pets;
//                             $payment_option=$pricecheck->payment_option;



//                             $price1 = new Pricenew();
//                             $price1->company_id =$com_id;
//                             $price1->client_id = $cli_id;
//                             $price1->pet_id = $pe_id;
//                             $price1->service_id = $ser_id;
//                             $price1->full_hour_price = $full;
//                             $price1->half_hour_price = $half;
//                             $price1->additional_hours_price = $add_hour;
//                             $price1->additional_visits_price = $add_visit;
//                             $price1->price_per_walk = $walk;
//                             $price1->additional_pets = $additional_pets;
//                             $price1->payment_option = $payment_option;
//                             $price1->p_flag = 0;
//                             $price1->save();
//                             $price1->price_id = (int) $price1->price_id;



//                         }else{

//                             $price1 = new Pricenew();
//                             $price1->company_id = $id;
//                             $price1->client_id = $cli->client_id;
//                             $price1->pet_id = $p->pet_id;
//                             $price1->service_id = $s->service_id;
//                             $price1->full_hour_price = 0;
//                             $price1->half_hour_price = 0;
//                             $price1->additional_hours_price = 0;
//                             $price1->additional_visits_price = 0;
//                             $price1->price_per_walk = 0;
//                             $price1->additional_pets = 0;
//                             $price1->payment_option = 0;
//                             $price1->p_flag = 0;
//                             $price1->save();
//                             $price1->price_id = (int) $price1->price_id;

//                             // $response['error_code'] = 0;
//                             // $response['status'] = true;
//                             // $response['message'] = 'New price 222add successfully .';

//                             }
//             }
//            // die;


//         }

//     }
//          $response['error_code'] = 0;
//                             $response['status'] = true;
//                             $response['message'] = 'New price 111add successfully .';

//       // print_r($pet_ids);
//     echoResponse(200, $response);


// });


function getMonths($startdate, $enddate) {
    $begin = new DateTime($startdate);
    $end = new DateTime($enddate);

    while ($begin <= $end) {
        $w[] = $begin->format('Y-m');
        $begin->modify('first day of next month');
    }
    return $w;
}

function weekOfMonth($date) {
//Get the first day of the month.
    $firstOfMonth = strtotime(date("Y-m-01", $date));
//Apply above formula.
    $ft = date("w", $date);
    ;
    if ($ft == 0) {
        return intval(date("W", $date)) - intval(date("W", $firstOfMonth)) + 2;
    } else {

        return intval(date("W", $date)) - intval(date("W", $firstOfMonth)) + 1;
    }
}

function getStartAndEndDate($week, $year) {

    $dto = new DateTime();
    $dto->setISODate($year, $week,0);
    $ret = '('.$dto->format('d-m-Y');
    $dto->modify('+6 days');
    $ret = $ret.'  -  '.$dto->format('d-m-Y').')';

    return $ret;
}

function getWeekday($date) {
    return date('w', strtotime($date));
}

function numWeeks($year, $month, $start = 0) {
    $unix = strtotime("$year-$month-01");
    $numDays = date('t', $unix);
    if ($start === 0) {
        $dayOne = date('w', $unix); // sunday based week 0-6
    } else {
        $dayOne = date('N', $unix); //monday based week 1-7
        $dayOne--; //convert for 0 based weeks
    }

    //if day one is not the start of the week then advance to start
    $numWeeks = floor(($numDays - (6 - $dayOne)) / 7);
    return $numWeeks;
}

function weeks_in_month($year, $month, $start_day_of_week) {
// Total number of days in the given month.
    $num_of_days = date("t", mktime(0, 0, 0, $month, 1, $year));
    $start_day = date("N", mktime(0, 0, 0, $month, 1, $year));
// Count the number of times it hits $start_day_of_week.
    $num_of_weeks = 0;
    for ($i = 1; $i <= $num_of_days; $i++) {
        $day_of_week = date('w', mktime(0, 0, 0, $month, $i, $year));
        if ($day_of_week == $start_day_of_week)
            $num_of_weeks++;
    }

    $s = date("D", strtotime('01-' . $month . '-' . $year));
    if ($s != 'Sun') {

        $num_of_weeks+=1;
    }
    return $num_of_weeks;
}

function getWeeks($month,$year){
    $month = intval($month);                //force month to single integer if '0x'
    $suff = array('st','nd','rd','th','th','th');       //week suffixes
    $end = date('t',mktime(0,0,0,$month,1,$year));      //last date day of month: 28 - 31
    $start = date('w',mktime(0,0,0,$month,1,$year));    //1st day of month: 0 - 6 (Sun - Sat)
    $last = 7 - $start;                     //get last day date (Sat) of first week
    $noweeks = ceil((($end - ($last + 1))/7) + 1);      //total no. weeks in month
    $output = [];                       //initialize string
    $monthlabel = str_pad($month, 2, '0', STR_PAD_LEFT);
    for($x=1;$x<$noweeks+1;$x++){
        if($x == 1){
            // $startdate = "$year-$monthlabel-01";
            $startdate = "01-$monthlabel-$year";
            $day = $last - 6;
        }else{
            $day = $last + 1 + (($x-2)*7);
            $day = str_pad($day, 2, '0', STR_PAD_LEFT);
            //$startdate = "$year-$monthlabel-$day";
            $startdate = "$day-$monthlabel-$year";
        }
        if($x == $noweeks){
            //$enddate = "$year-$monthlabel-$end";
            $enddate = "$end-$monthlabel-$year";
        }else{
            $dayend = $day + 6;
            $dayend = str_pad($dayend, 2, '0', STR_PAD_LEFT);
            //$enddate = "$year-$monthlabel-$dayend";
            $enddate = "$dayend-$monthlabel-$year";
        }
        $output []= '('.$startdate ."  -  ".$enddate .')';
    }
    return $output;
}
function printDays($from, $to) {
    $from_date=strtotime($from);
    $to_date=strtotime($to);
    $current=$from_date;
    while($current<=$to_date) {
        $days[]=date('l', $current);
        $current=$current+86400;
    }
    $day="";
    foreach($days as $key => $day) {
        echo $day."\n";
    }
}

function getDatesFromRange($start, $end, $format = 'd-m-Y') {
    $array = array();
    $interval = new DateInterval('P1D');

    $realEnd = new DateTime($end);
    $realEnd->add($interval);

    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

    foreach($period as $date) {
        $array[] = $date->format($format);
    }

    return $array;
}
	
