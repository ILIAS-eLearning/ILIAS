<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function __construct(
        protected ilObjTermsOfService $tos,
        protected ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory,
        protected ilGlobalTemplateInterface $tpl,
        protected ilCtrlInterface $ctrl,
        protected ilLanguage $lng,
        protected ilRbacSystem $rbacsystem,
        protected ilErrorHandling $error,
        protected GlobalHttpState $http,
        protected \ILIAS\Refinery\Factory $refinery,
        protected Factory $uiFactory,
        protected Renderer $uiRenderer,
        protected ilTermsOfServiceTableDataProviderFactory $tableDataProviderFactory
    ) {
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        if (
            (defined('USER_FOLDER_ID') && !$this->rbacsystem->checkAccess('read', USER_FOLDER_ID)) ||
            !$this->rbacsystem->checkAccess('read', $this->tos->getRefId())
        ) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }
        if ($cmd === null || $cmd === '' || !method_exists($this, $cmd)) {
            $cmd = 'showAcceptanceHistory';
        }
        $this->$cmd();
    }

    protected function getAcceptanceHistoryTable(): ilTermsOfServiceAcceptanceHistoryTableGUI
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

    protected function showAcceptanceHistory(): void
    {
        $table = $this->getAcceptanceHistoryTable();

        $table->populate();

        $this->tpl->setContent($table->getHTML());
    }

    protected function applyAcceptanceHistoryFilter(): void
    {
        $table = $this->getAcceptanceHistoryTable();
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->showAcceptanceHistory();
    }

    protected function resetAcceptanceHistoryFilter(): void
    {
        $table = $this->getAcceptanceHistoryTable();
        $table->resetOffset();
        $table->resetFilter();

        $this->showAcceptanceHistory();
    }

    protected function addUserAutoComplete(): void
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
                $this->http->wrapper()->query()->retrieve('term', $this->refinery->kindlyTo()->string())
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
