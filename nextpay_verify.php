<?

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

	if($_SERVER['REQUEST_METHOD']!="POST") die("Forbidden!");
	
		define("IN_MYBB", "1");
		require("./global.php");
	if (!$mybb->user['uid'])
	error_no_permission();
		$au = $_GET['Authority'];
	    $api_key = $mybb->settings['nextpay_apikey'];
		$num = $_GET['num'];
	    $query0 = $db->query("SELECT * FROM ".TABLE_PREFIX."npgate WHERE num=$num");
        $npgate0 = $db->fetch_array($query0);
		$amount = $npgate0['price']; 
		$gid = $npgate0['group'];
		$pgid = $mybb->user['usergroup'];
		$uid = $mybb->user['uid'];
		$time = $npgate0['time'];		
		$period = $npgate0['period'];
		$trans_id = $_POST['trans_id'];
		$order_id = $_POST['order_id'];
		$bank = $npgate0['bank'];
		if(isset($order_id) && isset($order_id))
		{
					if ($mybb->settings['npgate_soap'] == 0)
				{
						$client = new SoapClient('http://api.nextpay.org/gateway/verify.wsdl', array('encoding'=>'UTF-8'));
						$res = $client->PaymentVerification(
									array(
									'api_key'	 => $api_key ,
									'trans_id' 	 => $trans_id ,
									'amount' 	 => $amount ,
									'order_id'	 => $order_id
								)
						);
				}
				if ($mybb->settings['npgate_soap'] == 1)
				{
					$client = new nusoap_client('http://api.nextpay.org/gateway/verify.wsdl', 'wsdl');
					$res = $client->call("PaymentVerification", array(
								array(
									'api_key'	 => $api_key ,
									'trans_id' 	 => $trans_id ,
									'amount' 	 => $amount ,
									'order_id'	 => $order_id
								)
					));
				}
					
						$res = $res->PaymentVerificationResult;
						$res = intval($res->code);
						$refid = $trans_id;
		}else{
		$res = 0;
		$refid = 0;
		//$info = "عملیات پرداخت توسط کاربر کنسل شده است";
		}
						
