<?php 
session_start();
include('config.php');

$seleect_product = mysql_query("SELECT * FROM shopify_prouct_vetrix_tabel WHERE status IN('no') AND Manufacturer IN('TOURMASTER','GMAX','ZOAN','FLY RACING','JUST 1') AND Discontinued = 'N'");
$total_products  = mysql_num_rows($seleect_product);
include('header.php') ?>
<style> .processing td{background-color:green;font-color:white}</style>
 
			<!-- END STYLE CUSTOMIZER -->
			<!-- BEGIN PAGE HEADER-->
      		<div class="row">
				<div class="col-md-12">
					<!-- BEGIN PAGE TITLE & BREADCRUMB-->
					<h3 class="page-title">Shopify Product Import</h3>
					<ul class="page-breadcrumb breadcrumb">
						<li>
							<i class="fa fa-home"></i>
							<a href="#">
								Home
							</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
							Shopify Product Migrate 
						</li>
						<li class="pull-right">
							<div id="dashboard-report-range" class="dashboard-date-range tooltips" data-placement="top" data-original-title="Change dashboard date range">
								<i class="fa fa-calendar"></i>
								<span>
								</span>
								<i class="fa fa-angle-down"></i>
							</div>
						</li>
					</ul>
					<!-- END PAGE TITLE & BREADCRUMB-->
				</div>
			</div>
			<!-- END PAGE HEADER-->
			<!-- BEGIN DASHBOARD STATS -->
            <div style="clear:both"></div>            
			
			
            
            <?php //if(isset($url)){?>
				<div class="row" style="margin-top:20px;" > 
				
				<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
					<div class="dashboard-stat green">
						<div class="visual">
							<i class="glyphicon glyphicon-import"></i>
						</div>
						<div class="details">
							<div class="number">
								 <?php echo $total_products ?>
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
								 <?php echo $total_products ?>
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
				
				
			<div class="row">
			<div class="col-md-12">
			<div class="portlet box green">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-cogs"></i>Product List 
								<span><?php echo $total_products ?> / </span>
								<span id="total_imported_product">0</span>
							</div>
							<div class="tools">
								<a href="javascript:;" class="collapse">
								</a>
								<a href="#portlet-config" data-toggle="modal" class="config">
								</a>
								<a href="javascript:;" class="reload">
								</a>
								<a href="javascript:;" class="remove">
								</a>
							</div>
						</div>
						<div class="portlet-body flip-scroll">
							<table class="table table-bordered table-striped table-condensed flip-content">
							<thead class="flip-content">
							<tr>
								<th width="5%">
									 #
								</th>
								<th width="20%">
									Product SKU
								</th>
								<th width="20%">
									Product Name
								</th>
								<th class="numeric" width="15%">
									 Status
								</th>
							</tr>
							</thead>
							<tbody>
							<?php
							if(count($total_products) > 0)
							{
								$no=1;
								$product_data = array();
								while($result_product = mysql_fetch_object($seleect_product)){
								
									$product_data[$result_product->Item_Description_1]['product_sku'] = $result_product->Item;
									$product_data[$result_product->Item_Description_1]['title'] 	  = $result_product->Item_Description_1;
									$product_data[$result_product->Item_Description_1]['chid_sku'][]  = $result_product->Item;
								}
								foreach($product_data as $product_data_s){?>
									<tr <?php if($no==1) { echo 'class="start_process"'; } ?> data-child="<?php echo base64_encode(serialize($product_data_s['chid_sku']));?>"  data-code="<?php echo $product_data_s['product_sku']; ?>"  >
										<td><?php echo $no++ ;?></td>
										<td><?php echo($product_data_s['product_sku'])?></td>
										<td><?php echo($product_data_s['title'])?></td>
										<td class="numeric respose_tag">Pending</td>
									</tr>
								<?php }
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
			
			
			
            <?php //} ?>
			<!-- END DASHBOARD STATS -->
<script language="javascript">
function pauseRquest(){
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
function sendRquest(){
	jQuery('#start_stop_controller').show();
	jQuery('#start_stop_action').removeClass('glyphicon-play');
	jQuery('#start_stop_action').addClass('glyphicon-pause');										
	var code = jQuery('.start_process').attr('data-code');
	var child_data = jQuery('.start_process').attr('data-child');
	if(code){
		jQuery('.processing').removeClass('processing');
		jQuery('.start_process').find('.respose_tag').html('Please wait...');
		jQuery('.start_process').addClass('processing');
		$.ajax({
		url: 'product_ajax.php',
		data: {
			code: code,
			child_data: child_data,
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
		
			
<?php include('footer.php') ?>            