<div class="page-sidebar-wrapper">
		 <div class="page-sidebar navbar-collapse collapse">
			<!-- add "navbar-no-scroll" class to disable the scrolling of the sidebar menu -->
			<!-- BEGIN SIDEBAR MENU -->
			 <ul class="page-sidebar-menu  page-header-fixed " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
             	<li class="sidebar-toggler-wrapper hide">
					<div class="sidebar-toggler">
						<span></span>
					</div>
				</li>
				<li class="sidebar-search-wrapper">
					<!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
					<form class="sidebar-search" action="extra_search.html" method="POST">
						<div class="form-container">
						</div>
					</form>
					<!-- END RESPONSIVE QUICK SEARCH FORM -->
				</li>
				<!---=============== MENU PART START ================--->
				<!---=============== DASHBOARD ================--->
				<li class="nav-item start <?php if($this->router->class =='dashboard')echo 'active';?>">
					<a class="nav-link" href="<?php echo $this->config->site_url();?>/admin/dashboard">
						<i class="icon-home"></i>
						<span class="title">
							<?php echo $this->lang->line('DASHBOARD_MENU');?>
						</span>
					</a>
				</li>				 
				<li class="nav-item start <?php if($this->router->class =='category')echo 'active';?>">
					<a class="nav-link" href="<?php echo $this->config->site_url();?>/admin/category">
						<i class="fa fa-sitemap"></i>
						<span class="title">
							 Category Import 
					 	</span>
					</a>
				</li>
				<li class="nav-item start <?php if($this->router->class =='product')echo 'active';?>">
					<a class="nav-link" href="<?php echo $this->config->site_url();?>/admin/product">
						<i class="fa fa-sitemap"></i>
						<span class="title">
							Product Import 
						</span>
					</a>
				</li>
				<li class="nav-item <?php if($this->router->class =='setting')echo 'active open';?>">
					<a href="javascript:;" class="nav-link nav-toggle">
						<i class="fa fa-wrench"></i>
						<span class="title"> 
							<?php echo $this->lang->line('CONFIGRATION_MENU');?>
							
						</span>
						<span class="arrow ">
						</span>
					</a>
					<ul class="sub-menu">
						
						<li class="<?php if($this->router->class =='setting')echo 'active';?>">
							
							<a href="<?php echo $this->config->site_url();?>/admin/setting/">
								<i class="icon-settings"></i>
								<?php echo $this->lang->line('GENERAL_SETTING_MENU');?>
							</a>
						</li>
					</ul>
				</li>				
			<!---=============== MENU PART END ================--->
			</ul>
			<!-- END SIDEBAR MENU -->
		</div>
</div>