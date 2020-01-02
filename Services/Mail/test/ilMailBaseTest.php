<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilMailBaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();

        parent::setUp();
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setGlobalVariable($name, $value)
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    /**
     * @param string $exception_class
     */
    protected function assertException($exception_class)
    {
        if (version_compare(PHPUnit_Runner_Version::id(), '5.0', '>=')) {
            $this->setExpectedException($exception_class);
        }
    }
}
