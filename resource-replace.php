<?php 
/**
 * @package resource-replace.php
 * @version 0.1
 */
/*
Plugin Name: Resource Replace
Plugin URI: https://github.com/EJEHardenberg/Resource-Replace
Description: Replaces script and css sources included via wp_enqueue's domain name with what you specify.
Author: Ethan J. Eldridge
Version: 0.1
Author URI: http://ejehardenberg.github.io
*/


$rr_options = array();
$rr_defaults = array('rr_css_domain' => '', 'rr_js_domain' => '','rr_version_kill' => '0');

function resource_replace_css($src){
	if( is_admin() ) return $src;

	$rr_t_options = get_option('resource_replace_option',$rr_defaults);
	if(!empty($rr_t_options['rr_css_domain'])){
		$src = str_replace(site_url(), $rr_t_options['rr_css_domain'], $src);
	}
	if($rr_t_options['rr_version_kill'] == '1'){
		$src = remove_query_arg('ver',$src);
		$src = remove_query_arg('version',$src);
	}
	return $src;
}
function resource_replace_js($src){
	if( is_admin() ) return $src;

	$rr_t_options = get_option('resource_replace_option',$rr_defaults);
	if(!empty($rr_t_options['rr_js_domain'])){
		$src = str_replace(site_url(), $rr_t_options['rr_js_domain'], $src);
	}
	if($rr_t_options['rr_version_kill'] == '1'){
		$src = remove_query_arg('ver',$src);
		$src = remove_query_arg('version',$src);
	}
	return $src;
}
function resource_replace_uninstall(){
	/* delete the option and clean up after ourselves */
	delete_option('resource_replace_option');
}
function rr_print_section_info(){
	print "Choose the domain name to change your resource links to";
}
function rr_css_domain_callback(){
	global $rr_options;
	printf(
            '<input type="text" id="rr_css_domain" name="resource_replace_option[rr_css_domain]" value="%s" />',
            isset( $rr_options['rr_css_domain'] ) ? esc_attr( $rr_options['rr_css_domain']) : ''
        );
}
function rr_sanitize_options( $input ){
    $new_input = array();
    if( isset( $input['rr_css_domain'] ) )
            $new_input['rr_css_domain'] = sanitize_text_field( $input['rr_css_domain'] );
    if( isset( $input['rr_js_domain'] ) )
            $new_input['rr_js_domain'] = sanitize_text_field( $input['rr_js_domain'] );
    if( isset( $input['rr_version_kill']) && $input['rr_version_kill'] )
    	$new_input['rr_version_kill'] = true;
    else
    	$new_input['rr_version_kill'] = false;

    return $new_input;
}
function rr_js_domain_callback(){
	global $rr_options;
	printf(
            '<input type="text" id="rr_js_domain" name="resource_replace_option[rr_js_domain]" value="%s" />',
            isset( $rr_options['rr_js_domain'] ) ? esc_attr( $rr_options['rr_js_domain']) : ''
        );
}
function rr_ver_kill_callback(){
	global $rr_options;
	printf(
			'<input type="checkbox" id="rr_version_kill" name="resource_replace_option[rr_version_kill]" %s />',
			isset($rr_options['rr_version_kill']) && $rr_options['rr_version_kill'] == '1' ? 'checked' : ''
		);
}
function register_resource_replace_settings() {
	register_setting( 'resource_replace_option_group', 'resource_replace_option_group', 'intval' ); 
		register_setting(
            'resource_replace_option_group', // Option group
            'resource_replace_option', // Option name
           	'rr_sanitize_options' // Sanitize
        );

        add_settings_section(
            'resource_section_id', // ID
            'Resource Replace Settings', // Title
            'rr_print_section_info' , // Callback
            'resource-replace-admin' // Page
        );  

        add_settings_field(
            'rr_css_domain', // ID
            'CSS Doman', // Title 
            'rr_css_domain_callback', // Callback
            'resource-replace-admin', // Page
            'resource_section_id' // Section           
        );      

        add_settings_field(
            'rr_js_domain', 
            'JS Domain', 
            'rr_js_domain_callback', 
            'resource-replace-admin', 
            'resource_section_id'
        );   

        add_settings_field(
            'rr_version_kill', 
            'Remove ?ver= and ?version from urls', 
            'rr_ver_kill_callback', 
            'resource-replace-admin', 
            'resource_section_id'
        );  
}
function resource_replace_settings_page(){
	add_options_page(
            'Resource Replace Settings', 
            'Resource Replace', 
            'manage_options', 
            'resource-replace-admin', 
            'resource_replace_options_page' 
        );
}

function resource_replace_options_page(){
	global $rr_options;
	$rr_options = get_option( 'resource_replace_option' );
?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Resource Replace Options</h2>
		<form method="post" action="options.php"> 
			<?php 
				settings_fields( 'resource_replace_option_group' ); 
				do_settings_sections( 'resource-replace-admin' );
				submit_button(); 
			?>
		</form>
	</div>
<?
}

add_filter( 'style_loader_src', 'resource_replace_css');
add_filter( 'style_loader_src', 'resource_replace_js');
add_action( 'admin_menu', 'resource_replace_settings_page' );
add_action( 'admin_init', 'register_resource_replace_settings' );
register_uninstall_hook(__FILE__, 'resource_replace_uninstall');
?>
