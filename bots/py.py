#!/usr/bin/env python3

import requests

LONG_POLLING_TIMEOUT = 20
REQUEST_TIMEOUT = LONG_POLLING_TIMEOUT * 2
API_URL = "https://api.telegram.org/bot{token}/{method}"


class TelegBot:
    def __init__(self, token):
        self.token = token
    
    def connect(self):
        self.data = self.__api_request('getMe')
        self.quit = False
    
    def get_bot_token(self):
        return self.token

    def get_bot_username(self):
        return self.data["username"]
    
    
    def run(self):
        last_message_update_id = 0
        while not self.quit:
            response = self.__api_request('getUpdates', {
                "offset": last_message_update_id + 1,
                "limit": 100,
                "timeout": LONG_POLLING_TIMEOUT
            })
            for update in response:
                if update["update_id"] > last_message_update_id:
                    last_message_update_id = update["update_id"]
                self.__run_event(update["message"])
        
        response = self.__api_request('getUpdates', {
            "offset": last_message_update_id + 1,
            "limit": 1,
            "timeout": 0
        })
     
    def send_message(self, chat_id, text, disable_web_page_preview=False, reply_to_message_id=None, reply_markup=None):
        response = self.__api_request('sendMessage', {
            "chat_id": chat_id,
            "text": text,
            "disable_web_page_preview": disable_web_page_preview,
            "reply_to_message_id": reply_to_message_id,
            "reply_markup": reply_markup
        })
        self.__run_event(response)
    
    def __void_callback(self, data={}):
        return

    def __api_request(self, method, parameters={}, files=None):
        url = API_URL.format(token=self.get_bot_token(), method=method)
        http_method = "get"
        response = requests.request(http_method, url, timeout=REQUEST_TIMEOUT, params=parameters, files=files)
        result = response.json()
        return result["result"]

    
    def __run_event(self, event):
        if "text" in event:
            print("[" + event["from"]["first_name"] + "]: " + event["text"])
            if event["from"]["id"] == 43804645:
              if event["text"] == "/quit":
                  self.quit = True
            if event["text"] == "ping":
                self.send_message(event["chat"]["id"], "pong")
        elif "new_chat_member" in event:
            print("New member(" + event["chat"]["title"] + "): " + event["new_chat_member"]["first_name"])
        elif "left_chat_member" in event:
            print("Left member(" + event["chat"]["title"] + "): " + event["left_chat_member"]["first_name"])
        else:
            print("Something happened")



bot = TelegBot('TOKEN_HERE')
bot.connect()
bot.run()







