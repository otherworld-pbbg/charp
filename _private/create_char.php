<?php
$cname = isset($_POST['cname']) ? $mysqli->real_escape_string($_POST['cname']) : '';
$sex = isset($_POST['csex']) ? setBint($_POST['csex'], 1, 3, 3) : 3;
$cdesc = isset($_POST['cdesc']) ? $mysqli->real_escape_string($_POST['cdesc']) : '';

$newChar = $player->addCharacter($cname, $sex);
if (!$newChar->getId()) header('index.php?page=pIndex&msg=ccreate_fail');
else if ($cdesc) {
	if (!$newChar->setDescription($cdesc)) header('Location: index.php?page=pIndex&msg=desc_fail');
	else header('Location: index.php?page=pIndex&msg=ccreate_success');
}
else header('Location: index.php?page=pIndex&msg=ccreate_success');
?>