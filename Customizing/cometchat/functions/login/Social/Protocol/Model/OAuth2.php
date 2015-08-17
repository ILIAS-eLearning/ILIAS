<?php
class Social_Protocol_Model_OAuth2 extends Social_Protocol_Model_Base {
    public $scope = "";

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
            throw new Social_Exception( "Application key and secret needed to connect to {$this->network_name}}.", 1 );
        }

        if( isset( $this->config["scope"] ) && ! empty( $this->config["scope"] ) ){
            $this->scope = $this->config["scope"];
        }

        require_once Social_Auth::$config["sdk_path"] . "OAuth/OAuth2Client.php";

        $this->api = new OAuth2Client( $this->config["keys"]["key"], $this->config["keys"]["secret"], $this->client );

        if( $this->getToken( "access_token" ) ){
            $this->api->access_token = $this->getToken( "access_token" );
            $this->api->refresh_token = $this->getToken( "refresh_token" );
            $this->api->access_token_expires_in = $this->getToken( "expires_in" );
            $this->api->access_token_expires_at = $this->getToken( "expires_at" );
        }
    }

    function startLogin() {
        Social_Auth::redirect( $this->api->authorizeUrl( array( "scope" => $this->scope ) ) );
    }

    function finishLogin() {
        $error = (array_key_exists('error',$_REQUEST))?$_REQUEST['error']:"";

        if ( $error ){
            throw new Social_Exception( "Authentication failed! {$this->network_name} returned an error: $error", 1 );
        }

        $code = (array_key_exists('code',$_REQUEST))?$_REQUEST['code']:"";

        try{
            $this->api->authenticate( $code );
        }
        catch( Exception $e ){
            throw new Social_Exception( "User profile request failed! {$this->network_name} returned an error: $e", 1 );
        }

        if ( ! $this->api->access_token ){
            throw new Social_Exception( "Authentication failed! {$this->network_name} returned an invalid access token.", 1 );
        }

        $this->setToken( "access_token" , $this->api->access_token  );
        $this->setToken( "refresh_token", $this->api->refresh_token );
        $this->setToken( "expires_in"   , $this->api->access_token_expires_in );
        $this->setToken( "expires_at"   , $this->api->access_token_expires_at );

        $this->connectUser();
    }

    function refreshToken()
    {
        if( $this->api->access_token ){

            if( $this->api->refresh_token && $this->api->access_token_expires_at ){

                if( $this->api->access_token_expires_at <= time() ){
                    $response = $this->api->refreshToken( array( "refresh_token" => $this->api->refresh_token ) );

                    if( ! isset( $response->access_token ) || ! $response->access_token ){
                        // set the user as disconnected at this point and throw an exception
                        $this->connectUser();

                        throw new Social_Exception( "Invalid response on new access token request. " . (string) $response->error );
                    }

                    $this->api->access_token = $response->access_token;

                    if( isset( $response->refresh_token ) )
                        $this->api->refresh_token = $response->refresh_token;

                    if( isset( $response->expires_in ) ){
                        $this->api->access_token_expires_in = $response->expires_in;

                        $this->api->access_token_expires_at = time() + $response->expires_in;
                    }
                }
            }

            $this->setToken( "access_token" , $this->api->access_token  );
            $this->setToken( "refresh_token", $this->api->refresh_token );
            $this->setToken( "expires_in"   , $this->api->access_token_expires_in );
            $this->setToken( "expires_at"   , $this->api->access_token_expires_at );
        }
    }

    function getUserProfile() {
        Social_Logger::error( "Social_Auth do not provide users contacts list for {$this->network_name}." );

        throw new Social_Exception( "This feature has not been implemented" );
    }

    function getUserActivity() {
        Social_Logger::error( "Social_Auth do not provide users activity for {$this->network_name}." );

        throw new Social_Exception( "This feature has not been implemented" );
    }

    function getUserContacts() {
        Social_Logger::error( "Social_Auth do not provide users contacts for {$this->network_name}." );

        throw new Social_Exception( "This feature has not been implemented" );
    }
}