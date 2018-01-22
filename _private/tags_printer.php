<?php
//The purpose of these abbreviations is to generally ensure that all tags are closed and followed by a line break so that it won't appear in one line if someone views the source

function line($str) {
	echo $str . "<br>" . PHP_EOL;
}

function para($str)
{
	//Later this can be edited to contain a standard class
	echo "<p>$str</p>" . PHP_EOL;
}

function infomsg($str) {
	echo "<div class='alert alert-info'>" . PHP_EOL;
	echo $str . PHP_EOL;
	echo "</div>" . PHP_EOL;
}

function warnmsg($str) {
	echo "<div class='alert alert-warning'>" . PHP_EOL;
	echo "<strong>Warning:</strong>" . $str . PHP_EOL;
	echo "</div>" . PHP_EOL;
}

function errormsg($str) {
	echo "<div class='alert alert-danger'>" . PHP_EOL;
	echo "<strong>Error:</strong>" . $str . PHP_EOL;
	echo "</div>" . PHP_EOL;
}

function displayBodywarning() {
	echo '<div class="alert alert-danger">' . PHP_EOL;
	echo "This character doesn't have a body, so it cannot be played." . PHP_EOL;
	echo "</div>" . PHP_EOL;
}

function ptag($tagname, $contents="", $attr=false)
{
	//prints html tag with opening and closing tags,
	//contents (if specificied) and possible attributes
	//as a change to the previous version, attributes are an associative array
	
	$selfclosing = array("area", "base", "basefront", "br", "hr", "input", "img", "link", "meta");//list from w3 schools
	$astr = "";
	
	if ($attr) {
		foreach ($attr as $key => $a) {
			$astr .= " " . $key . "='" . $a . "'";
		}
	}
	if (in_array($tagname, $selfclosing)) echo "<" . $tagname . $astr . " />" . PHP_EOL;
	else echo "<" . $tagname . $astr . ">" . $contents . "</" . $tagname . ">" . PHP_EOL;
}

function starttag($tagname, $contents="", $attr=false)
{
	//prints html tag without a closing tag
	//contents (if specificied) and possible attributes
	//attributes are an associative array
	
	$astr = "";
	if ($attr) {
		foreach ($attr as $key => $a) {
			$astr .= " " . $key . "='" . $a . "'";
		}
	}
	echo "<" . $tagname . $astr . ">" . $contents . PHP_EOL;
}

function closetag($tagname)
{
	//closes a previously opened tag
	echo "</" .$tagname. ">" . PHP_EOL;
}
?>