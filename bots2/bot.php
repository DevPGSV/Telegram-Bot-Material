<?php

define('BOT_TOKEN', '123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA');
define('LONG_POLLING_TIMEOUT', 20);

run();


function onTextMessageReceived($event) {
  $from = $event['from']['first_name'];
  $text = $event['text'];
  echo "[$from] << $text\n";
  if (strtolower($text) == '/ping') {
    sendMsg($event['from']['id'], 'Pong', $event['message_id']);
  } else if (strtolower($text) == '/getme') {
    sendMsg($event['from']['id'], '<pre>' . json_encode(getMe(), JSON_PRETTY_PRINT) . '</pre>', $event['message_id']);
  } else if (strtolower($text) == '/debug') {
    print_r($event);
    sendMsg($event['from']['id'], '<pre>' . json_encode($event, JSON_PRETTY_PRINT) . '</pre>', $event['message_id']);
  } else if (strtolower($text) == '/start') {
    sendMsg($event['from']['id'], "Hi", $event['message_id']);
  } else if (strtolower($text) == '/inlinekeyboard') {
    $k = [
      [
        ['text' => 'LibreLabUCM', 'callback_data' => 'Button pressed'],
      ],
      [
        ['text' => 'Web', 'url' => 'https://librelabucm.org/'],
        ['text' => 'Calendario', 'url' => 'https://calendar.librelabucm.org/'],
      ],
      [
        ['text' => 'Edit this message', 'callback_data' => 'editthismessage'],
      ],
    ];
    sendMsg($event['from']['id'], "Testing with inline keyboards", $event['message_id'], ['inline_keyboard'=>$k]);
  }
}


function onCallbackQueryReceived($event) {
  $from = $event['from']['first_name'];
  echo "[$from] [CallbackQuery] ".$event["data"]."\n";
  if ($event["data"] == "editthismessage") {
    sendApiRequest('editMessageText', [
      'chat_id' => $event['message']['chat']['id'],
      'message_id' => $event['message']['message_id'],
      'text' => 'Message edited! :)',
    ]);
  } else {
    sendApiRequest('answerCallbackQuery', [
      'callback_query_id' => $event['id'],
      'text' => 'Patience you must have my young padawan',
      //'show_alert' => true,
    ]);
  }
}







function run_event($event) {
  if (!empty($event['message'])) {
    $event = $event['message'];
    if (!empty($event["text"])) {
      onTextMessageReceived($event);
    } else {
      echo("Something happened2\n");
      print_r($event);
    }
  } else if (!empty($event['callback_query'])) {
    $event = $event['callback_query'];
    onCallbackQueryReceived($event);
  } else if (!empty($event['channel_post'])) {
    // ignore
  } else {
    echo("Something happened1\n");
    print_r($event);
  }
}

function run() {
  $last_message_update_id = 0;
  while (true) {
    $response = sendApiRequest('getUpdates', [
      'offset' => $last_message_update_id + 1,
      'limit' => 100,
      'timeout' => LONG_POLLING_TIMEOUT,
    ]);
    if (!is_array($response) || (isset($response['ok']) && !$response['ok'])) {
      sleep(5);
      continue;
    }
    foreach ($response as $update) {
        if (!empty($update["update_id"]) && $update["update_id"] > $last_message_update_id) {
        $last_message_update_id = $update["update_id"];
        run_event($update);
      }
    }
  }
}

function sendMsg($id, $text, $reply_to_message_id = "0", $reply_markup = null, $disable_web_page_preview = false) {
  if (!empty($reply_markup)) $reply_markup = json_encode($reply_markup);
  $r = sendApiRequest('sendMessage', [
    'chat_id' => $id,
    'text' => $text,
    'reply_markup' => $reply_markup,
    'parse_mode' => 'HTML',
    'reply_to_message_id' => $reply_to_message_id,
    'disable_web_page_preview' => $disable_web_page_preview
  ]);
  echo "[".$r["chat"]["first_name"]."] >> " . str_replace("\n", '\n', $r["text"]) . "\n";
  return $r;
}

function getMe() {
  $r = sendApiRequest('getMe');
  return $r;
}


function sendApiRequest($method, $params = []) {
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'https://api.telegram.org/bot'. BOT_TOKEN . '/' . $method . '?' . http_build_query($params),
    CURLOPT_SSL_VERIFYPEER => false
  ]);
  $d = json_decode(curl_exec($curl), true);
  if (!isset($d['result'])) {
    print_r($d);
    return $d;
  }
  return $d['result'];
}
