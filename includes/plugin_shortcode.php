<?php


function BC_get_currency_switcher_FX($atts) {
    
    global $wp_widget_factory;
    
    extract(shortcode_atts(array(
        'widget_name' => 'BC_multi_Currency_Widget',
		'currency_codes' => '',
		'title'			=> '',
		'show_reset'    => '',
		'message'    => '',
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '',
		'after_title' => '',
    ), $atts));
    
    $widget_name = esc_html($widget_name);
    
    if (!is_a($wp_widget_factory->widgets[$widget_name], 'WP_Widget')):
        $wp_class = 'WP_Widget_'.ucwords(strtolower($class));
        
        if (!is_a($wp_widget_factory->widgets[$wp_class], 'WP_Widget')):
            return '<p>'.sprintf(__("%s: Widget class not found. Make sure this widget exists and the class name is correct", "bootclean"),'<strong>'.$class.'</strong>').'</p>';
        else:
            $class = $wp_class;
        endif;
    endif; 
	
	$instance['from_shortcode'] = '1';
	$instance['currency_codes'] = $currency_codes;
	$instance['title'] = $title;
	$instance['show_reset'] = $show_reset;
	$instance['message'] = $message;
	
	$args['before_widget'] = $before_widget;
	$args['after_widget'] = $after_widget;
	$args['before_title'] = $before_title;
	$args['after_title'] = $after_title;
	
    ob_start();
    the_widget($widget_name, $instance, $args);
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
    
}
add_shortcode('BC_get_currency_switcher','BC_get_currency_switcher_FX');

?>