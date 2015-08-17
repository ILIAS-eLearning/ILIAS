<?php
/**
 * Social_Auth provides multiple social login functionality at one gateway
 *
 * @author Hüseyin BABAL
 */

class Social_Auth {

    public static $version = "4.0";

    public static $config  = array();

    public static $session   = null;

    public static $logger  = null;

    public static $user_dao = null;

    function __construct( $config ) {
        Social_Auth::init( $config );
    }

    public static function init( $config )
    {
        if( ! is_array( $config ) && ! file_exists( $config ) ){
            throw new Exception( "Config needed for SocialAuth", 1 );
        }

        if( ! is_array( $config ) ){
            $config = include $config;
        }

        $config["base_path"]        = dirname( __FILE__ )  . DIRECTORY_SEPARATOR;
        $config["sdk_path"]   = $config["base_path"] . "Sdk" . DIRECTORY_SEPARATOR;
        $config["network_path"]   = $config["base_path"] . "Network" . DIRECTORY_SEPARATOR;

        require_once $config["base_path"] .  "Adapter.php";
        require_once $config["base_path"] .  "Auth.php";
        require_once $config["base_path"] .  "Client.php";
        require_once $config["base_path"] .  "Exception.php";
        require_once $config["base_path"] .  "Logger.php";
        require_once $config["base_path"] .  "Session.php";
        require_once $config["base_path"] .  "User.php";

        require_once $config["base_path"] .  DIRECTORY_SEPARATOR . "Protocol" . DIRECTORY_SEPARATOR . "Model" . DIRECTORY_SEPARATOR . "Base.php";
        require_once $config["base_path"] .  DIRECTORY_SEPARATOR . "Protocol" . DIRECTORY_SEPARATOR . "Model" . DIRECTORY_SEPARATOR . "OAuth.php";
        require_once $config["base_path"] .  DIRECTORY_SEPARATOR . "Protocol" . DIRECTORY_SEPARATOR . "Model" . DIRECTORY_SEPARATOR . "OAuth2.php";

        require_once $config["base_path"] .  DIRECTORY_SEPARATOR . "User" . DIRECTORY_SEPARATOR . "Activity.php";
        require_once $config["base_path"] .  DIRECTORY_SEPARATOR . "User" . DIRECTORY_SEPARATOR . "Contact.php";
        require_once $config["base_path"] .  DIRECTORY_SEPARATOR . "User" . DIRECTORY_SEPARATOR . "Profile.php";




        Social_Auth::$config = $config;

        Social_Auth::$logger = new Social_Logger();

        Social_Auth::$session = new Social_Session();

        Social_Auth::checkRequirements();
    }

    public static function session() {
        return Social_Auth::$session;
    }

    public static function checkRequirements() {
        if ( ! function_exists('curl_init') ) {
            Social_Logger::error('Curl extension needed!');
            throw new Social_Exception("Curl extenson needed!", 1);
        }

        if ( ! function_exists('json_decode') ) {
            Social_Logger::error('JSON extension needed!');
            throw new Exception('JSON extension needed!');
        }

        if( session_name() != "PHPSESSID" ){
            Social_Logger::info('Custom session name detected!');
        }

        if( ini_get('safe_mode') ){
            Social_Logger::info('safe_mode is on');
        }

        // open basedir is on
        if( ini_get('open_basedir') ){
            Social_Logger::info('open_basedir is on');
        }
    }

    public static function authenticate( $network_name, $params = null ) {
        Social_Logger::info( "###Social_Auth::authenticate( $network_name )" );
        if( ! Social_Auth::session()->get( "sa_session.$network_name.logged_in" ) ) {
            $network_adapter = Social_Auth::prepare( $network_name, $params );
            $network_adapter->login();
        } else {
            return Social_Auth::getAdapter( $network_name );
        }
    }

    public static function getAdapter( $network_name = null ) {
        Social_Logger::info( "###Social_Auth::getAdapter( $network_name )" );
        return Social_Auth::prepare( $network_name );
    }

    public static function prepare( $network_name, $params = null ) {
        Social_Logger::info( "###Social_Auth::prepare( $network_name )", $params );

        if( ! $params ){
            $params = Social_Auth::session()->get( "sa_session.$network_name.network_params" );
            Social_Logger::debug( "Social_Auth::prepare( $network_name ), params will be fetched from session" );
        }

        if( ! $params ){
            $params = array();
            Social_Logger::info( "Social_Auth::prepare( $network_name ), initialize new instance for params" );
        }

        if( ! isset( $params["sa_callback_url"] ) ){
            $params["sa_callback_url"] = Social_Auth::getCurrentPage();
        }

        Social_Logger::debug( "Social_Auth::prepare( $network_name ). SocialAuth callback url: " . $params["sa_callback_url"] );

        # instantiate a new IDProvider Adapter
        $network = new Social_Adapter();

        $network->factory( $network_name, $params );

        return $network;
    }

    public static function getCurrentPage() {
        $pageURL = 'http';
        if (!empty($_SERVER["HTTPS"]) && ( $_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == 1 )
            || !empty($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https") {
            $pageURL .= "s";
        }

        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    public static function redirect( $url ){
        Social_Logger::info( "###Social_Auth::redirect( $url )" );
        header( "Location: $url" );
        die();
    }

    public static function isNetworkConnected( $network_name ) {
        return (bool) Social_Auth::session()->get( "sa_session.{$network_name}.logged_in" );
    }

    public static function getConnectedNetworks() {
        $networks = array();

        foreach( Social_Auth::$config["networks"] as $network => $params ){
            if( Social_Auth::isNetworkConnected( $network ) ){
                $networks[] = $network;
            }
        }

        return $networks;
    }

    public static function getAllNetworks() {
        $networks = array();

        foreach( Social_Auth::$config["networks"] as $network => $params ){
            if($params['enabled']) {
                $networks[$$network] = array( 'connected' => false );

                if( Social_Auth::isNetworkConnected( $$network) ){
                    $networks[$network]['connected'] = true;
                }
            }
        }

        return $networks;
    }

    public static function logout($network = null) {
        $networks = Social_Auth::getConnectedNetworks();
        if ( $network ) {
            $adapter = Social_Auth::getAdapter( $network );
            $adapter->logout();
        } else {
            foreach( $networks as $networkToLogout ){
                $adapter = Social_Auth::getAdapter( $networkToLogout );
                $adapter->logout();
            }
        }
    }
}