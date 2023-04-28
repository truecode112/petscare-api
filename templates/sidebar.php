<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?php echo $url;?>/uploads/user-profiles/1507664260-aaaaaa.jpg" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?php if(isset($_SESSION)){echo $_SESSION['user'];}?></p>
          <!--<a href="#"><i class="fa fa-circle text-success"></i> Online</a>--->
        </div>
      </div>
      <!-- search form -->
     <!-- <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Search...">
          <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form>--->
      <!-- /.search form -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu" data-widget="tree">
        <!--<li class="header">MAIN NAVIGATION</li>-->
        <li class="">
          <a href="<?php echo $url;?>/api/admin">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>  
          </a>
        </li>
        <li class="active ">
          <a href="<?php echo $url;?>/api/clients">
            <i class="fa  fa-users"></i> <span>Client</span>  
          </a>
        </li>  
		<li class="active ">
          <a href="<?php echo $url;?>/api/companies">
            <i class="fa fa-briefcase"></i> <span>Company</span>  
          </a>
        </li>        
        
        
        
        
        
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>
