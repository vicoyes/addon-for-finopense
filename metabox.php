function dariobf_metabox() {
	add_meta_box(
        'woocommerce-order-verifyemail',
        __( 'Cargar de la reserva' ),
        'trusted_list_order_meta_box_content',
        'shop_order',
        'side',
        'default'
    );
}



function trusted_list_order_meta_box_content( $order_id ){ 
    global $wpdb;
		
		
	//echo $order_id->ID;
	
// toma los metdos de pago	
    $gates = WC()->payment_gateways->get_available_payment_gateways();
	$enabled_gateways = [];
	$id_gateways =[];
	
	
if( $gates ) {
    foreach( $gates as $index => $gate ) {
            $enabled_gateways[] = $gate->title;
		    $id_gateways[$index] = $gate->id;

    }
}

$values_key = array_keys($id_gateways);	

$medio_pago = isset( $values['reserva_fun'] ) ? esc_attr( $values['reserva_fun'][0] ) : '';
$medio_pago_p;
	if($medio_pago == ""){
		$medio_pago_p='cod';
	}else{
		$medio_pago_p='no vacio';
	}
	
$html;
	
foreach($enabled_gateways as $index => $enabled_gateway){
	   $html_string ='<option value="'.$values_key[$index].'"'.selected($medio_pago,$values_key[$index]).'>'.$enabled_gateway.'</option>';
	   $html .= $html_string;
	}
	
?>
<select name="reserva_fun" id="reserva_fun">
	<?php
	   echo $html
	?>
</select>
<?php

// obtiene los datos de pedido		
$order = wc_get_order( $order_id->ID);
echo print_r($html);

$date=strtotime($order->date_created);
$id = strval($order->id);
$total = $order->total;

$items_name=[];
$items = $order->get_items();

foreach($items as $item){
		$items_name[] = $item['name'];
	}
//echo gettype($items_name[0]);
$pre_name = $items_name[0];
$name;	
	if($pre_name == NULL){
		$name = 'No tiene producto - Pedido Especial';
	}else{
		$name = $pre_name.' - Pedido Especial';
	}
	
//obtiene el valor select
	
	
// crea el registro en la base de datos	
if (isset($_POST)){
	if($order->status == 'pedido-especial'){
		$copy = array();
        $copy['coid'] = $id;
        $copy['siteid'] = 0;
        $copy['cat'] = 'inventory';
        $copy['amount'] = $total;
        $copy['tr'] = 0.00;
        $copy['datepaid'] = $date;
        $copy['timecr'] = $date;
        $copy['vid'] = 0;
        $copy['paidwith'] = $medio_pago_p;
        $copy['items'] = 0;
        $copy['name'] = $name;
        $copy['notes'] = $order->status;
	
	$wpdb->insert('fin_costs', $copy);
	
	}

}		   	

}



add_action( 'add_meta_boxes', 'dariobf_metabox' );
add_action( 'init', function() {
    register_post_status( 'wc-en-fabricacion', array(
        'label'                     => 'Pedido Especial',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'En fabricación <span class="count">(%s)</span>', 'En fabricación <span class="count">(%s)</span>'),
    ) );
}, 10 );
 
add_filter ( 'wc_order_statuses', function( $estados ) {
    $estados['wc-pedido-especial'] = 'Pedido Especial';
    return $estados;
}, 10, 1 );

// dibuja el elemento	
//echo '<li class="wide">';	
/*echo   '<select name="reserva_fun" id="reserva_fun">
<option value="destiempo">Selecione la cuenta origen</option>';	
foreach($enabled_gateways as $index => $enabled_gateway){
	
	$html_string ='<option value="'.$values_key[$index].'">'.$enabled_gateway.'</option>';
	echo $html_string;
}

echo '</select>';
echo '<button type="submit" class="button save_order button-primary" name="reserva" value="reserva" id="cuenta">Tomar de la Reserva</button>';	
//echo '</li>';*/
