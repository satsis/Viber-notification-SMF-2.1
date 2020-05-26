<?php

    if (!defined('SMF'))
        die('Hacking attempt...');

class Viber
{
    public static function hooks()
    {
        add_integration_function('integrate_load_theme', __CLASS__ . '::loadTheme', false, __FILE__);
        add_integration_function('integrate_actions', __CLASS__ . '::actions', false, __FILE__);
        add_integration_function('integrate_display_topic', __CLASS__ . '::displayTopic', false, __FILE__);
        add_integration_function('integrate_prepare_display_context', __CLASS__ . '::prepareDisplayContext', false, __FILE__);
        add_integration_function('integrate_admin_areas', __CLASS__ . '::adminAreas', false, __FILE__);
        add_integration_function('integrate_admin_search', __CLASS__ . '::adminSearch', false, __FILE__);
        add_integration_function('integrate_modify_modifications', __CLASS__ . '::modifyModifications', false, __FILE__);
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

        $config_vars[] = array('select', 'viber_enable', $txt['viber_where_is']);
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

    public static function actions(&$actions)
    {
        $actions['viber'] = array('Viber.php', '');
    }

}

    function viber_send_message($data) {
        return viber_request('https://chatapi.viber.com/pa/send_message', $data);
    }

    function viber_request($url, $data = array())
    {
        global $context, $txt, $scripturl, $modSettings;
        $auth_token = $modSettings['viber_api'];
        if (!isset($data['auth_token'])) {
            $data['auth_token'] = $auth_token;
        }

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