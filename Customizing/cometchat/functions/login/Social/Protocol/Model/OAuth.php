<?php
/**
 * Class Social_Protocol_Model_Oauth for oauth protocols
 */
class Social_Protocol_Model_OAuth extends Social_Protocol_Model_Base {
    public $request_tokens_raw;
    public $access_tokens_raw;

    function errorMessageByStatus( $code = null ) {
        $http_status_codes = array(
            200 => "OK: Success!",
            304 => "Not Modified: There was no new data to return.",
            400 => "Bad Request: The request was invalid.",
            401 => "Unauthorized.",
            403 => "Forbidden: The request is understood, but it has been refused.",
            404 => "Not Found: The URI requested is invalid or the resource requested does not exists.",
            406 => "Not Acceptable.",
            500 => "Internal Server Error: Something is broken.",
            502 => "Bad Gateway.",
            503 => "Service Unavailable."
        );

        if( ! $code && $this->api )
            $code = $this->api->http_code;

        if( isset( $http_status_codes[ $code ] ) )
            return $code . " " . $http_status_codes[ $code ];
    }

    function init() {
        if ( ! $this->config["keys"]["key"] || ! $this->config["keys"]["secret"] ){
            throw new Social_Exception( "Application key and secret needed to connect to $this->network_name");
        }

        require_once Social_Auth::$config["sdk_path"] . "OAuth/OAuth.php";
        require_once Social_Auth::$config["sdk_path"] . "OAuth/OAuth1Client.php";

        if( $this->getToken( "access_token" ) ){
            $this->api = new OAuth1Client(
                $this->config["keys"]["key"], $this->config["keys"]["secret"],
                $this->getToken( "access_token" ), $this->getToken( "access_token_secret" )
            );
        } elseif( $this->getToken( "request_token" ) ) {
            $this->api = new OAuth1Client(
                $this->config["keys"]["key"], $this->config["keys"]["secret"],
                $this->getToken( "request_token" ), $this->getToken( "request_token_secret" )
            );
        } else {
            $this->api = new OAuth1Client( $this->config["keys"]["key"], $this->config["keys"]["secret"] );
        }
    }

    function startLogin() {
        $tokens = $this->api->requestToken( $this->client );

        $this->request_tokens_raw = $tokens;


        if ( $this->api->http_code != 200 ){
            throw new Social_Exception( "Auth failed! {$this->network_name} error: " . $this->errorMessageByStatus( $this->api->http_code ));
        }

        if ( ! isset( $tokens["oauth_token"] ) ){
            throw new Social_Exception( "Auth failed! $this->network_name: invalid oauth token.");
        }

        $this->setToken( "request_token", $tokens["oauth_token"] );
        $this->setToken( "request_token_secret", $tokens["oauth_token_secret"] );

        Social_Auth::redirect( $this->api->authorizeUrl( $tokens ) );
    }

    function finishLogin() {
        $oauth_token    = (array_key_exists('oauth_token',$_REQUEST))?$_REQUEST['oauth_token']:"";
        $oauth_verifier = (array_key_exists('oauth_verifier',$_REQUEST))?$_REQUEST['oauth_verifier']:"";

        if ( ! $oauth_token || ! $oauth_verifier ){
            throw new Social_Exception( "Auth failed! $this->network_name: invalid oauth verifier." );
        }

        $tokens = $this->api->accessToken( $oauth_verifier );

        $this->access_tokens_raw = $tokens;

        if ( $this->api->http_code != 200 ){
            throw new Social_Exception( "Auth failed! $this->network_name error: " . $this->errorMessageByStatus( $this->api->http_code ) );
        }

        if ( ! isset( $tokens["oauth_token"] ) ){
            throw new Social_Exception( "Auth failed! $this->network_name: invalid access token." );
        }

        $this->deleteToken( "request_token" );
        $this->deleteToken( "request_token_secret" );

        $this->setToken( "access_token", $tokens['oauth_token'] );
        $this->setToken( "access_token_secret" , $tokens['oauth_token_secret'] );

        $this->connectUser();
    }
}