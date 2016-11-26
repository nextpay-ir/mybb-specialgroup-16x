<?php
/**
 * Created by NextPay.ir
 * author: Nextpay Company
 * ID: @nextpay
 * Date: 09/22/2016
 * Time: 5:05 PM
 * Website: NextPay.ir
 * Email: info@nextpay.ir
 * @copyright 2016
 * @package NextPay_Gateway
 * @version 1.0
 */

	include_once('nusoap.php');
	define("IN_MYBB", "1");
	require("./global.php");
	
	if($_SERVER['REQUEST_METHOD']!="POST") die("Forbidden!");

	$merchantID = $mybb->settings['nextpay_apikey'];
	$num = $_POST['npgate_num'];
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."npgate WHERE num=$num");
    $npgate = $db->fetch_array($query);
	$amount = $npgate['price']; //Amount will be based on Toman
	$callBackUrl = "{$mybb->settings['bburl']}/nextpay_verify.php?num={$npgate['num']}";
	$desc = "{$npgate['description']}  ({$mybb->user['username']})";
	
if ($mybb->settings['npgate_soap'] == 0)
{
	$client = new SoapClient('http://api.nextpay.org/gateway/token.wsdl', array('encoding'=>'UTF-8'));
	$res = $client->TokenGenerator(
	array(
					'api_key' 	=> $merchantID ,
					'amount' 		=> $amount ,
					'order_id' 		=> time() ,
					'callback_uri' 	=> $callBackUrl

		)
	);
}
if ($mybb->settings['npgate_soap'] == 1)
{
	$client = new nusoap_client('http://api.nextpay.org/gateway/token.wsdl', 'wsdl');
	$res = $client->call('TokenGenerator', array(
			array(
					'api_key' 	=> $merchantID ,
					'amount' 		=> $amount ,
					'order_id' 		=> time() ,
					'callback_uri' 	=> $callBackUrl

		)
	
	
	));
}
	
	$res = $res->TokenGeneratorResult;
	if (intval($res->code) == -1){
	Header('Location: http://api.nextpay.org/gateway/payment/' . $res->trans_id );
	}else{
		echo'ERR:'.$res->code ;
	}
?>
