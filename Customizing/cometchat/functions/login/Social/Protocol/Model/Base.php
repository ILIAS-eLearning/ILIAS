<?php
/**
 * Social_Protocol_Model_Base provides oommons abstract class for several Networks
 * e.g. Facebook, Twitter, Google, Yahoo, Linkedin, etc...
 *
 * @author Hüseyin BABAL
 */

abstract class Social_Protocol_Model_Base {

    public $network_name;

    public $config;

    public $params;

    public $client;

    public $user;

    public $api;

    function __construct( $network_name, $config, $params = null ) {
        if( ! $params ){
            $this->params = Social_Auth::session()->get( "sa_session.$network_name.network_params" );
        } else {
            $this->params = $params;
        }

        $this->network_name = $network_name;

        $this->client = Social_Auth::session()->get( "sa_session.$network_name.sa_client" );

        $this->config = $config;

        $this->user = new Social_User();
        $this->user->network_name = $network_name;

        $this->init();

        Social_Logger::debug( "Social_Protocol_Model_Base initialized for $network_name : ", serialize( $this ) );
    }

    abstract protected function init();

    abstract protected function startLogin();

    abstract protected function finishLogin();

    function logout() {
        Social_Logger::info( "###$this->network_name::logout()" );
        $this->clearTokens();
        return true;
    }

    public function disconnectUser() {
        Social_Logger::info( "###$this->network_name::disconnectUser()" );
        Social_Auth::session()->set( "sa_session.{$this->network_name}.logged_in", 0 );
    }

    public function isUserConnected() {
        return (bool) Social_Auth::session()->get( "sa_session.{$this->network_name}.logged_in" );
    }

    public function connectUser() {
        Social_Logger::info( "###$this->network_name::connectUser()" );
        Social_Auth::session()->set( "sa_session.{$this->network_name}.logged_in", 1 );
    }

    public function setToken( $token, $value ) {
        Social_Auth::session()->set( "sa_session.{$this->network_name}.token.$token", $value );
    }

    public function getToken( $token ){
        return Social_Auth::session()->get( "sa_session.{$this->network_name}.token.$token" );
    }

    public function deleteToken( $token ) {
        Social_Auth::session()->deleteByKey( "sa_session.{$this->network_name}.token.$token" );
    }

    public function clearTokens() {
        Social_Auth::session()->deleteByNetwork( "sa_session.{$this->network_name}." );
    }


}