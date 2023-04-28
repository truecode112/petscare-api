<header class="main-header">
    <!-- Logo -->
    <a href="#" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <!--<span class="logo-mini"><b>P</b>HB</span>-->
      <span class="logo-mini"><img src="<?php echo $url;?>/templates/dist/img/company.png" width="20"height="20"></span>
      <!-- logo for regular state and mobile devices -->
    <!--  <span class="logo-lg"><b>Pets</b>HUB</span>-->
	 <div class="logo-lg"><img src="<?php echo $url;?>/templates/dist/img/company.png"></div>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
          
          <!-- Notifications: style can be found in dropdown.less -->
          
          <!-- Tasks: style can be found in dropdown.less -->
         <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo $url;?>/uploads/user-profiles/1507664260-aaaaaa.jpg" class="user-image" alt="">
              <span class="hidden-xs"><?php if(isset($_SESSION)){echo $_SESSION['user'];}?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="<?php echo $url;?>/uploads/user-profiles/1507664260-aaaaaa.jpg" class="img-circle" alt="User Image">

                <p>
                  <?php if(isset($_SESSION)){echo $_SESSION['user'];}?>
                  <!--<small>Member since Nov. 2012</small>-->
                </p>
              </li>
              <!-- Menu Body -->
              
              <!-- Menu Footer-->
              <li class="user-footer">
              <!--  <div class="pull-left">
                  <a href="#" class="btn btn-default btn-flat">Profile</a>
                </div>-->
                <div class="pull-right">
                  <a href="<?php echo $url;?>/api/logout" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
         <!-- <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li>--->
        </ul>
      </div>
    </nav>
  </header>