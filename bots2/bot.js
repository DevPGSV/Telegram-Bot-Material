const moment = require('moment'),
      TelegramBot = require('node-telegram-bot-api');

const token = '123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

const bot = new TelegramBot(token, {
  polling: true
});


bot.onText(/^\/ping$/i, (msg, match) => {
  bot.sendMessage(msg.chat.id, 'Pong', {
    reply_to_message_id: msg.id
  });
});

bot.onText(/^\/getme$/i, (msg, match) => {
  bot.getMe().then((response) => {
    bot.sendMessage(msg.chat.id, "<pre>" + JSON.stringify(response, null, 2) + "</pre>", {
      reply_to_message_id: msg.id,
      parse_mode: 'HTML',
    });
  });
});

bot.onText(/^\/debug$/i, (msg, match) => {
  bot.sendMessage(msg.chat.id, "<pre>" + JSON.stringify(msg, null, 2) + "</pre>", {
    reply_to_message_id: msg.id,
    parse_mode: 'HTML',
  });
});

bot.onText(/^\/start\s?(.+)?$/, (msg, match) => {
  bot.sendMessage(msg.chat.id, 'Hi', {
    reply_to_message_id: msg.id
  });
});

bot.onText(/^\/inlinekeyboard\s?(.+)?$/, (msg, match) => {
  var k = [
    [{
      'text': 'LibreLabUCM',
      'callback_data': 'Button pressed'
    }, ],
    [{
        'text': 'Web',
        'url': 'https://librelabucm.org/'
      },
      {
        'text': 'Calendario',
        'url': 'https://calendar.librelabucm.org/'
      },
    ],
    [{
      'text': 'Edit this message',
      'callback_data': 'editthismessage'
    }, ],
  ];
  bot.sendMessage(msg.chat.id, 'Testing with inline keyboards', {
    reply_to_message_id: msg.id,
    reply_markup: JSON.stringify({
      inline_keyboard: k
    }),
  });
});

bot.on('callback_query', (msg) => {
  var from = msg.from.first_name;
  console.log('[' + msg.message.message_id + '][' + moment.unix(msg.message.date).format('HH:mm:ss') + '][CallbackQuery] ' + msg.data);

  if (msg.data == "editthismessage") {
    bot.editMessageText('Message edited! :)', {
      'chat_id': msg.message.chat.id,
      'message_id': msg.message.message_id,
    });
  } else {
    bot.answerCallbackQuery({
      'callback_query_id': msg.id,
      'text': 'Patience you must have my young padawan',
      //'show_alert': true,
    });
  }
});



bot.on('message', (msg) => {
  var data = '';

  if (msg.reply_to_message !== undefined) {
    data += '[Reply: ' + msg.reply_to_message.message_id + '(' + chat2string(msg.reply_to_message.from) + ')' + '] ';
  }
  if (msg.forward_from !== undefined) {
    data += '[Forward: ' + chat2string(msg.forward_from) + '] ';
  }


  if (msg.text !== undefined) {
    data += msg.text;
  } else if (msg.audio !== undefined) {
    if (msg.audio.title !== undefined) {
      data += '[audio: ' + moment.utc(msg.audio.duration * 1000).format("HH:mm:ss") + ' "' + msg.audio.title + '"]';
    } else {
      data += '[audio: ' + moment.utc(msg.audio.duration * 1000).format("HH:mm:ss") + ']';
    }
  } else if (msg.document !== undefined) {
    if (msg.document.file_name !== undefined) {
      data += '[document: "' + msg.document.file_name + '"]';
    } else {
      data += '[document: ]';
    }
    console.log(msg);
  } else if (msg.photo !== undefined) {
    data += '[photo]';
  } else if (msg.sticker !== undefined) {
    data += '[sticker]';
  } else if (msg.video !== undefined) {
    data += '[video: ' + moment.utc(msg.video.duration * 1000).format("HH:mm:ss") + ']';
  } else if (msg.voice !== undefined) {
    data += '[voice: ' + moment.utc(msg.voice.duration * 1000).format("HH:mm:ss") + ']';
  } else if (msg.contact !== undefined) {
    data += '[contact: ' + msg.contact.phone_number + ': ' + msg.contact.first_name + ']';
  } else if (msg.location !== undefined) {
    data += '[location: (' + msg.location.longitude + ', ' + msg.location.latitude + ')]';
  } else if (msg.new_chat_participant !== undefined) {
    data += '[new_chat_participant: ' + chat2string(msg.new_chat_participant) + ']';
  } else if (msg.left_chat_participant !== undefined) {
    data += '[left_chat_participant: ' + chat2string(msg.left_chat_participant) + ']';
  } else if (msg.new_chat_title !== undefined) {
    data += '[new_chat_title: "' + msg.new_chat_title + '"]';
  } else if (msg.new_chat_photo !== undefined) {
    data += '[new_chat_photo]';
  } else if (msg.delete_chat_photo !== undefined) {
    data += '[delete_chat_photo]';
  } else if (msg.group_chat_created !== undefined) {
    data += '[group_chat_created]';
  } else {
    data += '[Unkown message]';
  }

  if (msg.caption !== undefined) {
    data += ' (' + msg.caption + ')';
  }

  if (msg.chat.type === 'group' || msg.chat.type === 'supergroup') {
    var direction = msg.chat.type + ':' + chat2string(msg.chat) + ' << ' + chat2string(msg.from);
  } else if (msg.chat.type === 'private' || msg.chat.type === 'channel') {
    var direction = 'BOT << ' + msg.chat.type + ':' + chat2string(msg.from);
  } else {
    var direction = msg.chat.type + ':' + chat2string(msg.from) + '?';
  }
  console.log('[' + msg.message_id + '][' + moment.unix(msg.date).format('HH:mm:ss') + '][' + direction + '] ' + data);
});




function chat2string(chat) {
  if (chat.title !== undefined) {
    return chat.title;
  } else if (chat.username !== undefined) {
    return chat.username;
  } else if (chat.first_name !== undefined) {
    return chat.first_name;
  } else {
    return '?';
  }
}
