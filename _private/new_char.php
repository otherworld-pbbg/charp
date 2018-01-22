<?php
include_once('header2.php');

ptag('h1', 'Create new character');

starttag('form', '', array(
	'action' => 'index.php?page=createChar',
	'method' => 'post',
	'id' => 'newCharForm',
	'class' => 'narrow'));
starttag('p');
ptag('label', 'Name: ', array(
	'for' => 'cname',
	'class' => 'minwide'));
ptag('input', '', array(
	'type' => 'text',
	'name' => 'cname',
	'id' => 'cname',
	'size' => '25'));
closetag('p');
starttag('p');
ptag('label', 'Sex: ', array(
	'for' => 'csex',
	'class' => 'minwide'));
starttag('select', '', array(
	'id' => 'csex',
	'name' => 'csex'));
ptag('option', 'Male', array('value' => '1'));
ptag('option', 'Female', array('value' => '2'));
ptag('option', 'Ambiguous', array('value' => '3', 'selected' => 'selected'));
closetag('select');
closetag('p');
starttag('p');
ptag('label', 'Description: ', array(
	'for' => 'cdesc',
	'class' => 'minwide'));
ptag('textarea', '', array(
	'cols' => '100',
	'rows' => '4',
	'name' => 'cdesc',
	'id' => 'cdesc'));
closetag('p');
starttag('p', '', array('class' => 'right'));
ptag('input', '', array(
	'type' => 'submit',
	'value' => 'Create',
	'id' => 'csubmit'));
closetag('p');
closetag('form');
?>