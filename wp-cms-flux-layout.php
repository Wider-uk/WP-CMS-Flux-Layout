<?php
/*
 Plugin Name: WP-CMS Flux Layout
 Plugin URI: http://wp-cms.com
 Description: Adds the Flux Layout responsive CSS framework to your WordPress site. Configure options through the WordPress Customizer (View website -> Top Admin Bar -> Customize).
 Author: Jonny Allbut
 Version: 0.1
 Author URI: http://jonnya.net
*/

/*

/////////  VERSION HISTORY

0.1 - Initial release

*/

/**
 *
 * Setup text domain for translation
 * Same name as plugin directory
 *
 */
function wpcms_fluxl_textdom() {
	load_plugin_textdomain( 'wpcms-flux-layout', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'wpcms_fluxl_textdom') ;

/**
 *
 * Execute plugin
 *
 */
function wpcms_fluxl_do() {
	$wpcms_flux_do = ( is_customize_preview() ) ? new wpcms_flux_layout : '';
}
add_action( 'after_setup_theme','wpcms_fluxl_do', 1 );

/**
 *
 * All the widget control functionality
 *
 */
class wpcms_flux_layout {

	//var $plugin; /* Common plugin definition */

	function __construct() {

	    //$this->widget_data = get_option( $this->plugin );
		add_action('customize_register', array($this, 'customizer_do') );

	}

	function customizer_do($wp_customize){

		//////// PANELS ////////

		$wp_customize->add_panel( 'wpcms_flux_layout', array(
		  'title'			=> esc_html__( 'Flux Layout', 'wpcms-flux-layout' ),
		  'description'		=> esc_html__( 'Configure Flux Layout CSS options.', 'wpcms-flux-layout' ),
		  // 'priority'		=> 20
		) );

		//////// SECTIONS ////////

		$wp_customize->add_section('wpcms_fluxl_core', array(
			'title'			=> esc_html__( 'Main configuration', 'wpcms-flux-layout' ),
			'description'	=> esc_html__( 'Setup the dimensions of your CSS layout columns (grid system).', 'wpcms-flux-layout' ),
			'panel'			=> 'wpcms_flux_layout'
		));

		$wp_customize->add_section('wpcms_fluxl_content', array(
			'title'			=> esc_html__( 'Main content and sidebar', 'wpcms-flux-layout' ),
			'description'	=> esc_html__( 'Setup the dimensions of your main content area and sidebar.', 'wpcms-flux-layout' ),
			'panel'			=> 'wpcms_flux_layout'
		));

		////// SITE/LOCATION SPECIFIC CONTROLS //////
		// Site param = 'subsites', 'all'

		$controls = array(

			/* Main config */

			'wpcms_flux_opts[columns_num]' => array(
				'label'		=> 'Number of Vertical columns',
				'desc'		=> 'Number of vertical columns in core configuration. Flux Layout also includes other common columns configurations automatically.',
				'datatype'	=> 'option',
				'default'	=> 16,
				'transport'	=> 'refresh',
				'section'	=> 'wpcms_fluxl_core',
				'type'		=> 'select_range',
				'val_low'	=> 2,
				'val_high'	=> 100,
				'val_step'	=> 1,
				'sanitize'	=> 'numeric'
			),

			/* Content and sidebar */

			'wpcms_flux_opts[content_s]' => array(
				'label'		=> 'Content width (relative size)',
				'desc'		=> 'Moomin2',
				'datatype'	=> 'option',
				'default'	=> 500,
				'transport'	=> 'refresh',
				'section'	=> 'wpcms_fluxl_content',
				'type'		=> 'text',
				'sanitize'	=> 'numeric'
			)

		);

		foreach ( $controls as $opt => $val ) {

			$wp_customize->add_setting( $opt, array(
				'type'				=> $val['datatype'], // option or theme_mod
				'default'			=> ( isset($val['default']) ) ? $val['default'] : false,
				'transport'			=> $val['transport'], // refresh or postMessage
				'sanitize_callback' => ( isset($val['sanitize']) ) ? array( $this, 'sanitize_' . $val['sanitize'] ) : false,
				'sanitize_js_callback' => ( isset($val['sanitize']) ) ? array( $this, 'sanitize_' . $val['sanitize'] ) : false

			));

			switch ( $val['type'] ) {

				case 'image_upload':

					$wp_customize->add_control(
						new WP_Customize_Upload_Control( $wp_customize, $opt,
						array(
							'label'			=> $val['label'],
							'section'		=> $val['section'],
							'settings'		=> $opt,
							'description'	=> ( isset($val['desc']) ) ? $val['desc'] : false
						)
					));

				break;

				case 'select':

					$wp_customize->add_control( $opt, array(
						'label'   			=> $val['label'],
						'section' 			=> $val['section'],
						'type'    			=> $val['type'],
						'choices'			=> $val['choices'],
						'description'		=> ( isset($val['desc']) ) ? $val['desc'] : false
					));

				break;

				case 'select_range':

					$vals = $this->helper_int_range($val['val_low'], $val['val_high'], $val['val_step']);

					$wp_customize->add_control( $opt, array(
						'label'   			=> $val['label'],
						'section' 			=> $val['section'],
						'type'    			=> 'select',
						'choices'			=> $vals,
						'description'		=> ( isset($val['desc']) ) ? $val['desc'] : false
					));

				break;

				default:

					$wp_customize->add_control( $opt, array(
						'label'   			=> $val['label'],
						'section' 			=> $val['section'],
						'type'    			=> $val['type'],
						'description'		=> ( isset($val['desc']) ) ? $val['desc'] : false
					));

				break;

			}

		}

	}

	/**
	 *
	 * Returns array of values numeric ready to use with dropdown
	 *
	 * @param  [integer]	$low Number where youd like to start
	 * @param  [integer]	$high Number where youd like to end
	 * @param  [integer]	$step Increase number by how many each time
	 * @return [array]		Array of numbers
	 */
	function helper_int_range( $low, $high, $step=1 ) {

		$items = range( $low,$high,$step );
		$output = array();
		foreach ($items as $val) { $output[$val] = $val; }
		return $output;

	}

	/**
	 *
	 * Common customizer sanitization callback function
	 *
	 */
	function sanitize_numeric( $input ) {
		return ( isset($input) && is_numeric($input) ) ? $input : false;
	}

	/**
	 *
	 * Common customizer sanitization callback function
	 *
	 */
	function sanitize_nohtml( $input ) {
		return ( isset($input) ) ? wp_filter_nohtml_kses( trim($input) ) : false;
	}

	/**
	 *
	 * Common customizer sanitization callback function
	 *
	 */
	function sanitize_checkbox( $input ) {
		return ( isset($input) && $input === true ) ? true : false;
	}

}
?>