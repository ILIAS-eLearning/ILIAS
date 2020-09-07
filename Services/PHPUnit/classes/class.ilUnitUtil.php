<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Utilities for Unit Testing
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: class.ilSetting.php 15697 2008-01-08 20:04:33Z hschottm $
 */
class ilUnitUtil
{
    /**
     * @static
     */
    public static function performInitialisation()
    {
        /**
         * @var $ilErr ilErrorHandling
         */
        global $ilErr;

        if (!defined('IL_PHPUNIT_TEST')) {
            define('IL_PHPUNIT_TEST', true);
        }

        session_id('phpunittest');
        $_SESSION = array();

        include 'Services/PHPUnit/config/cfg.phpunit.php';

        include_once 'Services/Context/classes/class.ilContext.php';
        ilContext::init(ilContext::CONTEXT_UNITTEST);

        include_once('Services/Init/classes/class.ilInitialisation.php');
        ilInitialisation::reinitILIAS();
        $GLOBALS['DIC']['ilAuthSession']->setUserId($_SESSION["AccountId"]);
        ilInitialisation::initUserAccount();

        $ilUnitUtil = new self();
        $ilErr->setErrorHandling(PEAR_ERROR_CALLBACK, array($ilUnitUtil, 'errorHandler'));
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ilUnitUtil, 'errorHandler'));
    }

    /**
     * @param $a_error_obj
     * @throws Exception
     */
    public function errorHandler($a_error_obj)
    {
        echo "Error occured: " . get_class($a_error_obj) . "\n";
        try {
            throw new Exception("dummy");
        } catch (Exception $e) {
            echo($e->getTraceAsString() . "\n\n");
        }
        exit();
    }
}
