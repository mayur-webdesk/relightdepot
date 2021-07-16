<div class="clearfix">
</div>
	<div class="page-container">
	<?php echo $left_nav; ?>
	<div class="page-content-wrapper">
		<div class="page-content">
			<div class="row">
				<div class="col-md-12">
					<h3 class="page-title">
						 Bigcommerce to Bigcommerce Blogs
					</h3>
					<ul class="page-breadcrumb breadcrumb">
						<li>
							<i class="fa fa-home"></i>
							<a href="<?php echo $this->config->site_url(); ?>/admin/dashboard">
								<?php echo $this->lang->line('HOME'); ?>
							</a>
						</li>
						<li>
							 Bigcommerce to Bigcommerce Blogs
						</li>
					</ul>
					<!-- END PAGE TITLE & BREADCRUMB-->
				</div>
			</div>
			<div class="note note-success">
				<h4>Importent Notes:</h4>
				<p><b>Step 1:</b> Empty blog table <a href="<?php echo $this->config->site_url(); ?>/admin/blog/empty_table"> Click here</a></p>
				<p><b>Step 2:</b> Import blog in database<a href="<?php echo $this->config->site_url(); ?>/admin/blog/importblogdb"> Click here</a></p>
			</div>
			<div class="row" > 
					<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
						<div class="dashboard-stat green">
							<div class="visual">
								<i class="glyphicon glyphicon-import"></i>
							</div>
							<div class="details">
								<div class="number">
									 <?php echo $total_blogs; ?>
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
									 <?php echo $total_blogs; ?>
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
					<div class="portlet box green"">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-globe"></i>Blogs
								<span><?php echo $total_blogs; ?> / </span>
								<span id="total_imported_product">0</span>
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr>
										<th width="10%">
											BlogID
										</th>
										<th width="40%">
											Blog Name
										</th>
										<th width="30%">
											 Status
										</th>
									</tr>
								</thead>
								<tbody>
									<?php 
                                        if (isset($blogs_data) && !empty($blogs_data) && count($blogs_data) > 0) {
                                            $no = 1;
                                            foreach ($blogs_data as $blogs_data_s) {
                                                ?>
												<tr <?php if ($no == 1) {
                                                    echo 'class="start_process"';
                                                } ?>  data-code="<?php echo $blogs_data_s['ID']; ?>"  >
													<td><?php ++$no;
                                                echo $blogs_data_s['ID']; ?></td>
													<td><?php echo $blogs_data_s['post_title']; ?></td>
													<td class="numeric respose_tag">Pending</td>
												</tr>
												<?php
                                            }
                                        } else {
                                            ?>
											<tr>
												<td  colspan="3" class="numeric respose_tag">Please try again</td>
											</tr>
									<?php
                                        } ?>
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
			url: '<?php echo $this->config->site_url(); ?>/admin/blog/Importblogs',
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