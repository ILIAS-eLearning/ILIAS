<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilTermsOfServiceAcceptanceHistoryGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryGUI implements \ilTermsOfServiceControllerEnabled
{
	/** @var \ilTermsOfServiceTableDataProviderFactory */
	protected $factory;

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

	/** @var Factory */
	protected $uiFactory;
	
	/** @var Renderer */
	protected $uiRenderer;

	/** @var ServerRequestInterface */
	protected $request;

	/**
	 * ilTermsOfServiceDocumentGUI constructor.
	 * @param \ilObjTermsOfService $tos
	 * @param \ilTemplate $tpl
	 * @param \ilCtrl $ctrl
	 * @param \ilLanguage $lng
	 * @param \ilRbacSystem $rbacsystem
	 * @param \ilErrorHandling $error
	 * @param ServerRequestInterface $request
	 * @param Factory $uiFactory
	 * @param Renderer $uiRenderer
	 * @param \ilTermsOfServiceTableDataProviderFactory $factory
	 */
	public function __construct(
		\ilObjTermsOfService $tos,
		\ilTemplate $tpl,
		\ilCtrl $ctrl,
		\ilLanguage $lng,
		\ilRbacSystem $rbacsystem,
		\ilErrorHandling $error,
		ServerRequestInterface $request,
		Factory $uiFactory,
		Renderer $uiRenderer,
		ilTermsOfServiceTableDataProviderFactory $factory
	) {
		$this->tos = $tos;
		$this->tpl = $tpl;
		$this->ctrl = $ctrl;
		$this->lng = $lng;
		$this->rbacsystem = $rbacsystem;
		$this->error = $error;
		$this->request = $request;
		$this->uiFactory = $uiFactory;
		$this->uiRenderer = $uiRenderer;
		$this->factory = $factory;
	}

	/**
	 *
	 */
	public function executeCommand()
	{
		$nextClass = $this->ctrl->getNextClass($this);
		$cmd       = $this->ctrl->getCmd();

		if (
			!$this->rbacsystem->checkAccess('read', '', $this->tos->getRefId()) ||
			!$this->rbacsystem->checkAccess('read', '', USER_FOLDER_ID)
		) {
			$this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
		}

		switch (strtolower($nextClass)) {
			default:
				if ($cmd == '' || !method_exists($this, $cmd)) {
					$cmd = 'showAcceptanceHistory';
				}
				$this->$cmd();
				break;
		}
	}

	/**
	 * @return ilTermsOfServiceAcceptanceHistoryTableGUI
	 * @throws ilTermsOfServiceMissingDatabaseAdapterException
	 * @throws ilTermsOfServiceMissingLanguageAdapterException
	 */
	protected function getAcceptanceHistoryTable(): \ilTermsOfServiceAcceptanceHistoryTableGUI
	{
		$table = new \ilTermsOfServiceAcceptanceHistoryTableGUI(
			$this,
			'showAcceptanceHistory',
			$this->uiFactory,
			$this->uiRenderer
		);
		$table->setProvider($this->factory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY));

		return $table;
	}

	/**
	 * 
	 */
	protected function showAcceptanceHistory()
	{
		$table = $this->getAcceptanceHistoryTable();

		$table->populate();

		$this->tpl->setContent($table->getHTML());
	}


	/**
	 *
	 */
	protected function applyAcceptanceHistoryFilter()
	{
		$table = $this->getAcceptanceHistoryTable();
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->showAcceptanceHistory();
	}

	/**
	 *
	 */
	protected function resetAcceptanceHistoryFilter()
	{
		$table = $this->getAcceptanceHistoryTable();
		$table->resetOffset();
		$table->resetFilter();

		$this->showAcceptanceHistory();
	}

	/**
	 * Show auto complete results
	 */
	protected function addUserAutoComplete()
	{
		$auto = new \ilUserAutoComplete();
		$auto->setSearchFields(array('login', 'firstname', 'lastname', 'email'));
		$auto->enableFieldSearchableCheck(false);
		$auto->setMoreLinkAvailable(true);

		$isFetchAllRequest = $this->request->getQueryParams()['fetchall'] ?? false;
		if ((bool)$isFetchAllRequest) {
			$auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}

		$query = \ilUtil::stripSlashes($this->request->getQueryParams()['term'] ?? '');
		echo $auto->getList($query);
		exit();
	}
}