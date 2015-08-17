<?php
class Social_Network_Facebook extends Social_Protocol_Model_Base {

    public $scope = "email, user_about_me, user_birthday, user_hometown, user_website, read_stream, offline_access, publish_stream, read_friendlists";

    function init() {
        if ( ! $this->config["keys"]["key"] || ! $this->config["keys"]["secret"] ){
            throw new Social_Exception( "ID and Secret needed for connecting to {$this->network_name}.", 1 );
        }

        if ( ! class_exists('FacebookApiException', false) ) {
            require_once Social_Auth::$config["sdk_path"] . "Facebook/base_facebook.php";
            require_once Social_Auth::$config["sdk_path"] . "Facebook/facebook.php";
        }

        $this->api = new Facebook( array( 'appId' => $this->config["keys"]["key"], 'secret' => $this->config["keys"]["secret"] ) );

        if ( $this->getToken("access_token") ) {
            $this->api->setAccessToken( $this->getToken("access_token") );
            $this->api->setExtendedAccessToken();
            $access_token = $this->api->getAccessToken();

            if( $access_token ){
                $this->setToken("access_token", $access_token );
                $this->api->setAccessToken( $access_token );
            }

            $this->api->setAccessToken( $this->getToken("access_token") );
        }

        $this->api->getUser();

    }

    function startLogin() {
        $parameters = array("scope" => $this->scope, "redirect_uri" => $this->client, "display" => "popup");
        $optionals  = array("scope", "redirect_uri", "display");

        foreach ($optionals as $parameter){
            if( isset( $this->config[$parameter] ) && ! empty( $this->config[$parameter] ) ){
                $parameters[$parameter] = $this->config[$parameter];
            }
        }
        $url = $this->api->getLoginUrl( $parameters );

        Social_Auth::redirect( $url );
    }

    function finishLogin() {
        if ( isset( $_REQUEST['error'] ) && $_REQUEST['error'] == "access_denied" ){
            throw new Social_Exception( "Auth failed due to user denial.", 1 );
        }

        if ( ! $this->api->getUser() ){
            throw new Social_Exception( "Auth failed! {$this->network_name} returned an invalid user id.", 1 );
        }

        $this->connectUser();

        $this->setToken( "access_token", $this->api->getAccessToken() );
    }

    function logout() {
        $this->api->destroySession();
        parent::logout();
    }

    function getUserProfile() {
        try{
            $data = $this->api->api('/v2.0/me');
        } catch( FacebookApiException $e ) {
            throw new Social_Exception( "User profile cannot loaded for  {$this->network_name}: $e", 1 );
        }

        if ( ! isset( $data["id"] ) ){
            throw new Social_Exception( "User profile cannot loaded for  {$this->network_name}", 1 );
        }

        $this->user->profile->identifier    = (array_key_exists('id',$data))?$data['id']:"";
        $this->user->profile->displayName   = (array_key_exists('name',$data))?$data['name']:"";
        $this->user->profile->firstName     = (array_key_exists('first_name',$data))?$data['first_name']:"";
        $this->user->profile->lastName      = (array_key_exists('last_name',$data))?$data['last_name']:"";
        $this->user->profile->photoURL      = "https://graph.facebook.com/" . $this->user->profile->identifier . "/picture?width=150&height=150";
        $this->user->profile->profileURL    = (array_key_exists('link',$data))?$data['link']:"";
        $this->user->profile->webSiteURL    = (array_key_exists('website',$data))?$data['website']:"";
        $this->user->profile->gender        = (array_key_exists('gender',$data))?$data['gender']:"";
        $this->user->profile->description   = (array_key_exists('bio',$data))?$data['bio']:"";
        $this->user->profile->email         = (array_key_exists('email',$data))?$data['email']:"";
        $this->user->profile->emailVerified = (array_key_exists('email',$data))?$data['email']:"";
        $this->user->profile->region        = (array_key_exists("hometown",$data)&&array_key_exists("name",$data['hometown']))?$data['hometown']["name"]:"";

        if( array_key_exists('birthday',$data) ) {
            list($birthday_month, $birthday_day, $birthday_year) = explode( "/", $data['birthday'] );

            $this->user->profile->birthDay   = (int) $birthday_day;
            $this->user->profile->birthMonth = (int) $birthday_month;
            $this->user->profile->birthYear  = (int) $birthday_year;
        }

        return $this->user->profile;
    }

    function getUserContacts() {
        try{
            $response = $this->api->api('/v2.0/me/friends');
        } catch( FacebookApiException $e ){
            throw new Social_Exception( "User contact cannot loaded! {$this->network_name} error: $e" );
        }

        if( ! $response || ! count( $response["data"] ) ){
            return array();
        }

        $contacts = array();

        foreach( $response["data"] as $item ){
            $uc = new Social_User_Contact();

            $uc->identifier  = (array_key_exists("id",$item))?$item["id"]:"";
            $uc->displayName = (array_key_exists("name",$item))?$item["name"]:"";
            $uc->profileURL  = "https://www.facebook.com/profile.php?id=" . $uc->identifier;
            $uc->photoURL    = "https://graph.facebook.com/" . $uc->identifier . "/picture?width=150&height=150";

            $contacts[] = $uc;
        }

        return $contacts;
    }

    function getUserActivity( $stream ) {
        try {
            if( $stream == "me" ){
                $response = $this->api->api( '/v2.0/me/feed' );
            }
            else{
                $response = $this->api->api('/v2.0/me/home');
            }
        } catch( FacebookApiException $e ){
            throw new Exception( "User activity cannot loaded! {$this->network_name} error: $e" );
        }

        if( ! $response || ! count(  $response['data'] ) ){
            return array();
        }

        $activities = array();

        foreach( $response['data'] as $item ){
            if( $stream == "me" && $item["from"]["id"] != $this->api->getUser() ){
                continue;
            }

            $ua = new Social_User_Activity();

            $ua->id = (array_key_exists("id",$item))?$item["id"]:"";
            $ua->date = (array_key_exists("created_time",$item))?strtotime($item["created_time"]):"";

            if( $item["type"] == "video" ){
                $ua->text           = (array_key_exists("link",$item))?$item["link"]:"";
            }

            if( $item["type"] == "link" ){
                $ua->text           = (array_key_exists("link",$item))?$item["link"]:"";
            }

            if( empty( $ua->text ) && isset( $item["story"] ) ){
                $ua->text           = (array_key_exists("link",$item))?$item["link"]:"";
            }

            if( empty( $ua->text ) && isset( $item["message"] ) ){
                $ua->text           = (array_key_exists("message",$item))?$item["message"]:"";
            }

            if( ! empty( $ua->text ) ){
                $ua->user->identifier   = (array_key_exists("id",$item["from"]))?$item["from"]["id"]:"";
                $ua->user->displayName  = (array_key_exists("name",$item["from"]))?$item["from"]["name"]:"";
                $ua->user->profileURL   = "https://www.facebook.com/profile.php?id=" . $ua->user->identifier;
                $ua->user->photoURL     = "https://graph.facebook.com/" . $ua->user->identifier . "/picture?type=square";

                $activities[] = $ua;
            }
        }

        return $activities;
    }
}