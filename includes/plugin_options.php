<?php 

////////////////////////////////////////////////////////////////
/**
 * custom option and settings
 */
function bc_multi_currency_settings_init() {
	// register a new setting for "bc_multi_currency" page
	register_setting( 'bc_multi_currency', 'bc_multi_currency_options' );

	// add_settings_section( $id, $title, $callback, $page );
	add_settings_section(
		 'bc_multi_currency_section_developers',
		 __( 'Settings', 'bc_multi_currency' ),
		 'bc_multi_currency_section_developers_cb',
		 'bc_multi_currency'
	);

	// add_settings_field( $id, $title, $callback, $page, $section, $args );
	add_settings_field(
		 'bc_multi_currency_open_exchange_api', 
		 __( 'Open Exchange API Key', 'bc_multi_currency' ),
		 'bc_multi_currency_open_exchange_cb',
		 'bc_multi_currency',
		 'bc_multi_currency_section_developers',
		 [
			 'label_for' => 'bc_multi_currency_open_exchange_api',
			 'class' => 'bc_multi_currency_row',
			 'bc_multi_currency_custom_data' => 'custom',
		 ]
	);
	
	if('BCMC_WOO'){
		
		add_settings_field(
			 'bc_multi_currency_woo_currencies', 
			 __( 'Woocommerce custom currencies:', 'bc_multi_currency' ),
			 'bc_multi_currency_woo_currencies_cb',
			 'bc_multi_currency',
			 'bc_multi_currency_section_developers',
			 [
				 'label_for' => 'bc_multi_currency_woo_currencies',
				 'class' => 'bc_multi_currency_row',
				 'bc_multi_currency_custom_data' => 'custom',
				 'dinamic_type' => 'textarea',
				 'dinamic_table_columns' => array('Currency Code','Currency Symbol', 'Description')
			 ]
		);
		 
	}
	
	add_settings_field(
		 'bc_multi_currency_geo_enable', 
		 __( 'Geolocate by IP', 'bc_multi_currency' ),
		 'bc_multi_currency_geo_enable_cb',
		 'bc_multi_currency',
		 'bc_multi_currency_section_developers',
		 [
			 'label_for' => 'bc_multi_currency_geo_enable',
			 'class' => 'bc_multi_currency_row',
			 'bc_multi_currency_custom_data' => 'custom',
		 ]
	);
	
	add_settings_field(
		 'bc_multi_currency_geo_rules', 
		 __( 'Geolocate Rules', 'bc_multi_currency' ),
		 'bc_multi_currency_geo_rules_cb',
		 'bc_multi_currency',
		 'bc_multi_currency_section_developers',
		 [
			 'label_for' => 'bc_multi_currency_geo_rules',
			 'class' => 'bc_multi_currency_row',
			 'bc_multi_currency_custom_data' => 'custom',
		 ]
	);
	
	add_settings_field(
		 'bc_multi_currency_geo_ip_test', 
		 __( 'Geolocate Force IP Test', 'bc_multi_currency' ),
		 'bc_multi_currency_geo_ip_test_cb',
		 'bc_multi_currency',
		 'bc_multi_currency_section_developers',
		 [
			 'label_for' => 'bc_multi_currency_geo_ip_test',
			 'class' => 'bc_multi_currency_row',
			 'bc_multi_currency_custom_data' => 'custom',
		 ]
	);
  
}
  
add_action( 'admin_init', 'bc_multi_currency_settings_init' );
////////////////////////////////////////////////////////////////
 
if('BCMC_WOO'){
	 
	$options = get_option( 'bc_multi_currency_options' );
	$bc_multi_currency_woo_currencies = $options['bc_multi_currency_woo_currencies'] ? $options['bc_multi_currency_woo_currencies'] : '';
	if($bc_multi_currency_woo_currencies){ 
		$text = trim($bc_multi_currency_woo_currencies);
		global $textAr;
		$textAr = explode("\n", $text);
		$textAr = array_filter($textAr, 'trim'); 
		
		add_filter( 'woocommerce_currencies', 'bc_multi_currency_woo_currency' ); 
		function bc_multi_currency_woo_currency( $currencies ) {
			 //$currencies['UYU'] = __( 'Uruguay Pesos', 'woocommerce' );
			 global $textAr;
			 foreach ($textAr as $line) { 
				$line_args = explode(' : ', $line);
				$currencies[$line_args[0]] = $line_args[2];
			} 
			return $currencies;
		} 
		
		add_filter('woocommerce_currency_symbol', 'bc_multi_currency_woo_currency_symbol', 10, 2);
		function bc_multi_currency_woo_currency_symbol($currency_symbol, $currency ) {
			global $textAr;
			foreach ($textAr as $line) { 
				$line_args = explode(' : ', $line); 
				switch( $currency ) {
					case $line_args[0]: $currency_symbol = apply_filters('bc_multi_currency_symbol_before','').$line_args[1].apply_filters('bc_multi_currency_symbol_after',''); break; 
				}
			} 
			 
			 return $currency_symbol;
		}
	}
	
}
 
