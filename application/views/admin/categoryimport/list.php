<div class="clearfix">
</div>
	<div class="page-container">
	<?php echo $left_nav;?>
	<div class="page-content-wrapper">
		<div class="page-content">
			<div class="row">
				<div class="col-md-12">
					<h3 class="page-title">
						Magento To BC Category Import
					</h3>
					<ul class="page-breadcrumb breadcrumb">
						<li>
							<i class="fa fa-home"></i>
							<a href="<?php echo $this->config->site_url();?>/admin/categoryimport">
								<?php echo $this->lang->line('HOME');?>
							</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							 Magento To BC Category Import
						</li>
					</ul>
					<!-- END PAGE TITLE & BREADCRUMB-->
				</div>
			</div>
			
			<div class="row" > 
					<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
						<div class="dashboard-stat green">
							<div class="visual">
								<i class="glyphicon glyphicon-import"></i>
							</div>
							<div class="details">
								<div class="number">
									 <?php echo $total_category; ?>
								</div>
								<div class="desc">
									 Start Import
								</div>
							</div>
							<a class="more" href="#" onclick="return sendRquest()">
								 Start Import <i class="m-icon-swapright m-icon-white"></i>
							</a>
						</div>
					</div>
					<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="display:none" id="start_stop_controller">
						<div class="dashboard-stat green">
							<div class="visual">
								<i class="glyphicon glyphicon-play" id="start_stop_action"></i>
							</div>
							<div class="details">
								<div class="number">
									 <?php echo $total_category ?>
								</div>
								<div class="desc">
									 Pause
								</div>
							</div>
							<a class="more" href="#" onclick="return pauseRquest()">
								 Pause <i class="m-icon-swapright m-icon-white"></i>
							</a>
						</div>
					</div>
					<input type="hidden" name="request_action" id="request_action" value="start"  />
			</div>
			 <div class="row">
				<div class="col-md-12">
					<!-- BEGIN EXAMPLE TABLE PORTLET-->
					<div class="portlet box light-grey">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-globe"></i>Category import
								<span><?php echo $total_category ?> / </span>
								<span id="total_imported_product">0</span>
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr>
										<th width="2%">
											 CategoryID
										</th>
										<th width="30%">
											 Category Name
										</th>
										<th width="3%">
											 ParentID
										</th>
										<th width="35%">
											 Status
										</th>
									</tr>
								</thead>
								<tbody>
									<?php 
										
										if(isset($category_tree_data) && !empty($category_tree_data) && count($category_tree_data) > 0)
										{
											$no=1;
											foreach($category_tree_data as $category_tree_data_s) 
											{	
												?>
												<tr <?php if($no==1) { echo 'class="start_process"'; } ?>  data-code="<?php echo $category_tree_data_s['category_id'] ?>"  >
													<td><?php $no++; echo ($category_tree_data_s['category_id']);?></td>
													<td><?php echo ($category_tree_data_s['name']);?></td>
													<td><?php echo  $category_tree_data_s['parent_id'];?></td>
													<td class="numeric respose_tag">Pending</td>
												</tr>
												<?php 
											}
										}else{ ?>
											<tr>
												<td  colspan="3" class="numeric respose_tag">Please try again</td>
											</tr>
									<?php } ?>
							    </tbody>
							</table>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
</div>


<script language="javascript">
function pauseRquest()
{
	if(jQuery('#request_action').val()=='start'){
		jQuery('#request_action').val('stop');			
		jQuery('#start_stop_action').removeClass('glyphicon-pause');
		jQuery('#start_stop_action').addClass('glyphicon-play');										
	}else{
		sendRquest();
		jQuery('#request_action').val('start');
		jQuery('#start_stop_action').removeClass('glyphicon-play');
		jQuery('#start_stop_action').addClass('glyphicon-pause');										
	}
	return false;
}
function sendRquest()
{
	jQuery('#start_stop_controller').show();
	jQuery('#start_stop_action').removeClass('glyphicon-play');
	jQuery('#start_stop_action').addClass('glyphicon-pause');										
	var code=jQuery('.start_process').attr('data-code');
	if(code){
		jQuery('.processing').removeClass('processing');
		jQuery('.start_process').find('.respose_tag').html('Please wait...');
		jQuery('.start_process').addClass('processing');
		$.ajax({
			url: '<?php echo $this->config->site_url();?>/admin/categoryimport/ImportCategory',
			data: {
				code: code,
				send:'yes'
			},
			error: function() {
				var obj=jQuery('.start_process');
				obj.addClass('error');
				obj.next().addClass('start_process');
					obj.removeClass('start_process');
					sendRquest();
			},
			success: function(data) {
				$('#total_imported_product').html( eval($('#total_imported_product').html())+1);
				var obj=jQuery('.start_process');
					obj.find('.respose_tag').html(data);
					obj.next().addClass('start_process');
					obj.removeClass('start_process');
						obj.addClass('completed');
					
					if(jQuery('#request_action').val()=='start'){
						sendRquest();
					}
			},
			type: 'GET'
		});
	}
	return false;
}
</script>