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
	/** @var PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceTableDataProviderFactory */
	protected $tableDataProviderFactory;

	/** @var PHPUnit_Framework_MockObject_MockObject|\ilObjTermsOfService */
	protected $tos;

	/** @var PHPUnit_Framework_MockObject_MockObject|\ilTemplate */
	protected $tpl;

	/** @var PHPUnit_Framework_MockObject_MockObject|\ilCtrl */
	protected $ctrl;

	/** @var PHPUnit_Framework_MockObject_MockObject|\ilLanguage */
	protected $lng;

	/** @var PHPUnit_Framework_MockObject_MockObject|\ilRbacSystem */
	protected $rbacsystem;

	/** @var PHPUnit_Framework_MockObject_MockObject|\ilErrorHandling */
	protected $error;

	/** @var PHPUnit_Framework_MockObject_MockObject|\ilObjUser */
	protected $user;

	/** @var PHPUnit_Framework_MockObject_MockObject|\ilLogger */
	protected $log;

	/** @var PHPUnit_Framework_MockObject_MockObject|Factory */
	protected $uiFactory;

	/** @var PHPUnit_Framework_MockObject_MockObject|Renderer */
	protected $uiRenderer;

	/** @var PHPUnit_Framework_MockObject_MockObject|ILIAS\HTTP\GlobalHttpState */
	protected $httpState;

	/** @var PHPUnit_Framework_MockObject_MockObject|\ilToolbarGUI */
	protected $toolbar;

	/** @var PHPUnit_Framework_MockObject_MockObject|FileUpload */
	protected $fileUpload;

	/** @var PHPUnit_Framework_MockObject_MockObject|Filesystems */
	protected $fileSystems;

	/** @var PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceCriterionTypeFactoryInterface */
	protected $criterionTypeFactory;

	/**
	 *
	 */
	public function setUp()
	{
		parent::setUp();

		$this->tos                      = $this->getMockBuilder(\ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();
		$this->criterionTypeFactory     = $this->getMockBuilder(\ilTermsOfServiceCriterionTypeFactoryInterface::class)->disableOriginalConstructor()->getMock();
		$this->tpl                      = $this->getMockBuilder(\ilTemplate::class)->disableOriginalConstructor()->setMethods(['g'])->getMock();
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
	public function testAccessDeniedErrorIsRaisedWhenPermissionsAreMissing()
	{
		$this->tos
			->expects($this->any())
			->method('getRefId')
			->willReturn(4711);

		$this->ctrl
			->expects($this->any())
			->method('getCmd')
			->willReturnOnConsecutiveCalls(
				'default_____read',
				'confirmReset', 'reset',
				'saveAddDocumentForm', 'showAddDocumentForm',
				'saveEditDocumentForm', 'showEditDocumentForm',
				'deleteDocuments', 'saveDocumentSorting',
				'showAttachCriterionForm', 'saveAttachCriterionForm',
				'showChangeCriterionForm', 'saveChangeCriterionForm',
				'detachCriterionAssignment'
			);

		$this->rbacsystem
			->expects($this->exactly(27))
			->method('checkAccess')
			->willReturnOnConsecutiveCalls(
				false,
				true, false,
				true, false,
				true, false,
				true, false,
				true, false,
				true, false,
				true, false,
				true, false,
				true, false,
				true, false,
				true, false,
				true, false,
				true, false
			);

		$gui = new \ilTermsOfServiceDocumentGUI(
			$this->tos, $this->criterionTypeFactory, $this->tpl,
			$this->user, $this->ctrl, $this->lng,
			$this->rbacsystem, $this->error, $this->log,
			$this->toolbar, $this->httpState, $this->uiFactory,
			$this->uiRenderer, $this->fileSystems, $this->fileUpload,
			$this->tableDataProviderFactory
		);

		$this->error
			->expects($this->any())
			->method('raiseError')
			->willThrowException(new \ilException('no_permission'));

		for ($i = 0; $i < 14; $i++) {
			try {
				$gui->executeCommand();
			} catch (\ilException $e) {
				$this->assertEquals(
					'no_permission',
					'no_permission',
					'Failed asserting exception is raised when permissions are missing'
				);
			}
		}
	}
}