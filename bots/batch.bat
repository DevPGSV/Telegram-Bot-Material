@if (@This==@IsBatch) @then
@echo off

    Setlocal enableextensions EnableDelayedExpansion
    set id=%1
    set text=%2
    set text=!text: =%%20!
    setlocal enableextensions disabledelayedexpansion
    if not "%~2"=="" (
        wscript //E:JScript "%~dpnx0" "sendMessage" "chat_id=%id%&text=%text%"
    )
    exit /b
@end
var token = "TOKEN_HERE"
var url = "https://api.telegram.org/bot"+token
var http = WScript.CreateObject('Msxml2.XMLHTTP.6.0');
var method = WScript.Arguments.Item(0)
var data = WScript.Arguments.Item(1)
http.open("GET", url+"/"+method+"?"+data, false);
http.send();
WScript.Quit(0);
