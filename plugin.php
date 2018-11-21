<?php
/**
 * Plugin Name:     WP Contact Form Block
 * Plugin URI:      https://horttcore.de
 * Description:     A contact form block plugin
 * Author:          Ralf Hortt
 * Author URI:      https://horttcore.de
 * Text Domain:     wp-contact-form-block
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Horttcore/wp-contact-form-block
 */

namespace Horttcore\ContactFormBlock;

use Horttcore\ContactFormBlock\ContactForm;

# ------------------------------------------------------------------------------
# Autoloader
# ------------------------------------------------------------------------------
#
# Load composer autoloader file
$autoloader = dirname( __FILE__ ) . '/vendor/autoload.php';

if (is_readable( $autoloader )) :
    require_once $autoloader;
endif;

if ( ! defined( 'WPINC' ) ) :
    die;
endif;


/**
 * Enqueue block script and backend stylesheet.
 */
add_action( 'enqueue_block_editor_assets', function() {
	wp_enqueue_script( 'contact-form', plugins_url( 'src/js/index.js', __FILE__ ), [], filemtime( plugin_dir_path( __FILE__ ) . 'src/js/index.js' ), TRUE );
} );


/**
 * Load translation
 */
add_action( 'plugins_loaded', function(){
	load_plugin_textdomain( 'wp-contact-form-block', false, plugin_dir_path( __FILE__ ) . 'languages' );
});


/**
 * Register the dynamic block.
 */
add_action( 'plugins_loaded', function(){
	// Only load if Gutenberg is available.
	if ( ! function_exists( 'register_block_type' ) )
		return;

	// Hook server side rendering into render callback
	register_block_type( 'horttcore/contact-form', [
		'render_callback' => function(){
			return (new ContactForm())->render();
		},
	] );
} );