$query1 = $db->simple_select("npgate_tractions", "*", "trackid='$refid'");
$check1 = $db->fetch_array($query1);
if ($check1)
{
$info = "این تراکنش قبلاً ثبت شده است. بنابراین شما نمی‌توانید به صورت غیر مجاز از این سیستم استفاده کنید.";
}
else
{

$query2 = $db->simple_select("npgate", "*", "`num` = '$num'");
while($check = $db->fetch_array($query2))
{
if ($amount != $check['price'])
{
$info = "اطلاعات داده شده اشتباه می باشد . به همین دلیل عضویت انجام نشد.";
}

function code_error($error_code)
    {
        $error_code = intval($error_code);
        $error_array = array(
            0 => "Complete Transaction",
	    -1 => "Default State",
	    -2 => "Bank Failed or Canceled",
	    -3 => "Bank Payment Pendding",
	    -4 => "Bank Canceled",
	    -20 => "api key is not send",
	    -21 => "empty trans_id param send",
	    -22 => "amount in not send",
	    -23 => "callback in not send",
	    -24 => "amount incorrect",
	    -25 => "trans_id resend and not allow to payment",
	    -26 => "Token not send",
	    -30 => "amount less of limite payment",
	    -32 => "callback error",
	    -33 => "api_key incorrect",
	    -34 => "trans_id incorrect",
	    -35 => "type of api_key incorrect",
	    -36 => "order_id not send",
	    -37 => "transaction not found",
	    -38 => "token not found",
	    -39 => "api_key not found",
	    -40 => "api_key is blocked",
	    -41 => "params from bank invalid",
	    -42 => "payment system problem",
	    -43 => "gateway not found",
	    -44 => "response bank invalid",
	    -45 => "payment system deactived",
	    -46 => "request incorrect",
	    -48 => "commission rate not detect",
	    -49 => "trans repeated",
	    -50 => "account not found",
	    -51 => "user not found"
        );

        return $error_array[$error_code];
    }

if($res == 0)
{
		$query1 = $db->simple_select('usergroups', 'title, gid', '1=1');
        while($group = $db->fetch_array($query1))
{
	$groups[$group['gid']] = $group['title'];
}
		$query5 = $db->simple_select('users', 'username, uid', '');
        while($uname1 = $db->fetch_array($query5, 'username, uid'))
{
	$usname[$uname1['uid']] = $uname1['username'];
}

} 
else{
$info = code_error($res);
}
}
if	($time == "1")
{
$dateline = strtotime("+{$period} days");
}

if	($time == "2")
{
$dateline = strtotime("+{$period} weeks");
}
if	($time == "3")
{
$dateline = strtotime("+{$period} months");
}
if	($time == "4")
{
$dateline = strtotime("+{$period} years");
}
$stime = time();
$add_traction = array(
'packnum' => $num,
'uid' => $uid,
'gid' => $gid ,
'pgid' => $pgid ,
'stdateline' => $stime,
'dateline' => $dateline,
'trackid' => $refid,
'payed' => $amount,
'stauts' => "1",
);
if ($db->table_exists("bank_pey") && $bank != 0)
{
	$query7 = $db->simple_select("bank_pey", "*", "`uid` = '$uid'");
    $bankadd = $db->fetch_array($query7);
    $bank_traction = array(
    'uid' => $uid,
    'tid' => 0,
    'pid' => 0,
    'pey' => $bank ,
    'type' => '<img src="'.$mybb->settings['bburl'].'/images/inc.gif">',
    'username' => "مدیریت",
    'time' => $stime,
     'info' => "خرید از درگاه نکست پی",
);
	
		if(!$bankadd)
		{
$add_money = array(
'uid' => $uid,
'username' => $usname[$uid],
'pey' => $bank ,
);
                   $db->insert_query("bank_pey", $add_money);
				   $db->insert_query("bank_buy", $bank_traction);
		}
		if($bankadd)
		{
		$pey = $bankadd['pey'];
		$type='<img src="'.$mybb->settings['bburl'].'/images/inc.gif">';
                   $db->query("update ".TABLE_PREFIX."bank_pey set pey=$pey+$bank where uid=$uid");
                   $db->insert_query("bank_buy", $bank_traction);

		}
		
}
else{
$bank = "0";
}
$db->insert_query("npgate_tractions", $add_traction);
$db->update_query("users", array("usergroup" => $gid), "`uid` = '$uid'");
$expdate = my_date($mybb->settings['dateformat'], $dateline).", ".my_date($mybb->settings['timeformat'], $dateline);
$profile_link = "[url={$mybb->settings['bburl']}/member.php?action=profile&uid={$uid}]{$usname[$uid]}[/url]";
$profile_link1 = build_profile_link($usname[$uid], $uid, "_blank");
$info = preg_replace(
			array(
				'#{username}#',
				'#{group}#',
				'#{refid}#',
				'#{expdate}#',
				'#{bank}#',
				
			),
			array(
				$profile_link1,
				$groups[$gid],
				$refid,
				$expdate,
				$bank,
				
			),
			$mybb->settings['npgate_note']
		);
$username = $mybb->user['username'];
// Notice User By PM
	require_once MYBB_ROOT."inc/datahandlers/pm.php";
	$pmhandler = new PMDataHandler();
		$from_id = intval($mybb->settings['npgate_uid']);
		$recipients_bcc = array();
		$recipients_to = array(intval($uid));
        $subject = "گزارش پرداخت";
		$message = preg_replace(
			array(
				'#{username}#',
				'#{group}#',
				'#{refid}#',
				'#{expdate}#',
				'#{bank}#',
				
			),
			array(
				$profile_link,
				$groups[$gid],
				$refid,
				$expdate,
				$bank,
				
			),
			$mybb->settings['npgate_pm']
		);
		$pm = array(
			'subject' => $subject,
			'message' => $message,
			'icon' => -1,
			'fromid' => $from_id,
			'toid' => $recipients_to,
			'bccid' => $recipients_bcc,
			'do' => '',
			'pmid' => ''
		);
		
		$pm['options'] = array(
			"signature" => 1,
			"disablesmilies" => 0,
			"savecopy" => 1,
			"readreceipt" => 1
		);
	
		$pm['saveasdraft'] = 0;
		$pmhandler->admin_override = true;
		$pmhandler->set_data($pm);
			if($pmhandler->validate_pm())
	{
		$pmhandler->insert_pm();
	}

// Notice Admin By PM
	require_once MYBB_ROOT."inc/datahandlers/pm.php";
	$pmhandler = new PMDataHandler();
	$uidp=$mybb->settings['npgate_uid'];
		$from_id = intval($mybb->settings['npgate_uid']);
		$recipients_bcc = array();
		$recipients_to = array(intval($uidp));
        $subject = "عضویت کاربر در گروه ویژه";
		$message = preg_replace(
			array(
				'#{username}#',
				'#{group}#',
				'#{refid}#',
				'#{expdate}#',
				'#{bank}#',
				
			),
			array(
				$profile_link,
				$groups[$gid],
				$refid,
				$expdate,
				$bank,
				
			),
			"کاربر [B]{username}[/B] با شماره تراکنش [B]{refid}[/B] در گروه [B]{group}[/B] عضو شد.
			تاریخ پایان عضویت:[B]{expdate}[/B]"
			);
		$pm = array(
			'subject' => $subject,
			'message' => $message,
			'icon' => -1,
			'fromid' => $from_id,
			'toid' => $recipients_to,
			'bccid' => $recipients_bcc,
			'do' => '',
			'pmid' => ''
		);
		
		$pm['options'] = array(
			"signature" => 1,
			"disablesmilies" => 0,
			"savecopy" => 1,
			"readreceipt" => 1
		);
	
		$pm['saveasdraft'] = 0;
		$pmhandler->admin_override = true;
		$pmhandler->set_data($pm);
		
	if($pmhandler->validate_pm())
	{
		$pmhandler->insert_pm();
	}
		
}
}
eval("\$verfypage = \"".$templates->get('npgate_payinfo')."\";");
output_page($verfypage);



?>	
