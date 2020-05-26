<?php
if (!defined('SMF'))
	die('Hacking attempt...');

    include_once 'Class-Viber.php';

	global $scripturl, $context, $txt, $user_info, $sourcedir, $user_profile, $user_settings, $smcFunc;
	loadTemplate('Viber');

	$context['sub_template'] = 'manual';
	
//		if (!isset($_GET['help']) || !is_string($_GET['help']))
//		fatal_lang_error('no_access', false);

    $viber_id = isset($_GET['viber_id']) ? trim($_GET['viber_id']) : false;
    $viber_id = str_replace(" ", "+", $viber_id);
    $d = strlen($viber_id);
    $user = $user_settings['real_name'];
    $user_id = $user_info['id'];
    $member_ip = get_client_ip($ipaddress);
    $group_id = $user_settings['id_group'];

    if (preg_match("/==\z/i", $viber_id) and strlen($viber_id) == '24' and $user_id != '') {
		$result = $smcFunc['db_query']('', "UPDATE smf_members SET viber_id='$viber_id', viber_on='1' WHERE id_member='$user_id'");
		if ($result == 'true')
		{
		$viber_ok = "Вы успешно подписались";
		$viber_mess = "Вы успешно подписались на логин " . $user . " с ip адреса: " . $member_ip;
		$data = [
		'receiver' => $viber_id,
		'type' => 'text',
		'text' => $viber_mess
		];
		$response = viber_send_message($data);
		}
		else
		{
		$viber_ok = "Что-то пошло не так";
		}
     
        $context['viber_id'] = "Всё нормально, вы подписались!";

	} else {

	$context['viber_id'] = "Ошибка!!! Вы не авторизированы на форуме";

	}
?>