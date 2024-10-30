<?php
/********************************************************************************************************
Plugin Name: JE editable
Plugin URI: https://github.com/jameserie/je-editable
Description: This plugin allows you to create editable section on your page.
Author: James Erie
Version: 1.1
Author URI: http://jameserie.info
---------------------------------------------------------------------------------------------------------
Copyright 2011  James Erie  (jameserie81188@gmail.com : JE editable James Erie jameserie81188@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*******************************************************************************************************/

global $wpdb;

// absolute path to plugins directory
define('JE_BASE_DIR', WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)));

// jeditable source
define('JE_EDITABLE_JS', JE_BASE_DIR.'js/jquery.jeditable.js');

// action URL to ajax POST
define('JE_POST_ACTION', str_replace('%7E', '~', $_SERVER['REQUEST_URI']));

// database name used
define('JE_TABLE', $wpdb->prefix.'je_editable');

///////////////////////// END CONSTANTS ///////////////////////////////////////

// add menu option to settings tab
add_action('admin_menu', 'je_editable');

function je_editable() {
	add_options_page('JE Editable', 'JE Editable', 'manage_options', 'je-editable', 'je_options');
}

function je_options(){
	include 'je_options.php';
}

//////////////////////////////////////////////////////////////////////////////

// create table if not exists
if(! is_db_exists('je_table')){
	je_create_table(); // create the table
	je_add_option('je_table', true); // flag if table was created
}

// insert this bit of code to the head
function je_wp_head(){
	?>
	<script>!window.jQuery && document.write(unescape('%3Cscript src="<?php echo JE_BASE_DIR.'js/jquery-1.5.1.min.js'; ?>"%3E%3C/script%3E'))</script>
	<?php
	// Insert this bit into the head section
	if (current_user_can('manage_options') AND get_option('je_enable_editable') == 1): ?>
	<link rel="stylesheet" href="<?php echo JE_BASE_DIR .'css/style.css'?>" />	
	<script src="<?php echo JE_EDITABLE_JS; ?>"></script>	
	<script type="text/javascript">
	jQuery(function(){
		jQuery('.editable').editable('<?php echo JE_POST_ACTION; ?>', {
			type		: 'textarea',
		    name	   	: 'value',
			id		   	: 'id',
			uri			: 'uri',
			cancel    	: 'Discard',
	        submit    	: 'Save',
			callback	: function(){
				location.reload();
			}
	     });
	}); // end docready
	</script>
	<?php endif; ?>
	<script type="text/javascript">
		jQuery(function(){
			// count all editable elements
			var total_editable_elem = jQuery(".editable").length;
			var cur_url = document.URL;
			
			if( total_editable_elem > 0 ) {
				// loop through the editable elements
				jQuery('.editable').each(function(i){
					var this_id = jQuery(this).attr('id');
					// add an ID to the element if not exists
					if(! this_id){
						jQuery(this).attr('id', 'je_editkey_'+i); // insert an ID
					}
				}); // end loop
			}
			<?php foreach(je_get_keys() as $key) : ?>
				<?php if($key->repeat == 'yes'): ?>
					jQuery("#<?php echo $key->jekey; ?>").html('<?php echo html_entity_decode(je_get_repeat_content($key->jekey)); ?>'); // repeat
				<?php endif; ?>
			
				<?php if($key->jeurl == je_cur_url() AND $key->repeat == 'no'): ?>
					jQuery("#<?php echo $key->jekey; ?>").html('<?php echo html_entity_decode(je_get_option($key->jekey, "jeval")); ?>'); // same url
				<?php endif; ?>
			
			<?php endforeach; ?>
		});
	</script>
<?php
} 


// create table
function je_create_table(){
	global $wpdb;
	
	$create_tbl = $wpdb->query(
			"CREATE TABLE IF NOT EXISTS ".JE_TABLE." (
			`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`jekey` VARCHAR( 255 ) NOT NULL ,
			`jeval` TEXT NOT NULL,
			`jeurl` TEXT NULL,
			`repeat` ENUM( 'no', 'yes' ) NOT NULL
			) ENGINE = InnoDB;"
	);
	if(! $create_tbl){
		die('<pre>Error crearing '.JE_TABLE.'</pre>');
	}

}


// get all editable keys from database
function je_get_keys(){
	global $wpdb;
	
	$jekeys = $wpdb->get_results("select * from ".JE_TABLE." where jekey != 'je_table'");
	
	return $jekeys;
}


// return the value of the given key
function je_get_option($jekey, $jefield){
	global $wpdb;
	
	$jeval = $wpdb->get_row("SELECT $jefield FROM ".JE_TABLE." WHERE jekey = '{$jekey}' AND jeurl = '".je_cur_url()."'");
	
	return $jeval->$jefield;
}

//
function je_get_repeat_content($jekey){
	global $wpdb;
	
	$jeval = $wpdb->get_row("SELECT jeval FROM ".JE_TABLE." WHERE jekey = '{$jekey}' AND `repeat` = 'yes'");
	
	return $jeval->jeval;
}

function is_db_exists($jekey){
	global $wpdb;
	
	$return_row = $wpdb->get_var("SELECT jeval FROM ".JE_TABLE." WHERE jekey = '{$jekey}'");
	if($return_row > 0)
		return true;
	return false;
}

// add option value
function je_add_option($key, $value){
	global $wpdb;
	
	$wpdb->query("insert into ".JE_TABLE." (jekey, jeval) values ('{$key}', '{$value}')");
}


// delete the plugin database data
function je_delete_plugin() {
	global $wpdb;
	
	delete_option('je_enable_editable');
	
	$table_name = $wpdb->prefix . "je_editable";
	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

// get current url
function je_cur_url() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
	$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}


// fetch data and display in admin
function editable_data(){
	global $wpdb;
	
	$jekeys = $wpdb->get_results("select * from ".JE_TABLE." where jekey != 'je_table'");
	return 	$jekeys;
}

// when user deactivate the plugin
register_deactivation_hook( __FILE__, 'je_delete_plugin' );

add_action('wp_head', 'je_wp_head');
add_action('wp_head', function(){
	global $wpdb;
	
	if($_POST){
		$key 	= $_POST['id'];
		$uri	= $_POST['uri'];
		$value 	= htmlentities(addslashes(nl2br($_POST['value'])));
		$value 	= str_replace("\n", "", $value);
		$value 	= str_replace("\r", "", $value);
		
		$count_row = $wpdb->get_var("select count(*) from ".JE_TABLE." where jekey = '{$key}' and jeurl = '{$uri}'");
		
		if($count_row > 0){
			$wpdb->query("update ".JE_TABLE." set jeval = '{$value}', jeurl = '{$uri}' where jekey = '{$key}'");
		}
		else{
			$wpdb->query("insert into ".JE_TABLE." (jekey, jeval, jeurl) values ('{$key}', '{$value}', '{$uri}')");
		}
	}
});


