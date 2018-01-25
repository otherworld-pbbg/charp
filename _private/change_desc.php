<?php
if (isset($_POST['desc'])) {
	$escaped = $mysqli->real_escape_string($_POST['desc']);
}
else $escaped = '';

$check = $curChar->setDescription($escaped);
if ($check) {
	customRedirect('index.php?page=cIndex&charid=' . $curCharid . '&msg=cdesc_success', 'Character page');
}
else customRedirect('index.php?page=cIndex&charid=' . $curCharid, 'Character page');
?>
