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
	
	define("IN_MYBB", "1");
	require("./global.php");	
	global $mybb;
	$ui = $mybb->user['uid'];
	$ug = $mybb->user['usergroup'];
	
	if (!$mybb->user['uid'])
	{
	error_no_permission();
	}
	$ban = explode(",",$mybb->settings['npgate_ban']) ;
	if(in_array($ui,$ban))
	{
	error_no_permission();
	}
	$bang = explode(",",$mybb->settings['npgate_bang']) ;
	if(in_array($ug,$bang))
	{
	error_no_permission();
	}
	
$query = $db->simple_select('usergroups', 'title, gid', '', array('order_by' => 'gid', 'order_dir' => 'asc'));
while($group = $db->fetch_array($query, 'title, gid'))
{
	$groups[$group['gid']] = $group['title'];
}


$query = $db->simple_select('npgate', '*', '', array('order_by' => 'price', 'order_dir' => 'ASC'));
while ($npgate = $db->fetch_array($query))
{
	$bgcolor = alt_trow();
	$npgate['num'] = intval($npgate['num']);
	$npgate['title'] = htmlspecialchars_uni($npgate['title']);
	$t= " تومان ";
	$npgate['price'] = floatval($npgate['price'])."$t";
	$npgate['usergroup'] = $groups[$npgate['group']];

	if($npgate['time']== 1)
	{
	$time= "روز";
}	
	if($npgate['time']== 2)
	{
	$time= "هفته";
}	
	if($npgate['time']== 3)
	{
	$time= "ماه";
}	
	if($npgate['time']== 4)
	{
	$time= "سال";
}	

	$period = intval($npgate['period']);
	$npgate['period'] = intval($npgate['period'])." ".$time;
	$uid = $mybb->user['uid'];
$query5 = $db->query("SELECT * FROM ".TABLE_PREFIX."npgate_tractions WHERE uid=$uid AND stauts = 1");
$check5 = $db->fetch_array($query5);
if ($check5)
{
$note = "<div class=\"red_alert\">به دلیل اینکه شما قبلاً یکی از این بسته ها را خریداری کرده اید و زمان عضویت شما به پایان نرسیده است ، نمی توانید  بسته ی جدیدی را خریداری نمایید </div>";
$buybutton = "
					<input type='image' src='{$mybb->settings['bburl']}/images/buy-pack.png' border='0'  name='submit'alt='خرید بسته {$npgate['title']}' />";

}
else{
$buybutton = " 							<form action='{$mybb->settings['bburl']}/nextpay_gate.php' method='post'>
<input type='hidden' name='npgate_num' value='{$npgate['num']}' /> 
					<input type='image' src='{$mybb->settings['bburl']}/images/buy-pack.png' border='0'  name='submit'alt='خرید بسته {$npgate['title']}' />

					</form>
";
	
}	
	eval("\$list .= \"".$templates->get('npgate_list_table')."\";");
}

if (!$list)
{
	eval("\$list = \"".$templates->get('npgate_no_list')."\";");
}

eval("\$npgatepage = \"".$templates->get('npgate_list')."\";");
output_page($npgatepage);
?>