<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/


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
    
    public static function initIlias($client_id) : void
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
        \ilContext::init(\ilContext::CONTEXT_SCORM);
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

class ilInitialisation extends \ilInitialisation
{
    /**
    * Function; initGlobal($a_name, $a_class, $a_source_file)
    *  Derive from protected to public...
    *
    * @see \ilInitialisation::initGlobal($a_name, $a_class, $a_source_file)
    */
    public static function initGlobal($a_name, $a_class, $a_source_file = null) : void
    {
        parent::initGlobal($a_name, $a_class, $a_source_file);
    }

    /**
    * Function: initDatabase()
    *  Derive from protected to public...
    *
    * @see \ilInitialisation::initDatabase()
    */
    public static function initDatabase() : void
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
    public static function initIliasIniFile() : void
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
    public static function initClientIniFile() : void
    {
        if (!isset($GLOBALS['initClientIniFile'])) {
            parent::initClientIniFile();
        }
    }
    
    //UK
    public static function initLog() : void
    {
        if (!isset($GLOBALS['ilLog'])) {
            parent::initLog();
            parent::initGlobal("ilAppEventHandler", "ilAppEventHandler", "./Services/EventHandling/classes/class.ilAppEventHandler.php");
        }
    }
}
