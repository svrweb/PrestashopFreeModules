<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
@include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/checkout.php');

$checkout = new checkout();
echo $checkout->execPayment($cart);

@include_once(dirname(__FILE__).'/../../footer.php');

?>
