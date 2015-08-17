<?php
class Social_Network_Twitter extends Social_Protocol_Model_OAuth {
    function init() {
        parent::init();

        $this->api->api_base_url = "https://api.twitter.com/1.1/";
        $this->api->authorize_url = "https://api.twitter.com/oauth/authenticate";
        $this->api->request_token_url = "https://api.twitter.com/oauth/request_token";
        $this->api->access_token_url  = "https://api.twitter.com/oauth/access_token";

        if ( isset( $this->config['api_version'] ) && $this->config['api_version'] ){
            $this->api->api_base_url  = "https://api.twitter.com/{$this->config['api_version']}/";
        }

        if ( isset( $this->config['authorize'] ) && $this->config['authorize'] ){
            $this->api->authorize_url = "https://api.twitter.com/oauth/authorize";
        }

        $this->api->curl_auth_header  = false;
    }

    function startLogin() {
        $tokens = $this->api->requestToken( $this->client );

        $this->request_tokens_raw = $tokens;

        if ( ! isset( $tokens["oauth_token"] ) ){
            throw new Social_Exception( "Auth failed! $this->network_name: invalid oauth token." );
            exit;
        }

        if ( $this->api->http_code != 200 ){
            throw new Social_Exception( "Auth failed! $this->network_name error: " . $this->errorMessageByStatus( $this->api->http_code ) );
        }

        $this->setToken( "request_token" , $tokens["oauth_token"] );
        $this->setToken( "request_token_secret", $tokens["oauth_token_secret"] );

        if ( isset( $this->config['force_login'] ) && $this->config['force_login'] ){
            Social_Auth::redirect( $this->api->authorizeUrl( $tokens, array( 'force_login' => true ) ) );
        }

        Social_Auth::redirect( $this->api->authorizeUrl( $tokens ) );
    }

    function finishLogin() {
        parent::finishLogin();
        $twitterUser = $this->getUserProfile();
    }

    function getUserProfile() {
        $response = $this->api->get( 'account/verify_credentials.json' );

        if ( $this->api->http_code != 200 ){
            throw new Social_Exception( "User profile request failed! $this->network_name error: " . $this->errorMessageByStatus( $this->api->http_code ) );
        }

        if ( ! is_object( $response ) || ! isset( $response->id ) ){
            throw new Social_Exception( "User profile request failed! $this->network_name: invalid response." );
        }

        $this->user->profile->identifier  = (property_exists($response,'id'))?$response->id:"";
        $this->user->profile->displayName = (property_exists($response,'screen_name'))?$response->screen_name:"";
        $this->user->profile->description = (property_exists($response,'description'))?$response->description:"";
        $this->user->profile->firstName   = (property_exists($response,'name'))?$response->name:"";
        $this->user->profile->photoURL    = (property_exists($response,'profile_image_url'))?$response->profile_image_url:"";
        $this->user->profile->profileURL  = (property_exists($response,'screen_name'))?("http://twitter.com/".$response->screen_name):"";
        $this->user->profile->webSiteURL  = (property_exists($response,'url'))?$response->url:"";
        $this->user->profile->region      = (property_exists($response,'location'))?$response->location:"";

        return $this->user->profile;
    }

    function getUserContacts() {
        $parameters = array( 'cursor' => '-1' );
        $response  = $this->api->get( 'friends/ids.json', $parameters );

        if ( $this->api->http_code != 200 ){
            throw new Social_Exception( "User contacts request failed! $this->network_name error: " . $this->errorMessageByStatus( $this->api->http_code ) );
        }

        if( ! $response || ! count( $response->ids ) ){
            return array();
        }

        $contactsids = array_chunk ( $response->ids, 75 );

        $contacts = array();

        foreach( $contactsids as $chunk ){
            $parameters = array( 'user_id' => implode( ",", $chunk ) );
            $response   = $this->api->get( 'users/lookup.json', $parameters );

            if ( $this->api->http_code != 200 ){
                throw new Social_Exception( "User contacts request failed! $this->network_name error: " . $this->errorMessageByStatus( $this->api->http_code ) );
            }

            if( $response && count( $response ) ){
                foreach( $response as $item ){
                    $uc = new Social_User_Contact();

                    $uc->identifier   = (property_exists($item,'id'))?$item->id:"";
                    $uc->displayName  = (property_exists($item,'name'))?$item->name:"";
                    $uc->profileURL   = (property_exists($item,'screen_name'))?("http://twitter.com/".$item->screen_name):"";
                    $uc->photoURL     = (property_exists($item,'profile_image_url'))?$item->profile_image_url:"";
                    $uc->description  = (property_exists($item,'description'))?$item->description:"";

                    $contacts[] = $uc;
                }
            }
        }

        return $contacts;
    }

    function setUserStatus( $status ) {
        $parameters = array( 'status' => $status );
        $response  = $this->api->post( 'statuses/update.json', $parameters );

        if ( $this->api->http_code != 200 ){
            throw new Social_Exception( "Update user status failed! $this->network_name error: " . $this->errorMessageByStatus( $this->api->http_code ) );
        }
    }

    function getUserActivity( $stream ) {
        if( $stream == "me" ){
            $response  = $this->api->get( 'statuses/user_timeline.json' );
        } else {
            $response  = $this->api->get( 'statuses/home_timeline.json' );
        }

        if ( $this->api->http_code != 200 ){
            throw new Social_Exception( "User activity stream request failed! $this->network_name error: " . $this->errorMessageByStatus( $this->api->http_code ) );
        }

        if( ! $response ){
            return array();
        }

        $activities = array();

        foreach( $response as $item ){
            $ua = new Social_User_Activity();

            $ua->id                 = (property_exists($item,'id'))?$item->id:"";
            $ua->date               = (property_exists($item,'created_at'))?strtotime($item->created_at):"";
            $ua->text               = (property_exists($item,'text'))?$item->text:"";

            $ua->user->identifier   = (property_exists($item->user,'id'))?$item->user->id:"";
            $ua->user->displayName  = (property_exists($item->user,'name'))?$item->user->name:"";
            $ua->user->profileURL   = (property_exists($item->user,'screen_name'))?("http://twitter.com/".$item->user->screen_name):"";
            $ua->user->photoURL     = (property_exists($item->user,'profile_image_url'))?$item->user->profile_image_url:"";

            $activities[] = $ua;
        }

        return $activities;
    }
}