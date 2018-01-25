<?php
if (isset($_POST['cname'])) {
	$escaped = $mysqli->real_escape_string($_POST['cname']);
}
else $escaped = '';

$check = $curChar->changeName($escaped);
if ($check) {
	customRedirect('index.php?page=cIndex&charid=' . $curCharid . '&msg=cname_success', 'Character page');
}
else customRedirect('index.php?page=cIndex&charid=' . $curCharid, 'Character page');
?>