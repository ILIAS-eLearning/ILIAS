<?php

class Social_Exception extends Exception
{
    public function __construct( $message, $code = 1, Exception $previous = null ) {
        if ( version_compare( PHP_VERSION, '5.3.0', '>=' ) && ($previous instanceof Exception) ) {
            parent::__construct( "SocialAuthException: " . $message, $code, $previous );
        }
        else{
            parent::__construct( "SocialAuthException: " . $message, $code );
        }

    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}