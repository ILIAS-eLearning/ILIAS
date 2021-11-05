<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\HTTP\Services;
use ILIAS\DI\Container;

/**
 * Class ilCtrlTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlTest extends TestCase
{
    private ilCtrl $ctrl;

    protected function setUp() : void
    {
        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', dirname(__FILE__, 4));
        }

        $GLOBALS['DIC'] = new Container();
        $GLOBALS['DIC']->offsetSet('lng', $this->createMock(ilLanguage::class));
        $GLOBALS['DIC']->offsetSet('http', $this->createMock(Services::class));

//        $this->ctrl = new ilCtrl();

        parent::setUp();
    }

    public function testGetHtmlSuccess() : void
    {
        $expected_value = "Hello World!";
        $test_gui = new TestGUI($expected_value);

        $this->assertEquals(
            $expected_value,
            $this->ctrl->getHTML($test_gui)
        );

        $expected_args = ["Hello", " ", "World!"];

        $this->assertEquals(
            $expected_value,
            $this->ctrl->getHTML($test_gui, ["Hello", " ", "World!"])
        );
    }
}

class TestGUI
{
    private string $test_phrase;

    public function __construct(string $test_phrase)
    {
        $this->test_phrase = $test_phrase;
    }

    public function getHTML(array $args = null) : string
    {
        if (null === $args) {
            return $this->test_phrase;
        }

        return implode('', $args);
    }
}