////////////////////////////////////////////////////////////////
function bc_multi_currency_section_developers_cb( $args ) {
	/*
	$options = get_option( 'bc_multi_currency_options' );
	?><h3>Options saved:</h3><pre><?php print_r($options);?></pre><?php
	
	$BCMC_rates = get_transient( 'bc_multi_currency_open_exchange_rates' ); 
	?>
	<h3>Rates Trastiend:</h3>
	<pre><?php print_r($BCMC_rates);?></pre>
	<?php
	*/  
}
////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////
/**
 * Settings fields html callbacks
 */
// bc_multi_currency_open_exchange_cb
function bc_multi_currency_open_exchange_cb( $args ) {
	$options = get_option( 'bc_multi_currency_options' );
	$value = $options['bc_multi_currency_open_exchange_api'] ? $options['bc_multi_currency_open_exchange_api'] : '';
	?>
	<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>"
	 data-custom="<?php echo esc_attr( $args['bc_multi_currency_custom_data'] ); ?>"
	 name="bc_multi_currency_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo $value; ?>" />
	 <p class="description">
	 <?php esc_html_e( 'Insert your Open Exchange Api Key [*]. Get one here:', 'bc_multi_currency' ); ?> <a href="https://openexchangerates.org/" title="Open Exchange Rates" target="_blank">Open Exchange Rates</a>
	 </p>
	 <p class="description">
	 <?php esc_html_e( '* Free Forever Plan offers hourly updates, with USD base and up to 1,000 requests/month.', 'bc_multi_currency' ); ?>
	 </p>
	<?php
	
}
// bc_multi_currency_open_exchange_cb END

// bc_multi_currency_woo_currencies_cb
function bc_multi_currency_woo_currencies_cb( $args ) {
	$options = get_option( 'bc_multi_currency_options' );
	$value = $options['bc_multi_currency_woo_currencies'] ? $options['bc_multi_currency_woo_currencies'] : '';
	
	$dinamic_args = $args['dinamic_table_columns'];
	$dinamic_args = json_encode($dinamic_args);
	?>
	<textarea data-dinamic='<?php echo $dinamic_args; ?>' class="bc-dinamic-textarea-field" id="<?php echo esc_attr( $args['label_for'] ); ?>"
	 data-custom="<?php echo esc_attr( $args['bc_multi_currency_custom_data'] ); ?>"
	 name="bc_multi_currency_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo $value; ?></textarea>
	 <p class="description"> 
	 <?php esc_html_e( 'You can use this to ADD currencies or to REPLACE existing ones, be carefull, you should not change currency codes, for example USD, must be USD in order to make payments to work :).', 'bc_multi_currency' ); ?>
	 <br>
	 <?php esc_html_e( 'If you plan to ADD a new one, you should use a real currency code. Symbol and Description could change without problem.', 'bc_multi_currency' ); ?> 
	 </p>
	 </p>
	<?php
	
}
// bc_multi_currency_woo_currencies_cb END

// bc_multi_currency_geo_enable_cb
function bc_multi_currency_geo_enable_cb( $args ) {
	$options = get_option( 'bc_multi_currency_options' );
	$value = $options['bc_multi_currency_geo_enable'] ? 'checked="checked"' : '';
	?>
	
	<label><input data-collapse="#bc_multi_currency_geo_rules" <?php echo $value; ?> type="checkbox" id="<?php echo esc_attr( $args['label_for'] ); ?>"
	 data-custom="<?php echo esc_attr( $args['bc_multi_currency_custom_data'] ); ?>"
	 name="bc_multi_currency_options[<?php echo esc_attr( $args['label_for'] ); ?>]"/> <?php esc_html_e( 'Enable Geolocation.', 'bc_multi_currency' ); ?></label> 
	<?php
	
}
function bc_multi_currency_geo_rules_cb( $args ) {
	$options = get_option( 'bc_multi_currency_options' );
	$value = $options['bc_multi_currency_geo_rules'] ? $options['bc_multi_currency_geo_rules'] : '';
	 
	?>
	<textarea id="<?php echo esc_attr( $args['label_for'] ); ?>"
	 data-custom="<?php echo esc_attr( $args['bc_multi_currency_custom_data'] ); ?>"
	 name="bc_multi_currency_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo $value; ?></textarea>
	 
	 <p class="description">
	 <?php esc_html_e( 'Insert one rule per line using this format:', 'bc_multi_currency' ); ?><br>
	 <code>US : USD</code><br>
	 That is: Country Code : Currency Code
	 </p>
	<?php
	
}
// bc_multi_currency_geo_rules_cb END

