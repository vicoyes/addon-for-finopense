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
/*$medio_pago_p;
	if($medio_pago == ""){
		$medio_pago_p='cod';
	}else{
		$medio_pago_p='no vacio';
	}*/
	
$html="";
	
foreach($enabled_gateways as $index => $enabled_gateway){
	   $html_string ='<option value="'.$values_key[$index].'"'.selected($medio_pago,$values_key[$index]).'>'.$enabled_gateway.'</option>';
	   $html .= $html_string;
	}
	
?>
<label><input type="checkbox" id="pedido_reserva" value="pedido_reserva" name="pedido_reserva" > Tomar de la Reserva</label><br>
<p><b>Cuenta Origen:</b></p>
<select name="reserva_fun" id="reserva_fun">
	<option value="nulo" name="no_select_payment">Selecciona una opcion<option>
	<?php
	   echo $html
	?>
</select>
<?php

	
// obtiene los datos de pedido		
$order = wc_get_order( $order_id->ID);

$date=strtotime($order->date_created);
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
$id = strval($order->id);	
$check_reserva = $order->get_meta('check_reserva');
//echo $check_reserva;
if($check_reserva == 'pedido_reserva'){
	echo "<script>document.querySelector('#pedido_reserva').checked=true; document.querySelector('#pedido_reserva').disabled=true </script>";
}else{
	echo "<script>document.querySelector('#pedido_reserva').checked=false </script>";
	
}	

/*if($order->get_meta('reserva')!= 'nulo'){
	echo "<script>document.querySelector('#reserva_fun').disabled = true; document.querySelector('#reserva_fun').value='".$order->get_meta('reserva')."';</script>";
}*/	
		
if (isset($_POST)){



	
	if($check_reserva == 'pedido_reserva'){
		$copy = array();
        $copy['coid'] = $id;
        $copy['siteid'] = 0;
        $copy['cat'] = 'pedido-especial';
        $copy['amount'] = $total;
        $copy['tr'] = 0.00;
        $copy['datepaid'] = $date;
        $copy['timecr'] = $date;
        $copy['vid'] = 0;
        $copy['paidwith'] = $order->get_meta('reserva');
        $copy['items'] = 0;
        $copy['name'] =$name;
        $copy['notes'] ="Order ID: ".$id;
	
	$wpdb->insert('fin_costs', $copy);
	
	}
	
	$registros = $wpdb->get_row("SELECT * FROM `fin_costs` WHERE coid = $order->id ");
	if($registros){
 ?>	
<table style="width: 100%; text-align: center; background: #efefef;">
	<p><b>Datos Gasto Reservar</b></p>	
<thead>
  <tr>
    <th>ID:</th>
    <th>Fecha:</th>
    <th>Monto:</th>
    <th>Medio:</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td>
	  <?php
	   echo $registros->coid;
	?>
	  </td>
    <td>
	  <?php
	   echo gmdate("Y-m-d", $registros->datepaid);
	  ?>
	  </td>
    <td>
	  <?php
	   echo $registros->amount;
	  ?>
	  </td>
    <td>
	   <?php
	   echo $registros->paidwith;
	  ?>
	  </td>
  </tr>
</tbody>
</table>
<?php	
}
	

  }		   	

}


add_action( 'save_post', 'myplugin_save_postdata' );
function myplugin_save_postdata( $post_id ) {
$order = wc_get_order($post_id);
$order->get_meta('reserva');
  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
 if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return $post_id;

  // Check the user's permissions. If want
  if ( 'page' == $_POST['post_type'] ) {

    if ( ! current_user_can( 'edit_page', $post_id ) )
        return $post_id;

  } else {

    if ( ! current_user_can( 'edit_post', $post_id ) )
        return $post_id;
  }


  // Sanitize user input. if you want
  $mydata = sanitize_text_field( $_POST['reserva_fun'] );
  $reserva = sanitize_text_field( $_POST['pedido_reserva'] );

  // Update the meta field in the database.
 	
  $order->update_meta_data( 'reserva',$mydata );
  $order->update_meta_data( 'check_reserva',$reserva );
  $order->save();
}

// Borrar gastos al borrar order woocommerce_trash_order
add_action( 'wp_trash_post', 'action_woocommerce_trash_order', 10, 1);
function action_woocommerce_trash_order( $order_id ) {
	global $wpdb;
	$order = wc_get_order($order_id);

	
    	$id = strval($order_id);
    	$table = 'fin_costs';
   		$wpdb->delete( $table, array( 'coid' => $id ) );
	

}; 
         
// add the action 






add_action( 'add_meta_boxes', 'dariobf_metabox' );
/*add_action( 'init', function() {
    register_post_status( 'wc-pedido-especial', array(
        'label'                     => 'Pedido Especial',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Pedido Especial <span class="count">(%s)</span>', 'Pedido Especial <span class="count">(%s)</span>'),
    ) );
}, 10 );
 
add_filter ( 'wc_order_statuses', function( $estados ) {
    $estados['wc-pedido-especial'] = 'Pedido Especial';
    return $estados;
}, 10, 1 );*/

