<?php

include_once("template.php");
include_once("settings.php");

function roleflag($number){
	global $roles,$rolename;
	$pool=array();
	$i=0;
	while ($i<$roles[$number][0]){
		array_push($pool,0);
		$i+=1;
	}
	$i=0;
	while ($i<$roles[$number][1]){
		array_push($pool,1);
		$i+=1;
	}
	shuffle($pool);
	$i=0;
	$roleflag=0;
	while ($i<$number){
		$roleflag+=$pool[$i]*pow(2,$i);
		$i+=1;
	}
	return $roleflag;
}

function getrole($roleflag,$order){
	$test=$roleflag & pow(2,$order-1);
	if ($test){
		return TRUE;
	} else{
		return FALSE;
	}
}

function create($number, $creator){
	global $roles,$rolename;
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	if (!$link) {
 		die('database fail '.mysql_error());
	}

	$db_selected = mysql_select_db(DB_NAME, $link);
	if (!$db_selected){
		die('database fail '.mysql_error());
	}

	$test_id_exist = mysql_query("SELECT room,role FROM player WHERE id='".$creator."'",$link);
	if (mysql_num_rows($test_id_exist)){
		$userinfo = mysql_fetch_assoc($test_id_exist);
		$fetch_room = mysql_query("SELECT totalnumber,currentnumber FROM room WHERE roomid=".$userinfo['room'],$link);
		$roominfo = mysql_fetch_assoc($fetch_room);
		mysql_close($link);
		return "你已在".$roominfo['totalnumber']."人房间（抵抗者".$roles[$roominfo['totalnumber']][0]."人，间谍".$roles[$roominfo['totalnumber']][1]."人），房间号".$userinfo['room']."。当前已有".$roominfo['currentnumber']."人。\n你的身份是".$rolename[$userinfo['role']]."。";
	}

	$test_room_limit = mysql_query("SELECT COUNT(*) AS totalrooms FROM room",$link);
	$row = mysql_fetch_assoc($test_room_limit);
	if ($row['totalrooms'] == 9000){
		mysql_close($link);
		return '总房间数已达上限。';
	}

	do{
		$roomid = mt_rand(1000,9999);
		$check_roomid_exist = mysql_query("SELECT roomid FROM room WHERE roomid=".$roomid,$link);
	} while (mysql_num_rows($check_roomid_exist));

	$roleflag = roleflag($number);
	if (getrole($roleflag,1)){
		mysql_query("INSERT INTO player (id, room, role, voted) VALUES ('".$creator."',".$roomid.",TRUE,FALSE)",$link);
	} else{
		mysql_query("INSERT INTO player (id, room, role, voted) VALUES ('".$creator."',".$roomid.",FALSE,FALSE)",$link);
	}
	mysql_query("INSERT INTO room (roomid, roleflag, totalnumber, currentnumber, turn, votes, disagree, status, success, fail, deny, last) VALUES (".$roomid.",".$roleflag.",".$number.",1,0,0,0,0,0,0,0,'还没有进行过投票。')",$link);
	mysql_close($link);
	return "你已开房".$roomid."，其中抵抗者".$roles[$number][0]."人，间谍".$roles[$number][1]."人。快召唤基/姬友一起来嘿嘿嘿吧！\n你的身份是".$rolename[getrole($roleflag,1)]."。";
}

