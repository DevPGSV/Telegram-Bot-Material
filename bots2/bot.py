import requests
import json


BOT_TOKEN='123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'
LONG_POLLING_TIMEOUT = 20
REQUEST_TIMEOUT = LONG_POLLING_TIMEOUT * 2

quit=False


def on_receive_message(event):
    message_from = event['from']['first_name']
    text = event['text']
    print("[", message_from,"] << ", text, sep="")
    if text.lower() == "/ping":
        send_message(event['from']['id'], 'Pong', False, event['message_id'])
    elif text.lower() == "/getme":
        send_message(event['from']['id'], '<pre>' + json.dumps(api_request('getMe'), sort_keys=True, indent=2) + '</pre>', False, event['message_id']);
    elif text.lower() == "/debug":
        send_message(event['from']['id'], '<pre>' + json.dumps(event, sort_keys=True, indent=2) + '</pre>', False, event['message_id']);
    elif text.lower() == "/start":
        send_message(event['from']['id'], 'Hi', False, event['message_id']);
    elif text.lower() == "/inlinekeyboard":
        k=[
            [
                {'text': 'LibreLabUCM', 'callback_data': 'Button pressed'},
            ],
            [
                {'text': 'Web', 'url': 'https://librelabucm.org/'},
                {'text': 'Calendario', 'url': 'https://calendar.librelabucm.org/'},
            ],
            [
                {'text': 'Edit this message', 'callback_data': 'editthismessage'},
            ],
        ];
        send_message(event['from']['id'], 'Testing with inline keyboards', False, event['message_id'], {'inline_keyboard':k});

def on_receive_callbackQuery(event):
    message_from = event['from']['first_name'];
    print("[", message_from,"] [CallbackQuery] ", event["data"], sep="")
    if event["data"] == "editthismessage":
        api_request('editMessageText', {
            "chat_id": event['message']['chat']['id'],
            "message_id": event['message']['message_id'],
            "text": 'Message edited! :)',
        })
    else:
        api_request('answerCallbackQuery', {
            "callback_query_id": event['id'],
            "text": 'Patience you must have my young padawan',
            #"show_alert" => True,
        })


def get_bot_username():
    return api_request('getMe')["username"]

def run():
    last_message_update_id = 0
    while not quit:
        response = api_request('getUpdates', {
            "offset": last_message_update_id + 1,
            "limit": 100,
            "timeout": LONG_POLLING_TIMEOUT
        })
        for update in response:
            if update["update_id"] > last_message_update_id:
                last_message_update_id = update["update_id"]
            run_event(update)

def send_message(chat_id, text, disable_web_page_preview=False, reply_to_message_id=None, reply_markup=None):
    if reply_markup != None:
        reply_markup = json.dumps(reply_markup)
    response = api_request('sendMessage', {
        "chat_id": chat_id,
        "text": text,
        "disable_web_page_preview": disable_web_page_preview,
        "reply_to_message_id": reply_to_message_id,
        "reply_markup": reply_markup,
        "parse_mode": 'HTML',
    })
    run_event(response)


def api_request(method, parameters={}, files=None):
    url = "https://api.telegram.org/bot{token}/{method}".format(token=BOT_TOKEN, method=method)

    http_method = 'get'

    try:
        response = requests.request(http_method, url, timeout=REQUEST_TIMEOUT, params=parameters, files=files)
    except requests.RequestException as e:
        raise Exception(str(e))

    try:
        result = response.json()
    except ValueError:  # typo on the url, no json to decode
        raise Exception('Error: Invalid URL', response.status_code)

    if not (response.status_code is requests.codes.ok):
        raise Exception(result['description'], response.status_code)  # Server reported error

    if not result["ok"]:
        raise Exception("Telegram API sent a non OK response")  # Telegram API reported error

    return result["result"]

def run_event(event):
    if "message" in event:
        run_message_event(event["message"])
    elif "callback_query" in event:
        on_receive_callbackQuery(event["callback_query"])

def run_message_event(event):
    if "text" in event:
        on_receive_message(event)
    if "new_chat_participant" in event:
        #on_new_chat_participant(event)
        print("new_chat_participant")
    if "left_chat_participant" in event:
        #on_left_chat_participant(event)
        print("left_chat_participant")
    if "audio" in event:
        #on_receive_audio(event)
        print("audio")
    if "document" in event:
        #on_receive_document(event)
        print("document")
    if "photo" in event:
        #on_receive_photo(event)
        print("photo")
    if "sticker" in event:
        #on_receive_sticker(event)
        print("sticker")
    if "video" in event:
        #on_receive_video(event)
        print("video")
    if "contact" in event:
        #on_receive_contact(event)
        print("contact")
    if "location" in event:
        #on_receive_location(event)
        print("location")
    if "new_chat_title" in event:
        #on_new_chat_title(event)
        print("new_chat_title")
    if "new_chat_photo" in event:
        #on_new_chat_photo(event)
        print("new_chat_photo")
    if "delete_chat_photo" in event:
        #on_delete_chat_photo(event)
        print("delete_chat_photo")
    if "group_chat_created" in event:
        #on_group_chat_created(event)
        print("group_chat_created")


run()
