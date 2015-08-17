<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))).'/cometchat_init.php');
class Social_Client {

    public static $request = null;
    public static $finish = false;

    public static function handle() {
        if ( strrpos( $_SERVER["QUERY_STRING"], '?' ) ) {
            $_SERVER["QUERY_STRING"] = str_replace( "?", "&", $_SERVER["QUERY_STRING"] );

            parse_str( $_SERVER["QUERY_STRING"], $_REQUEST );
        }

        Social_Client::$request = $_REQUEST;
        if ( isset( Social_Client::$request["sa_login"] ) && Social_Client::$request["sa_login"] ) {
            Social_Client::startAuth();
        } elseif ( isset( Social_Client::$request["sa_login_finish"] ) && Social_Client::$request["sa_login_finish"] ) {
            Social_Client::finishAuth();
        }
    }

    public static function startAuth() {
        Social_Client::init();

        $network_name = trim( strip_tags( Social_Client::$request["sa_login"] ) );

        if( ! Social_Auth::session()->get( "sa_session.$network_name.sa_client" ) ) {
            Social_Logger::error( "Direct access not allowed!. You need to set session for sa_session" . $network_name . "sa_client" );

            header( "HTTP/1.0 404 Not Found" );
            die( "Direct access not allowed" );
        }

        $network = Social_Auth::prepare( $network_name );

        if( ! $network ) {
            Social_Logger::error( "Invalid parameter on login start!" );

            header( "HTTP/1.0 404 Not Found" );
            die( "Invalid parameter on login start!" );
        }

        try {
            Social_Logger::info( "Login start: $network_name" );

            $network->adapter->startLogin();
        } catch ( Exception $e ) {
            $network->goToCallbackPage();
        }

        die();
    }

    public static function finishAuth() {
        Social_Client::init();

        $network_name = trim( strip_tags( Social_Client::$request["sa_login_finish"] ) );
        $network = Social_Auth::prepare( $network_name );

        if( ! $network ) {
            Social_Logger::error( "Invalid parameter given on sa_login_finish" );
            $network->adapter->disconnectUser();

            header("HTTP/1.0 404 Not Found");
            die( "Invalid parameter given on sa_login_finish. Try login again" );
        }

        try {
            Social_Logger::info( "Login finish: $network_name" );

            $network->adapter->finishLogin();
            $user_profile = $network->adapter->getUserProfile();
            if($network_name == 'facebook'){
                $user_profile->photoURL = $user_profile->photoURL.'&ts='.getTimeStamp();
            }
            $sql = "select ".DB_USERTABLE.".".DB_USERTABLE_USERID." from ".DB_USERTABLE." where ".DB_USERTABLE.".".DB_USERTABLE_USERNAME." = '".mysqli_real_escape_string($GLOBALS['dbh'],$network_name)."_".mysqli_real_escape_string($GLOBALS['dbh'],$user_profile->identifier)."'";
            $result = mysqli_query($GLOBALS['dbh'],$sql);
            if($row = mysqli_fetch_assoc($result)){
                $sql = "update ".DB_USERTABLE." set ".DB_USERTABLE.".".DB_USERTABLE_NAME."='".mysqli_real_escape_string($GLOBALS['dbh'],$user_profile->firstName)."',".DB_AVATARFIELD."='".mysqli_real_escape_string($GLOBALS['dbh'],$user_profile->photoURL)."',".DB_USERTABLE.".".DB_LINKFIELD."='".mysqli_real_escape_string($GLOBALS['dbh'],$user_profile->profileURL)."' where ".DB_USERTABLE.".".DB_USERTABLE_USERNAME."='".mysqli_real_escape_string($GLOBALS['dbh'],$network_name)."_".mysqli_real_escape_string($GLOBALS['dbh'],$user_profile->identifier)."'";
                mysqli_query($GLOBALS['dbh'],$sql);
                if(!empty($row[DB_USERTABLE_USERID])){
                    $userid = $row[DB_USERTABLE_USERID];
                }
            }else{
                $sql = "insert into ".DB_USERTABLE." (".DB_USERTABLE.".".DB_USERTABLE_USERNAME.",".DB_USERTABLE.".".DB_USERTABLE_NAME.",".DB_AVATARFIELD.",".DB_USERTABLE.".".DB_LINKFIELD.",".DB_USERTABLE.".".DB_GROUPFIELD.") values ( '".mysqli_real_escape_string($GLOBALS['dbh'],$network_name)."_".mysqli_real_escape_string($GLOBALS['dbh'],$user_profile->identifier)."','".mysqli_real_escape_string($GLOBALS['dbh'],$user_profile->firstName)."','".mysqli_real_escape_string($GLOBALS['dbh'],$user_profile->photoURL)."','".mysqli_real_escape_string($GLOBALS['dbh'],$user_profile->profileURL)."','".mysqli_real_escape_string($GLOBALS['dbh'],ucfirst($network_name))."')";
                mysqli_query($GLOBALS['dbh'],$sql);
                $userid = mysqli_insert_id($GLOBALS['dbh']);
            }
            $_SESSION['cometchat']['userid'] = $userid;
            $_SESSION['cometchat']['ccauth'] = '1';
            Social_Auth::session()->set("SA_USER", $user_profile);
        } catch( Exception $e ){
            $network->adapter->disconnectUser();
        }

        Social_Logger::info( "Returned to callback" );

        $network->goToCallbackPage();
        die();
    }

    public static function init()
    {
        if ( ! Social_Client::$finish) {
            Social_Client::$finish = true;

            try {
                require_once realpath(dirname(__FILE__)) . "/Session.php";

                $session = new Social_Session();

                if ( ! $session->get( "sa_config" ) ) {
                    header( "HTTP/1.0 404 Not Found" );
                    die( "Direct access not allowed" );
                    Social_Logger::error( "Direct access not allowed, " );
                }

                Social_Auth::init( $session->get( "sa_config" ) );
            } catch ( Exception $e ) {
                Social_Logger::error( "Error occured while Social_Auth init" );

                header( "HTTP/1.0 404 Not Found" );
                die( "Error occured!" );
            }
        }
    }
}