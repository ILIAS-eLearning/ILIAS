<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'libs/composer/vendor/autoload.php';
require_once 'Services/Utilities/classes/class.ilUtil.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Password/exceptions/class.ilPasswordException.php';

/**
 * Class ilPasswordTestSuite
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ServicesPassword
 */
class ilServicesPasswordSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * @return self
     */
    public static function suite()
    {
        // Set timezone to prevent notices
        date_default_timezone_set('Europe/Berlin');

        $suite = new self();

        require_once dirname(__FILE__) . '/encoders/ilMd5PasswordEncoderTest.php';
        $suite->addTestSuite('ilMd5PasswordEncoderTest');

        require_once dirname(__FILE__) . '/encoders/ilBcryptPasswordEncoderTest.php';
        $suite->addTestSuite('ilBcryptPasswordEncoderTest');

        require_once dirname(__FILE__) . '/encoders/ilBcryptPhpPasswordEncoderTest.php';
        $suite->addTestSuite('ilBcryptPhpPasswordEncoderTest');

        return $suite;
    }
}
