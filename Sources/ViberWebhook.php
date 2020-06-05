<?php

    require("../SSI.php");

    $content = file_get_contents("php://input");

    $content = json_decode($content);

    global $scripturl, $context, $txt, $user_info, $sourcedir, $modSettings;

    $keyboard = array(
        'Type' => "keyboard",
        'DefaultHeight' => "false",
        'BgColor' => "#FFFFFF",
        'Buttons' => array(
            array(
                'Columns' => "3",
                'Rows' => "1",
                'ActionType' => "reply",
                'ActionBody' => $txt['viber_keyb_news'],
                'Text' => $txt['viber_keyb_news'],
                'TextVAlign' => "bottom",
                'TextHAlign' => "center",
            ),
            array(
                'Columns' => "3",
                'Rows' => "1",
                'ActionType' => "reply",
                'ActionBody' => $txt['viber_keyb_subs'],
                'Text' => $txt['viber_keyb_subs'],
                'TextVAlign' => "bottom",
                'TextHAlign' => "center",
            ),
        )
    );

    ## Письмо в личку по заходу
        if ($content->event == 'conversation_started') {
            $data = array(
            'sender' => array(
            'name' => $context['forum_name'],
            'avatar' => $settings['images_url'] . '/viber.png'
            ),
      'type' => 'text',
      'text' => $modSettings['viber_1st_mess'],
      'keyboard' => $keyboard
      );
     echo json_encode($data);
     die();
    }
    ## Письмо в личку по заходу

    if (isset($content->message) && isset($content->message->text)) {
        include_once 'Class-Viber.php';
        if ($content->message->text != '') {
            $sendertext = $content->sender->id;
            $data = array(
            'receiver' => $sendertext,
            'type' => 'text',
            'text' => $modSettings['viber_mess_subs'] . "\n\n" . $boardurl . '/index.php?action=viber&viber_id=' . $sendertext,
            'keyboard' => $keyboard,
            );
        $response = viber_send_message($data);
        var_dump($response);
        exit;
    }
}
    ?>