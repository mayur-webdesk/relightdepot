<?php
$admin_session = $this->session->userdata('admin_session');
?>
<div class="clearfix">
</div>
<div class="page-container">
	<?php echo $left_nav; ?>
	<div class="page-content-wrapper">
		<div class="page-content">
			<div class="row">
				<div class="col-md-12">
					<ul class="page-breadcrumb breadcrumb">
						<li>
							<i class="fa fa-home"></i>
							<a href="<?php echo $this->config->site_url(); ?>/admin/dashboard">
								<?php echo $this->lang->line('HOME'); ?>
							</a>
						</li>
						<li>
							<a href="javascript:void(0)">
								<?php echo $this->lang->line('CONFIGRATION_MENU'); ?>
							</a>
						</li>
						<li>
							<?php echo $this->lang->line('GENERAL_SETTING_MENU'); ?>
						</li>
					</ul>
					<!-- END PAGE TITLE & BREADCRUMB-->
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="tabbable-line boxless tabbable-reversed">
						<ul class="nav nav-tabs">
							<li class="active">
								<a href="#tab_0" data-toggle="tab"> General Settings </a>
							</li>
						</ul>
						<div class="tab-content">
							 <?php if ($success == 1): ?>
							<div class="alert alert-success">
								<button class="close" data-dismiss="alert">×</button>
								<strong><?php echo $this->lang->line('GENERAL_SUCC'); ?></strong>
							</div>
							<?php endif; ?>
							
							<?php 
							$success_u = $this->session->userdata('succe');
							if ($success_u == 1): ?>
							<div class="alert alert-success">
								<button class="close" data-dismiss="alert">×</button>
								<strong><?php echo $this->lang->line('GENERAL_SUCC'); ?></strong>
							</div>
							<?php $this->session->unset_userdata('succe'); endif; ?>
							 <div class="tab-pane active" id="tab_0">
								<div class="portlet box green ">
									<div class="portlet-title">
										<div class="caption">
											<i class="icon-settings"></i> General Settings
										</div>
									</div>
									<div class="portlet-body form">
										<form action="<?php echo $this->config->site_url(); ?>/admin/setting" id="setting" name="setting" class="form-horizontal"  method="post" enctype="multipart/form-data">
											<div class="form-body">
												<div class="alert alert-danger display-hide">
													<button class="close" data-close="alert"></button>
													<?php echo $this->lang->line('FORM_ERROR'); ?>
												</div>
												<h3 class="form-section">BigCommerce From Details</h3>
												<div class="form-group">
													<label class="control-label col-md-3">Store URL</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa"></i>
															<input value="<?php echo $settingdata["storeurl"] ?>" id="storeurl" name="storeurl" type="text" class="form-control">
														</div>	
													</div>
												</div>
												<div class="form-group">
													<label class="control-label col-md-3">Store Front URL</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa"></i>
															<input value="<?php echo $settingdata["store_front_url"] ?>" id="store_front_url" name="store_front_url" type="text" class="form-control">
														</div>	
													</div>
												</div>
												<div class="form-group">
													<label class="control-label col-md-3">API Username</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa"></i>
															<input value="<?php echo $settingdata["apiusername"] ?>" id="apiusername" name="apiusername" type="text" class="form-control">
														</div>	
													</div>
												</div>
												<div class="form-group">
													<label class="control-label col-md-3">API Path</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa"></i>
															<input value="<?php echo $settingdata["apipath"] ?>" id="apipath" name="apipath" type="text" class="form-control">
														</div>	
													</div>
												</div>
												<div class="form-group">
													<label class="control-label col-md-3">Access Token</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa"></i>
															<input value="<?php echo $settingdata["apitoken"] ?>" id="apitoken" name="apitoken" type="text" class="form-control">
														</div>	
													</div>
												</div>	
												<div class="form-group">
													<label class="control-label col-md-3">Store Hash</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa"></i>
															<input value="<?php echo $settingdata["storehash"] ?>" id="storehash" name="storehash" type="text" class="form-control">
														</div>	
													</div>
												</div>		
												<div class="form-group">
													<label class="control-label col-md-3">Client Id</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa"></i>
															<input value="<?php echo $settingdata["client_id"] ?>" id="client_id" name="client_id" type="text" class="form-control">
														</div>	
													</div>
												</div>
												<div class="form-group">
													<label class="control-label col-md-3">Client Secret</label>
													<div class="col-md-8">
														<div class="input-icon right">
															<i class="fa"></i>
															<input value="<?php echo $settingdata["client_secret"] ?>" id="client_secret" name="client_secret" type="text" class="form-control">
														</div>	
													</div>
												</div>		
												<h3 class="form-section">Store Logo</h3>
												<div class="form-group">
													<label class="control-label col-md-3">Store Logo</label>
													<div class="col-md-8">
														<span class="imagelist" id="files">
															<?php if (isset($settingdata['logo_image']) && !empty($settingdata['logo_image'])) {
																?>
															<div class="image_maindiv" id="<?php echo $settingdata['logo_image']; ?>" style="display:block">
																	<img  src='<?php echo $this->config->base_url(); ?>application/uploads/sitelogo/thumb200/<?php echo $settingdata['logo_image']; ?>' border="0" alt="<?php echo $settingdata['logo_image']; ?>" class="group2">
																	<a class="btn red delete image_removediv" onclick="removeimage('<?php echo $settingdata['logo_image']; ?>')" ><i class="fa fa-trash"></i><span> Delete</span></a>
															</div>
															<?php
															} ?>
														</span>
														<button id="banner_img" class="btn">Upload</button>
														<span id="status"></span>
													</div>	
												</div>
												<div class="form-actions">
													<div class="row">
														<div class="col-md-12">
															<div class="col-md-offset-3 col-md-9">
																<button type="submit" class="btn green"><i class="fa fa-check"></i> <?php echo $this->lang->line('SAVE'); ?></button>
																<button onclick="window.location='<?php echo $this->config->site_url(); ?>/admin/dashboard'" type="button" class="btn default"><?php echo $this->lang->line('CANCEL'); ?></button>
															</div>
														</div>
													</div>
												</div>
											</div>
										</form>
									</div>
								</div>
							</div>      
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script language="javascript" type="text/javascript">
jQuery(document).ready(function() { 
	var btnUpload=$('#banner_img');
	var status=$('#status');
	new AjaxUpload(btnUpload, {
	action: '<?php echo $this->config->site_url(); ?>/admin/setting/ajaxupload/',
	name: 'uploadfile[]',
	multiple: false,
	onSubmit: function(file, ext)
	{
	
	if (! (ext && /^(jpg|png|jpeg|gif)$/.test(ext))){ 
    status.text('Only JPG, PNG or GIF files are allowed');
	return false;
	}status.html('<img src="<?php echo $this->config->base_url(); ?>/assets/img/loader.gif">');
	},
	onComplete: function(file, response)
	{
		status.html('');
		status.text('');
		var responseObj = jQuery.parseJSON(response);
		if(responseObj.status=="success")
		{
			var images_data = responseObj.success_data.original;
			
			$.each(images_data,function(index, value ){
				var  imagename = "'"+value.file_name+"'";
				$('#files').html(''); 
				$('<span></span>').appendTo('#files').html('<div class="image_maindiv" id="'+value.file_name+'" style="display:block"><img src="<?php echo $this->config->base_url().'application/uploads/sitelogo/thumb200/'; ?>'+value.file_name+'" alt=""  /><a class="btn red delete image_removediv" onclick="removeimage('+imagename+')" ><i class="fa fa-trash"></i><span> Delete</span></a><input type="hidden" name="banner_images[]" value="'+value.file_name+'" />').addClass('success');
			});
			
		}
		else
		{
			$('<span></span>').appendTo('#files').text(response.error_data).addClass('error');
		}
	}});
});

function removeimage(str)
{
  var status=$('#status');
  status.html('<img src="<?php echo $this->config->base_url(); ?>/assets/img/loader.gif">');
  if (window.XMLHttpRequest)
  {
  xmlhttp=new XMLHttpRequest();
  }
  else
  {
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function()
  {
	 if (xmlhttp.readyState==4 && xmlhttp.status==200)
	{
		 status.html('');
		 $("#banner_images").val('');
		 document.getElementById(str).style.display="none";
	}
  }
  var url="<?php echo $this->config->site_url(); ?>/admin/setting/ajaxdelete";
  url=url+"?imgname="+str;
  xmlhttp.open("GET",url,true);
  xmlhttp.send();
  return false;

}
</script>
<style>
.imagelist .image_maindiv > img {
    border: 1px solid #c2cad8;
    border-radius: 5px;
    display: inline-block;
    padding: 10px;
}
.imagelist .image_removediv {
    margin-bottom: 10px;
    margin-top: 10px;
}
</style>