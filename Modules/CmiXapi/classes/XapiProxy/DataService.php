<?php

namespace XapiProxy;

class DataService
{
    public static function initIlias($client_id)
    {
        define("CLIENT_ID", $client_id);

        // see: commit 2844b5d7bfffe08728ecb54c21acf00fd65d5969
        //
        // or set clientId Cookie and Context class ilContextScorm: supportsPersistentSessions = true
        // or set clientId Cookie support own Context with supportsPersistentSessions = true
        $_GET['client_id'] = $client_id;

        // Im Plugin war das auskommentiert(?)
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
 *  required by XapiProxy and included plugin classes
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
        if (!isset($GLOBALS['ilDB'])) {
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
