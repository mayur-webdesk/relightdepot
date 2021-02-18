// Customer check
var baseurl = $("#base_url").val();
//========================================= Step 1 =================================

$( document ).ready(function(){
	setTimeout(function() { $('#order_place_msg').fadeOut();}, 30000 );
	$("#newproductadd").val('');
	$("#add_product_s").hide();
	$('#address_type').val("residential").trigger("change");
	$("#customer_content #customer_exist").hide();
	$('#tab1 input[type="radio"]:last').trigger('click');
	$('#tab3 input[type="radio"]:first').trigger('click');
	
	// Cleare last step value.
	$('#paymentMethod').val("").trigger("change");
	$('#order_status').val("").trigger("change");
	$('#order_comments').val("");
	$('#staff_note').val("");
});

// Check new customer or exist customer
function customer_check(check_val)
{
	if(check_val == 'customer_exist'){
		$('#customer_type_check').val('new_customer');
		$('#email').val('');
		$('#password').val('');
		$('#rpassword').val('');
		$('#customer_group').val('').trigger("change");
		$('#b_first_name').val('');
		$('#b_last_name').val('');
		$('#b_company_name').val('');
		$('#b_phone_number').val('');
		$('#b_address_1').val('');
		$('#b_address_2').val('');
		$('#b_city').val('');
		$('#b_country').val("").trigger("change");
		$('#b_state').val("").trigger("change");
		$('#b_zipcode').val('');
		$('#address_type').val('residential');
		$('#find_exist_customer').val("").trigger("change");
		$("#customer_content #customer_exist").show();
		$("#customer_content #new_customer").hide();
	}else if(check_val == 'new_customer'){
		$('#customer_type_check').val('new_customer');
		$('#email').val('');
		$('#password').val('');
		$('#rpassword').val('');
		$('#customer_group').val('').trigger("change");
		$('#b_first_name').val('');
		$('#b_last_name').val('');
		$('#b_company_name').val('');
		$('#b_phone_number').val('');
		$('#b_address_1').val('');
		$('#b_address_2').val('');
		$('#b_city').val('');
		$('#b_country').val("").trigger("change");
		$('#b_state').val("").trigger("change");
		$('#b_zipcode').val('');
		$('#address_type').val('residential');
		$('#find_exist_customer').val("").trigger("change");
		$("#customer_content #customer_exist").hide();
		$("#customer_content #new_customer").show();
	}
}
// Get country to state
function getStateCustomer(val) {
	$.ajax({
		type: "POST",
		url: baseurl+'index.php/admin/order/getStates',
		data:'country_id='+val,
		success: function(returndata){
			$("#b_state").html(returndata);
		}
	});
}

// Get selected customer details.
function FillCustomerDetails(customer_id) {
	if(customer_id != '-')
	{
		$('#loader_d').css('position','relative');
		$('#loader_e').show();
		$.ajax({
			type:'post',
			dataType: "json",
			url: baseurl+'index.php/admin/order/getCustomer',
			data:{customer_id:customer_id},
			cache:false,
			success: function(returndata){
				$('#loader_d').css('');
				$('#loader_e').hide();
				var email = returndata.email;
				if (typeof returndata != 'undefined') {
					var first_name    = returndata.first_name;
					var last_name  	  = returndata.last_name;
					var company  	  = returndata.company;
					var phone  	      = returndata.phone;
					var street_1  	  = returndata.street_1;
					var street_2  	  = returndata.street_2;
					var city  	      = returndata.city;
					var country  	  = returndata.country;
					var state  	  	  = returndata.state;
					var zip  	 	  = returndata.zip;
					var address_type  = returndata.address_type;
					var customer_group_id  = returndata.customer_group_id;
					
					$('#b_first_name').val(first_name);
					$('#b_last_name').val(last_name);
					$('#b_company_name').val(company);
					$('#b_phone_number').val(phone);
					$('#b_address_1').val(street_1);
					$('#b_address_2').val(street_2);
					$('#b_city').val(city);
					$('#b_country').val(country);
					$('#b_state').val(state);
					$('#b_zipcode').val(zip);
					$('#address_type').val(address_type);
					$('#customer_group').val(customer_group_id);
				}
				$('#email').val(email);
				$('#b_country option[value="'+country+'"]').attr('selected','selected');
				$("#b_country").trigger('change');
				setTimeout(function(){  
					$('#customer_group').val(customer_group_id).trigger("change");
					$('#b_state').val(state).trigger("change");
					$('#b_state option[value="'+state+'"]').attr('selected','selected');
				},1000);
			}
		});
	}
	else
	{
		$('#email').val('');
		$('#password').val('');
		$('#rpassword').val('');
		$('#customer_group').val('').trigger("change");
		$('#b_first_name').val('');
		$('#b_last_name').val('');
		$('#b_company_name').val('');
		$('#b_phone_number').val('');
		$('#b_address_1').val('');
		$('#b_address_2').val('');
		$('#b_city').val('');
		$('#b_country').val("").trigger("change");
		$('#b_state').val("").trigger("change");
		$('#b_zipcode').val('');
		$('#address_type').val('residential');
		$('#find_exist_customer').val("").trigger("change");
	}
}

