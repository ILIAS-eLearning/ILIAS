<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilTermsOfServiceAcceptanceHistoryGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryGUI implements ilTermsOfServiceControllerEnabled
{
    /** @var ilTermsOfServiceTableDataProviderFactory */
    protected $tableDataProviderFactory;

    /** @var ilObjTermsOfService */
    protected $tos;

    /** @var ilGlobalTemplate */
    protected $tpl;

    /** @var ilCtrl */
    protected $ctrl;

    /** @var ilLanguage */
    protected $lng;

    /** @var ilRbacSystem */
    protected $rbacsystem;

    /** @var ilErrorHandling */
    protected $error;

    /** @var Factory */
    protected $uiFactory;

    /** @var Renderer */
    protected $uiRenderer;

    /** @var ServerRequestInterface */
    protected $request;

    /** @var ilTermsOfServiceCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /**
     * ilTermsOfServiceDocumentGUI constructor.
     * @param ilObjTermsOfService                           $tos
     * @param ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory
     * @param ilGlobalTemplate                              $tpl
     * @param ilCtrl                                        $ctrl
     * @param ilLanguage                                    $lng
     * @param ilRbacSystem                                  $rbacsystem
     * @param ilErrorHandling                               $error
     * @param ServerRequestInterface                        $request
     * @param Factory                                       $uiFactory
     * @param Renderer                                      $uiRenderer
     * @param ilTermsOfServiceTableDataProviderFactory      $tableDataProviderFactory
     */
    public function __construct(
        ilObjTermsOfService $tos,
        ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        ilGlobalTemplate $tpl,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilRbacSystem $rbacsystem,
        ilErrorHandling $error,
        ServerRequestInterface $request,
        Factory $uiFactory,
        Renderer $uiRenderer,
        ilTermsOfServiceTableDataProviderFactory $tableDataProviderFactory
    ) {
        $this->tos                      = $tos;
        $this->criterionTypeFactory     = $criterionTypeFactory;
        $this->tpl                      = $tpl;
        $this->ctrl                     = $ctrl;
        $this->lng                      = $lng;
        $this->rbacsystem               = $rbacsystem;
        $this->error                    = $error;
        $this->request                  = $request;
        $this->uiFactory                = $uiFactory;
        $this->uiRenderer               = $uiRenderer;
        $this->tableDataProviderFactory = $tableDataProviderFactory;
    }

    /**
     *
     */
    public function executeCommand() : void
    {
        $nextClass = $this->ctrl->getNextClass($this);
        $cmd       = $this->ctrl->getCmd();

        if (
            !$this->rbacsystem->checkAccess('read', $this->tos->getRefId()) ||
            !$this->rbacsystem->checkAccess('read', USER_FOLDER_ID)
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
     */
    protected function getAcceptanceHistoryTable() : ilTermsOfServiceAcceptanceHistoryTableGUI
    {
        $table = new ilTermsOfServiceAcceptanceHistoryTableGUI(
            $this,
            'showAcceptanceHistory',
            $this->criterionTypeFactory,
            $this->uiFactory,
            $this->uiRenderer,
            $this->tpl
        );
        $table->setProvider($this->tableDataProviderFactory->getByContext(ilTermsOfServiceTableDataProviderFactory::CONTEXT_ACCEPTANCE_HISTORY));

        return $table;
    }

    /**
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    protected function showAcceptanceHistory() : void
    {
        $table = $this->getAcceptanceHistoryTable();

        $table->populate();

        $this->tpl->setContent($table->getHTML());
    }

    /**
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    protected function applyAcceptanceHistoryFilter() : void
    {
        $table = $this->getAcceptanceHistoryTable();
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showAcceptanceHistory();
    }

    /**
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    protected function resetAcceptanceHistoryFilter() : void
    {
        $table = $this->getAcceptanceHistoryTable();
        $table->resetOffset();
        $table->resetFilter();

        $this->showAcceptanceHistory();
    }

    /**
     * Show auto complete results
     */
    protected function addUserAutoComplete() : void
    {
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(['login', 'firstname', 'lastname', 'email']);
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        $isFetchAllRequest = $this->request->getQueryParams()['fetchall'] ?? false;
        if ((bool) $isFetchAllRequest) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        $query = ilUtil::stripSlashes($this->request->getQueryParams()['term'] ?? '');
        echo $auto->getList($query);
        exit();
    }
}