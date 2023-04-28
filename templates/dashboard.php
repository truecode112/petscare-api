<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
        <!--<small>Control panel</small>-->
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-sm-6 col-xs-12">
          <!-- small box -->
          <div class="small-box bg-aqua">
            <div class="inner contract">
              <h3><?php 
				$contratAll = Contract::all();
				$contrat = Contract::find(array('conditions' => "status = 'pending'"));
			  echo count($contrat);?></h3>

              <p>New Contract Request</p>
            </div>
            <!--<div class="icon">
              <i class="ion ion-bag"></i>
            </div>--->
             <!--<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>-->
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-sm-6 col-xs-12">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner order">
              <h3><?php 
			  $Payment = Payment::all(); 
			  $Appointment = Appointment::all();
				$order = round(((count($Payment)/count($Appointment))*100));
				echo $order;
			  ?><sup style="font-size: 20px">%</sup></h3>

              <p>Completed Order</p>
            </div>
            <!--<div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>--->
             <!--<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>-->
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-sm-6 col-xs-12">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner client">
              <h3><?php $client = Client::all();
			  echo count($client);?></h3>

              <p>Total client</p>
            </div>
            <!--<div class="icon">
              <i class="ion ion-person-add"></i>
            </div>-->
             <!--<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>-->
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-sm-6 col-xs-12">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner genuine">
              <h3><?php $company = Company::find('all',array('conditions' => 'is_active = 1')); echo count($company);?></h3>

              <p>Genuine Company</p>
            </div>
            <!--<div class="icon">
              <i class="ion ion-pie-graph"></i>
            </div>-->
            <!--<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>-->
          </div>
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->
      <!-- Main row -->
      <div class="row">
	  
		<!-- Service Pricing table -->
		<section class="col-lg-12 ">
           <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">Payment Detail</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding" style="">
             
              <table class="table table-hover">
                <tbody><tr>
                  <th>No.</th>
                  <th>Client</th>
                  <th>Service </th>
                  <th>Company</th>
                  <th>Total Price</th>
                  <th>Date</th>
                </tr>
				<?php 
				 
				$p=1;
				foreach($Payment as $py){
								$ap = Appointment::find(array('conditions'=>"appointment_id = {$py->appointment_id}"));
								$s = Service::find(array('conditions' => "service_id = {$ap->service_id}"));
					echo '<tr>
							<td>'.$p.'</td>
							<td>'.$ap->client->firstname.' '.$ap->client->lastname.'</td>
							<td>'.$s->service_name.'</td>
							<td>'.$ap->company->company_name.'</td>
							<td>'.$ap->price.'<i class="fa fa-fw fa-usd"></i></td>
							<td>'.date('d ,M Y h:i a',strtotime($py->created_at)).'</td>
						</tr>'; 
						$p++;
				}?>
                
              </tbody></table>
            
          </div>
            </div>
            <!-- /.box-body -->
         
        </section>
	  <!-- Appoinment Booked by Company -->
		
	  <section class="col-lg-12 ">
           <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">Appoinment Booked by Company</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding" style="">
             
              <table class="table table-hover">
                <tbody><tr>
                  <th>No.</th>
                  <th>Client</th>
                  <th>Service Taken</th>
                  <th>Company</th>
                  <th>Pet Name</th>
                  <th>Visits</th>
                  <th>Visit Hours</th>
                  <th>AdditioNal Visit</th>
                  <th>AdditioNal Visit Hours</th>
                  <th>Date</th>
                </tr>
				<?php 
				 $appointment = Appointment::find('all',array('conditions' => "created_by = 'company'"));
				$a=1;
				if(count($appointment)>0){
				foreach($appointment as $ap){
								
								$s = Service::find(array('conditions' => "service_id = {$ap->service_id}"));
					echo '<tr>
							<td>'.$a.'</td>
							<td>'.$ap->client->firstname.'</td>
							<td>'.$s->service_name.'</td>
							<td>'.$ap->company->company_name.'</td>
							<td>'.$ap->pet->pet_name.'</td>
							<td>'.$ap->visits.'</td>
							<td>'.$ap->visit_hours.'</td>
							<td>NA</td>
							<td>NA</td>
							<td>'.date('d ,M Y h:i a',strtotime($ap->created_at)).'</td>
						</tr>'; 
						$a++;
				}}?>
                
              </tbody></table>
            
          </div>
            </div>
            <!-- /.box-body -->
         
        </section>
        <!-- Left col(graph) -->
		<!-- Contract table -->
        <section class="col-lg-6 ">
           <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">Contract Details</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding" style="">
             
              <table class="table table-hover">
                <tbody><tr>
                  <th>No.</th>
                  <th>Client</th>
                  <th>Company</th>
                  <th>Status</th>
                  <th>Date</th>
                </tr>
				<?php $i=1;
				foreach($contratAll as $ca){
					if($ca->status == 'accepted'){
						$st = '<span class="label label-success">Approved</span>';
					}else if($ca->status == 'pending'){
						$st = '<span class="label label-warning">Pending</span>';	
					}else{
						$st = '<span class="label label-danger">Rejected</span>';
					}
					
					
					echo '<tr>
							<td>'.$i.'</td>
							<td>'.$ca->client->firstname.' '.$ca->client->lastname.'</td>
							<td>'.$ca->company->company_name.'</td>
							<td>'.$st.'</td>
							<td>'.date('d-m-y H:i a',strtotime($ca->created_at)).'</td>
						</tr>'; 
						$i++;
				}?>
                
              </tbody></table>
            </div>
           
            </div>
            <!-- /.box-body -->
         
        </section>
        <!-- /.Left col -->
        <!-- right col -->
		<!-- Service Pricing table -->
		<section class="col-lg-6  ">
           <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">Service  Pricing</h3>

              <div class="box-tools">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body table-responsive no-padding" style="">
             
              <table class="table table-hover">
                <tbody><tr>
                  <th>No.</th>
                  <th>Service</th>
                  <th>1 Hour</th>
                  <th>1/2 Hour</th>
                  <th>Additional Visit</th>
                  <th>Additional Hour</th>
                </tr>
				<?php 
				$servicePrice = Price::all(); 
				$j=1;
				foreach($servicePrice as $sp){
										
					echo '<tr>
							<td>'.$j.'</td>
							<td>'.$sp->service->service_name.'</td>
							<td>'.$sp->full_hour_price.'<i class="fa fa-fw fa-usd"></i></td>
							<td>'.$sp->half_hour_price.'<i class="fa fa-fw fa-usd"></i></td>
							<td>'.$sp->additional_visits_price.'<i class="fa fa-fw fa-usd"></i></td>
							<td>'.$sp->additional_hours_price.'<i class="fa fa-fw fa-usd"></i></td>
						</tr>'; 
						$j++;
				}?>
                
              </tbody></table>
            
          </div>
            </div>
            <!-- /.box-body -->
         
        </section>
		
		
        <!-- /right col -->

      </div>
      <!-- /.row (main row) -->

  
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->