// Customer details store in session.
$( "#form_wizard_1 .button-next" ).click(function() {
	var tab_1_active = $( "#tab1" ).hasClass( "active" );
	if(tab_1_active == true){
		var customer_type_check = $('#customer_type_check').val();
		var email = $('#email').val();
		var password = $('#password').val();
		var customer_group = $('#customer_group').val();
		var b_first_name = $('#b_first_name').val();
		var b_last_name = $('#b_last_name').val();
		var b_company_name = $('#b_company_name').val();
		var b_phone_number = $('#b_phone_number').val();
		var b_address_1 = $('#b_address_1').val();
		var b_address_2 = $('#b_address_2').val();
		var b_city = $('#b_city').val();
		var b_country = $('#b_country').val();
		var b_state = $('#b_state').val();
		var b_zipcode = $('#b_zipcode').val();
		var address_type = $('#address_type').val();
		if(address_type  == ''){
			address_type = 'residential';
		}
		var dataString = '&customer_type_check=' + customer_type_check + '&email=' + email + '&password=' + password + '&customer_group=' + customer_group + '&b_first_name=' + b_first_name + '&b_last_name=' + b_last_name+ '&b_company_name=' + b_company_name + '&b_phone_number=' + b_phone_number + '&b_address_1=' + b_address_1+ '&b_address_2=' + b_address_2 + '&b_city=' + b_city+ '&b_country=' + b_country + '&b_state=' + b_state + '&b_zipcode=' + b_zipcode + '&address_type=' + address_type;
		$.ajax({
			type: "POST",
			url: baseurl+'index.php/admin/order/StoreCustomerDetails',
			data:dataString,
			success: function(returndata){
				//console.log(returndata);
			}
		});
	}
});

//========================================= Step 2 =================================
$( document ).ready(function(){
	$('#loader_c').hide();
	$('#loader_e').hide();
	$('#loader_g').hide();
	$('#loader_c_f_p').hide();
	getCustomCart();
	$('#discount_section_new').hide();
});

// Add custom product block hide show.																			
$( "#show_toggle_custom_product" ).click(function() {
	if ($("#add_product_s").is(':visible')) {
	  $("#newproductadd").val('');
	}else{
	   $("#newproductadd").val('custom_product');
	   $("#product_target").val('');
	   $("#result").html('');
	}
    $("#add_product_s").toggle();
});

// Get cart details
function getCustomCart() {
	$('#loader_b').css('position','relative');
	$('#loader_c').show();
	$('#loader_b_f').css('position','relative');
	$('#loader_c_f').show();
	 $.ajax({
		type: "GET",
		url: baseurl+'index.php/admin/order/getCustomCart',
		success: function(returndata){
			$('#loader_b').css(''); 
			$('#loader_c').hide();
			$('#loader_b_f').css(''); 
			$('#loader_c_f').hide();
			$("#cart_data").html(returndata); 
		}
	}); 
} 

// Get selected product details
function GetProductDetails(product_id) {
	
	$('#variants_hidden_v').html('');
	$('#product_details').html('');
	$('#loader_b').css('position','relative');
	$('#loader_c').show();
	$('#loader_b_f').css('position','relative');
	$('#loader_c_f').show();
	if(product_id != 0)
	{ 
		$.ajax({
			type:'post',
			url: baseurl+'index.php/admin/order/getProductDetails',
			data:{id:product_id},
			cache:false,
			success: function(returndata){
				$('#loader_b').css('');
				$('#loader_c').hide();
				$('#loader_b_f').css('');
				$('#loader_c_f').hide();
				$('#product_details').html(returndata);
				$('.select2me').select2();
				
			}
		});
	}
}

