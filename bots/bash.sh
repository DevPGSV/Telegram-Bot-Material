#!/bin/bash

token="TOKEN_HERE"

ret=`curl -s https://api.telegram.org/bot${token}/getMe`

if [[ "$ret" =~ ^.*?\"username\"\:\"(.*?)\".*?$ ]]; then
    username=${BASH_REMATCH[1]}
else
    echo ERROR: unkown username!
    exit 1
fi

echo "Bot: $username"


curl -s https://api.telegram.org/bot${token}/sendMessage?chat_id=$1\&text=$2

echo -e "\n\n"
