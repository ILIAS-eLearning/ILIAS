<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilTermsOfServiceDocumentGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentGUITest extends \ilTermsOfServiceBaseTest
{
	/** @var \ilTermsOfServiceTableDataProviderFactory */
	protected $tableDataProviderFactory;

	/** @var \ilObjTermsOfService */
	protected $tos;

	/** @var \ilTemplate */
	protected $tpl;

	/** @var \ilCtrl */
	protected $ctrl;

	/** @var \ilLanguage */
	protected $lng;

	/** @var \ilRbacSystem */
	protected $rbacsystem;

	/** @var \ilErrorHandling */
	protected $error;

	/** @var \ilObjUser */
	protected $user;

	/** @var \ilLogger */
	protected $log;

	/** @var Factory */
	protected $uiFactory;

	/** @varRenderer */
	protected $uiRenderer;

	/** @var ILIAS\HTTP\GlobalHttpState */
	protected $httpState;

	/** @var \ilToolbarGUI */
	protected $toolbar;

	/** @var FileUpload */
	protected $fileUpload;

	/** @var Filesystems */
	protected $fileSystems;

	/** @var \ilTermsOfServiceCriterionTypeFactoryInterface */
	protected $criterionTypeFactory;

	/**
	 *
	 */
	public function setUp()
	{
		parent::setUp();

		$this->tos                      = $this->getMockBuilder(\ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();
		$this->criterionTypeFactory     = $this->getMockBuilder(\ilTermsOfServiceCriterionTypeFactoryInterface::class)->disableOriginalConstructor()->getMock();
		$this->tpl                      = $this->getMockBuilder(\ilTemplate::class)->disableOriginalConstructor()->getMock();
		$this->ctrl                     = $this->getMockBuilder(\ilCtrl::class)->disableOriginalConstructor()->getMock();
		$this->lng                      = $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock();
		$this->rbacsystem               = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
		$this->error                    = $this->getMockBuilder(\ilErrorHandling::class)->disableOriginalConstructor()->getMock();
		$this->user                     = $this->getMockBuilder(\ilObjUser::class)->disableOriginalConstructor()->getMock();
		$this->log                      = $this->getMockBuilder(\ilLogger::class)->disableOriginalConstructor()->getMock();
		$this->toolbar                  = $this->getMockBuilder(\ilToolbarGUI::class)->disableOriginalConstructor()->getMock();
		$this->httpState                = $this->getMockBuilder(GlobalHttpState::class)->disableOriginalConstructor()->getMock();
		$this->uiFactory                = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
		$this->uiRenderer               = $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock();
		$this->fileSystems              = $this->getMockBuilder(Filesystems::class)->disableOriginalConstructor()->getMock();
		$this->fileUpload               = $this->getMockBuilder(FileUpload::class)->disableOriginalConstructor()->getMock();
		$this->tableDataProviderFactory = $this->getMockBuilder(\ilTermsOfServiceTableDataProviderFactory::class)->disableOriginalConstructor()->getMock();
	}

	/**
	 *
	 */
	public function testWritePermissionIsCheckedOnWriteOperations()
	{
		$gui = new \ilTermsOfServiceDocumentGUI(
			$this->tos, $this->criterionTypeFactory, $this->tpl,
			$this->user, $this->ctrl, $this->lng,
			$this->rbacsystem, $this->error, $this->log,
			$this->toolbar, $this->httpState, $this->uiFactory,
			$this->uiRenderer, $this->fileSystems, $this->fileUpload,
			$this->tableDataProviderFactory
		);
	}
}