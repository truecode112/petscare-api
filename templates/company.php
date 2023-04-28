<?php
 $companies = Company::all();
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Company
        <!--<small>List of registered companies</small>--->
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Company</a></li>
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
              <table id="example2" class="table table-striped table-bordered dt-responsive nowrap companylist">
                <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Contact Number</th>
                  <th>Company Picture</th>
                  <th>Webiste</th>
                  <th>Adress</th>
                  <th>About</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
				<?php foreach( $companies as $key => $value) { 
				if(!empty($value->company_image)){
						$img = "<img src = '$url/uploads/company-profiles/$value->company_image' width=50 height=50 >";
					}else{
						$img = "<img src = '$url/uploads/company-profiles/default-50x50.gif' width=50 height=50 >";
					}
				
				if($value->is_active == 1){
					$status = '<button class="btn btn-success btn-sm">Active</button>';
				}else{
					
					$status = '<button class="btn btn-danger btn-sm">Pending</button>';
				}
                echo "<tr>
                  <td><a href='$url/api/company/$value->company_id/details'>$value->company_name</a></td>
                  <td>$value->emailid</td>
                  <td>$value->contact_number</td>
                  <td>$img</td>
                  <td>$value->website</td>
                  <td>$value->address</td>
                  <td>$value->about</td>
                  <td>$status</td>
                  <td><a href='$value->company_id/delete/company' id='delete'>delete</a></td>
                </tr>";
                }
				?>
                </tbody>
             <!--   <tfoot>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Contact Number</th>
                  <th>Company Picture</th>
                  <th>Webiste</th>
                  <th>Adress</th>
                  <th>About</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
                </tfoot>--->
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

