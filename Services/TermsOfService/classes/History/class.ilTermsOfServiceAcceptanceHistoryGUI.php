<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilTermsOfServiceAcceptanceHistoryGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryGUI implements ilTermsOfServiceControllerEnabled
{
    protected ilTermsOfServiceTableDataProviderFactory $tableDataProviderFactory;
    protected ilObjTermsOfService $tos;
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilRbacSystem $rbacsystem;
    protected ilErrorHandling $error;
    protected Factory $uiFactory;
    protected Renderer $uiRenderer;
    protected GlobalHttpState $http;
    protected \ILIAS\Refinery\Factory $refinery;
    protected ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory;

    public function __construct(
        ilObjTermsOfService $tos,
        ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        ilGlobalTemplateInterface $tpl,
        ilCtrlInterface $ctrl,
        ilLanguage $lng,
        ilRbacSystem $rbacsystem,
        ilErrorHandling $error,
        GlobalHttpState $http,
        \ILIAS\Refinery\Factory $refinery,
        Factory $uiFactory,
        Renderer $uiRenderer,
        ilTermsOfServiceTableDataProviderFactory $tableDataProviderFactory
    ) {
        $this->tos = $tos;
        $this->criterionTypeFactory = $criterionTypeFactory;
        $this->tpl = $tpl;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->rbacsystem = $rbacsystem;
        $this->error = $error;
        $this->http = $http;
        $this->refinery = $refinery;
        $this->uiFactory = $uiFactory;
        $this->uiRenderer = $uiRenderer;
        $this->tableDataProviderFactory = $tableDataProviderFactory;
    }

    public function executeCommand() : void
    {
        $nextClass = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        if (
            (defined('USER_FOLDER_ID') && !$this->rbacsystem->checkAccess('read', USER_FOLDER_ID)) ||
            !$this->rbacsystem->checkAccess('read', $this->tos->getRefId())
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
    
        // PHP8-Review: 'switch' with single 'case'
        switch (strtolower($nextClass)) {
            default:
                if ($cmd === '' || !method_exists($this, $cmd)) {
                    $cmd = 'showAcceptanceHistory';
                }
                $this->$cmd();
                break;
        }
    }

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

    protected function showAcceptanceHistory() : void
    {
        $table = $this->getAcceptanceHistoryTable();

        $table->populate();

        $this->tpl->setContent($table->getHTML());
    }

    protected function applyAcceptanceHistoryFilter() : void
    {
        $table = $this->getAcceptanceHistoryTable();
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showAcceptanceHistory();
    }

    protected function resetAcceptanceHistoryFilter() : void
    {
        $table = $this->getAcceptanceHistoryTable();
        $table->resetOffset();
        $table->resetFilter();

        $this->showAcceptanceHistory();
    }

    protected function addUserAutoComplete() : void
    {
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(['login', 'firstname', 'lastname', 'email']);
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        if ($this->http->wrapper()->query()->has('fetchall')) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        if ($this->http->wrapper()->query()->has('term')) {
            $query = ilUtil::stripSlashes(
                $this->http->wrapper()->query()->retrieve('term', $this->refinery->kindlyTo()->string())  // PHP8-Review: Parameter #1 $a_str of static method ilUtil::stripSlashes() expects string, mixed given
            );
            $this->http->saveResponse(
                $this->http->response()
                    ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
                    ->withBody(
                        Streams::ofString($auto->getList($query))
                    )
            );
        }

        $this->http->sendResponse();
        $this->http->close();
    }
}
