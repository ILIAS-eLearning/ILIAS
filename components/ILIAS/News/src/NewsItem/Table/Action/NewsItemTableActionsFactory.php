<?php

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

declare(strict_types=1);

namespace ILIAS\News\NewsItem\Table\Action;

use ILIAS\News\Common\Table\TableActionsFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\News\Access\NewsAccess;
use ILIAS\News\Common\HttpService;
use ILIAS\News\Common\Table\TableActions;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ilLanguage;
use ilCtrl;
use ilGlobalTemplateInterface;

class NewsItemTableActionsFactory implements TableActionsFactory
{
    public const string ACTION_EDIT = 'edit';
    public const string ACTION_DELETE = 'delete';

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly NewsAccess $news_access,
        private readonly ilCtrl $ctrl,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private readonly HttpService $http_service,
        private readonly ilGlobalTemplateInterface $main_tpl,
        private readonly array $records
    ) {
    }

    public function getTableActions(): TableActions
    {
        return new TableActions(
            $this->ctrl,
            $this->lng,
            $this->main_tpl,
            $this->ui_factory,
            $this->ui_renderer,
            $this->refinery,
            $this->http_service,
            [
                self::ACTION_EDIT => $this->getEditNewsItemTableAction(),
                self::ACTION_DELETE => $this->getDeleteNewsItemTableAction(),
            ]
        );
    }

    private function getEditNewsItemTableAction(): EditNewsItemTableAction
    {
        return new EditNewsItemTableAction(
            $this->ui_factory,
            $this->lng,
            $this->news_access,
            $this->ctrl,
            $this->http_service
        );
    }

    private function getDeleteNewsItemTableAction(): DeleteNewsItemTableAction
    {
        return new DeleteNewsItemTableAction(
            $this->ui_factory,
            $this->lng,
            $this->ctrl,
            $this->news_access,
            $this->http_service,
            $this->main_tpl,
            $this->records
        );
    }
}