// bc_multi_currency_geo_ip_test_cb
function bc_multi_currency_geo_ip_test_cb($args){
	$options = get_option( 'bc_multi_currency_options' );
	$value = $options['bc_multi_currency_geo_ip_test'] ? $options['bc_multi_currency_geo_ip_test'] : '';
	
	if ( $value === '' || !filter_var($value, FILTER_VALIDATE_IP) === false ) {
		$show_error = '';
	} else {
		$show_error = __('Not valid IP number, try new one. ','bc_multi_currency');
	} 
	
	?>
	<input type="text" <?php if($show_error) echo ' style="border-color:red; color:red;" ';?> id="<?php echo esc_attr( $args['label_for'] ); ?>"
	 data-custom="<?php echo esc_attr( $args['bc_multi_currency_custom_data'] ); ?>"
	 name="bc_multi_currency_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo $value; ?>" />
	 <p class="description">
	 <?php esc_html_e( 'Insert some IP and force detection/currency switch on load. (For testings)', 'bc_multi_currency' ); ?>
	 </p>
	<?php
	if($show_error){
		?>
		<small style="color:red;"><?php echo $show_error; ?></small>
		<?php
	}
}

// bc_multi_currency_geo_ip_test_cb END

////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////
/**
 * top level menu
 */
function bc_multi_currency_options_page() {
	add_menu_page(__("BC Multi Currency","bc_multi_currency"), __("BC Multi Currency","bc_multi_currency"), 'manage_options', 'bc_multi_currency', 'bc_multi_currency_options_page_html', plugins_url('../assets/img/currency_blue_dollar.png', __FILE__));
} 
add_action( 'admin_menu', 'bc_multi_currency_options_page' );
////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////
/**
 * top level menu:
 * callback functions
 */
function bc_multi_currency_options_page_html() {
	// check user capabilities
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_GET['settings-updated'] ) ) {
		 // add settings saved message with the class of "updated"
		 add_settings_error( 'bc_multi_currency_messages', 'bc_multi_currency_message', __( 'Settings Saved', 'bc_multi_currency' ), 'updated' );
	}
	
	settings_errors( 'bc_multi_currency_messages' );
	
	?>
	 <div class="wrap">
		<div class="optionspage-head">
			<h2><span class="optionspage-icon"><img src="<?php echo plugins_url('../assets/img/currency_blue_dollar.png', __FILE__); ?>"/></span><?php echo esc_html( get_admin_page_title() ); ?></h2>
		</div>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">
					<div class="meta-box-sortables ui-sortable">
						
						<form action="options.php" method="post">
						 <?php 
						 settings_fields( 'bc_multi_currency' );
						 do_settings_sections( 'bc_multi_currency' ); 
						 submit_button( 'Save Settings' );
						 ?>
						 <h3>Debug (delete)</h3>
						 <?php
						 if ( BCMC_WOO ) {
							?>
							<p><?php _e('Woocommerce Running','bc_multi_currency');?></p>
							<?php
						}
						 ?>
						 </form>
						
					</div>
				</div>
			</div>
		</div>
		
		 
	 </div>
	 <?php
}
////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////

