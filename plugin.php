function action_woocommerce_order_actions_end( $order_id ) {
	global $wpdb;

// toma los metdos de pago	
    $gates = WC()->payment_gateways->get_available_payment_gateways();
	$enabled_gateways = [];
	    
	    
	
if( $gates ) {
    foreach( $gates as $gate ) {
            $enabled_gateways[] = $gate->title;

    }
}

echo '<form action="" method="post">';
echo   '<select name="reserva_fun"><option value="destiempo">Selecione la cuenta origen</option>';	
foreach($enabled_gateways as $enabled_gateway){
	
	$html_string ='<option value="'.$enabled_gateway.'">'.$enabled_gateway.'</option>';
	echo $html_string;
}
echo '</select>';
echo '<button type="submit" class="button save_order button-primary" name="save" value="destiempo">Tomar de la Reserva</button>';
echo '</form>';
	
	
if(isset($_POST['reserva_fun'])){
	echo 'Funcion esdasdas';
	$copy = array();
        $copy['coid'] = 'eebedd5c';
        $copy['siteid'] = 0;
        $copy['cat'] = 'inventory';
        $copy['amount'] = 25.00;
        $copy['tr'] = 0.00;
        $copy['datepaid'] = 1643673600;
        $copy['timecr'] = 1642020011;
        $copy['vid'] = 0;
        $copy['paidwith'] = '';
        $copy['items'] = 0;
        $copy['name'] = 'Hector';
        $copy['notes'] ='Added by inventory module';
	
	$insert = $wpdb->insert('fin_costs', $copy);		  
}			   	
	
	
	// funcion para crear el gasto base de datos.
	$order = wc_get_order( $order_id );
	echo $order;
	
	    	
	
}; 
         
// add the action 
add_action( 'woocommerce_order_actions_end', 'action_woocommerce_order_actions_end', 10, 1 ); 
