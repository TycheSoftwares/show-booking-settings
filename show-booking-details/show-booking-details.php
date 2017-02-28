<?php
/*
Plugin Name: Show Booking Types for WooCommerce products
Plugin URI: https://www.tychesoftwares.com/
Description: A simple plugin that displays the booking details for each product on WooCommerce -> Products page, like "Only Date", "Date & Time", "Multiple Days".
Version: 1.0
Author: Vishal Kothari
Author URI: https://profiles.wordpress.org/ashokrane
*/

class bkap_booking_details {

	public $bkap_column_key                         = 'bkap_booking_details';
	
	public $bkap_column_name                        = '';
	
	public $bkap_show_booking_details_before_column = 'date';

	public function __construct() {
	
		$this->bkap_column_name                          = __( 'Booking<br/>Details', 'bkap-show-booking-details' );
		
		add_filter( 'manage_edit-product_columns',        array( $this, 'bkap_woocommerce_booking_details_column' ), 10, 1 );
		add_action( 'manage_product_posts_custom_column', array( $this, 'bkap_woocommerce_show_booking_details' ), 10, 2 );
		add_action( 'admin_enqueue_scripts',              array( $this, 'bkap_booking_column_style' ) );
	}

	public function bkap_woocommerce_show_booking_details( $column, $post_id ) {
		if ( 'bkap_booking_details' == $column ) {
			$post_id          =   bkap_common::bkap_get_product_id( $post_id );
			$booking_settings =   get_post_meta( $post_id, 'woocommerce_booking_settings', true );

			$booking_details  = '';
			if ( isset( $booking_settings ) && 0 < count( $booking_settings ) ) {
				if ( isset( $booking_settings[ 'booking_enable_date' ] ) && 'on' == $booking_settings[ 'booking_enable_date' ] ) {
					// check booking method
					if ( 'on' == $booking_settings[ 'booking_enable_multiple_day' ] ) {
						_e( 'Multiple Day', 'bkap-show-booking-details' );
						
						if ( isset( $booking_settings[ 'booking_fixed_block_enable' ] ) && 
							 'yes' == $booking_settings[ 'booking_fixed_block_enable' ] ) {
							_e( '<br/>',        'bkap-show-booking-details' );
							_e( 'Fixed Blocks', 'bkap-show-booking-details' );
						}
						
						if ( isset( $booking_settings[ 'booking_block_price_enable' ] ) && 
							 'yes' == $booking_settings[ 'booking_block_price_enable' ] ) {
							_e( '<br/>',          'bkap-show-booking-details' );
							_e( 'Price by Range', 'bkap-show-booking-details' );
						}
					} else {
						// check if time is enabled & time slots are added
						if ( isset( $booking_settings[ 'booking_enable_time' ] ) && 
							 'on' == $booking_settings[ 'booking_enable_time' ] ) {
							_e( 'Date & Time', 'bkap-show-booking-details' );
						} else {
							_e( 'Date',        'bkap-show-booking-details' );
						}

						if ( isset( $booking_settings[ 'booking_recurring_booking' ] ) && 
							 'on' == $booking_settings[ 'booking_recurring_booking' ] ) {
							_e( '<br/>',          'bkap-show-booking-details' );
							_e( 'Recurring Days', 'bkap-show-booking-details' );
							$weekdays_count      = 0;
							$booking_recurring   = $booking_settings[ 'booking_recurring' ];
							foreach( $booking_recurring as $bkey => $bvalue ) {
								if ( 'on' == $bvalue ) {
									$weekdays_count++;
								}
							}
							_e( ' (' . $weekdays_count . ')', 'bkap-show-booking-details' );
						} elseif ( isset( $booking_settings[ 'booking_specific_booking' ] ) &&
								  'on' == $booking_settings[ 'booking_specific_booking' ] ) {
							_e( '<br/>',          'bkap-show-booking-details' );
							_e( 'Specific Dates', 'bkap-show-booking-details' );
							$dates_count      = count( $booking_settings[ 'booking_specific_date' ] );
							_e( ' (' . $dates_count . ')', 'bkap-show-booking-details' );
						}
					}
					
					/**
					 * This is for "Requires Confirmation" setting
					 */
					if ( isset( $booking_settings[ 'booking_confirmation' ] ) &&
						 'on' == $booking_settings[ 'booking_confirmation' ] ) {
						_e( '<br/>',                 'bkap-show-booking-details' );
						_e( 'Requires Confirmation', 'bkap-show-booking-details' );
					}
					
					/**
					 * This is for fetching settings of special price
					 */
					$booking_special_prices     = get_post_meta( $post_id, 'booking_special_price', true );
					$booking_special_price_cnt  = count( $booking_special_prices );
			
					if ( is_array( $booking_special_prices ) && $booking_special_price_cnt > 0 ) { 
						_e( '<br/>',                                 'bkap-show-booking-details' );
						_e( 'Special Price',                         'bkap-show-booking-details' );
						_e( ' (' . $booking_special_price_cnt . ')', 'bkap-show-booking-details' );
					}
				}
			}
		}
	}

	public function bkap_woocommerce_booking_details_column( $columns ) {
		// get all columns up to and excluding the $this->bkap_show_booking_details_before_column column
		$new_columns = array();
		foreach ( $columns as $name => $value ) {
			if ( $name == $this->bkap_show_booking_details_before_column ) {
				prev( $columns );
				break;
			}
			$new_columns[ $name ] = $value;
		}
		
		// inject our columns
		$new_columns[ $this->bkap_column_key ] = $this->bkap_column_name;
	
		// add the $this->bkap_show_booking_details_before_column column, and any others
		foreach ( $columns as $name => $value ) {
			$new_columns[ $name ] = $value;
		}
		return $new_columns;
	}
	
	/**
	 * Include the css file only on the WooCommerce Products page
	 */
	public function bkap_booking_column_style( $hook ) {
		$post_type = get_query_var( 'post_type', '' );
		if ( 'edit.php' == $hook && 'product' == $post_type ) {
			wp_enqueue_style( 'bkap-booking-column-style', plugins_url( '/booking-column-style.css', __FILE__ ) );
		}
	}
}

$bkap_booking_details_obj = new bkap_booking_details();

