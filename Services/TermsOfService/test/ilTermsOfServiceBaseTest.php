<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ilTermsOfServiceBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceBaseTest extends TestCase
{
    protected Container $dic;

    protected function setUp() : void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('lng', $this->getLanguageMock());
        $this->setGlobalVariable(
            'ilCtrl',
            $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock()
        );

        parent::setUp();
    }

    /**
     * @return MockObject|ilLanguage
     */
    protected function getLanguageMock() : ilLanguage
    {
        $lng = $this
            ->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'getInstalledLanguages', 'loadLanguageModule'])
            ->getMock();

        return $lng;
    }

    /**
     * @return MockObject|Factory
     */
    protected function getUiFactoryMock() : Factory
    {
        $ui = $this
            ->getMockBuilder(Factory::class)
            ->getMock();

        $ui->method('legacy')->willReturnCallback(function ($content) {
            $legacyMock = $this
                ->getMockBuilder(Legacy::class)
                ->getMock();
            $legacyMock->method('getContent')->willReturn($content);

            return $legacyMock;
        });

        return $ui;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    /**
     * @param mixed $value
     * @return ilTermsOfServiceCriterionConfig
     */
    protected function getCriterionConfig($value = null) : ilTermsOfServiceCriterionConfig
    {
        if (null === $value) {
            return new ilTermsOfServiceCriterionConfig();
        }

        return new ilTermsOfServiceCriterionConfig($value);
    }
}
