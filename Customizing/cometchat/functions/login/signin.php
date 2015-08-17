<?php
if ( ! session_id() ){
    if( ! session_start() ){
        throw new Exception( "In order to use CometChat Social Authentication, you need to start session with 'session_start()'", 1 );
    }
}

/**
 * Check if any error exists
 */
$error = "";
if (!empty($_GET["error"])) {
    $error = trim( strip_tags(  $_GET["error"] ) );
}

if( isset( $_GET["network"] ) && $_GET["network"] ) {
    $config = dirname(__FILE__) . '/config.php';
    require_once( dirname(__FILE__) . '/Social/Auth.php' );

    try{
        $socialAuth = new Social_Auth( $config );

        $network = trim( strip_tags( $_GET["network"] ) );

        $adapter = $socialAuth->authenticate( $network );

    } catch( Exception $e ) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>CometChat Social Authentication</title>
    <!-- Custom Css Styles Start-->
    <style>
        .hidden {
            display: none;
        }
    </style>
    <!-- Custom Css Styles End -->
</head>

<body style="padding: 40px 15px;">
<script>
var window_opener = window.opener;
try {
    if(typeof(window_opener.jqcc) == 'undefined') {
        window_opener = window_opener.window.opener;
        opener.close();
    }
} catch(e){
}
<?php if( isset( $_GET["network"] ) && $_GET["network"] ) {
    $network = trim( strip_tags( $_GET["network"] ) );
    if(!$socialAuth->isNetworkConnected($network)) {
?>
        window_opener.postMessage("alert^<?php echo ucfirst($network); ?> has not been configured correctly.",'*');
<?php
    }else {
?>
        window_opener.postMessage('cc_reinitializeauth','*');

<?php }
?>
    window.close();
<?php }
?>
</script>
</body>
</html>