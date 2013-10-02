<?php 
/*
Plugin Name: WooCommerce Gold Feed Live
Plugin URI: http://wordpress.org/extend/plugins/Woocommerce-gold-price-live
Description: This is an extention to WooCommerce that allows you to place precious metals items for sale on your Wordpress/WooCommerce ecommerce store using the real time price of gold. Easily add markup, weight and purity of your items and you are good to go. You can be up and running in 10 minutes or less with real time pricing. Brought to you by www.gold-feed.com.
Version: 1.0
Author: Gold Feed Inc.
Author URI: https://gold-feed.com
*/

/*
	Copyright 2013 Gold Feed Inc.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



?>
<?php
ob_start();
extract($_REQUEST);
wp_enqueue_script('transaction_management.js',plugins_url('transaction_management.js', __FILE__),array('jquery'),'','true');
function database_setting() {

	global $wpdb;
	$sql = "CREATE TABLE xml_feed  (
	`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`url` VARCHAR( 1000 ) NOT NULL
	) ENGINE = InnoDB;";
 
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	   dbDelta($sql);
	 $wpdb->query("insert into xml_feed values('','https://gold-feed.com/woo_gold.php')");
	  
}
function database_deactive_setting() {
	global $wpdb;
	$wpdb->query("drop table xml_feed");
	  
}

register_activation_hook( __FILE__, 'database_setting' );


function add_fields(){
global $wpdb;
ini_set('allow_url_include','On');
ini_set('extension','php_openssl.dll');
$xml_settings=$wpdb->get_row("SELECT url FROM xml_feed  ", ARRAY_N);

$map_url="https://gold-feed.com/woo_gold.php";
if (($response_xml_data = file_get_contents($map_url))===false){
    echo "Error fetching XML\n";
} else {
   libxml_use_internal_errors(true);
   $data = simplexml_load_string($response_xml_data);
   if (!$data) {
       echo "Error loading XML\n";
       foreach(libxml_get_errors() as $error) {
           echo "\t", $error->message;
       }
   } else {
   
   foreach($data->children() as $metal)
   {
	   $metal_name= $metal->getName();
	   $metal_price=$metal->children();
	   global $wpdb;
	   $table_name1 = $wpdb->prefix . "posts";
	   $table_name2 = $wpdb->prefix . "postmeta";
	   $metalId=$wpdb->get_results("SELECT id FROM $table_name1 WHERE post_name like '%$metal_name%' ", ARRAY_N);
		   foreach ( $metalId as $metalId ){
		   $weight=$wpdb->get_row("SELECT meta_value FROM $table_name2 WHERE meta_key='_weight' and post_id='$metalId[0]' ", ARRAY_N);
		   $purity=$wpdb->get_row("SELECT meta_value FROM $table_name2 WHERE meta_key='_regular_price' and post_id='$metalId[0]' ", ARRAY_N);
		   $markPrice=$wpdb->get_row("SELECT meta_value FROM $table_name2 WHERE meta_key='_sale_price' and post_id='$metalId[0]' ", ARRAY_N);
			
			
		   $first_value=((float)$metal_price*(float)$purity[0]*(float)$weight[0]);
			
		   $second_value=((float)$markPrice[0]/100);
		   
		   $third_value=$first_value*$second_value;
		   
		   $last_value=$third_value+$first_value;
		  
		  
		   
		   $wpdb->query("update $table_name2 set meta_value='$last_value' WHERE post_id='$metalId[0]' and meta_key='_price' ", ARRAY_N);
		   }
	   }
      
   }
}

?>
<script type="text/javascript">
jQuery(document).ready(function(){
//jQuery(".pricing").html('');
var data = {
								action: 'pricing_detail',
								<?php if(isset($_GET['post'])){  ?>post_id: <?php echo $_GET['post']; }?>
					};
	
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data, function(response) {
					
					jQuery(".pricing").html(response);
					jQuery("._weight_field label").html("Weight(Gram) ");
					
					
				});

});
</script>
<?php }
add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);

function change_existing_currency_symbol(  ) {
     $currency_symbol = '$'; 
     
     return $currency_symbol;
}

add_action( 'admin_menu', 'add_menu_section' );
function add_menu_section(){
	/*-----------------------------------------Add XML Feed URL--------------------------------------------------------------------------*/	
    add_menu_page( '', 'XML Setting', 'manage_options', 'myplugin/myplugin-admin.php', 'manage_options','', 6 );
	/*-----------------------------------------Add XML Feed URL--------------------------------------------------------------------------*/
	}
function manage_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include('xml_manage.php');
	}
function check_feed(){
	global $wpdb;
ini_set('allow_url_include','On');
ini_set('extension','php_openssl.dll');
$xml_settings=$wpdb->get_row("SELECT url FROM xml_feed  ", ARRAY_N);

$map_url="https://gold-feed.com/woo_gold.php";

if (($response_xml_data = file_get_contents($map_url))===false){
    echo "Error fetching XML\n";
} else {
   libxml_use_internal_errors(true);
   $data = simplexml_load_string($response_xml_data);
   if (!$data) {
       echo "Error loading XML\n";
       foreach(libxml_get_errors() as $error) {
           echo "\t", $error->message;
       }
   } else{
   
	   foreach($data->children() as $metal)
	   {
		   $metal_name= $metal->getName();
		   $metal_price=$metal->children();
		   global $wpdb;
		   $table_name1 = $wpdb->prefix . "posts";
		   $table_name2 = $wpdb->prefix . "postmeta";
		   $metalId=$wpdb->get_row("SELECT id FROM $table_name1 WHERE post_name like '%$metal_name%' ", ARRAY_N);
		   $metalId=$wpdb->get_results("SELECT id FROM $table_name1 WHERE post_name like '%$metal_name%' ", ARRAY_N);
		   foreach ( $metalId as $metalId ){
		   $weight=$wpdb->get_row("SELECT meta_value FROM $table_name2 WHERE meta_key='_weight' and post_id='$metalId[0]' ", ARRAY_N);
		   $purity=$wpdb->get_row("SELECT meta_value FROM $table_name2 WHERE meta_key='_regular_price' and post_id='$metalId[0]' ", ARRAY_N);
		   $markPrice=$wpdb->get_row("SELECT meta_value FROM $table_name2 WHERE meta_key='_sale_price' and post_id='$metalId[0]' ", ARRAY_N);
			
			
		   $first_value=((float)$metal_price*(float)$purity[0]*(float)$weight[0]);
			
		   $second_value=((float)$markPrice[0]/100);
		   
		   $third_value=$first_value*$second_value;
		   
		   $last_value=$third_value+$first_value;
		  
		  
		   
		   $wpdb->query("update $table_name2 set meta_value='$last_value' WHERE post_id='$metalId[0]' and meta_key='_price' ", ARRAY_N);
		   }

		}
      
   }
} ?>
<script type="text/javascript" >
jQuery(document).ready(function(){

string=jQuery(".product_weight").html();
weight=string.split('kg')[0];
jQuery(".product_weight").html(weight+" Gram");
});
</script>

<?php }
add_action('wp_head', 'check_feed');
add_action('admin_head', 'add_fields');
add_action('wp_ajax_pricing_detail', 'pricing');
add_action('wp_ajax_nopriv_pricing_detail', 'pricing');
function pricing(){
include('show_pricing.php');
die();
}
register_deactivation_hook( __FILE__, 'database_deactive_setting' );

?>

