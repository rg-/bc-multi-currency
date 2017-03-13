<?php

// Woo main functions
function bc_BC_multi_Currency_woo_details_add($order){
	
	$converted_currency = get_post_meta( $order->id, 'Converted Order Total', true ) ;
	$converted_currency_symbol = get_post_meta( $order->id, 'Viewed Currency', true ) ;
	if($converted_currency){
		$converted_currency = filter_var($converted_currency, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
		$converted_round = (round($converted_currency*.01))/.01;
		
		$current_symbol = get_woocommerce_currency_symbol($converted_currency_symbol); 
	?>
	<div class="form-field form-field-wide">
		<p style="<?php echo apply_filters('bc_multi_currency_details_add','background:#e9e9e9; padding:15px!important;');?>">
		<strong><?php _e('Payment made by viewing site in currency', 'bc_multi_currency'); ?> <?php echo $converted_currency_symbol; ?>: </strong>
		<br>
		<small><?php _e('Not rounded', 'bc_multi_currency'); ?>:</small> <?php echo $current_symbol; ?> <span style="font-size:1.2em;" class="accounting_round"><?php echo $converted_currency; ?></span>
		<br>
		<small><?php _e('Rounded', 'bc_multi_currency'); ?>:</small> <?php echo $current_symbol; ?> <span style="font-size:1.2em;" class="accounting_round"><?php echo $converted_round; ?></span>
		</p> 
	</div>
	<?php
	}
	
}
function bc_BC_multi_Currency_woo_advice_details_add($order){
	
}

// Woo END

?>