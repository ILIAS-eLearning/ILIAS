<?php declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */
use ILIAS\DI\Container;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ilRTEBaseTest
 * @author Jephte Abijuru <jephte.abijuru@minervis.com>
 */
abstract class ilRTEBaseTest extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        $GLOBALS['DIC'] = new Container();
        $this->setMocks();

        parent::setUp();
    }

    /**
     * @param string $name
     * @param $value
     */
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }
    protected function setMocks(){
        $tpl_mock = $this->getMockBuilder(\ilTemplate::class)->disableOriginalConstructor()->getMock();
        $this->setGlobalVariable('tpl', $tpl_mock);
        $lng = $this
            ->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->setMethods(['txt', 'getInstalledLanguages', 'loadLanguageModule'])
            ->getMock();
        $this->setGlobalVariable('lng', $lng);
        $this->setGlobalVariable(
            'ilCtrl',
            $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock()
        );
        $this->setGlobalVariable(
            'ilBrowser',
            $this->getMockBuilder(ilBrowser::class)->disableOriginalConstructor()->getMock()
        );
        $this->setGlobalVariable(
            'ilClientIniFile',
            $this->getMockBuilder(ilIniFile::class)->disableOriginalConstructor()->getMock()
        );
        $this->setGlobalVariable(
            'ilUser',
            $this->getMockBuilder(ilUser::class)->disableOriginalConstructor()->getMock()
        );
        
    }
}