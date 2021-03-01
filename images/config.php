<?php 
//define('STORE_URL','https://webdesksolution-3.myshopify.com');
//define('SHOPIFY_U_P','23878147f61ebf7301b51d521483c7a7:06afaf5b3919e9c4814c346c8b3c797f');

define('STORE_URL','https://halifaxmotorsports.myshopify.com');
define('SHOPIFY_U_P','0f66e1b0fc0fdd45063dda8871787e13:c9c788e7520044b71edce466d05d9c7c');


//Sandbox
//define('SHOPIFY_SHOP','e7e0b50189e69f0517d72391caa42a85:295bcad1eefec0aa3918207c3061f6a5@webdesksolution-3.myshopify.com');

 
//Live 
define('SHOPIFY_SHOP','0f66e1b0fc0fdd45063dda8871787e13:c9c788e7520044b71edce466d05d9c7c@halifaxmotorsports.myshopify.com');

$con = mysql_connect('localhost','realchea_realche','r@)PQLZ*oUNP');
mysql_select_db('realchea_realcheapfloors',$con);
	
	
/*Shop: https://halifaxmotorsports.myshopify.com/admin
thevertexdimension@gmail.com
vertex123

API key
0f66e1b0fc0fdd45063dda8871787e13
Password
c9c788e7520044b71edce466d05d9c7c
Shared secret
66dbd987090a7a097534a809b0fbca5c
URL format
https://apikey:password@hostname/admin/resource.json
Example URL
https://0f66e1b0fc0fdd45063dda8871787e13:c9c788e7520044b71edce466d05d9c7c@halifaxmotorsports.myshopify.com/admin/orders.json*/	

?>