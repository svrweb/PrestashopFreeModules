<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/checkout.php');

/* Retrieve entered information from 2checkout form post. */
$sid					= Configuration::get('CHECKOUT_SID');
$secret_word			= Configuration::get('CHECKOUT_SECRET');
$credit_card_processed	= $_REQUEST['credit_card_processed'];
$order_number			= $_REQUEST['order_number'];
$cart_id 				= $_REQUEST['merchant_order_id'];
$secure_key             = $_REQUEST['secure_key'];

$cart=new Cart($cart_id);
$total = number_format($cart->getOrderTotal(true, 3), 2, '.', '');

//Check the hash
if ($_REQUEST['demo'] == 'Y') {
$order_number = 1;
}
$compare_string = $secret_word . $sid . $order_number . $total;
$compare_hash1 = strtoupper(md5($compare_string));
$compare_hash2 = $_REQUEST['key'];

if ($compare_hash1 == $compare_hash2) {

	$message = '2Checkout Order Number: ' . $order_number;
	/* Create Necessary variables for order placement */
	$currency = new Currency(intval(isset($_REQUEST['currency_payement']) ? $_REQUEST['currency_payement'] : $cookie->id_currency));
	$checkout = new checkout();
	$checkout->validateOrder($cart_id, _PS_OS_PAYMENT_, $total, $checkout->displayName,  $message, array(), NULL, false, $secure_key);
	$order = new Order($checkout->currentOrder);
	/*  Once complete, redirect to order-confirmation.php */
	$url=__PS_BASE_URI__."order-confirmation.php?id_cart={$cart_id}&id_module={$checkout->id}&id_order={$checkout->currentOrder}";

	echo '<script type="text/javascript">location.replace("'.$url.'")</script>';
} else {
	echo 'Hash Mismatch! Please contact the seller directly for assistance.</br>';
	echo 'Total: '.$total.'</br>';
	echo '2CO Total: '.$_REQUEST['total'];
}

?>
