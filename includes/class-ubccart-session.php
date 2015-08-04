<?php
/**
 * UBCCart Session
 *
 * This is a wrapper class for WP_Session and handles the storage of cart items, purchase sessions, etc
 *
 * @package     UBC Cart
 * @subpackage  Classes/Session
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// -- Class Name : UBCCART_Session
// -- Purpose : 
// --           1. Creates a cart session - uses class WP Session.
// --           2. Sets the cookie
// --           3. Getter and Setter for ubc-cart (as serialized array)
// --           4. Getter for session id
// --           5. Unsetter for ubc-cart
// -- Created On : March 21st 2015
class UBCCART_Session {

	private $session;//Holds session data
	private $prefix = '';//Session index prefix
	
	public function __construct() {

		// Always use WP_Session (default)

		if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
			define( 'WP_SESSION_COOKIE', 'ubccart_wp_session' );
		}

		if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
			require_once UBCCART_PLUGIN_DIR . 'includes/wp-session/class-recursive-arrayaccess.php';
		}

		if ( ! class_exists( 'WP_Session' ) ) {
				require_once UBCCART_PLUGIN_DIR . 'includes/wp-session/class-wp-session.php';
				require_once UBCCART_PLUGIN_DIR . 'includes/wp-session/wp-session.php';
		}

		add_filter( 'wp_session_expiration_variant', array( $this, 'set_expiration_variant_time' ), 99999 );
		add_filter( 'wp_session_expiration', array( $this, 'set_expiration_time' ), 99999 );

		if ( empty( $this->session ) ) {
			$this->init();
		} 

	}

	/**
	 * Setup the WP_Session instance
	 */
	public function init() {

		$this->session = WP_Session::get_instance();

		$cart = $this->get( 'ubc-cart' );

		if( ! empty( $cart ) ) {
			$this->set_cart_cookie();
		} else {
			$this->set_cart_cookie( false );
		}

		return $this->session;
	}


	/**
	 * Retrieve session ID
	 */
	public function get_id() {
		return $this->session->session_id;
	}


	/**
	 * Retrieve a session variable
	 */
	public function get( $key ) {
		$key = sanitize_key( $key );
		return isset( $this->session[ $key ] ) ? maybe_unserialize( $this->session[ $key ] ) : false;
	}

	/**
	 * Delete a session variable
	 */
	public function session_unset( $key ) {
		$key = sanitize_key( $key );
		$this->session[ $key ] = array();
		$this->session->reset();
	}

	/**
	 * Set a session variable
	 */
	public function set( $key, $value ) {

		$key = sanitize_key( $key );

		if ( is_array( $value ) ) {
			$this->session[ $key ] = serialize( $value );
		} else {
			$this->session[ $key ] = $value;
		}

		return $this->session[ $key ];
	}

	/**
	 * Set a cookie to identify whether the cart is empty or not
	 */
	public function set_cart_cookie( $set = true ) {
		if( ! headers_sent() ) {
			if( $set ) {
				@setcookie( 'ubccart_items_in_cart', '1', time() + 30 * 60, COOKIEPATH, COOKIE_DOMAIN, false );
			} else {
				@setcookie( 'ubccart_items_in_cart', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false );
			}
		}
	}

	/**
	 * Force the cookie expiration variant time to 23 hours
	 */
	public function set_expiration_variant_time( $exp ) {
		return ( 23 * 60  );
	}

	/**
	 * Force the cookie expiration time to 24 hrs
	 */
	public function set_expiration_time( $exp ) {
		return ( 24 * 60 );
	}

	/**
	 * Starts a new session if one hasn't started yet - if using php session.
	 */
	public function maybe_start_session() {
		if( ! session_id() && ! headers_sent() ) {
			session_start();
		}
	}

}