function room($roomid, $user){
	global $roles,$rolename,$tasks;
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	if (!$link) {
 		die('database fail '.mysql_error());
	}

	$db_selected = mysql_select_db(DB_NAME, $link);
	if (!$db_selected){
		die('database fail '.mysql_error());
	}

	$test_room = mysql_query("SELECT totalnumber, currentnumber FROM room WHERE roomid=".$roomid,$link);
	if (!mysql_num_rows($test_room)){
		mysql_close($link);
		return '该房间不存在。';
	}
	$roominfo = mysql_fetch_assoc($test_room);
	$number = $roominfo['totalnumber'];
	$current = $roominfo['currentnumber'];

	if ($number==$current){
		mysql_close($link);
		return '该房间已满。';
	}
	$test_id_exist = mysql_query("SELECT room,role FROM player WHERE id='".$user."'",$link);
	if (mysql_num_rows($test_id_exist)){
		$userinfo = mysql_fetch_assoc($test_id_exist);
		$fetch_room = mysql_query("SELECT totalnumber,currentnumber FROM room WHERE roomid=".$userinfo['room'],$link);
		$roominfo = mysql_fetch_assoc($fetch_room);
		mysql_close($link);
		return "你已在".$roominfo['totalnumber']."人房间（抵抗者".$roles[$roominfo['totalnumber']][0]."人，间谍".$roles[$roominfo['totalnumber']][1]."人），房间号".$userinfo['room']."。当前已有".$roominfo['currentnumber']."人。\n你的身份是".$rolename[$userinfo['role']]."。";
	}

	$current += 1;
	$test_roleflag = mysql_query("SELECT roleflag FROM room WHERE roomid=".$roomid,$link);
	$roleflag = mysql_fetch_assoc($test_roleflag)['roleflag'];
	if (getrole($roleflag,$current)){
		mysql_query("INSERT INTO player (id, room, role, voted) VALUES ('".$user."',".$roomid.",TRUE,FALSE)",$link);
	} else{
		mysql_query("INSERT INTO player (id, room, role, voted) VALUES ('".$user."',".$roomid.",FALSE,FALSE)",$link);
	}
	mysql_query("UPDATE room SET currentnumber=".$current." WHERE roomid=".$roomid,$link);
	$result = "你已加入".$number."人房间（抵抗者".$roles[$number][0]."人，间谍".$roles[$number][1]."人），房间号".$roomid."。当前已有".$current."人。\n你的身份是".$rolename[getrole($roleflag,$current)]."。";
	if ($current==$number){
		mysql_query("UPDATE room SET status=1 WHERE roomid=".$roomid,$link);
		$result=$result."\n房间已满，请大家闭眼，间谍互相确认身份。然后请领袖选出".$tasks[$number][0]."人组队。";
	}
	mysql_close($link);
	return $result;
}

function command($command, $user){
	global $document,$tasks,$fails;
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	if (!$link) {
 		die('database fail '.mysql_error());
	}

	$db_selected = mysql_select_db(DB_NAME, $link);
	if (!$db_selected){
		die('database fail '.mysql_error());
	}

	switch($command){
		case 'exit':
		case 'quit':
		case '退出':
			$test_id_exist=mysql_query("SELECT room FROM player WHERE id='".$user."'",$link);
			if (mysql_num_rows($test_id_exist)){
				$row = mysql_fetch_assoc($test_id_exist);
				$room = $row['room'];
				mysql_query("DELETE FROM player WHERE room=".$room,$link);
				mysql_query("DELETE FROM room WHERE roomid=".$room,$link);
				$result="房间".$room."已被注销。请告诉和你在同一房间的人。";
			} else{
				$result="你不在任何房间中。";
			}
			break;
		case 'no confidence':
		case '不受信任':
		case '推翻':
		case '反对':
		case '造反':
		case '起义':
		case '颠覆':
			$test_id_exist=mysql_query("SELECT room FROM player WHERE id='".$user."'",$link);
			if (mysql_num_rows($test_id_exist)){
				$userinfo = mysql_fetch_assoc($test_id_exist);
				$fetch_room = mysql_query("SELECT totalnumber,turn,status,deny FROM room WHERE roomid=".$userinfo['room'],$link);
				$roominfo = mysql_fetch_assoc($fetch_room);
				if ($roominfo['status']!=2){
					$result="只有组队成功后才可使用“不受信任”。";
				} else{
					$deny=$roominfo['deny']+1;
					$turn=$roominfo['turn']-1;
					$result="你已推翻当前领袖。\n这是第".$deny."次组队失败。";
					if ($deny==5){
						$result=$result."\n游戏结束，间谍胜利。";
						mysql_query("DELETE FROM player WHERE room=".$userinfo['room'],$link);
						mysql_query("DELETE FROM room WHERE roomid=".$userinfo['room'],$link);
					} else{
						$result=$result."\n请下一位领袖选出".$tasks[$roominfo['totalnumber']][$turn]."人做任务。允许".$fails[$roominfo['totalnumber']][$turn]."人破坏。";
						mysql_query("UPDATE player SET voted=FALSE WHERE room=".$userinfo['room'],$link);
						mysql_query("UPDATE room SET turn=".$turn.",votes=0,disagree=0,status=1,deny=".$deny." WHERE roomid=".$userinfo['room'],$link);
					}
				}
			} else{
				$result="你不在任何房间中。";
			}
			break;
		case 'result':
		case '结果':
			$test_id_exist=mysql_query("SELECT room FROM player WHERE id='".$user."'",$link);
			if (mysql_num_rows($test_id_exist)){
				$userinfo = mysql_fetch_assoc($test_id_exist);
				$fetch_room = mysql_query("SELECT last FROM room WHERE roomid=".$userinfo['room'],$link);
				$roominfo = mysql_fetch_assoc($fetch_room);
				$result=$roominfo['last'];
			} else{
				$result="你不在任何房间中。";
			}
			break;
		default:
			$result=$document;
	}
	mysql_close($link);
	return $result;
}

