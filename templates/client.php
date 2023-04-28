<?php
 $client = Client::all(); 
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
       Client
        <!--<small>All registered client</small>-->
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Client</a></li>
        <li class="active">List</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          

          <div class="box">
          
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example2" class="table table-striped table-bordered dt-responsive nowrap clientlist">
                <thead>
                <tr>
                  <th>FirstName</th>
                  <th>LastName</th>
                  <th>Email</th>
                  <th>Prifile Picture</th>
                  <th>Contact Number</th>
                  <th>Address</th>
                  <th>Notes</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
				<?php foreach($client as $key => $value) {
					if(!empty($value->profile_image)){
						$img = "<img src = '$url/uploads/user-profiles/$value->profile_image' width=50 height=50 >";
					}else{
						$img = "<img src = '$url/templates/dist/img/default.jpg' width=50 height=50 >";
					}
					
                echo "<tr>
                  <td><a href='$url/api/clients/$value->client_id/details'>$value->firstname</a></td>
                  <td>$value->lastname</td>
                  <td>$value->emailid</td>
                  <td>$img</td>
                  <td>$value->contact_number</td>
                  <td>$value->client_address</td>
                  <td>$value->client_notes</td>
				  <td><a href='$value->client_id/delete/client' id='delete'>delete</a></td>
                </tr>";
                }
				?>
                </tbody>
               <!-- <tfoot>
                <tr>
                  <th>FirstName</th>
                  <th>LastName</th>
                  <th>Email</th>
                  <th>Prifile Picture</th>
                  <th>Contact Number</th>
                  <th>Address</th>
                  <th>Notes</th>
                  <th>Action</th>
                </tr>
                </tfoot>-->
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
 