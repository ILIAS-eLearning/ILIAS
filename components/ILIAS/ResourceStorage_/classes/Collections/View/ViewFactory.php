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

namespace ILIAS\Services\ResourceStorage\Collections\View;

use ILIAS\UI\Factory;
use ILIAS\Services\ResourceStorage\Collections\DataProvider\TableDataProvider;
use ILIAS\Services\ResourceStorage\Collections\DataProvider\DataTableDataProviderAdapter;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ViewFactory
{
    private Factory $ui_factory;
    private \ilLanguage $language;
    private \ILIAS\HTTP\Services $http;

    public function __construct(
        private TableDataProvider $data_provider,
        private ActionBuilder $action_builder,
        private ViewControlBuilder $view_control_builder,
        private UploadBuilder $upload_builder
    ) {
        global $DIC;
        $this->http = $DIC->http();
        $this->ui_factory = $DIC->ui()->factory();
        $this->language = $DIC->language();
    }

    public function getComponentProvider(
        Request $request
    ): RequestToComponents {
        switch ($request->getMode()) {
            case Mode::DATA_TABLE:
                return new RequestToDataTable(
                    $request,
                    $this->ui_factory,
                    $this->language,
                    $this->http,
                    $this->data_provider,
                    $this->action_builder,
                    $this->view_control_builder,
                    $this->upload_builder
                );
            case Mode::DECK:
                return new RequestToDeckOfCards(
                    $request,
                    $this->ui_factory,
                    $this->language,
                    $this->http,
                    $this->data_provider,
                    $this->action_builder,
                    $this->view_control_builder,
                    $this->upload_builder
                );
            case Mode::ITEMS:
                return new RequestToItems(
                    $request,
                    $this->ui_factory,
                    $this->language,
                    $this->http,
                    $this->data_provider,
                    $this->action_builder,
                    $this->view_control_builder,
                    $this->upload_builder
                );
            case Mode::PRESENTATION_TABLE:
                return new RequestToPresentationTable(
                    $request,
                    $this->ui_factory,
                    $this->language,
                    $this->http,
                    $this->data_provider,
                    $this->action_builder,
                    $this->view_control_builder,
                    $this->upload_builder
                );
            default:
                throw new \InvalidArgumentException('Unknown mode');
        }
    }
}
