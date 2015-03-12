<?php

class MarketingMagnet{

    private $plugin_path;
    private $plugin_url;
    private $l10n;
    private $marketingMagnet;
    private $namespace = _marketing_magnet;
    private $settingName = 'Marketing Magnet';

    function __construct() 
    {	
        $this->plugin_path = plugin_dir_path( __FILE__ );
        $this->plugin_url = plugin_dir_url( __FILE__ );
        $this->l10n = 'wp-settings-framework';
        add_action( 'admin_menu', array(&$this, 'admin_menu'), 99 );
        
        // Include and create a new WordPressSettingsFramework
        require_once( $this->plugin_path .'wp-settings-framework.php' );
        $settings_file = $this->plugin_path .'settings/settings-general.php';
        
        $this->marketingMagnet = new WordPressSettingsFramework( $settings_file, $this->namespace, $this->get_settings() );
        // Add an optional settings validation filter (recommended)
        //add_filter( $this->marketingMagnet->get_option_group() .'_settings_validate', array(&$this, 'validate_settings') );
        
       // add_action( 'init', array(&$this, 'marketing_magnet_register_shortcodes'));
        //for tinymce button add_action('init', array(&$this, 'add_marketing_magnet_icon'));
        //add_action( 'wp_enqueue_scripts', array(&$this,'plugin_template_stylesheet' ));
       
    }
    
    function admin_menu()
    {
        $page_hook = add_menu_page( __( $this->settingName, $this->l10n ), __( $this->settingName, $this->l10n ), 'update_core', $this->settingName, array(&$this, 'settings_page') );
        add_submenu_page( $this->settingName, __( 'Settings', $this->l10n ), __( 'Settings', $this->l10n ), 'update_core', $this->settingName, array(&$this, 'settings_page') );
    }
    
    function settings_page()
	{
	    // Your settings page
	    
	    ?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2><?php $this->settingName ?></h2>
			
			<p>the shortcode tag <code>[marketing_magnet/]</code> on your page or post</p>	
						
			
			<?php 
			$this->marketingMagnet->settings(); 
			?>
			
		</div>
		<?php
		
	}
	
	function validate_settings( $input )
	{
	    // Do your settings validation here
	    // Same as $sanitize_callback from http://codex.wordpress.org/Function_Reference/register_setting
    	return $input;
	}
	
	
        
        function get_settings(){
        	$wpsf_settings[] = array(
		    'section_id' => 'general',
		    'section_title' => $this->settingName.' Settings',
		    //'section_description' => 'Some intro description about this section.',
		    'section_order' => 5,
		    'fields' => array(
			        array(
			            'id' => 'is_enabled_site_wide',
			            'title' => 'Enable Site Wide Default Message',
			            'desc' => 'Enables Marketing Magnet to show a default message on all your pages and post if a shortcode is not present on a page or post and the page or post is not on the excluded list.',
			            'type' => 'checkbox',
			            'std' => '',
			        ), 
			        array(
			            'id' => 'default_id',
			            'title' => 'Set Default Message Id',
			            'desc' => 'Put the id of the message you want to show if a shortcode is not present on a page or post and the page is not on the excluded list.',
			            'type' => 'text',
			            'std' => '',
			        ),
			         array(
			            'id' => 'default_where',
			            'title' => 'Default placement',
			            'desc' => 'Choose whether to place the message before or after the default div',
			            'type' => 'radio',
			            'std' => 'after',
			            'choices' => array(
			                'before' => 'before',
			                'after' => 'after',
			            )
			        ),  
			        array(
			            'id' => 'default_div',
			            'title' => 'Default div id',
			            'desc' => 'Set the div id you want to key off of for the default message. Include the # symbol before the div id.',
			            'type' => 'text',
			            'std' => '#header',
			        ), 
			         array(
			            'id' => 'excluded_list',
			            'title' => 'Exclude these post ids from showing the default message.',
			            'desc' => 'Put the ids of the post you don\'t want to show the default message.  Use commas between each post id, without spaces.',
			            'type' => 'textarea',
			            'std' => '',
			        ),          
		        )
		        
        
		    );
		    return $wpsf_settings;
		}
        

}
new MarketingMagnet();

?>