add_action( 'admin_head', 'bc_multi_currency_admin_print_scripts', 99 );
function bc_multi_currency_admin_print_scripts(){
	?>
	<script>
		jQuery(document).ready(function($) {
			var $ = jQuery;
			var dinamic_elements = $('.bc-dinamic-textarea-field');
			
			function dinamic_td_row_text(key){
				return '<td><input type="text" value="'+key+'"/></td>';
			}
			function dinamic_remove_button_html(id_key){
				return '<td class="remove"><button class="button button-small" data-refresh-remove="#dinamic-wrap_'+id_key+'" type="button"><span class="dashicons dashicons-no-alt"></span></button></td>';
			}
			
			dinamic_elements.each(function(){
				
				var me = $(this);
				me.addClass('hidden_field');
				var id_key = $(this).attr('id');
				var html_values = $(this).html();
				var array_values = new Array();
				array_values = html_values.split('\n');
				var array_values_html = '';
				
				var data_dinamic = $(this).attr('data-dinamic');
				data_dinamic = jQuery.parseJSON(data_dinamic); 
				
				if(array_values.length>0){
					array_values_html += '<table class="widefat fields">';
					array_values_html += '<thead><tr>';
					
					$.each(data_dinamic, function(index){ 
						array_values_html += '<th>'+data_dinamic[index]+'</th>'; 
					}); 
					
					array_values_html += '<th></th>';
					array_values_html += '</thead></tr>';
					
					for (a in array_values ) { 
						var array_values_args = new Array();
						array_values_args = array_values[a].split(' : ');
						
						var array_values_args_html = '';
						for(s in array_values_args){
							array_values_args_html += dinamic_td_row_text(array_values_args[s]);
						} 
						
						array_values_args_html += dinamic_remove_button_html(id_key); 
						
						array_values_html += '<tbody>';
						if(html_values != ''){
							array_values_html += '<tr id="'+id_key+'_'+a+'">'+array_values_args_html+'</tr>';
						}
						array_values_html += '</tbody>';						
					}
					array_values_html += '</table>';
				}
				
				me.wrap('<div class="bc-dinamic-wrap" id="dinamic-wrap_'+id_key+'"/>');
				var me_wrap = me.parent();
				
				me_wrap.append('<div class="bc-dinamic-fields"/>'); 
					me_wrap.find('.bc-dinamic-fields').append(array_values_html);
			
				var actions_html = '<input type="button" name="bc-dinamic-add-rule" data-add="'+id_key+'" id="'+id_key+'_bc-dinamic-add-rule" class="button" value="Add Rule"> <input data-refresh="#dinamic-wrap_'+id_key+'" type="button" name="'+id_key+'_bc-dinamic-refresh-rule" id="bc-dinamic-refresh-rule" class="button" value="Refresh Rule">';
				
				me_wrap.append('<div class="bc-dinamic-actions"/>');
					me_wrap.find('.bc-dinamic-actions').append(actions_html);
				
				$('[data-add]').on('click',function(){ 
					var key = $(this).attr('data-add');
					var l = $('#dinamic-wrap_'+key+'').find('table.fields tbody tr').size();
					l++;
					tr_add = '<tr id="'+key+'_'+l+'">'; 
					
					$.each(data_dinamic, function(index){ 
						tr_add += dinamic_td_row_text('');
					}); 
					
					tr_add += dinamic_remove_button_html(id_key);
					
					tr_add += '</tr>';
					
					$('#dinamic-wrap_'+key+'').find('table.fields tbody').append(tr_add);
					$('#dinamic-wrap_'+key+'').find('table.fields tbody').find('tr#'+key+'_'+l+'').find('[type="text"]').val('');
					enable_remove();
					enable_keypress(id_key);
					return false;
				});
				
				$('[data-refresh]').on('click',function(){
					populate_fields($(this).attr('data-refresh'));
					return false;
				});
				
				enable_remove();
				enable_keypress(id_key);
			})
			function enable_keypress(id_key){ 
				$('#dinamic-wrap_'+id_key+'').find('table.fields tbody td [type="text"]').keyup(function(ev){
					populate_fields('#dinamic-wrap_'+id_key+'');
				});
				
			}
			
			function enable_remove(){
				$('[data-refresh-remove]').on('click',function(){
					$(this).parent().parent().remove();
					populate_fields($(this).attr('data-refresh-remove'));
					return false;
				});
			}
			
			function populate_fields(elem){
				var out = '';
				var max = $(elem).find('table.fields tbody tr').length;
				$(elem).find('table.fields tbody tr').each(function(i){
					out += $(this).find('td').eq(0).find('input').val() + ' : ' + $(this).find('td').eq(1).find('input').val() + ' : ' + $(this).find('td').eq(2).find('input').val();
					if(i<(max-1)){
						out += '\n';
					}
				})
				if(max===0) out = '';
				$(elem).find('.bc-dinamic-textarea-field').html(out);
				
			}
		});
	</script>
	<?php
}

/**
 * Admin Styles
 */
add_action( 'admin_print_styles', 'bc_multi_currency_admin_print_styles' );
function bc_multi_currency_admin_print_styles(){
	
	?>
	<style>
		.hidden_field,
		[data-refresh]{
			display:none!important;
		}
		.bc-dinamic-wrap{}
		.bc-dinamic-wrap table.fields thead th{
			padding: 8px 10px;
		}
		.bc-dinamic-wrap table.fields tbody tr:nth-child(even)  {
			background:#f9f9f9;
		}
		.bc-dinamic-wrap table.fields input[type="text"]{
			width:auto;
		}
	
		.optionspage-head{ 
		}
			.optionspage-head h1,
			.optionspage-head h2,
			.optionspage-head h3{ 
			}
		.optionspage-icon{
			display:inline-block;
			width:70px;
			text-align:center;
		}
		.optionspage-icon img{
			vertical-align: middle;
			width:70%;
			height:auto;
		}
		.toplevel_page_bc_multi_currency .wp-menu-image img{
			padding: 6px 0 0!important;
			width:70%;
			height:auto;
		}
		
		.form-table input[type="text"]{
			width:100%;
		}
		.form-table textarea{
			
			width:100%;
			min-height:100px;
			
		}
	</style>
	<?php
	
}
////////////////////////////////////////////////////////////////
?>