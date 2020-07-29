<?php
session_start();
ob_start();

require_once 'config.php';


$fallback_url = $_POST['fallback_url'];


if(!isset($_POST['layer_payment_id']) || empty($_POST['layer_payment_id'])){

    header('Location: '.$fallback_url);
    return NULL ;
}
$error = "";
$status = "";
try {

    $data = array(
        'layer_pay_token_id'    => $_POST['layer_pay_token_id'],
        'layer_order_amount'    => $_POST['layer_order_amount'],
        'woo_order_id'     		=> $_POST['woo_order_id'],
    );

    if(verify_hash($data,$_POST['hash'],$access_key,$secret_key) && !empty($data['woo_order_id'])){

        $env = $sandbox;
        if($env != "yes"){  $env = "live"; }
        $layer_api = new LayerApi($env,$access_key,$secret_key);
        $payment_data = $layer_api->get_payment_details($_POST['layer_payment_id']);


        if(isset($payment_data['error'])){
            $error = "Layer: an error occurred E14".$payment_data['error'];
        }

        if(empty($error) && isset($payment_data['id']) && !empty($payment_data)){
            if($payment_data['payment_token']['id'] != $data['layer_pay_token_id']){
                $error = "Layer: received layer_pay_token_id and collected layer_pay_token_id doesnt match";
            }
            elseif($data['layer_order_amount'] != $payment_data['amount']){
                $error = "Layer: received amount and collected amount doesnt match";
            }
            else {
                switch ($payment_data['status']){
                    case 'authorized':
                    case 'captured':
                        $status = "Payment captured: Payment ID ". $payment_data['id'];
                        break;
                    case 'failed':								    
                    case 'cancelled':
                        $status = "Payment cancelled/failed: Payment ID ". $payment_data['id'];                        
                        break;
                    default:
                        $status = "Payment pending: Payment ID ". $payment_data['id'];
                        exit;
                    break;
                }
            }
        } else {
            $error = "invalid payment data received E98";
        }
    } else {
        $error = "hash validation failed";
    }

} catch (Throwable $exception){

   $error =  "Layer: an error occurred " . $exception->getMessage();
    
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Donation Status</title>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="<?php echo $remote_script;?>"></script>

<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" >

</head>
<body>

<div class="main">
    <div id="alertinfo">
    <?php 
		echo "Donation Id:". $_POST['woo_order_id']."<br />";
        if(!empty($error))
            echo $error;
        else
            echo $status;
    ?>
    </div>
    <div id="go">
        <a href="index.php">Another Payment</a>
    </div>
</div>

</body>
</html>