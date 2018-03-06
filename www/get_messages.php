<?php
session_start();
define('PRIV_PATH', '../_private/');//note that unlike in earlier versions, this also includes the last backslash, so don't double it
//------------------------end of constants--------------------------------------
require_once(PRIV_PATH . "conn_details.php");//defines host, dbname, user, pass, also defines BASE_URL
require_once(PRIV_PATH . "tags_printer.php");//html generator

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
	include_once(PRIV_PATH . "header.php");
	echo "Error: no db connection<br/>";
}
else {
	include_once(PRIV_PATH . 'classes/character.php');
	
	if (isset($_SESSION['user_id'])) {
		if (isset($_POST['charId'])) {
			$curChar = new Character($mysqli, 
				array('uid' => round($_POST['charId']) )
				);
			if ($curChar->getOwner()!=$_SESSION['user_id']) {
				echo "Unauthorized access<br />";
			}
			else {
				$chatId = $curChar->getCurrentChat();
				if (!$chatId) {
					echo '--- (not in chat)';
				}
				else {
					include_once(PRIV_PATH . 'classes/chat.php');
					$curChat = new Chat($mysqli, array(
						'uid' => $chatId
						));
					
					if (isset($_POST['msg'])) {
						$curChar->say($curChat, $mysqli->real_escape_string($_POST['msg']));
					}
					
					if (isset($_POST['lastseen'])) {
						$lastseenID = round($_POST['lastseen']);
					}
					else {
						$info = $curChar->getLastSeen($chatId);
						$lastseenID = $info['msg'];
					}
					
					if (isset($_POST['justcount'])) {
						echo $curChat->countUnseen($lastseenID);
					}
					else {
						$msgs = $curChat->getMessages($lastseenID);
						if ($msgs) {
							foreach ($msgs as $m) {
								$actor = new Character($mysqli, array(
									'uid' => $m['actor'])
									);
								starttag('p');
								echo '[' . $m['timestamp'] . '] ';
								echo $actor->getName();
								echo ': "';
								echo $m['contents'];
								echo '"';
								closetag('p');
								$lastseen = $m['uid'];
							}
							$curChar->setLastSeen($chatId, $lastseen);
						}
					}
				}
			}
		}
		else {
			echo "No character id.<br />";
		}
	}
	else {
		echo "Unauthorized access<br />";
	}
}
?>