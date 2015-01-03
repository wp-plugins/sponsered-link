<?php
/*
Plugin Name: Sponsered Link
Version:3.0
Author:Red Symbol Technologies 
Plugin URI:www.redsymboltechnologies.com
Description:Sponser Link is the best free WordPress  plugin. Sponsered Link is allows you to easily create and manage Sponsered Link through a simple admin interface.
*/
?>
<?php
define( 'SPONSERED_LINK_VERSION', '3.0' );
define( 'SPONSERED_LINK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SPONSERED_LINK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once(SPONSERED_LINK_PLUGIN_DIR.'/connectDb/install_db.php');
require_once(SPONSERED_LINK_PLUGIN_DIR.'/connectDb/unistall_db.php');

/*Add css in fronted*/

function sponseredLink_admin_init() {
	wp_enqueue_style( 'sponsercss', SPONSERED_LINK_PLUGIN_URL.'css/sponser.css', false, '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'sponseredLink_admin_init','20' );

/*Add js in plugin */

function sponsered_jquery() {
wp_enqueue_script( 'checkbox_validation', SPONSERED_LINK_PLUGIN_URL.'js/checkbox_validation.js', array(), '1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'sponsered_jquery' );

function show_sponser() 
{
    global $wpdb;
   include 'sponser_admin.php';   
}
function my_sponser_setting_page(){
	     
	     include('sponser_settings.php');
	}
function sponser_admin() 
{
    add_menu_page("sponser-page", "Sponser Link", 'manage_options', "sponser_admin", "show_sponser");
	add_submenu_page( 'sponser_admin', 'Settings','Settings', 'manage_options', 'sponser_settings','my_sponser_setting_page');
}
add_action('admin_menu', 'sponser_admin');

register_activation_hook(__FILE__,'sponser_activate' );
register_deactivation_hook(__FILE__,'sponser_deactivate' );

/*  Display Title and url*/
add_filter( 'widget_text', 'shortcode_unautop');

add_filter( 'widget_text', 'do_shortcode');

function show_sponser_fornt()
{
     global $wpdb;
	 $limit   = get_option('sponsersetting');
     $result  = $wpdb->get_results($wpdb->prepare("select * from ".$wpdb->prefix."sponser_link order by rand() LIMIT 0,%d",$limit));
	 $html    = '<div class="customSocialPanel"><ul>';
	 foreach($result as $value){
		if($value->publish == 1)
		{
	     $html .= "<li><a href='".$value->link."' target='_blank'>".$value->title."</a></li>";
		}
	}
	    $html  .= "</ul></div>";
    return $html;
}
add_shortcode( 'SponseredLink', 'show_sponser_fornt' );

/*Add custom url*/
add_action('wp_ajax_add_sponser', 'process_add_sponser');

function process_add_sponser(){
		if ( empty($_POST) || !wp_verify_nonce($_POST['add-sponser-url'],'add_sponser') ) {
			echo 'You targeted the right function, but sorry, your nonce did not verify.';
			die();
		} else {
				global $wpdb;
				$table_name = $wpdb->prefix."sponser_link";
				$title 	  =  sanitize_text_field($_REQUEST['title']);
	            $link     =  sanitize_text_field($_REQUEST['link']);
		        $created  =  time();
		        $publish  =  sanitize_text_field($_REQUEST['publish']);
				$wpdb->insert( 
					$table_name, 
					array( 
						'title'    => $title, 
						'link'     => $link,
						'created'  => $created,
						'publish'  => $publish
					), 
					array( 
						'%s', 
						'%s',
					 	'%s', 
						'%s'
					) 
				);
			    $displayUrl = $_SERVER['HTTP_REFERER'].'&addmsg=Added Successfully';
				echo "<script type='text/javascript'>location.href = '" . $displayUrl. "';</script>";
				die(0);
	}
}
/*Edit custom url*/

add_action('wp_ajax_edit_sponser', 'process_edit_sponser');

function process_edit_sponser(){
		if ( empty($_POST) || !wp_verify_nonce($_POST['edit-sponser-url'],'edit_sponser') ) {
			echo 'You targeted the right function, but sorry, your nonce did not verify.';
			die();
		} else {
			global $wpdb;
			$table_name = $wpdb->prefix."sponser_link";
			$title 		= sanitize_text_field($_REQUEST['title']);
			$link 		= sanitize_text_field($_REQUEST['link']);
            $publish 	= sanitize_text_field($_REQUEST['publish']);
            $id 		=  sanitize_text_field($_REQUEST['id']);
			$wpdb->update( 
				$table_name, 
				array( 
					'title'    => $title,	
					'link'     => $link,
					'publish'  => $publish 
				), 
				array( 'id' =>  $id ), 
				array( 
					'%s',	
					'%s',
					'%s'
				), 
				array( '%d' ) 
			);
		    $displayUrl2 = $_SERVER['HTTP_REFERER'];
			$Location22	= explode('&', $displayUrl2);
			echo "<script type='text/javascript'>location.href = '" . $Location22[0].'&editmsg=Update Successfully'. "';</script>";
			die(0);
	}
}
/*setting custom url*/

add_action('wp_ajax_sponser_setting', 'process_sponser_setting');

function process_sponser_setting(){
		if ( empty($_POST) || !wp_verify_nonce($_POST['setting-sponser-url'],'sponser_setting') ) {
			echo 'You targeted the right function, but sorry, your nonce did not verify.';
			die();
		} else {
			global $wpdb;
			$setting = sanitize_text_field($_POST['sponser_text']);
			if(isset($setting) && $setting !=''){
			 update_option( 'sponsersetting', $setting);
			}
		    $settingPage = $_SERVER['HTTP_REFERER'].'&display=Setting saved';
			echo "<script type='text/javascript'>location.href = '" . $settingPage. "';</script>";
			die(0);
	}
}
/*setting custom url*/

add_action('wp_ajax_sponser_pagination', 'process_sponser_pagination');

function process_sponser_pagination(){
		if ( empty($_POST) || !wp_verify_nonce($_POST['setting-sponser-pagination'],'sponser_pagination') ) {
			echo 'You targeted the right function, but sorry, your nonce did not verify.';
			die();
		} else {
			global $wpdb;
			$sponsered_pagination = sanitize_text_field($_POST['sponser_pagination']);
			if(isset($sponsered_pagination) && $sponsered_pagination!=''){
			update_option( 'sponserpagination', $sponsered_pagination);
			}
			$pos = strpos($_SERVER['HTTP_REFERER'], '&display=Setting saved');
			if($pos == false ){
			$settingPage = $_SERVER['HTTP_REFERER'].'&display=Setting saved';
			}else{
			$settingPage = $_SERVER['HTTP_REFERER'];
			}
		    echo "<script type='text/javascript'>location.href = '" . $settingPage. "';</script>";
			die(0);
	}
}
?>

