<div class="content-wrapper" style="min-height: 916px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        User Profile
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">User profile</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
	<?php if(isset($clientData)){
			
			if(!empty($clm['profile_image'])){
						$img = $clm['profile_image'];;
					}else{
						$img = "$url/templates/dist/img/default.jpg";
					}
			
	?>
<div class="row">
        <div class="col-md-3">
		<?php foreach($clientData as $clm){?>
          <!-- Profile Image -->
          <div class="box box-primary">
            <div class="box-body box-profile">
              <img class="profile-user-img img-responsive img-circle" src="<?php echo  $img;?>" alt="User profile picture">

              <h3 class="profile-username text-center"><?php echo $clm['firstname'].' '.$clm['lastname'];?></h3>

            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
		
          <!-- About Me Box -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">About Me</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			
              <strong><i class="fa fa-envelope-o margin-r-5"></i> Email Address</strong>

              <p class="text-muted">
               <?php echo  $clm['emailid'];?>
              </p>

              <hr> 
			  
			  <strong><i class="fa fa-phone margin-r-5"></i> Contact Number</strong>

              <p class="text-muted">
               <?php echo $clm['contact_number'];?>
              </p>

              <hr>

              <strong><i class="fa fa-map-marker margin-r-5"></i> Address</strong>

              <p class="text-muted"><?php echo $clm['client_address'];?></p>

              <hr>

              
              <strong><i class="fa fa-file-text-o margin-r-5"></i> Notes</strong>

              <p><?php echo $clm['client_notes'];?></p>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        
		 <!-- /.col -->
        <div class="col-md-9">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#activity" data-toggle="tab">Pets</a></li>
              <li><a href="#timeline" data-toggle="tab">Appoinment Detail</a></li>
              
            </ul>
            <div class="tab-content">
              <div class="active tab-pane" id="activity">
                <!-- Post -->
				<?php if(isset($clm['pet_detail']) && count($clm['pet_detail']) > 0)
				{
					foreach($clm['pet_detail'] as $pt) 
					{
						if(!empty($pt['pet_image'])){
							$petimg = $pt['pet_image'];
						}else{
							$petimg = "$url/templates/dist/img/default.jpg";
						}
					?>
                <div class="post">
                  <div class="user-block">
                    <img class="img-circle img-bordered-sm" src="<?php echo $petimg;?>" alt="user image">
                        <span class="username">
                          <?php echo $pt['pet_name'];?>
                        </span>
                    <span class="description">Pet's Age - <?php echo $pt['pet_age'].' Years';?></span>
                  </div>
                    <span class="description">Pet's Medical Detail :</span>
                  <!-- /.user-block -->
                  <p>
                    <?php echo $pt['medical_detail'];?>
                  </p>
				  
                  <hr>
                   <span class="description">Pet's Note :</span>
                  <!-- /.user-block -->
                  <p>
                    <?php echo $pt['pet_notes'];?>
                  </p>
                </div>
				<?php }
				}
				?>
                <!-- /.post -->
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="timeline">
                <!-- The timeline -->
				<ul class="timeline timeline-inverse">
				<?php 
				if(isset($clm['appointments']) && count($clm['appointments']) > 0)
				{
					$dt = '';
					foreach($clm['appointments'] as $clma)
					{
						?>
				
					<!-- timeline time label -->
					<?php if($dt != date('d M Y',strtotime($clma['date']))){?>
                  <li class=" panel time-label">
                        <span class="bg-red"> <a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $clma['appointment_id'];?>" style="color:white;">
                          <?php echo date('d M Y',strtotime($clma['date']));?></a>
                        </span>
                  </li><?php } else { ?>
                  <li class=" panel time-label" style="margin-left:20px;">
                        <span class="bg-gray"> <a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $clma['appointment_id'];?>" >
                          <?php echo date('h:i:s a',strtotime($clma['created_at']));?></a>
                        </span>
                  </li>					  
				  <?php } ?>
                  <!-- /.timeline-label -->
				  <ul id="<?php echo $clma['appointment_id'];?>" class=" timeline timeline-inverse panel-collapse collapse in">
				   <!-- timeline item -->
                  <li>
                    <i class="fa fa-envelope bg-blue"></i>

                    <div class="timeline-item">
                      <span class="time"><i class="fa fa-clock-o"></i> <?php echo date('h:i a',strtotime($clma['created_at']));?></span>

                      <h3 class="timeline-header"><a href="#"> <?php echo $clm['firstname'].' '.$clm['lastname'];?> </a> sent appointment request to <?php echo $clma['company_detail']['company_name'];?></h3>

                      
                    </div>
                  </li>
                  <!-- END timeline item -->
                  <!-- timeline item -->
				
                  <li>
                    <i class="fa fa-scribd bg-aqua"></i>

                    <div class="timeline-item">
                      

                      <h3 class="timeline-header no-border"><?php echo $clm['firstname'].' '.$clm['lastname'];?> appointment request is <?php if($clma['status'] == 'accepted'){
					  echo '<span class="label label-success">'.$clma['status'].'</span>';}else if($clma['status'] == 'rejected'){echo '<span class="label label-danger">'.$clma['status'].'</span>';}else if($clma['status'] == 'rejected'){echo '<span class="label label-primary">'.$cd['status'].'</span>';}else{echo '<span class="label label-warning">'.$clma['status'].'</span>';}?>
                      </h3>
                    </div>
                  </li>
                  <!-- END timeline item -->
                  <!-- timeline item -->
				 
                  <li>
                    <i class="fa fa-info bg-yellow"></i>

                    <div class="timeline-item">
                    

                      <h3 class="timeline-header">Information</h3>
				
                      <div class="timeline-body">
                        <p> Service : <?php echo $clma['service_name'];?></p>
                        <p> Visits : <?php echo $clma['visits'];?></p>
                        <p> Visit Hours : <?php echo $clma['visit_hours'];?></p>
                        <p> Price : <?php echo $clma['price'];?></p>
                      </div>
                    </div>
                  </li>
                  <!-- END timeline item -->
				  </ul>
				<?php $dt = date('d M Y',strtotime($clma['date']));}
				}else{
					echo '<li>
                    <i class="fa fa-commenting bg-yellow"></i>

                    <div class="timeline-item">
                      <span class="time"><i class="fa fa-clock-o"></i> 27 mins ago</span>

                      <h3 class="timeline-header">Message</h3>
				
                      <div class="timeline-body">
                        There is Not Appointment
                      </div>
                    </div>
                  </li>';
				}
				?>
					
				<li>
                    <i class="fa fa-clock-o bg-gray"></i>
                  </li>
				 </ul>
              </div>
	        
			 
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
		
		
      </div>
      <!-- /.row -->
		
	<?php }} if(isset($companies)){ ?>
	
		  <div class="row">
        <div class="col-md-3">
		<?php foreach($companies as $cmp){
			if(!empty($cmp['company_image'])){
						$img2 = $cmp['company_image'];
					}else{
						$img2 = "$url/uploads/company-profiles/default-50x50.gif";
					}	
		
		?>
          <!-- Profile Image -->
          <div class="box box-primary">
            <div class="box-body box-profile">
              <img class="profile-user-img img-responsive img-circle" src="<?php echo $img2;?>" alt="User profile picture">

              <h3 class="profile-username text-center"><?php echo $cmp['name'];?></h3>

            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
		
          <!-- About Me Box -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">About Me</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			
              <strong><i class="fa fa-envelope-o margin-r-5"></i> Email Address</strong>

              <p class="text-muted">
               <?php echo  $cmp['emailid'];?>
              </p>

              <hr> 
			  
			  <strong><i class="fa fa-globe margin-r-5"></i> Website</strong>

              <p class="text-muted">
               <?php echo  $cmp['website'];?>
              </p>

              <hr> 
			  
			  <strong><i class="fa fa-phone margin-r-5"></i> Contact Number</strong>

              <p class="text-muted">
               <?php echo $cmp['contact_number'];?>
              </p>

              <hr>

              <strong><i class="fa fa-map-marker margin-r-5"></i> Address</strong>

              <p class="text-muted"><?php echo $cmp['address'];?></p>

              <hr>

              <strong><i class="fa fa-file-text-o margin-r-5"></i> About</strong>

              <p><?php echo $cmp['about'];?></p>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        
		 <!-- /.col -->
        <div class="col-md-9">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#activity" data-toggle="tab">Service's</a></li>
              <li><a href="#timeline" data-toggle="tab">Appoinment Detail</a></li>
              <li><a href="#staff" data-toggle="tab">Staff Detail</a></li>
               <li><a href="#clients" data-toggle="tab">Client Detail</a></li>
            </ul>
            <div class="tab-content">
              <div class="active tab-pane" id="activity">
                <!-- Post -->
				
                <div class="post">
                  <div class="user-block">
                    <img class="img-circle img-bordered-sm" src="<?php echo $img2;?>" alt="user image">
                        <span class="username">
                          <a href="#"><?php echo $cmp['name'];?></a>
                        </span>
                    <span class="description">Total Service - <?php echo count($cmp['services']);?></span>
                  </div>
                    <span class="description">Service List :</span>
                  <!-- /.user-block -->
				   <hr>
				  <?php if(count($cmp['services'])>0){foreach($cmp['services'] as $s) {?>
                  <p>
                    <?php echo $s['name'];?>
                  </p>
				  <?php }}?>
                 
                   
                </div>
                <!-- /.post -->

              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="timeline">
                <!-- The timeline -->
				<ul class="timeline timeline-inverse">
				<?php 
				if(isset($cmp['appointmnets']) && count($cmp['appointmnets']) > 0)
				{
					$dt1 ='';
					foreach($cmp['appointmnets'] as $cm)
					{
						?>
				
					<!-- timeline time label -->
					<?php if($dt1 != date('d M Y',strtotime($cm['date']))) {?>
                  <li class="time-label">
                        <span class="bg-red"><a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $cm['appointment_id'];?>" style="color:white;">
                          <?php echo date('d M Y',strtotime($cm['date']));?></a>
                        </span>
					</li><?php } else {?>
					<li class="time-label" style="margin-left:20px;">
                        <span class="bg-gray"><a data-toggle="collapse" data-parent="#accordion" href="#<?php echo $cm['appointment_id'];?>">
                          <?php echo date('h:i:s a',strtotime($cm['created_at']));?></a>
                        </span>
					</li>
					<?php }?>
                  <!-- /.timeline-label -->
				  <ul id="<?php echo $cm['appointment_id'];?>" class=" timeline timeline-inverse panel-collapse collapse in">
				  <!-- timeline item -->
                  <li>
                    <i class="fa fa-envelope bg-blue"></i>

                    <div class="timeline-item">
                      <span class="time"><i class="fa fa-clock-o"></i> <?php echo date('h:i a',strtotime($cm['created_at']));?></span>

                      <h3 class="timeline-header">appointment requested by <?php echo $cm['owner_detail']['firstname'].' '.$cm['owner_detail']['lastname'];?></h3>

                      
                    </div>
                  </li>
                  <!-- END timeline item -->
				   <!-- timeline item -->
				
                  <li>
                    <i class="fa fa-scribd bg-aqua"></i>

                    <div class="timeline-item">
                     

                      <h3 class="timeline-header no-border">appointment request status <?php echo $cm['status'];?>
                      </h3>
                    </div>
                  </li>
                  <!-- END timeline item -->
				  <!-- timeline item -->
				 
                  <li>
                    <i class="fa fa-info bg-yellow"></i>

                    <div class="timeline-item">
                     

                      <h3 class="timeline-header">Information</h3>
				
                      <div class="timeline-body">
                        <p> Service : <?php echo $cm['service_name'];?></p>
                        <p> Visits : <?php echo $cm['visits'];?></p>
                        <p> Visit Hours : <?php echo $cm['visit_hours'];?></p>
                        <p> Price : <?php echo $cm['price'];?></p>
                      </div>
                    </div>
                  </li>

                  <!-- END timeline item -->
				  <!-- timeline item -->
				 
                  <li>
                    <i class="fa fa-paw bg-green"></i>

                    <div class="timeline-item">
                      
                      <h3 class="timeline-header">Pet Detail</h3>
				
                      <div class="timeline-body">
                        <p> Pet Name : <?php echo $cm['pet_detail']['pet_name'];?></p>
                        <p> Pet age : <?php echo $cm['pet_detail']['pet_age'];?></p>
                        <p> Medical Detail : <?php echo $cm['pet_detail']['medical_detail'];?></p>
                        <p> Pet Notes : <?php echo $cm['pet_detail']['pet_notes'];?></p>
                      </div>
                    </div>
                  </li>

                  <!-- END timeline item -->
				  <!-- timeline item -->
				 
                  <li>
                    <i class="fa fa-commenting bg-yellow"></i>

                    <div class="timeline-item">
                      

                      <h3 class="timeline-header">Message</h3>
				
                      <div class="timeline-body">
                        <?php echo $cm['message'];?>
                      </div>
                    </div>
                  </li>

                  <!-- END timeline item -->
				  </ul>
				<?php $dt1 = date('d M Y',strtotime($cm['date']));}
				}else{
					echo '<li>
                    <i class="fa fa-commenting bg-yellow"></i>

                    <div class="timeline-item">
                      <span class="time"><i class="fa fa-clock-o"></i> 27 mins ago</span>

                      <h3 class="timeline-header">Message</h3>
				
                      <div class="timeline-body">
                        There is Not Appointment
                      </div>
                    </div>
                  </li>';
				}
				?>
					
				<li>
                    <i class="fa fa-clock-o bg-gray"></i>
                  </li>
				 </ul>
              </div>
				 
     <div class="tab-pane" id="staff">
              
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">List Of Staff Member</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
              <table class="table table-condensed">
                <tr>
                  <th style="width: 10px">No.</th>
                  <th>Name</th>
                  <th>Profile Picture</th>
                  <th>Email</th>
                  <th>Contact Number</th>
                </tr>
				<?php
				$id = $cmp['id'];
					 $staff = Staff::find('all', array('conditions' => "company_id = {$id}"));
					 $i=1;
					 foreach($staff as $s)
					 {
				?>
                <tr>
                  <td><?php echo $i;?></td>
                  <td><?php echo $s->firstname.' '.$s->lastname;?></td>
                  <td><img class='img-circle img-bordered-sm' src="<?php echo STAFF_PIC_PATH.$s->profile_image;?>" width=40 heigh=40 ></td>
                  <td><?php echo $s->emailid;?></td>
                  <td><?php echo $s->contact_number;?></td>
                </tr>
				<?php $i++;}?>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
              </div>
				 
             <!-- clientData --->
			   <div class="tab-pane" id="clients">
              
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">List Of Clients</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
              <table class="table table-condensed">
                <tr>
                  <th style="width: 10px">No.</th>
                  <th>FirstName</th>
                  <th>LastName</th>
                  <th>Email</th>
                  <th>Prifile Picture</th>
                  <th>Contact Number</th>
                  <th>Address</th>
                  <th>Notes</th>
				  <th>Type</th>
                </tr>
				<?php
				if(isset($cmp['appointmnets']) && count($cmp['clients']) > 0)
				{	
					$m=1;
					 foreach($cmp['clients'] as $c)
					 {
						 $type = $c['isManualClient']?'<small class="label bg-green" style="margin-left:5px;">manually</small>':'<small class="label bg-blue" style="margin-left:5px;">registered</small>';
				?>
                <tr >
                  <td><?php echo $m;?></td>
                  <td><?php echo $c['firstname']; ?></td>
				  <td><?php echo $c['lastname'];?></td>
                  <td><?php echo $c['emailid'];?></td>
                  <td><img class='img-circle img-bordered-sm' src="<?php echo $c['profile_image'];?>" width=40 heigh=40 ></td>
                  <td><?php echo $c['contact_number'];?></td>
                  <td><?php echo $c['client_address'];?></td>
                  <td><?php echo $c['client_notes'];?></td>
				  <td><?php echo $type;?></td>
                </tr>
				<?php 
					$m++;}
				}
				?>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
         </div>
            </div>
            <!-- /.tab-content -->
          </div>
          <!-- /.nav-tabs-custom -->
        </div>
        <!-- /.col -->
		
		
      </div>
      <!-- /.row -->
		<?php }}?>
    </section>
    <!-- /.content -->
  </div>