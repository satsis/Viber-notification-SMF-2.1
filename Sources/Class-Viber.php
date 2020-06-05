<?php

    if (!defined('SMF'))
        die('Hacking attempt...');

class Viber
{
    public static function hooks()
    {
        add_integration_function('integrate_load_theme', __CLASS__ . '::loadTheme', false, __FILE__);
        add_integration_function('integrate_actions', __CLASS__ . '::actions', false, __FILE__);
		add_integration_function('integrate_actions', __CLASS__ . '::actions_webhook', false, __FILE__);
        add_integration_function('integrate_actions', __CLASS__ . '::actions_set_webhook', false, __FILE__);
        //add_integration_function('integrate_display_topic', __CLASS__ . '::displayTopic', false, __FILE__);
        //add_integration_function('integrate_prepare_display_context', __CLASS__ . '::prepareDisplayContext', false, __FILE__);
        add_integration_function('integrate_admin_areas', __CLASS__ . '::adminAreas', false, __FILE__);
        add_integration_function('integrate_admin_search', __CLASS__ . '::adminSearch', false, __FILE__);
        add_integration_function('integrate_modify_modifications', __CLASS__ . '::modifyModifications', false, __FILE__);
        add_integration_function('integrate_menu_buttons', __CLASS__ . '::menuButtons', false, __FILE__);
        add_integration_function('integrate_member_context', __CLASS__ . '::printViberForMember', false, __FILE__);
        add_integration_function('integrate_menu_buttons', __CLASS__ . '::addCopyright', false, __FILE__);
    }

    public static function loadTheme()
    {
        loadLanguage('Viber/');
    }

    /**
     * Добавляем секцию с названием мода в раздел настроек
     * @param array $admin_areas
     * @return void
     */
    public static function adminAreas(&$admin_areas)
    {
        global $txt;

        $admin_areas['config']['areas']['modsettings']['subsections']['viber'] = array($txt['viber_settings']);
    }

    /**
     * Легкий доступ к настройкам мода через быстрый поиск в админке
     *
     * @param array $language_files
     * @param array $include_files
     * @param array $settings_search
     * @return void
     */
    public static function adminSearch(&$language_files, &$include_files, &$settings_search)
    {
        $settings_search[] = array(__CLASS__ . '::settings', 'area=modsettings;sa=viber');
    }

    /**
     * Подключаем настройки мода
     *
     * @param array $subActions
     * @return void
     */
    public static function modifyModifications(&$subActions)
    {
        $subActions['viber'] = array(__CLASS__, 'settings');
    }

    /**
     * Определяем настройки мода
     *
     * @param boolean $return_config
     * @return array|void
     */
    public static function settings($return_config = false)
    {
        global $context, $txt, $scripturl, $modSettings;

        $context['page_title'] = $context['settings_title'] = $txt['viber_settings'];
        $context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=viber';
        $context[$context['admin_menu_name']]['tab_data']['tabs']['viber'] = array('description' => $txt['viber_desc']);

        if (!isset($modSettings['viber_button_after']))
            updateSettings(array('viber_button_after' => $txt['viber_button_after_deaf']));

        $config_vars[] = array('check', 'viber_enable', 'subtext' => $txt['viber_enable_desc']);
        $config_vars[] = array('text', 'viber_url', 'subtext' => $txt['viber_url_desc']);
        $config_vars[] = array('text', 'viber_button_after', 'subtext' => $txt['viber_button_after_desc']);
        $config_vars[] = array('large_text', 'viber_1st_mess', '" style="width:80%');
        $config_vars[] = array('large_text', 'viber_mess_subs', '" style="width:80%');

        $config_vars[] = array('title', 'viber_api_title');
        $config_vars[] = array('desc', 'viber_api_desc');
        $config_vars[] = array('text', 'viber_api', '" style="width:360px');


        if ($return_config)
            return $config_vars;

        // Saving?
        if (isset($_GET['save'])) {
            checkSession();
            $save_vars = $config_vars;
            saveDBSettings($save_vars);
            clean_cache();
            redirectexit('action=admin;area=modsettings;sa=viber');
        }

        prepareDBSettingContext($config_vars);
    }

    /**
     * Функция перехода по ссылке из бота для подтверждения регистрации
     *
     */
    public static function actions(&$actions)
    {
        $actions['viber'] = array('Viber.php', '');
    }

    /**
     * Функция Viber Webhook
     *
     */
    public static function actions_webhook(&$actions)
    {
        $actions['ViberWebhook'] = array('ViberWebhook.php', 'sig');
    }

