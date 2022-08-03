<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
        define("CLIENT_ID", $client_id);
        define('IL_COOKIE_HTTPONLY', true); // Default Value
        define('IL_COOKIE_EXPIRE', 0);
        define('IL_COOKIE_PATH', '/');
        define('IL_COOKIE_DOMAIN', '');
        \ilContext::init(\ilContext::CONTEXT_SCORM);
        \ilInitialisation::initILIAS();
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