// select product hide custom product block
$("#product_target").change(function() {
	var control = $(this);
	if (control.val() == "-") {
		$("#product_option").hide(); 
		$("#add_product_s").show(); 
	} else { 
		$("#product_details").show();
		$("#add_product_s").hide(); 					
	}
});
$('#product_target').click(function(e){
	 $("#add_product_s").hide();
});
	
// Add to cart product
$('#add_to_cart').click(function(){
	
	var selectproductornot = $('#newproductadd').val();
	var productname		   = $('#productname').val();
	var productsku 		   = $('#productsku').val();
	var productprice 	   = $('#productprice').val();
	var option_id 	 = '';
	var product_option_id 	 = '';
	var option_price = '';
	var option_title = '';
	var option_price_adjuster = '';
	var product_id 		= '';
	var product_name 		= '';
	var price 		= '';
	var cost_price 		= '';
	var product_qty 		= '';
	var sku 		= '';
	
	if(selectproductornot == 'custom_product')
	{	
		if(productname == '')
		{
			$( "#productname" ).focus();
			alert('Please enter product name.');
			return false;
		}
		if(productsku == '')
		{
			$( "#productsku" ).focus();
			alert('Please enter product SKU.');
			return false;
		}
		if(productprice == '')
		{
			$( "#productprice" ).focus();
			alert('Please enter product price.');
			return false;
		}
		
		if(productname !="" && productsku !="" && productprice !="")
		{
			$('#loader_b').css('position','relative');
			$('#loader_c').show();
			$('#loader_b_f').css('position','relative');
			$('#loader_c_f').show();
			var uniqid		= '';
			uniqid			= Math.floor(Math.random() * 26) + Date.now();
			product_id 		= uniqid;
			product_name  	= $("#productname").val();
			price        	= $("#productprice").val();
			variation_id    = '';
			product_qty     = 1;
			sku     		= $("#productsku").val();
			variation_title = '';
		}
	}
	else
	{	
		
		if($("#product_target").val() == ''){
			$( "#product_target" ).focus();
			alert('Please select product');
			return false;
		}
		
		$('#loader_b').css('position','relative');
		$('#loader_c').show();
		$('#loader_b_f').css('position','relative');
		$('#loader_c_f').show();
		product_id 		= $("#hidden_product_id").val();
		product_name	= $("#hidden_product_name").val();
		price	        = $("#hidden_product_price").val();
		cost_price	    = $("#hidden_product_cost_price").val();
		product_qty     = $("#hidden_product_qty").val();
		sku     		= $("#hidden_product_sku").val();
		
		if(typeof $("#hidden_option_value_id").val() != 'undefined'){
			option_id    	= $("#hidden_option_value_id").val();
		}
		if(typeof $("#hidden_option_id").val() != 'undefined'){
			product_option_id    	= $("#hidden_option_id").val();
		}
		if(typeof $("#hidden_option_price").val() != 'undefined'){
			option_price   	= $("#hidden_option_price").val();
		}
		if(typeof $("#hidden_option_title").val() != 'undefined'){
			option_title 	= $("#hidden_option_title").val();
		}
		if(typeof $("#hidden_option_price_adjuster").val() != 'undefined'){
			option_price_adjuster 	= $("#hidden_option_price_adjuster").val();
		}
	}
	
	if(product_id !="" && product_id != 'undefined'){
		 
		 if(selectproductornot != 'custom_product')
		 {
			 $.ajax({
				type: "POST",
				dataType: "json",
				url: baseurl+'index.php/admin/order/checkproductstock',
				data:{product_id:product_id},
				success: function(prostock_status){
					if(prostock_status.stock != '')
					{
						$.ajax({
							type: "POST",
							url: baseurl+'index.php/admin/order/addProductToCart',
							data:'product_id='+product_id+'&product_name='+product_name+'&price='+price+'&cost_price='+cost_price+'&qty='+product_qty+'&sku='+sku+'&option_id='+option_id+'&product_option_id='+product_option_id+'&option_price='+option_price+'&option_title='+option_title+'&option_price_adjuster='+option_price_adjuster+'&custom_product=no',
							success: function(returndata){
								$("#productname").val('');
								$("#productsku").val('');
								$("#productprice").val('');
								$("#product_target").val('');
								$("#product_details").html('');
								$("#add_product_s").hide();
								$('#newproductadd').val('');
								getCustomCart();
							}
					   }); 
					}else{
						alert("We don't have enough "+prostock_status.product_title+" stock on hand for the quantity you selected. Please try again.");
						$("#productname").val('');
						$("#productsku").val('');
						$("#productprice").val('');
						$("#product_target").val('');
						$("#product_details").html('');
						$("#add_product_s").hide();
						$('#newproductadd').val('');
						getCustomCart();
						return false;
					}
				}
			}); 
		}
		if(selectproductornot == 'custom_product')
		{
			//var product_id = '';
			 $.ajax({
					type: "POST",
					url: baseurl+'index.php/admin/order/addProductToCart',
					data:'product_id='+product_id+'&product_name='+product_name+'&price='+price+'&qty='+product_qty+'&sku='+sku+'&option_id='+option_id+'&option_price='+option_price+'&option_title='+option_title+'&option_price_adjuster='+option_price_adjuster+'&custom_product=yes',
					success: function(returndata){
						$("#productname").val('');
						$("#productsku").val('');
						$("#productprice").val('');
						$("#product_target").val('');
						$("#product_details").html('');
						$("#add_product_s").hide();
						$('#newproductadd').val('');
						getCustomCart();
					}
			  }); 
		}
	}
});

