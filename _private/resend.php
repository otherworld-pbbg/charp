<?php
include_once('header.php');
ptag("h2", "Resend activation code");
para("If you have a pending account or request and the activation code hasn't come through in a reasonable time, enter your email address below to have it resent.");
starttag("form", "", array(
	'action'=>'index.php?page=resend',
	'method'=>'post',
	'class'=>'narrow'));
ptag("h3", "Type of activation");
starttag("p");
ptag("input", "", array(
	'type'=>'radio',
	'id'=>'type1',
	'name'=>'type',
	'value'=>'1',
	'checked'=>'checked'));
ptag("label", "New account", array('for'=>'type1'));
closetag("p");

starttag("p");
ptag("input", "", array(
	'type' => 'radio',
	'id' => 'type2',
	'name' => 'type',
	'value' => '2'));
ptag("label", "New email",  array('for'=>'type2'));
closetag("p");

starttag("p");
ptag("input", "", array(
	'type' => 'radio',
	'id' => 'type3',
	'name' => 'type',
	'value' => '3'));
ptag("label", "New password",  array('for'=>'type3'));
closetag("p");

starttag("p");
ptag("label", "Email: ", array('for'=>'email', 'class' => 'minwide'));
ptag("input", "", array(
	'type' => 'text',
	'id' => 'email',
	'name' => 'email',
	'size' => '30',
	'maxlength' => '60'));
closetag("p");

starttag("p");
ptag("input", "", array(
	'type' => 'submit',
	'id' => 'submit_btn2',
	'name' => 'submit_btn',
	'value' => 'Send'));
closetag("p");
?>
