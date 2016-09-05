<?php
include('./launchDate.php');
$bypass = @$_GET['bypass'];
$countdown = @$_GET['countdown'];
if( 	$delta > 0
	&& 	$bypass != 1
	&& 	$countdown !== '0'
	&& 	$countdown !== 'no' ) {
	header('Location: ./countdown.php');
}