// Get option details change option.
function GetVariantsDetails(option_id) {
	if(option_id != 0)
	{
		$('#loader_b').css('position','relative');
		$('#loader_c').show();
		$('#loader_b_f').css('position','relative');
		$('#loader_c_f').show();
		var product_id = $("#hidden_product_id").val();
		$.ajax({
			type:'post',
			url: baseurl+'index.php/admin/order/getVarationDetails',
			data:{option_id:option_id,product_id:product_id},
			cache:false,
			success: function(returndata){
				$('#loader_b').css('');
				$('#loader_c').hide();
				$('#loader_b_f').css('');
				$('#loader_c_f').hide();
				$('#variants_hidden_v').html(returndata);
			}
		});
	}
}

// Remove cart items
function removecart(remove_id)
{
	var txt;
	var r = confirm("Are you sure to delete this item?");
	if (r == true) {
		$('#loader_b').css('position','relative');
		$('#loader_b_f').css('position','relative');
		$('#loader_c').show();
		$('#loader_c_f').show();
		$.ajax({
			type: "POST",
			url: baseurl+'index.php/admin/order/RemoveCartItem',
			data:'remove_id='+remove_id,
			success: function(returndata){
				$('#loader_b').css('');
				$('#loader_c').hide();
				$('#loader_b_f').css('');
				$('#loader_c_f').hide();
				getCustomCart();
			}
		});
	} else {
		return false;
	}
}
// QTY change to update cart price
function updateqty(update_id,qty) {
	$.ajax({
		type: "POST",
		url: baseurl+'index.php/admin/order/UpdateQty',
		data:'update_id='+update_id+'&qty='+qty,
		success: function(returndata){
			getCustomCart();
		}
	});	
}  

// Price change to update cart price.
function update_order_price(update_id)
{	
	var price = $("#price_edit_"+update_id).val();
	if(typeof price != 'undefined' && price != '')
	{
		var price_f = price.split("$");
		var price_final = '';
		if(typeof price_f[1] != 'undefined' && price_f[1] != '')
		{
			price_final = price_f[1];
		}else{
			price_final = price_f[0];
		}
		$.ajax({
			type: "POST",
			url: baseurl+'index.php/admin/order/UpdatePriceOrder',
			data:'update_id='+update_id+'&price='+price_final,
			success: function(returndata){
				getCustomCart();
			}
		});	
	}else{
		alert("Please enter price.");
		return false;
	}
	
}

// Apply discount code in card
function DiscountApply()
{
	$('#discount_section').hide();
	$('#discount_section_new').hide();
	$('#discount_section_edit').show();
	var discount_title 	= $("#discount_title").val();
	var discount_type  	= $("#discount_type").val();
	var discount_value  = $("#discount_value").val();
	$.ajax({
		type: "POST",
		url: baseurl+'index.php/admin/order/ApplyDiscount',
		data:'discount_title='+discount_title+'&discount_type='+discount_type+'&discount_value='+discount_value,
		success: function(returndata){
			getCustomCart();
		}
	});
}