function vote($option, $user, $msgid){
	global $tasks, $fails;
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
	if (!$link) {
 		die('database fail '.mysql_error());
	}

	$db_selected = mysql_select_db(DB_NAME, $link);
	if (!$db_selected){
		die('database fail '.mysql_error());
	}

	$test_id_exist = mysql_query("SELECT room,role,voted,msgid FROM player WHERE id='".$user."'",$link);
	if (mysql_num_rows($test_id_exist)){
		$userinfo = mysql_fetch_assoc($test_id_exist);
		if ($userinfo['msgid']==$msgid){
			return;
		}
		if ($userinfo['voted']){
			mysql_close($link);
			return "你已投票。";
		}
		$fetch_room = mysql_query("SELECT totalnumber,turn,votes,disagree,status,success,fail,deny FROM room WHERE roomid=".$userinfo['room'],$link);
		$roominfo = mysql_fetch_assoc($fetch_room);
		$votes=$roominfo['votes'];
		$max=$roominfo['totalnumber'];
		$disagree=$roominfo['disagree'];
		if ($roominfo['status']==0){
			mysql_close($link);
			return "房间未满，无法进行游戏。快召唤你的基/姬友吧！";
		}
		if ($roominfo['status']==1){
			if ($option=='0'){
				$disagree+=1;
			}
			$votes+=1;
			$result="投票成功。";
			mysql_query("UPDATE player SET voted=TRUE,msgid=".$msgid." WHERE id='".$user."'",$link);
			mysql_query("UPDATE room SET disagree=".$disagree.",votes=".$votes." WHERE roomid=".$userinfo['room'],$link);
			if ($votes==$max){
				mysql_query("UPDATE player SET voted=FALSE WHERE room=".$userinfo['room'],$link);
				mysql_query("UPDATE room SET votes=0,disagree=0 WHERE roomid=".$userinfo['room'],$link);
				if ($disagree>=(ceil($votes)/2)){
					$deny=$roominfo['deny']+1;
					$result=$result."\n".($votes-$disagree)."人支持，".$disagree."人反对，组队失败。\n这是第".$deny."次组队失败。";
					$last=($votes-$disagree)."人支持，".$disagree."人反对，组队失败。\n这是第".$deny."次组队失败。";
					if ($deny==5){
						$result=$result."\n游戏结束，间谍胜利。";
						mysql_query("DELETE FROM player WHERE room=".$userinfo['room'],$link);
						mysql_query("DELETE FROM room WHERE roomid=".$userinfo['room'],$link);
						mysql_close($link);
						return $result;
					}
					mysql_query("UPDATE room SET deny=".$deny.",last='".$last."' WHERE roomid=".$userinfo['room'],$link);
				} else{
					$result=$result."\n".($votes-$disagree)."人支持，".$disagree."人反对，组队成功。请开始做任务。";
					$last=($votes-$disagree)."人支持，".$disagree."人反对，组队成功。请开始做任务。";
					mysql_query("UPDATE room SET turn=".($roominfo['turn']+1).",status=2,last='".$last."' WHERE roomid=".$userinfo['room'],$link);
					mysql_close($link);
					return $result;
				}
			}
			return $result;
		}
		if ($roominfo['status']==2){
			$maxtask=$tasks[$max][($roominfo['turn']-1)];
			$role=$userinfo['role'];
			if ($option=='0' && $role){
				$disagree+=1;
			}
			$votes+=1;
			$result="投票成功（抵抗者一律视为做任务）。";
			mysql_query("UPDATE player SET voted=TRUE,msgid=".$msgid." WHERE id='".$user."'",$link);
			mysql_query("UPDATE room SET disagree=".$disagree.",votes=".$votes." WHERE roomid=".$userinfo['room'],$link);
			if ($votes==$maxtask){
				mysql_query("UPDATE player SET voted=FALSE WHERE room=".$userinfo['room'],$link);
				mysql_query("UPDATE room SET votes=0,disagree=0,deny=0 WHERE roomid=".$userinfo['room'],$link);
				if ($disagree>$fails[$max][($roominfo['turn']-1)]){
					$result=$result."\n".$disagree."人破坏任务，第".$roominfo['turn']."回合任务失败。";
					$last=$disagree."人破坏任务，第".$roominfo['turn']."回合任务失败。";
					$fail=$roominfo['fail']+1;
					if ($fail==3){
						$result=$result."\n"."游戏结束，间谍胜利。";
						mysql_query("DELETE FROM player WHERE room=".$userinfo['room'],$link);
						mysql_query("DELETE FROM room WHERE roomid=".$userinfo['room'],$link);
						mysql_close($link);
						return $result;
					}
					mysql_query("UPDATE room SET status=1,fail=".$fail.",last='".$last."' WHERE roomid=".$userinfo['room'],$link);
				} else{
					$result=$result."\n".$disagree."人破坏任务，第".$roominfo['turn']."回合任务成功。";
					$last=$disagree."人破坏任务，第".$roominfo['turn']."回合任务成功。";
					$success=$roominfo['success']+1;
					if ($success==3){
						$result=$result."\n"."游戏结束，抵抗者胜利。";
						mysql_query("DELETE FROM player WHERE room=".$userinfo['room'],$link);
						mysql_query("DELETE FROM room WHERE roomid=".$userinfo['room'],$link);
						mysql_close($link);
						return $result;
					}
					mysql_query("UPDATE room SET status=1,success=".$success.",last='".$last."' WHERE roomid=".$userinfo['room'],$link);
				}
				$result=$result."进入第".($roominfo['turn']+1)."轮，请领袖选出".$tasks[$max][$roominfo['turn']]."人做任务。允许".$fails[$max][$roominfo['turn']]."人破坏。";
			}
			return $result;
		}
	} else{
		mysql_close($link);
		return "你不在任何房间中。";
	}
}

$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

if (!empty($postStr)){
	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
	$fromUsername = $postObj->FromUserName;
	$toUsername = $postObj->ToUserName;
	$form_MsgType = $postObj->MsgType;
	$msgid = $postObj->MsgId;

if($form_MsgType=="event"){
	$form_Event = $postObj->Event;
	if($form_Event=="subscribe"){
		$contentStr = "感谢您关注抵抗组织助手！\n游戏介绍请点击http://45.118.133.173/resistance.jpg\n输入“帮助”获取游戏指南。";
		$resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $contentStr);
		echo $resultStr;
		exit;
	}
} elseif ($form_MsgType=="text"){
	$form_content = trim($postObj->Content);
	if (preg_match($pattern_vote, $form_content)){
	$feedback=vote($form_content, $fromUsername, $msgid);
	} elseif (preg_match($pattern_create, $form_content)){
	$feedback=create($form_content, $fromUsername);
	} elseif (preg_match($pattern_room, $form_content)){
	$feedback=room($form_content, $fromUsername);
	} else{
	$feedback=command($form_content, $fromUsername);
	}
	
	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $feedback);
	echo $resultStr;
	exit;
}

} else{
	echo "";
	exit;
}

?>