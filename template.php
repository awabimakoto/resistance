<?php

$textTpl = "<xml>
	 <ToUserName><![CDATA[%s]]></ToUserName>
         <FromUserName><![CDATA[%s]]></FromUserName>
         <CreateTime>%s</CreateTime>
         <MsgType><![CDATA[text]]></MsgType>
         <Content><![CDATA[%s]]></Content>
         <FuncFlag>0</FuncFlag>
         </xml>";
$voiceTpl = "<xml>
	 <ToUserName><![CDATA[%s]]></ToUserName>
	 <FromUserName><![CDATA[%s]]></FromUserName>
	 <CreateTime>%s</CreateTime>
	 <MsgType><![CDATA[voice]]></MsgType>
	 <Voice>
	 <MediaId><![CDATA[%s]]></MediaId>
	 </Voice>
	 </xml>";
$imageTpl = "<xml>
	 <ToUserName><![CDATA[%s]]></ToUserName>
	 <FromUserName><![CDATA[%s]]></FromUserName>
	 <CreateTime>%s</CreateTime>
	 <MsgType><![CDATA[image]]></MsgType>
	 <Image>
	 <MediaId><![CDATA[%s]]></MediaId>
	 </Image>
	 </xml>";
?>