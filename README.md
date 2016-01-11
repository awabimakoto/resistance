<h3>抵抗组织助手</h3>

本项目在微信平台上实现抵抗组织游戏（虽然我很不喜欢这个平台）。

<h4>部署方法</h4>
<ul>
<li>在MySQL中新建数据库并分配权限。在文件settings.php的开头部分正确填写。</li>
<li>在MySQL中进入数据库，根据文件sql新建表。<pre>mysql -u username -p < sql</pre></li>
<li>将所有php文件放入网站根目录。</li>
<li>网站和微信平台的对接请参考腾讯的说明。</li>
</ul>

<h4>游戏流程</h4>
<ol>
<li>发送总人数（5-10）创建房间，服务器回复房间号和自己的身份；</li>
<li>其他玩家发送房间号（四位数）进入房间并获取身份；</li>
<li>房间满员后，领袖组队，每人发送1赞同组队，0反对组队；投票结束后最后一个投票的人收到结果；</li>
<li>组队成功后进入任务，连续五次组队失败则间谍获胜；</li>
<li>任务环节，回复1做任务，0破坏任务（抵抗者无论发送哪个均视为做任务），最后一个发送的人收到结果；</li>
<li>任务成功三次则抵抗者获胜，失败三次则间谍获胜。</li>
</ol>

<h4>命令列表</h4>
<ul>
<li>整数5-10：创建相应人数的房间，如已在某房间内则不可创建。</li>
<li>四位数：进入相应房间。</li>
<li>1或0：组队和任务投票。</li>
<li>“退出”：房间及其玩家均被清除。</li>
<li>“帮助”：显示游戏说明。</li>
</ul>

<h4>特别说明</h4>
<ul>
<li>暂时无法支持谋略卡。</li>
<li>做任务时，系统并不确认身份，收集指定数目的投票即显示结果，请非队员不要投票。</li>
<li>由于腾讯的权限限制，投票结果等本应群发的消息只能发给最后一个操作者。其应如实向其他玩家公布。</li>
</ul>
