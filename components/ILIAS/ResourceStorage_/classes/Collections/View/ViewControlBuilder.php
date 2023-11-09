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

namespace ILIAS\components\ResourceStorage_\Collections\View;

use ILIAS\UI\Factory;
use ILIAS\components\ResourceStorage_\Collections\DataProvider\TableDataProvider;
use ILIAS\UI\Component\ViewControl\Pagination;
use ILIAS\UI\Component\ViewControl\Sortation;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ViewControlBuilder
{
    public function __construct(
        private Request $request,
        private TableDataProvider $data_provider,
        private \ilCtrlInterface $ctrl,
        private Factory $ui_factory,
        private \ilLanguage $language,
    ) {
    }

    public function getPagination(): Pagination
    {
        return $this->ui_factory->viewControl()
                                ->pagination()
                                ->withPageSize($this->request->getItemsPerPage())
                                ->withCurrentPage($this->request->getPage())
                                ->withTotalEntries($this->data_provider->getTotal())
                                ->withTargetURL(
                                    $this->ctrl->getLinkTargetByClass(
                                        \ilResourceCollectionGUI::class,
                                        \ilResourceCollectionGUI::CMD_INDEX
                                    ),
                                    Request::P_PAGE
                                );
    }

    public function getSortation(): Sortation
    {
        return $this->ui_factory->viewControl()->sortation([
            Request::BY_TITLE_ASC => $this->language->txt(Request::BY_TITLE_ASC),
            Request::BY_TITLE_DESC => $this->language->txt(Request::BY_TITLE_DESC),
            Request::BY_CREATION_DATE_ASC => $this->language->txt(Request::BY_CREATION_DATE_ASC),
            Request::BY_CREATION_DATE_DESC => $this->language->txt(Request::BY_CREATION_DATE_DESC),
            Request::BY_SIZE_ASC => $this->language->txt(Request::BY_SIZE_ASC),
            Request::BY_SIZE_DESC => $this->language->txt(Request::BY_SIZE_DESC),
        ])->withTargetURL(
            $this->ctrl->getLinkTargetByClass(
                \ilResourceCollectionGUI::class,
                \ilResourceCollectionGUI::CMD_INDEX
            ),
            Request::P_SORTATION
        );
    }
}
