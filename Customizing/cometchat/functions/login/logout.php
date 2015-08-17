<?php
$config = dirname(__FILE__) . '/config.php';
require_once( "Social/Auth.php" );

try{
    $socialAuth = new Social_Auth( $config );
    Social_Auth::session()->deleteByKey( "SA_USER" );
    @session_start();
    unset($_SESSION['cometchat']);
    if(!empty($_GET['callback'])){
    	echo $_GET['callback'].'('.json_encode(array(1)).')';
    }else{
    	echo '1';
    }
} catch( Exception $ex ) {
    echo "Error occured: " . $ex->getMessage();
}