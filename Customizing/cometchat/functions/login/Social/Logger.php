<?php
/**
 * Class Social_Logger for log utility
 *
 * @author Hüseyin BABAL
 */
class Social_Logger {

    /**
     * Init log settings, decide which file to write
     */
    function __construct() {

        if ( Social_Auth::$config["debug_enabled"] ){
            if ( ! file_exists( Social_Auth::$config["log_file"] ) ){
                throw new Exception( " File not found for debug log: " . Social_Auth::$config['log_file'], 1 );
            }

            if ( ! is_writable( Social_Auth::$config["log_file"] ) ){
                throw new Exception( "Debug file ". Social_Auth::$config["log_file"] . " is not writable", 1 );
            }
        }
    }

    /**
     * Debug log
     * @param $message
     * @param null $object
     */
    public static function debug( $message, $object = null ) {
        if( Social_Auth::$config["debug_enabled"] ){
            Social_Logger::write_to_file("SOCIAL_AUTH_DEBUG", $message, $object);
        }
    }

    /**
     * Info log
     * @param $message
     */
    public static function info( $message ) {
        if( Social_Auth::$config["debug_enabled"] ){
            Social_Logger::write_to_file("SOCIAL_AUTH_INFO", $message);
        }
    }

    /**
     * Error log
     * @param $message
     * @param null $object
     */
    public static function error($message, $object = null) {
        if( Social_Auth::$config["debug_enabled"] ){
            Social_Logger::write_to_file("SOCIAL_AUTH_ERROR", $message, $object);
        }
    }

    /**
     * Write log message and objects in to log file specified
     * @param $type
     * @param $message
     * @param null $object
     */
    protected static function write_to_file($type, $message, $object = null) {
        $datetime = new DateTime();
        $datetime =  $datetime->format("d-m-Y H:i:s");

        file_put_contents(
            Social_Auth::$config["log_file"],
            $type . " | " . $_SERVER['REMOTE_ADDR'] . " | " . $datetime . " | " . $message . " | " . print_r($object, true) . "\n",
            FILE_APPEND
        );
    }
}
