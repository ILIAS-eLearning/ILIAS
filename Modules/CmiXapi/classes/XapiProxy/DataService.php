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

namespace XapiProxy;

class DataService
{
    public static function initIlias(string $client_id) : void
    {
        define("CLIENT_ID", $client_id);
        // Im Plugin war das auskommentiert(?)
//        define('IL_COOKIE_HTTPONLY', true); // Default Value
//        define('IL_COOKIE_EXPIRE', 0);
//        define('IL_COOKIE_PATH', '/');
//        define('IL_COOKIE_DOMAIN', '');
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
     * @param string      $a_class
     * @param string|null $a_source_file
     * @see \ilInitialisation::initGlobal($a_name, $a_class, $a_source_file)
     */
    //    public static function initGlobal(string $a_name, string $a_class, ?string $a_source_file = null) : void
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
