<?php
/**
 * Plugin Name: BC Multi Currency
 * Plugin URI:  https://github.com/rg-/bc-multi-currency
 * Description: Currency switcher for Wordpress, ACF, Woocommerce, etc....
 * Version:     1.0.0
 * Author:      Roberto Garcia
 * Author URI:  https://github.com/rg-
 * Requires at least: 4.7.2
 * Tested up to: 4.7.2
 * License:     GPLv2+
 * Text Domain: bc_multi_currency
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2017
 * Roberto Garcia (roberto@rgdesign.org) and contributors.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
 
defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); 
define('BCMC_VERSION', '1.0.0' );
define('BCMC_BASENAME', dirname( plugin_basename(__FILE__) ));
add_action('plugins_loaded', 'BCMC_load_textdomain'); 
function BCMC_load_textdomain() {
	if ( class_exists( 'WooCommerce' )  ) { // class_exists( 'WooCommerce' )  ??
		define('BCMC_WOO', 1 );
	}else{
		define('BCMC_WOO', 0 );
	}
	load_plugin_textdomain( 'bc_multi_currency', false, BCMC_BASENAME . '/languages/' );
}

/*

	BC_multi_Currency

*/

//delete_transient( 'bc_multi_currency_open_exchange_rates');

if (!class_exists('BC_multi_Currency')) {
	 	
	include_once( 'includes/plugin_options.php' );
	
	include_once( 'includes/plugin_shortcode.php' );
	include_once( 'includes/plugin_widget.php' );
	
	include_once( 'includes/plugin_woocommerce.php' ); 
	
	function bc_get_curl_url_data($url){
		if( function_exists('curl_version') ){
			
			$url = $url;
			$ch = curl_init();
			$timeout = 5;
			
			$userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			
			$data = curl_exec($ch);
			curl_close($ch);
			
			return $data;
			
		}
	}
	
	function bc_BC_multi_Currency_GeoLocalization(){
		
		$options = get_option( 'bc_multi_currency_options' ); 
		$bc_multi_currency_geo_ip_test = $options['bc_multi_currency_geo_ip_test'] ? $options['bc_multi_currency_geo_ip_test'] : '';
		if (!filter_var($bc_multi_currency_geo_ip_test, FILTER_VALIDATE_IP) === false) {
			$IP = $bc_multi_currency_geo_ip_test;
		} else {
			$IP = $_SERVER['REMOTE_ADDR'];
		} 
		//$IP = '87.236.211.85'; // UK for testings
		//$IP = '200.40.52.74'; // UY for testings
		//$IP = '151.101.192.73'; // US for testings
		//$IP = '177.69.219.145'; // BR for testings
		
		define('GEO_IP', $IP);
		
		$geoplugin_currency_by_ip = @file_get_contents('http://www.geoplugin.net/php.gp?ip='.$IP);
		
		if( $geoplugin_currency_by_ip === false ){
			
			$geoplugin_currency_by_ip = bc_get_curl_url_data('http://www.geoplugin.net/php.gp?ip='.$IP);
			
		}
		
		if( $geoplugin_currency_by_ip === false ){
			define('GEO_ENABLED', false);
			define('GEO_CONTINENT', 'NA');
			define('GEO_COUNTRY', 'US');
			define('GEO_CURRENCYCODE','USD');
			define('GEO_CURRENCYSYMBOL_UTF8','$');
			}else{ 
			define('GEO_ENABLED', true);
			$geoplugin_currency_array = unserialize($geoplugin_currency_by_ip);
			define('GEO_CONTINENT', $geoplugin_currency_array['geoplugin_continentCode']);
			define('GEO_COUNTRY', $geoplugin_currency_array['geoplugin_countryCode']);
			define('GEO_CURRENCYCODE', $geoplugin_currency_array['geoplugin_currencyCode']);
			define('GEO_CURRENCYSYMBOL_UTF8', $geoplugin_currency_array['geoplugin_currencySymbol_UTF8']);
			} 
		
	}
	
	class BC_multi_Currency {
		
		var $BCMC_currency_base;
		var $BCMC_currency;
		var $BCMC_rates;
		var $BCMC_settings; 
		
		/*
		
		__construct
		
		*/
		
		public function __construct() {
			//delete_transient( 'bc_multi_currency_open_exchange_rates' );
			$options = get_option( 'bc_multi_currency_options' ); 
			
			if(''===$options['bc_multi_currency_open_exchange_api'])delete_transient( 'bc_multi_currency_open_exchange_rates');
			
			if ( false === ( $BCMC_rates = get_transient( 'bc_multi_currency_open_exchange_rates' ) ) ) {

				$bc_multi_currency_open_exchange_api = $options['bc_multi_currency_open_exchange_api'] ? $options['bc_multi_currency_open_exchange_api'] : apply_filters('bc_multi_currency_open_exchange_api','');
				
				//c2901b1b147249739875c77d4ab3b624

				$BCMC_rates = wp_remote_retrieve_body( wp_remote_get( 'http://openexchangerates.org/api/latest.json?app_id=' . $bc_multi_currency_open_exchange_api ) );

				// Cache for 12 hours
				if ( $BCMC_rates && $bc_multi_currency_open_exchange_api ){
					set_transient( 'bc_multi_currency_open_exchange_rates', $BCMC_rates, 60*60*12 );
				 } 
			}

			$BCMC_rates = json_decode( $BCMC_rates );

			if ( $BCMC_rates && $BCMC_rates->base && $BCMC_rates->rates) { 
				
				$this->BCMC_currency_base	= $BCMC_rates->base;
				$this->BCMC_rates			= $BCMC_rates->rates; 
			
				if($options['bc_multi_currency_geo_enable']){
					
					if($options['bc_multi_currency_geo_rules']){
						
						bc_BC_multi_Currency_GeoLocalization();
						
						$value_f = trim($options['bc_multi_currency_geo_rules']); 
						$value_f = explode("\n", $value_f);
						$value_f = array_filter($value_f, 'trim');  
						$textAr_json = array(); 
						foreach ($value_f as $line) {  
							$line_args = explode(' : ',trim($line)); 
							$textAr_json['rules'][$line_args[0]] = $line_args[1];  
						}
						$textAr_json['GEO_ENABLED'] = GEO_ENABLED;
						$textAr_json['GEO_IP'] = GEO_IP;
						$textAr_json['GEO_CONTINENT'] = GEO_CONTINENT;
						$textAr_json['GEO_COUNTRY'] = GEO_COUNTRY;
						$textAr_json['GEO_CURRENCYCODE'] = GEO_CURRENCYCODE;
						$textAr_json['GEO_CURRENCYSYMBOL_UTF8'] = GEO_CURRENCYSYMBOL_UTF8;
						
						$this->BCMC_geo_rules = json_encode($textAr_json);
						
						
					}
					
				}
				 
				// Start the resst....
				add_action('wp_enqueue_scripts', array($this, 'bc_BC_multi_Currency_wp_enqueue_scripts'));  
				
				add_action('widgets_init', array($this, 'bc_BC_multi_Currency_widgets'));
				
				add_action('wp_enqueue_scripts', array(&$this, 'bc_BC_multi_Currency_styles'));
				
				if ( 'BCMC_WOO' ) {
					
					add_filter('woocommerce_order_items_table', array($this, 'bc_BC_multi_Currency_woo_order_details'));
					
					add_action( 'woocommerce_admin_order_data_after_order_details', array($this, 'bc_BC_multi_Currency_woo_order_details'),10,1 ); 
					
					add_action( 'woocommerce_email_order_details', array($this, 'bc_BC_multi_Currency_woo_email_order_details'),5, 4 );
					
					add_action( 'woocommerce_proceed_to_checkout', array($this, 'bc_BC_multi_Currency_woo_advice_details'),30,1 );
					
					add_action('woocommerce_checkout_update_order_meta', array($this, 'bc_BC_multi_Currency_woo_update_order_meta'));
				}
			
			}else{
				
				$this->BCMC_fail = true;
				
			}
		}
		 
		
		/*
		
			widgets
		
		*/
		public function bc_BC_multi_Currency_widgets(){
			register_widget('BC_multi_Currency_Widget');
		}
		
		function bc_BC_multi_Currency_styles() {
			wp_enqueue_style( 'bc_multi_currency_styles', plugins_url( '/assets/css/converter.css', __FILE__ ) );
		}
		
		/*
		
		bc_BC_multi_Currency_wp_enqueue_scripts
		
		*/
		
		public function bc_BC_multi_Currency_wp_enqueue_scripts(){
			
			if ( is_admin() ) return; 
			
				// Scripts
				wp_register_script( 'moneyjs', plugins_url('/assets/js/money.min.js', __FILE__), 'jquery', '0.1.2', true );
				wp_register_script( 'accountingjs', plugins_url('/assets/js/accounting.min.js', __FILE__), 'jquery', '0.3.2', true );
				wp_register_script( 'jquery-cookie', plugins_url('/assets/js/jquery-cookie/jquery.cookie.min.js', __FILE__), 'jquery', '1.3.1', true );
				wp_enqueue_script( 'wc_currency_converter', plugins_url('/assets/js/conversion.js', __FILE__), array( 'jquery', 'moneyjs', 'accountingjs', 'jquery-cookie' ), '1.2.3', true );
				//wp_enqueue_script( 'wc_currency_converter', plugins_url('/assets/js/conversion.min.js', __FILE__), array( 'jquery', 'moneyjs', 'accountingjs', 'jquery-cookie' ), '1.2.3', true );

				$symbols = array();

				if ( function_exists( 'get_woocommerce_currencies' ) ) {
					$codes   = get_woocommerce_currencies();
					foreach ( $codes as $code => $name )
						$symbols[ $code ] = get_woocommerce_currency_symbol( $code );
				}

				$zero_replace = '.';
				for ( $i = 0; $i < absint( get_option( 'woocommerce_price_num_decimals' ) ); $i++ )
					$zero_replace .= '0';

				$wc_currency_converter_params = array(
					'current_currency' => isset( $_COOKIE['woocommerce_current_currency'] ) ? $_COOKIE['woocommerce_current_currency'] : '',
					'currencies'       => json_encode( $symbols ),
					'rates'            => $this->BCMC_rates,
					'base'             => $this->BCMC_currency_base,
					'currency'         => get_option( 'woocommerce_currency' ),
					'currency_pos'     => get_option( 'woocommerce_currency_pos' ),
					'num_decimals'     => absint( get_option( 'woocommerce_price_num_decimals' ) ),
					'trim_zeros'       => get_option( 'woocommerce_price_trim_zeros' ) == 'yes' ? true : false,
					'thousand_sep'     => get_option( 'woocommerce_price_thousand_sep' ),
					'decimal_sep'      => get_option( 'woocommerce_price_decimal_sep' ),
					'i18n_oprice'      => __( 'Original price:', 'bc_multi_currency').': ',
					'zero_replace'     => $zero_replace,
					
					'geolocacte_rules' => $this->BCMC_geo_rules,
					
					'round_rules'		=> apply_filters('bc_multi_currency_round_rules',''),
				);

				wp_localize_script( 'wc_currency_converter', 'wc_currency_converter_params', apply_filters( 'wc_currency_converter_params', $wc_currency_converter_params ) );
			
		}
		   
		public function bc_BC_multi_Currency_woo_update_order_meta( $order_id ) {
			global $woocommerce;

			if (isset($_COOKIE['woocommerce_current_currency']) && $_COOKIE['woocommerce_current_currency']) {

				update_post_meta( $order_id, 'Viewed Currency', $_COOKIE['woocommerce_current_currency'] );

				$order_total = number_format($woocommerce->cart->total, 2, '.', '');

				$store_currency = get_option('woocommerce_currency');
				$target_currency = $_COOKIE['woocommerce_current_currency'];

				if ($store_currency && $target_currency && $this->BCMC_rates->$target_currency && $this->BCMC_rates->$store_currency) {

					$new_order_total = ( $order_total / $this->BCMC_rates->$store_currency ) * $this->BCMC_rates->$target_currency;

					$new_order_total = round($new_order_total, 2) . ' ' . $target_currency;

					update_post_meta( $order_id, 'Converted Order Total', $new_order_total );

				}

			}
		}
		
		public function bc_BC_multi_Currency_woo_advice_details($order){ 
			bc_BC_multi_Currency_woo_advice_details_add($order); 
		}
		
		
		public function bc_BC_multi_Currency_woo_email_order_details($order, $sent_to_admin, $plain_text, $email){ 
			bc_BC_multi_Currency_woo_details_add($order); 
		}
		
		public function bc_BC_multi_Currency_woo_order_details($order){ 
			bc_BC_multi_Currency_woo_details_add($order); 
		}
		 
	}
	
	$BC_multi_Currency = new BC_multi_Currency(); 
	
} 
?>