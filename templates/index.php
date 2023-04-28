<?php
//session_set_cookie_params(3600);
//session_start();
$_SESSION['previous_url'] = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:NULL;
        if (!in_array($_SERVER['SERVER_NAME'], array('192.168.2.150', 'localhost')) && (isset($_SERVER['HTTP_CLIENT_IP']) || isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !(in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '192.168.2.150', 'fe80::1', '::1')) || php_sapi_name() === 'cli-server'))) {
            $url = "http://18.220.158.43/petscare";
        } else {
            $url = "http://192.168.2.150:8989";
        }
		
if(isset($_SESSION['user'])){
	//	print_r($_SESSION);	
		//flash['user'];
?> 
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>PetsHub | Dashboard</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="<?php echo $url;?>/templates/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo $url;?>/templates/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?php echo $url;?>/templates/bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo $url;?>/templates/dist/css/AdminLTE.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="<?php echo $url;?>/templates/dist/css/skins/_all-skins.min.css">
  <!-- Morris chart -->
  <link rel="stylesheet" href="<?php echo $url;?>/templates/bower_components/morris.js/morris.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="<?php echo $url;?>/templates/bower_components/jvectormap/jquery-jvectormap.css">
  <!-- Date Picker -->
  <!--<link rel="stylesheet" href="<?php //echo $url;?>/templates/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">-->
  <!-- Daterange picker -->
  <!--<link rel="stylesheet" href="<?php// echo $url;?>/templates/bower_components/bootstrap-daterangepicker/daterangepicker.css">-->
  <!-- bootstrap wysihtml5 - text editor -->
  <!--<link rel="stylesheet" href="<?php //echo $url;?>/templates/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">-->
<!-- DataTables -->
  <link rel="stylesheet" href="<?php echo $url;?>/templates/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.0/css/responsive.bootstrap.min.css">	

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <!-- Header -->
	<?php require("header.php");?>
  <!-- / Header -->
 
  <!-- Left side column. contains the logo and sidebar -->
	<?php require("sidebar.php");?>
  <!-- /Left side column. contains the logo and sidebar -->
 
  <!-- Content Wrapper. Contains page content -->
	<?php
		
			
			if($page == 'dashboard'){
				require("dashboard.php");
			}else if($page == 'company'){
				require("company.php");
			}else if($page == 'client'){
				require("client.php");
			}else {
				require("detail.php");
			}
		
	?>
  <!-- /Content Wrapper. Contains page content -->
  
  <!-- Footer -->
	<?php require("footer.php");?>
  <!-- /Footer -->

  <!-- Control Sidebar -->
  
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- jQuery 3 -->
<script src="<?php echo $url;?>/templates/bower_components/jquery/dist/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?php echo $url;?>/templates/bower_components/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.7 -->
<script src="<?php echo $url;?>/templates/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Morris.js charts -->
<script src="<?php echo $url;?>/templates/bower_components/raphael/raphael.min.js"></script>
<script src="<?php echo $url;?>/templates/bower_components/morris.js/morris.min.js"></script>
<!-- Sparkline -->
<script src="<?php echo $url;?>/templates/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script>
<!-- jvectormap -->
<script src="<?php echo $url;?>/templates/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="<?php echo $url;?>/templates/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<!-- jQuery Knob Chart -->
<script src="<?php echo $url;?>/templates/bower_components/jquery-knob/dist/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<!--<script src="<?php //echo $url;?>/templates/bower_components/moment/min/moment.min.js"></script>-->
<!--<script src="<?php //echo $url;?>/templates/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>-->
<!-- datepicker -->
<!--<script src="<?php //echo $url;?>/templates/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>-->
<!-- Bootstrap WYSIHTML5 -->
<!--<script src="<?php// echo $url;?>/templates/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>-->
<!-- Slimscroll -->
<script src="<?php echo $url;?>/templates/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="<?php echo $url;?>/templates/bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo $url;?>/templates/dist/js/adminlte.min.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="<?php echo $url;?>/templates/dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="<?php echo $url;?>/templates/dist/js/demo.js"></script>
<!-- DataTables -->
<script src="<?php echo $url;?>/templates/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo $url;?>/templates/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.0/js/responsive.bootstrap.min.js"></script>
<script>
  $(function () {
	  
	    var url = window.location.pathname,
    urlRegExp = new RegExp(url.replace(/\/$/, '') + "$"); 
	var user = url.split('/').reverse()[2];
	var fn = url.split('/').reverse()[0];
	
	
        $('ul.sidebar-menu  li  a').each(function (index, value) {
            if (urlRegExp.test(this.href.replace(/\/$/, ''))) {
                $(this).parent().addClass('active');
				//alert(this.href.replace(/\/$/, ''));
            }
			if(fn=='details')
			{
				if(user=='clients' && index == 1){
					$(this).parent().addClass('active');
				}else if(user=='company' && index == 2){
					$(this).parent().addClass('active');
				}
				
			}
        });
	  
	  
    $('#example1').DataTable()
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': true,
      'searching'   : true,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })
  
  
  $(".clientlist > tbody tr").find("td:eq(7) > #delete").click(function(){
	var x = confirm("Are you sure you want to delete?");
  if (x)
      return true;
  else
    return false;
  });
$(".companylist > tbody tr").find("td:eq(8) > #delete").click(function(){
	var x = confirm("Are you sure you want to delete?");
  if (x)
      return true;
  else
    return false;
  });
</script>
</body>
</html>
<?php
}else{
	//$_SESSION['previous_url'] = $_SERVER['HTTP_REFERER'];
	require("login.php");
}
?>