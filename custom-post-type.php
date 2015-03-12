<?php

/*
*Instruction - if you are using this as a template for a plugin, change the class name, the call to create an object from this class *at the bottom, and modify the private variables to meet your needs.
*/

$marketing_magnet_block_default = "";
class MarketingMagnetCustomPostType{

private $post_type = 'marketingmagnet';
private $post_label = 'Marketing Magnet';

function __construct() {
	/**
	 * Hack to fi media loader
	 */
	add_action( 'load-async-upload.php', array(&$this,'insert_button_hack' ));
	add_action( 'load-media-upload.php', array(&$this,'insert_button_hack' ));
	add_filter( 'cmb_meta_boxes', array(&$this,'metaboxes' ));
	add_action( 'init', array(&$this,'initialize_meta_boxes'), 9999 );
	add_action("init", array(&$this,"create_post_type"));
	add_action( 'init', array(&$this,'marketing_magnet_register_shortcodes'));
	//add_action('wp_enqueue_scripts',array(&$this,'mm_enqueue_scripts'));
	add_action('wp_footer', array(&$this, 'show_marketing_message'));
	register_activation_hook( __FILE__, array(&$this,'activate' ));
}

function create_post_type(){

	register_post_type($this->post_type, array(
	         'label' => _x($this->post_label, $this->post_type.' label'), 
	         'singular_label' => _x('All '.$this->post_label, $this->post_type.' singular label'), 
	         'public' => true, // These will be public
	         'show_ui' => true, // Show the UI in admin panel
	         '_builtin' => false, // This is a custom post type, not a built in post type
	         '_edit_link' => 'post.php?post=%d',
	         'capability_type' => 'page',
	         'hierarchical' => false,
	         'rewrite' => array("slug" => $this->post_type), // This is for the permalinks
	         'query_var' => $this->post_type, // This goes to the WP_Query schema
	         //'supports' =>array('title', 'editor', 'custom-fields', 'revisions', 'excerpt'),
	         'supports' =>array('title'),
	         'add_new' => _x('Add New', 'Event')
	         ));
}

/**
 * Hack to fi media loader
 */
//add_action( 'load-async-upload.php', array(&$this,'plugin_template_insert_button_hack' ));
//add_action( 'load-media-upload.php', array(&$this,'plugin_template_insert_button_hack' ));

 
function insert_button_hack(){
 
      /*  if ( 'image' != $_REQUEST['type'] )
                return;
 */
        $the_post_type = get_post_type( $_REQUEST['post_id'] );
 
        if (  $the_post_type == $this->post_type ){
                add_post_type_support( $this->post_type, 'editor' );
 	}
}




/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function metaboxes( array $meta_boxes ) {
	
	// Start with an underscore to hide fields from custom fields list
	$prefix = '_marketing_magnet_';
	
	

	$meta_boxes[] = array(
		'id'         => 'content_metabox',
		'title'      => 'Content',
		'pages'      => array( $this->post_type ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields'     => array(
			array(
				'name' => 'Marketing Message',
				//'desc' => 'This is a title description',
				'id'   => $prefix . 'marketing_message',
				'type' => 'title',
			),
			array(
				'name'    => 'Message Type',
				'desc'    => 'Choose whether you want to use the wysiwyg message or the code message.',
				'id'      => $prefix . 'message_type',
				'type'    => 'radio_inline',
				'options' => array(
					array( 'name' => 'wysiwyg', 'value' => 'wysiwyg', ),
					array( 'name' => 'code', 'value' => 'code', ),
				),
				'std' => 'wysiwyg',
			),
		        array(
				'name'    => 'WYSIWYG Message',
				'desc'    => 'Place an optin form or some other kind of marketing message here. This uses wpautop to automatically add paragraph and break tags.',
				'id'      => $prefix . 'message_area',
				'type'    => 'wysiwyg',
				'options' => array(	'textarea_rows' => 16, 'wpautop' => false),
			),
			array(
				'name' => 'Code Message',
				'desc' => 'This will be processed just as it appears.  Place html, css, js here and it will work just as you see it.',
				'id'   => $prefix . 'code_message',
				'type' => 'textarea_code',
			),
			
		),
	);

	

	// Add other metaboxes as needed

	return $meta_boxes;
}



function marketing_magnet_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'where' => 'after',
			'message_id' => '',
			'div' => '#main',
		), $atts ) );
		global $marketing_magnet_block_default;
		
		if($message_id){
			$thePost = get_post($message_id);
			if ($thePost->post_type == $this->post_type){
				$messageType = get_post_meta($message_id, '_marketing_magnet_message_type', true);
				if($messageType == 'wysiwyg'){
					$marketingMessage = get_post_meta( $message_id, '_marketing_magnet_message_area', true );
				}else{
					$marketingMessage = get_post_meta( $message_id, '_marketing_magnet_code_message', true );
				}
				$marketing_magnet_block_default = $message_id;
				
				$params = array(
					'mm_message_id' => '#mm_'.$message_id,
					'mm_div' => $div,
				);
				if($where == 'before'){
					//echo "<script type='text/javascript'>jQuery(document).ready(function() {jQuery('#mm_".$message_id."').insertBefore('".$div."');jQuery('#mm_".$message_id."').show();});</script>";
					wp_enqueue_script('marketing_magnet_script', plugin_dir_url( __FILE__ ) . 'js/marketingMagnetBefore.js', array( 'jquery' ));
				}else{
					//echo "<script type='text/javascript'>jQuery(document).ready(function() {jQuery('#mm_".$message_id."').insertAfter('".$div."');jQuery('#mm_".$message_id."').show();});</script>";
					wp_enqueue_script('marketing_magnet_script', plugin_dir_url( __FILE__ ) . 'js/marketingMagnetAfter.js', array( 'jquery' ));
				}
				//wp_enqueue_script('marketing_magnet_script', plugin_dir_url( __FILE__ ) . 'js/marketingMagnetAfter.js', array( 'jquery' ));
				wp_localize_script( 'marketing_magnet_script', 'MarketingMagnetParams', $params );
				/*
				if($where == 'before'){
					//echo "<script type='text/javascript'>jQuery(document).ready(function() {jQuery('#mm_".$message_id."').insertBefore('".$div."');jQuery('#mm_".$message_id."').show();});</script>";
					wp_enqueue_script('marketing_magnet_script', plugin_dir_url( __FILE__ ) . 'js/marketingMagnetBefore.js', array( 'jquery' ));
				}else{
					//echo "<script type='text/javascript'>jQuery(document).ready(function() {jQuery('#mm_".$message_id."').insertAfter('".$div."');jQuery('#mm_".$message_id."').show();});</script>";
					wp_enqueue_script('marketing_magnet_script', plugin_dir_url( __FILE__ ) . 'js/marketingMagnetAfter.js', array( 'jquery' ));
				}
				*/
				
				ob_start();
				echo '<div id="mm_'.$message_id.'" style="display:none">'.$marketingMessage.'</div>';
				
			return ob_get_clean();
			}
		}
		
		
		
		
	}
	function marketing_magnet_register_shortcodes(){
		add_shortcode( 'marketing_magnet', array(&$this, 'marketing_magnet_shortcode') );
		
	}
	
	
	function mm_enqueue_scripts(){
		wp_enqueue_script('marketing_magnet_script', plugin_dir_url( __FILE__ ) . 'js/marketingMagnetAfter.js', array( 'jquery' ));
	}
	
	
	function show_marketing_message(){
		global $post;
		global $marketing_magnet_block_default;
		$marketing_magnet_options = get_option('_marketing_magnet_settings');
		$marketing_magnet_option_start = '_marketing_magnet_general_';
		if($marketing_magnet_options[$marketing_magnet_option_start.'is_enabled_site_wide']){
			$marketing_magnet_default_id = $marketing_magnet_options[$marketing_magnet_option_start.'default_id'];
			$marketing_magnet_default_where = $marketing_magnet_options[$marketing_magnet_option_start.'default_where'];
			$marketing_magnet_default_div = $marketing_magnet_options[$marketing_magnet_option_start.'default_div'];
			if($marketing_magnet_block_default == $marketing_magnet_default_id){
				return;
			}
			//$excluded_list = $marketing_magnet_options[$marketing_magnet_option_start.'excluded_list'];
			$excluded_list = isset($marketing_magnet_options[$marketing_magnet_option_start.'excluded_list']) ? $marketing_magnet_options[$marketing_magnet_option_start.'excluded_list'] : "";
			if((strlen($excluded_list) > 0) && !(preg_match('/'.$post->ID.'/', $excluded_list) > 0)){
				echo do_shortcode('[marketing_magnet message_id="'.$marketing_magnet_default_id.'" where="'.$marketing_magnet_default_where.'" div="'.$marketing_magnet_default_div.'"]');
				//echo do_shortcode('[marketing_magnet message_id="387" where="after" div="#site-description"]');
			}
		}
	}
	

	function activate() {
		// register taxonomies/post types here
		$this->marketing_magnet_post_type();
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
	
	/*
	 * Initialize the metabox class.
	 */
	 
	function initialize_meta_boxes() {
	
		if ( ! class_exists( 'cmb_Meta_Box' ) )
			require_once 'lib/metabox/init.php';
	
	}


}

new MarketingMagnetCustomPostType();


	

?>