    /**
     * Функция установки Viber Webhook
     *
     */
    public static function actions_set_webhook(&$actions)
    {
        $actions['ViberSetWebhook'] = array('ViberSetWebhook.php', '');
    }

    /**
     * Функция создания кнопки на бота в меню с выбором расположения
     *
     */
    public static function menuButtons(&$buttons)
    {
        global $txt, $modSettings;

        if (empty($modSettings['viber_enable']))
            return;

        if (!isset($txt['viber_menu']))
            $txt['viber_menu'] = 'Viber';

        $counter = 0;
        foreach ($buttons as $name => $array)
        {
            $counter++;
            if ($name == $modSettings['viber_button_after'])
                break;
        }

        $buttons = array_merge(
            array_slice($buttons, 0, $counter, TRUE),
            array('viberbutton' => array(
                'title' => $txt['viber_menu'], // переменная с названием кнопки
                'href' => $modSettings['viber_url'], // наша ссылка на бота из админки
                'show' => true, // если не хотим показывать кнопку, пишем false
                'sub_buttons' => array(), // вложенные пункты, по умолчанию отсутствуют
                'icon' => 'viber.png', // наша иконка
            )),
            array_slice($buttons, $counter, NULL, TRUE)
        );
    }

    /**
     * Иконка viber в профиле пользователя
     */
    public static function printViberForMember(&$memberContext, $user, $display_custom_fields)
    {
        global $memberContext, $context, $user_profile, $txt, $settings, $modSettings;

        if (empty($modSettings['viber_enable']))
            return;

        if (!empty($user_profile)) {
                $viber_ico = self::getViberByMemberID($user);
                if (!empty($viber_ico)) {
                    $viber_ico = '<a href="'. $modSettings['viber_url'] .'"><img src="' . $settings['images_url'] . '/viber.png" width="16" height="16" alt="'. $txt['viber_settings'] .'" title="'. $txt['viber_settings'] .'"></a>';
                    $memberContext[$user]['post_group'] .= (!empty($memberContext[$user]['post_group']) ? '<br>' : '') . $viber_ico;
                    unset($viber_ico);
                }
            }
        //}
    }

    /**
     * Получение иконки для member id
     * @param int $member_id
     * @return array|bool
     */
    public static function getViberByMemberID($member_id = 0)
    {
        global $smcFunc;

        if (!$member_id) {
            return false;
        }

        // Check cache
        $Viber = cache_get_data('Viber_' . $member_id);

        if (empty($Viber)) {

            $request = $smcFunc['db_query']('', '
			SELECT viber_id
			FROM {db_prefix}members
			WHERE id_member = {int:member_id}
			LIMIT 1', array(
                    'member_id' => $member_id,
                )
            );

            $Viber = array();
            list ($Viber['viber_id']) = $smcFunc['db_fetch_row']($request);
            $smcFunc['db_free_result']($request);

            cache_put_data('Viber_' . $member_id, $Viber);
        }

        if (!empty($Viber['viber_id'])) {
            return ($Viber['viber_id']);
        } else {
            return false;
        }
    }

    public static function addCopyright()
    {
        global $context;

        if ($context['current_action'] == 'credits') {
            $context['copyrights']['mods'][] = '<a href="https://hd.zp.ua" target="_blank">Viber notification</a> &copy; 2020, maxya';
        }
    }

}

    function viber_send_message($data) {
        return viber_request('https://chatapi.viber.com/pa/send_message', $data);
    }

    function viber_request($url, $data = array())
    {
        //require("../SSI.php");
        global $context, $txt, $scripturl, $modSettings;

        if (!isset($data['auth_token'])) {
            $auth_token = $modSettings['viber_api'];
            $data['auth_token'] = $auth_token;
        }
        //$data['auth_token'] = '4881cb0c3a27d34c-ce7661980203fda4-b632d1644b2f0d71';

        $headers = array(
            'Content-Type:application/json'
        );

    //	var_dump($data); #Вывод запроса
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $result .= ' : ' . date("Y-m-d H:i:s");

        $fp = fopen('/home/www/sat-integral/viber.txt', 'a+');
    	fwrite($fp, json_encode($data) ."\r\n");
        fwrite($fp, $result ."\r\n");
        fclose($fp);

        if ($result !== false) {
            $result = json_decode($result);
        }
        curl_close($ch);

        return $result;
    }

    function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }