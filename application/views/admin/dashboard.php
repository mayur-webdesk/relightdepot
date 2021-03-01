<?php 
$permission_data = $this->session->userdata('user_session_permission_data');
$permission_data = @$permission_data['permission_data'];
$admin_session 	 = $this->session->userdata('admin_session');
?>
<div class="clearfix">
</div>
<!-- BEGIN CONTAINER -->
<div class="page-container">
	<!-- BEGIN SIDEBAR -->
	<?php echo $left_nav;?>
	<!-- END SIDEBAR -->
<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
	<div class="page-content">
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN PAGE TITLE & BREADCRUMB-->
					
					<ul class="page-breadcrumb breadcrumb">
						<li>
							<i class="fa fa-home"></i>
							<a href="<?php echo $this->config->site_url();?>/admin/dashboard">
								<?php echo $this->lang->line('HOME');?>
							</a>
							<!--<i class="fa fa-angle-right"></i>-->
						</li>
						<li>
							<?php echo $this->lang->line('DASHBOARD_MENU');?>
						</li>
					</ul>
					<!-- END PAGE TITLE & BREADCRUMB-->
				</div>
			</div>	
			<div class="row">
				<?php if(isset($admin_session['username']) && !empty($admin_session['username']) && $admin_session['username'] == 'vvuser'){?>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat green">
						<div class="visual">
							<i class="fa fa-wrench"></i>
						</div>
						<div class="details">
							<div class="number">
								&nbsp;
							</div>
							<div class="desc">
								Settings 
							</div>
						</div>
						<a href="<?php echo $this->config->site_url();?>/admin/setting" class="more">
							 View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
				<?php } ?>
				<?php if(isset($admin_session['username']) && !empty($admin_session['username']) && $admin_session['username'] == '3dtobc'){?>
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat green">
						<div class="visual">
							<i class="fa fa-wrench"></i>
						</div>
						<div class="details">
							<div class="number">
								&nbsp;
							</div>
							<div class="desc">
								Settings 
							</div>
						</div>
						<a href="<?php echo $this->config->site_url();?>/admin/setting" class="more">
							 View more <i class="m-icon-swapright m-icon-white"></i>
						</a>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		
	</div>
<!-- END CONTENT -->
</div>
</div>