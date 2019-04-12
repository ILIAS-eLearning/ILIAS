<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Class ilTermsOfServiceBaseTest
 * @author Michael Jansen <mjansen@databay.de>
 */
abstract class ilTermsOfServiceBaseTest extends TestCase
{
	/**
	 * @var Container
	 */
	protected $dic;

	/**
	 * @inheritdoc
	 */
	protected function setUp(): void 
	{
		$this->dic = new Container();
		$GLOBALS['DIC'] = $this->dic;

		$initRefl = new \ReflectionClass(\ilInitialisation::class);
		$method = $initRefl->getMethod('initUIFramework');
		$method->setAccessible(true);
		$method->invoke($initRefl, $this->dic);

		$this->setGlobalVariable('lng', $this->getLanguageMock());
		$this->setGlobalVariable('ilCtrl', $this->getMockBuilder(\ilCtrl::class)->disableOriginalConstructor()->getMock());

		parent::setUp();
	}

	/**
	 * @return PHPUnit_Framework_MockObject_MockObject|\ilLanguage
	 */
	protected function getLanguageMock(): \ilLanguage
	{
		$lng = $this
			->getMockBuilder(\ilLanguage::class)
			->disableOriginalConstructor()
			->setMethods(['txt', 'getInstalledLanguages', 'loadLanguageModule'])
			->getMock();

		return $lng;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setGlobalVariable(string $name, $value)
	{
		global $DIC;

		$GLOBALS[$name] = $value;

		unset($DIC[$name]);
		$DIC[$name] = function ($c) use ($name) {
			return $GLOBALS[$name];
		};
	}

	/**
	 * @param mixed $value
	 * @return \ilTermsOfServiceCriterionConfig
	 */
	protected function getCriterionConfig($value = null): \ilTermsOfServiceCriterionConfig
	{
		if (null === $value) {
			return new \ilTermsOfServiceCriterionConfig();
		}

		return new \ilTermsOfServiceCriterionConfig($value);
	}
}