<?php

define ("LONG_POLLING_TIMEOUT", 20);
define("REQUEST_TIMEOUT", LONG_POLLING_TIMEOUT * 2);

$bot = new TelegBot('TOKEN_HERE');
$bot->connect();
$bot->run();



class TelegBot {
   private $token;
   private $data;
   private $quit;
   function __construct($token) {
      $this->token = $token;
      $this->quit = true;
   }
   
   function connect() {
      $this->data = $this->sendApiRequest('getMe');
      $this->quit = false;
   }
   
   function getBotUsername() {
      return $this->data["username"];
   }
   
   function run() {
      $last_message_update_id = 0;
      while (! $this->quit) {
         $response = $this->sendApiRequest('getUpdates', [
            'offset' => $last_message_update_id + 1,
            'limit' => 100,
            'timeout' => LONG_POLLING_TIMEOUT,
         ]);
         foreach ($response['result'] as $update) {
            if ($update["update_id"] > $last_message_update_id) {
                $last_message_update_id = $update["update_id"];
            }
            $this->run_event($update["message"]);
         }
      }
      
      $response = $this->sendApiRequest('getUpdates', [
         'offset' => $last_message_update_id + 1,
         'limit' => 1,
         'timeout' => 0,
      ]);
   }
   
   function sendMsg($id, $text, $keyboard = null, $reply_to_message_id = "0", $disable_web_page_preview = false) {
      $r = $this->sendApiRequest('sendMessage',
         array(
            'chat_id' => $id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_to_message_id' => $reply_to_message_id,
            'disable_web_page_preview' => $disable_web_page_preview
         )
      );
      $this->run_event($r["result"]);
      return $r;
   }
   
   function sendApiRequest($method, $params = array()) {
      $curl = curl_init();
      curl_setopt_array($curl, array(
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_URL => 'https://api.telegram.org/bot'. $this->token . '/' . $method . '?' . http_build_query($params),
          CURLOPT_SSL_VERIFYPEER => false
      ));
      $d = curl_exec($curl);
      return json_decode($d, true);
   }
   
   function run_event($event) {
      if (!empty($event["text"])) {
         echo "[" . $event["from"]["first_name"] . "]: " . $event["text"] . "\n";
         if ($event["from"]["id"] == "43804645") {
            if ($event["text"] == "/quit") {
                $this->quit = true;
            }
            
         }
         if ($event["text"] == "ping") {
            $this->sendMsg($event["chat"]["id"], "pong");
         }
      } else if (!empty($event["new_chat_member"])) {
         echo "New member(" . $event["chat"]["title"] . "): " . $event["new_chat_member"]["first_name"] . "\n";
      } else if (!empty($event["left_chat_member"])) {
         echo "Left member(" . $event["chat"]["title"] . "): " . $event["left_chat_member"]["first_name"] . "\n";
      } else {
         echo "Something happened\n";
      }
   }
}