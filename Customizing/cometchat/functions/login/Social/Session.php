<?php
/**
 * Social_Session is for common session utilities
 *
 * @author Hüseyin BABAL
 */

class Social_Session {

    public $session_key = "CCAUTH_SESSION";

    function __construct() {
        if ( ! session_id() ){
            if( ! session_start() ){
                throw new Exception( "In order to use SocialAuth, you need to sstart session with 'session_start()'", 1 );
            }
        }
    }

    /**
     * Set value in the config
     *
     * @param $key
     * @param $value
     */
    public function set( $key, $value ) {
        $_SESSION[$this->session_key][strtolower($key)] = serialize( $value );
    }

    /**
     * Get specific value from session by using key
     *
     * @param $key
     * @return mixed|null
     */
    public function get( $key ) {
        $key = strtolower( $key );
        if( !empty( $_SESSION[$this->session_key] ) && !empty( $_SESSION[$this->session_key][$key] ) ) {
            return unserialize( $_SESSION[$this->session_key][$key] );
        }
        return null;
    }

    /**
     * Delete entire session related to SocialAuth
     */
    function flush() {
        $_SESSION[$this->session_key] = array();
    }

    function deleteByKey( $key ) {
        $key = strtolower( $key );
        if( isset( $_SESSION[$this->session_key], $_SESSION[$this->session_key][$key] ) ){
            $temp = $_SESSION[$this->session_key];
            unset($temp[$key]);
            $_SESSION[$this->session_key] = $temp;
        }
    }

    /**
     * Delete session value elongs to specified network
     *
     * @param $network_name
     */
    function deleteByNetwork( $network_name ) {
        $key = strtolower( $network_name );

        if( isset( $_SESSION[$this->session_key] ) && count( $_SESSION[$this->session_key] ) ) {
            $temp = $_SESSION[$this->session_key];
            foreach( $temp as $k => $v ){
                if( strstr( $k, $key ) ){
                    unset( $temp[ $k ] );
                }
            }
            $_SESSION[$this->session_key] = $temp;

        }
    }

    /**
     * Get entire session object
     *
     * @return null|string
     */
    function getSessionObject() {
        if( isset( $_SESSION[$this->session_key] ) ){
            return serialize( $_SESSION[$this->session_key] );
        }

        return null;
    }

    /**
     * Reinit session with custom value
     *
     * @param null $data
     */
    function overrideSession( $data = null ) {
        $_SESSION[$this->session_key] = unserialize( $data );
    }
}