// Edit discount code
function DiscounEdit()
{
	$('#discount_section_new').show();
	$('#discount_section_edit').hide();
}

// Remove discount code
function RemoveDiscount()
{
	$.ajax({
		type: "POST",
		url: baseurl+'index.php/admin/order/RemoveDiscount',
		data:'',
		success: function(returndata){
			getCustomCart();
		}
	});
}

// Shipping method select
function fillshippingcustom(value,fieldname){
	if(fieldname == 'name'){
		$("#selected_shipping_method_name").val(value);
	}
	if(fieldname == 'price'){
		$("#selected_shipping_method_price").val(value);
	}
}

// Change shipping method
$("#ord_shippind_block input[type='radio']").on("change", function () {
   var select_shipping_val = this.value;
   var select_shipping_price = this.id;
   $("#selected_shipping_method_name").val('');
   $("#selected_shipping_method_price").val('');
  if(select_shipping_val == 'Custom Rate'){
	  $("#custom_shipping").show();
	  $('#custom_rate_name').val('');
	  $('#custom_rate_price').val('');
	
	}else{
	  $("#custom_shipping").hide();
	  $("#selected_shipping_method_name").val(select_shipping_val);
	  $("#selected_shipping_method_price").val(select_shipping_price);
   }
});

// Apply shipping method
$( "#apply_shipping" ).click(function() {
	 var shipping_name = $("#selected_shipping_method_name").val();
	 var shipping_price = $("#selected_shipping_method_price").val();
	 if(shipping_name == '')
	 {
	   $('#shipping_select_error').show();
	   return false;
	 }
	 else
	 {
		$('#shipping_select_error').hide();  
		$('#loader_c').show();
		var shipping_name 	= $("#selected_shipping_method_name").val();
		var shipping_price  = $("#selected_shipping_method_price").val();
		$.ajax({
			type: "POST",
			url: baseurl+'index.php/admin/order/ApplyShippingMethod',
			data:'shipping_title='+shipping_name+'&shipping_price='+shipping_price,
			success: function(returndata){
				$('#shipping').modal('hide');
				getCustomCart();
			}
		});
	 }
});

// Sales tax change store details
$("#sales_tax_d").change(function(){

  var sales_tax_id = '';
  var sales_tax_name = '';
  var sales_tax_rate = '';	
  var element = $(this);
  sales_tax_id = element.val();
  sales_tax_name = $('#sales_tax_d').find('option:selected').attr('ratename');
  sales_tax_rate = $('#sales_tax_d').find('option:selected').attr('rate');
  
  $("#selected_sales_tax_name").val(sales_tax_name);
  $("#selected_sales_tax_id").val(sales_tax_id);
  $("#selected_sales_tax_rate").val(sales_tax_rate);
  
});
// Apply Sales tax
$( "#apply_sales_tax" ).click(function() {
	 var sales_tax_name   = $("#selected_sales_tax_name").val();
	 var sales_tax_id     = $("#selected_sales_tax_id").val();
	 var sales_tax_rate   = $("#selected_sales_tax_rate").val();
	 if(sales_tax_id == '' || sales_tax_id == '-1')
	 {
	   $('#sales_tax_select_error').show();
	   return false;
	 }
	 else
	 {
		$('#sales_tax_select_error').hide();  
		$('#loader_c').show();
		var sales_tax_name   = $("#selected_sales_tax_name").val();
		var sales_tax_id     = $("#selected_sales_tax_id").val();
		var sales_tax_rate   = $("#selected_sales_tax_rate").val();
		$.ajax({
			type: "POST",
			url: baseurl+'index.php/admin/order/ApplySalesTax',
			data:'sales_tax_name='+sales_tax_name+'&sales_tax_id='+sales_tax_id+'&sales_tax_rate='+sales_tax_rate,
			success: function(returndata){
				$('#sales_tax').modal('hide');
				$("#sales_tax_d").val('-1');
				$("#selected_sales_tax_name").val('');
				$("#selected_sales_tax_id").val('');
				$("#selected_sales_tax_rate").val('');
				getCustomCart();
			}
		});
	 }
});

