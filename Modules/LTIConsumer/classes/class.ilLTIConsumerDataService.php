<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLTIConsumerLaunch
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
namespace LTI;

class ilLTIConsumerDataService
{
    // protected static function getIniHost() {
    // // Create ini-handler (onces)
    // ilInitialisation::initIliasIniFile();
    // global $ilIliasIniFile;
    // // Return [server] -> 'http_path' variable from 'ilias.init.php'
    // $http_path = $ilIliasIniFile->readVariable('server', 'http_path');
    // // Strip http:// & https://
    // if (strpos($http_path, 'https://') !== false)
    // $http_path = substr($http_path, 8);
    // if (strpos($http_path, 'http://') !== false)
    // $http_path = substr($http_path, 7);
    // // Return clean host
    // return $http_path;
    // }
    
    public static function initIlias($client_id)
    {
        // if (isset($_GET["client_id"]))
        // {
        // $cookie_domain = $_SERVER['SERVER_NAME'];
        // $cookie_path = dirname( $_SERVER['PHP_SELF'] );

        // /* if ilias is called directly within the docroot $cookie_path
        // is set to '/' expecting on servers running under windows..
        // here it is set to '\'.
        // in both cases a further '/' won't be appended due to the following regex
        // */
        // $cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

        // if($cookie_path == "\\") $cookie_path = '/';

        // $cookie_domain = ''; // Temporary Fix

        // setcookie("ilClientId", $_GET["client_id"], 0, $cookie_path, $cookie_domain);

        // $_COOKIE["ilClientId"] = $_GET["client_id"];
        // }

    
        
        define("CLIENT_ID", $client_id);
        define('IL_COOKIE_HTTPONLY', true); // Default Value
        define('IL_COOKIE_EXPIRE', 0);
        define('IL_COOKIE_PATH', '/');
        define('IL_COOKIE_DOMAIN', '');
        require_once('Services/Context/classes/class.ilContext.php');
        \ilContext::init(\ilContext::CONTEXT_SCORM);
        //UK
        require_once("Services/Init/classes/class.ilInitialisation.php");
        \ilInitialisation::initILIAS();
        // Remember original values
        // $_ORG_SERVER = array(
          // 'HTTP_HOST'    => $_SERVER['HTTP_HOST'],
          // 'REQUEST_URI'  => $_SERVER['REQUEST_URI'],
          // 'PHP_SELF'     => $_SERVER['PHP_SELF'],
        // );
        // // Overwrite $_SERVER entries which would confuse ILIAS during initialisation
        // $_SERVER['REQUEST_URI'] = '';
        // $_SERVER['PHP_SELF']    = '/index.php';
        // $_SERVER['HTTP_HOST']   = self::getIniHost();
        // require_once "./Services/Utilities/classes/class.ilUtil.php";
        // //ilInitialisation::initIliasIniFile();
        // ilInitialisation::initClientIniFile();
        // ilInitialisation::initDatabase();
        
        // // Restore original, since this could lead to bad side-effects otherwise
        // $_SERVER['HTTP_HOST']   = $_ORG_SERVER['HTTP_HOST'];
        // $_SERVER['REQUEST_URI'] = $_ORG_SERVER['REQUEST_URI'];
        // $_SERVER['PHP_SELF']    = $_ORG_SERVER['PHP_SELF'];
        // ilInitialisation::initLog();//UK
    }
}


/**
 *  Class: ilInitialisation_Public
 *  Helper class that derives from ilInitialisation in order
 *  to 'publish' some of its methods that are (currently)
 *  required by LTI and included plugin classes
 *
 */
require_once('Services/Init/classes/class.ilInitialisation.php');
class ilInitialisation extends \ilInitialisation
{
    /**
    * Function; initGlobal($a_name, $a_class, $a_source_file)
    *  Derive from protected to public...
    *
    * @see \ilInitialisation::initGlobal($a_name, $a_class, $a_source_file)
    */
    public static function initGlobal($a_name, $a_class, $a_source_file = null)
    {
        return parent::initGlobal($a_name, $a_class, $a_source_file);
    }

    /**
    * Function: initDatabase()
    *  Derive from protected to public...
    *
    * @see \ilInitialisation::initDatabase()
    */
    public static function initDatabase()
    {
        if (!isset($GLOBALS['ilDB'])) { //TODO DIC
            parent::initGlobal("ilBench", "ilBenchmark", "./Services/Utilities/classes/class.ilBenchmark.php");
            parent::initDatabase();
        }
    }

    /**
    * Function: initIliasIniFile()
    *  Derive from protected to public...
    *
    * @see \ilInitialisation::initIliasIniFile()
    */
    public static function initIliasIniFile()
    {
        if (!isset($GLOBALS['ilIliasIniFile'])) {
            parent::initIliasIniFile();
        }
    }
    
    /**
    * Function: initClientIniFile()
    *  Derive from protected to public...
    *
    * @see \ilInitialisation::initIliasIniFile()
    */
    public static function initClientIniFile()
    {
        if (!isset($GLOBALS['initClientIniFile'])) {
            parent::initClientIniFile();
        }
    }
    
    //UK
    public static function initLog()
    {
        if (!isset($GLOBALS['ilLog'])) {
            parent::initLog();
            parent::initGlobal("ilAppEventHandler", "ilAppEventHandler", "./Services/EventHandling/classes/class.ilAppEventHandler.php");
        }
    }
}
