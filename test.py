#!/usr/bin/env python
import re,random,pycurl
from cStringIO import StringIO
import xml.etree.cElementTree as ET
id='id0'
pattern=re.compile('^id[0-9]$')
command=''
print "Resistance test program. Input as you do with the app. Quit by hitting Enter without any input.\nHint: You can change you id by type id0~id9, the current is id0. Case sensitive."
buffer=StringIO()
while 1:
	command=raw_input(id+" > ")
	command=command.strip()
	if not command:
		break
	match=pattern.match(command)
	if match:
		id=command
		continue
	template='''<xml><ToUserName><![CDATA[server]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>1348831860</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content><MsgId>%d</MsgId></xml>'''
	feedback=template % (id,command,random.randint(1,1000))
	c=pycurl.Curl()
	c.setopt(c.URL,'url/of/php/file') #please replace here with the url of php file.
	c.setopt(pycurl.HTTPHEADER, ["Content-type: text/xml"])
	c.setopt(c.POSTFIELDS,feedback)
	c.setopt(c.WRITEFUNCTION,buffer.write)
	c.perform()
	c.close()
	feedback=buffer.getvalue().decode()
	element=ET.fromstring(feedback)
	content=element.find('Content')
	print content.text
	buffer.truncate(0)
exit