// Remove shipping method
function RemoveShipping()
{
	$.ajax({
		type: "POST",
		url: baseurl+'index.php/admin/order/RemoveShipping',
		data:'',
		success: function(returndata){
			getCustomCart();
		}
	});
}

// Remove sales tax
function RemoveSalestax()
{
	$.ajax({
		type: "POST",
		url: baseurl+'index.php/admin/order/RemoveSalsetax',
		data:'',
		success: function(returndata){
			getCustomCart();
		}
	});
}

// Product autocomplate  search box 
$("#product_target").keyup(function(){
	var search = jQuery(this).val();
	if(typeof search != 'undefined' && search != ''){
		jQuery.ajax({
			url: baseurl+"index.php/admin/order/getProducts",
			type: "post",
			data: {search: search} ,
			success: function (data) {
				$('#loader_c').hide();
				$("#result").html(data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
			   console.log(textStatus, errorThrown);
			}
		});
	}else{
		$('#loader_c').hide();
		$("#result").html('');
	}
}); 

// Get selected product name
function getProducts(id)
{
	$("#result").html('');
	if(typeof id != 'undefined' && id != '')
	{
		$('#product_bc_id').val(id);
		$.ajax({
			url: baseurl+"index.php/admin/order/getProductname",
			type: "post",
			data: {cid: id} ,
			success: function (data) {
				$('#loader_c').hide();
				$("#result").html('');
				$('#product_target').val(data);
				GetProductDetails(id);				
			},
			error: function(jqXHR, textStatus, errorThrown) {
			  console.log(textStatus, errorThrown);
			}
		});
	}else{
		$('#loader_c').hide();
		$("#result").html('');
	}
}

//================================================== Step 3 ==============================================
// Check shipping address
function shipping_address_check(val)
{
	if(val == 'same_as_billing_address'){
		$('#s_first_name').val('');
		$('#s_last_name').val('');
		$('#s_company_name').val('');
		$('#s_phone_number').val('');
		$('#s_address_1').val('');
		$('#s_address_2').val('');
		$('#s_city').val('');
		$('#s_country').val("").trigger("change");
		$('#s_state').val("").trigger("change");
		$('#s_zipcode').val('');
		$('#s_address_type').val('');
		$("#same_as_billing_address").show();
		$("#new_shipping_address").hide();
		$("#shipping_address_new").val('no');
	}
	if(val == 'new_shipping_address'){
		$('#s_first_name').val('');
		$('#s_last_name').val('');
		$('#s_company_name').val('');
		$('#s_phone_number').val('');
		$('#s_address_1').val('');
		$('#s_address_2').val('');
		$('#s_city').val('');
		$('#s_country').val("").trigger("change");
		$('#s_state').val("").trigger("change");
		$('#s_zipcode').val('');
		$('#s_address_type').val('');
		$("#shipping_address_new").val('yes');
		$("#new_shipping_address").show();
		$("#same_as_billing_address").hide();
	}
}

// Get country wise state 
function getshippingStateCustomer(val) {
	$.ajax({
		type: "POST",
		url: baseurl+'index.php/admin/order/getStates',
		data:'country_id='+val,
		success: function(returndata){
			$("#s_state").html(returndata);
		}
	});
}

// Get billing address
$( "#form_wizard_1 .button-next" ).click(function() {
	var tab_2_active = $( "#tab2" ).hasClass( "active" );
	if(tab_2_active == true){
		getcustomerbillinginfo();
	}
});
// Get customer billing information 
function getcustomerbillinginfo()
{
	$.ajax({
		url: baseurl+"index.php/admin/order/GetCustomerBillingDetails",
		type: "GET", 
		success: function (data) {
			$("#customer_biling_details_g").html(data); 			
		},
		error: function(jqXHR, textStatus, errorThrown) {
		  console.log(textStatus, errorThrown);
		}
	});
}

// Store shipping address
$( "#form_wizard_1 .button-next" ).click(function() {
	var tab_3_active = $( "#tab3" ).hasClass( "active" );
	
	if(tab_3_active == true){
		var shipping_new_address = $("#shipping_address_new").val();
		if(typeof shipping_new_address != 'undefined' && shipping_new_address != '' && shipping_new_address == 'yes')
		{
			var s_first_name = $('#s_first_name').val();
			var s_last_name = $('#s_last_name').val();
			var s_company_name = $('#s_company_name').val();
			var s_phone_number = $('#s_phone_number').val();
			var s_address_1 = $('#s_address_1').val();
			var s_address_2 = $('#s_address_2').val();
			var s_city = $('#s_city').val();
			var s_country = $('#s_country').val();
			var s_state = $('#s_state').val();
			var s_zipcode = $('#s_zipcode').val();
			var s_address_type = $('#s_address_type').val();
			if(s_address_type  == ''){
				s_address_type = 'residential';
			}
			var dataString = '&s_first_name=' + s_first_name + '&s_last_name=' + s_last_name+ '&s_company_name=' + s_company_name + '&s_phone_number=' + s_phone_number + '&s_address_1=' + s_address_1+ '&s_address_2=' + s_address_2 + '&s_city=' + s_city+ '&s_country=' + s_country + '&s_state=' + s_state + '&s_zipcode=' + s_zipcode + '&s_address_type=' + s_address_type;
			$.ajax({
				type: "POST",
				url: baseurl+'index.php/admin/order/StoreCustomerShippingDetails',
				data:dataString,
				success: function(returndata){
					//console.log(returndata);
				}
			});
		}else{
			var s_first_name = '';
			var s_last_name = '';
			var s_company_name = '';
			var s_phone_number = '';
			var s_address_1 = '';
			var s_address_2 = '';
			var s_city = '';
			var s_country = '';
			var s_state = '';
			var s_zipcode = '';
			var s_address_type = '';
			if(s_address_type  == ''){
				s_address_type = 'residential';
			}
			var dataString = '&s_first_name=' + s_first_name + '&s_last_name=' + s_last_name+ '&s_company_name=' + s_company_name + '&s_phone_number=' + s_phone_number + '&s_address_1=' + s_address_1+ '&s_address_2=' + s_address_2 + '&s_city=' + s_city+ '&s_country=' + s_country + '&s_state=' + s_state + '&s_zipcode=' + s_zipcode + '&s_address_type=' + s_address_type;
			$.ajax({
				type: "POST",
				url: baseurl+'index.php/admin/order/StoreCustomerShippingDetails',
				data:dataString,
				success: function(returndata){
					//console.log(returndata);
				}
			});
		}
		
	}
});

//====================================================== Step 4 ==============================
// Get billing address
$( "#form_wizard_1 .button-next" ).click(function() {
	var tab_3_active = $( "#tab3" ).hasClass( "active" );
	if(tab_3_active == true){
		getcartoverview();
	}
});
function getcartoverview()
{
	$('#loader_payment').css('position','relative');
	$('#loader_c_f_p').show();
	
	setTimeout(function(){ 
		$.ajax({
			url: baseurl+"index.php/admin/order/Getfinalsetpdetails",
			type: "GET", 
			success: function (data) {
				$('#loader_payment').css('');
				$('#loader_c_f_p').hide();
				$("#cart_over_view").html(data); 			
			},
			error: function(jqXHR, textStatus, errorThrown) {
			  console.log(textStatus, errorThrown);
			}
		});
	}, 1000);
}
function placeanorder()
{
	$('#loader_payment').css('position','relative');
	$('#loader_c_f_p').show();
	
	var payment_method = $("#paymentMethod").val();
	var check_number = $("#check_number").val();
	var order_comment = $("#order_comments").val();
	var staff_note = $("#staff_note").val();
	var place_order_user_id = $("#place_order_user_id").val();
	var order_status = $("#order_status").val();
	$.ajax({
		url: baseurl+"index.php/admin/order/importphoneorder",
		type: "POST", 
		data:'&payment_method='+payment_method+'&check_number='+check_number+'&order_comment='+order_comment+'&staff_note='+staff_note+'&order_status='+order_status+'&place_order_user_id='+place_order_user_id,
		success: function (data) {
			$('#loader_payment').css('');
			$('#loader_c_f_p').hide();
			
			// Cleare last step value
			$('#paymentMethod').val("").trigger("change");
			$('#order_status').val("").trigger("change");
			$('#order_comments').val("");
			$('#staff_note').val("");
			$('#check_number').val("");
			
			location.reload(); 			
		},
		error: function(jqXHR, textStatus, errorThrown) {
		  console.log(textStatus, errorThrown);
		}
	});
}
