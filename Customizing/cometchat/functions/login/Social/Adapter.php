<?php
/**
 * Social_Adapter is an adapter for social networks in one place
 *
 * @author Hüseyin BABAL
 */

class Social_Adapter {

    /* Network name (facebook, twitter, etc...) */
    public $network_name = null;

    // Configs belong to provided network
    public $config = null;

    // Network extra params
    public $params = null;

    // Network class
    public $network_class = null ;

    // Adapter instance
    public $adapter = null ;

    function factory( $network_name, $params = null ) {
        Social_Logger::info( "###Social_Adapter::factory( $network_name )" );
        $this->params = $params;
        $this->network_name = strtolower($network_name);
        $this->config = $this->getNetworkConfigs( $this->network_name );

        if( ! $this->network_name ){
            throw new Social_Exception( "Network provider: $network_name not found!", 1 );
        }

        if( ! $this->config ){
            throw new Social_Exception( "Couldn't found config of $this->network_name", 1 );
        }

        if( ! $this->config["enabled"] ){
            throw new Social_Exception( "'{$this->network_name}' is not enabled.", 1 );
        }


        require_once Social_Auth::$config["network_path"] . ucfirst( $this->network_name ) . ".php" ;

        $this->network_class = "Social_Network_" . $this->network_name;

        $this->adapter = new $this->network_class( $this->network_name, $this->config, $this->params );

        return $this;
    }

    function getNetworkConfigs( $network_name ) {
        foreach( Social_Auth::$config["networks"] as $network => $params ){
            if( strtolower( $network ) == strtolower( $network_name ) ){
                return $params;
            }
        }
        return null;
    }

    function goToCallbackPage() {
        $callback_url = Social_Auth::session()->get( "sa_session.{$this->network_name}.sa_callback" );
        // Flush session
        Social_Auth::session()->deleteByKey( "sa_session.{$this->network_name}.sa_callback" );
        Social_Auth::session()->deleteByKey( "sa_session.{$this->network_name}.sa_client" );
        Social_Auth::session()->deleteByKey( "sa_session.{$this->network_name}.network_params" );
        Social_Auth::redirect( $callback_url );
    }

    function login() {
        Social_Logger::info( "###Social_Adapter::login( {$this->network_name} ) " );

        if( ! $this->adapter ){
            throw new Social_Exception( "Social_Adapter::login() cannot be used directly" );
        }

        foreach( Social_Auth::$config["networks"] as $network => $params ){
            Social_Auth::session()->deleteByKey( "sa_session.{$network}.sa_callback"    );
            Social_Auth::session()->deleteByKey( "sa_session.{$network}.sa_client"     );
            Social_Auth::session()->deleteByKey( "sa_session.{$network}.network_params" );
        }

        $this->logout();

        $social_auth_base_url = Social_Auth::$config["base_url"];

        $this->params["sa_token"] = session_id();

        $this->params["sa_time"]  = time();

        $this->params["sa_login"] = $social_auth_base_url . ( strpos( $social_auth_base_url, '?' ) ? '&' : '?' ) . "sa_login={$this->network_name}&sa_time={$this->params["sa_time"]}";

        $this->params["sa_login_finish"]  = $social_auth_base_url . ( strpos( $social_auth_base_url, '?' ) ? '&' : '?' ) . "sa_login_finish={$this->network_name}";

        Social_Auth::session()->set( "sa_session.{$this->network_name}.sa_callback", $this->params["sa_callback_url"] );
        Social_Auth::session()->set( "sa_session.{$this->network_name}.sa_client", $this->params["sa_login_finish"] );
        Social_Auth::session()->set( "sa_session.{$this->network_name}.network_params", $this->params );

        Social_Auth::session()->set( "sa_config", Social_Auth::$config );

        Social_Logger::debug( "Social_Adapter::login( {$this->network_name} ), redirecting sa_login url." );
        Social_Auth::redirect( $this->params["sa_login"] );
    }

    function logout() {
        $this->adapter->logout();
    }

    public function __call( $name, $arguments ) {
        Social_Logger::info( "###Social_Adapter::$name(), Network: {$this->network_name}" );

        if ( ! $this->isUserConnected() ){
            throw new Social_Exception( "User not logged in to network:  {$this->network_name}");
        }

        if ( ! method_exists( $this->adapter, $name ) ){
            throw new Social_Exception( "_call undefined function Social_Network_{$this->network_name}::$name()" );
        }
        if( count( $arguments ) ){
            return $this->adapter->$name( $arguments[0] );
        }
        else{
            return $this->adapter->$name();
        }
    }

    public function isUserConnected() {
        return $this->adapter->isUserConnected();
    }
}