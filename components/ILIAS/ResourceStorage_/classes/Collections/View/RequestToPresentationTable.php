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
use ILIAS\components\ResourceStorage_\Collections\DataProvider\DataTableDataProviderAdapter;
use ILIAS\HTTP\Services;
use ILIAS\components\ResourceStorage_\Resources\UI\RevisionToComponent;
use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RequestToPresentationTable implements RequestToComponents
{
    use RIDHelper;

    private \ILIAS\ResourceStorage\Services $irss;

    public function __construct(
        private Request $request,
        private Factory $ui_factory,
        private \ilLanguage $language,
        private Services $http,
        private TableDataProvider $data_provider,
        private ActionBuilder $action_builder,
        private ViewControlBuilder $view_control_builder,
        private UploadBuilder $upload_builder
    ) {
        global $DIC;
        $this->irss = $DIC->resourceStorage();
    }

    private function buildTable(): \ILIAS\UI\Component\Table\Presentation
    {
        return $this->ui_factory->table()->presentation(
            '',
            [
                $this->view_control_builder->getPagination(),
                $this->view_control_builder->getSortation()
            ],
            function (PresentationRow $p, ResourceIdentification $rid) {
                $revision_to_component = new RevisionToComponent($this->getCurrentRevision($rid));
                $mapping = $revision_to_component->getAsRowMapping();
                return $mapping($p, $rid);
            }
        )->withData($this->data_provider->getIdentifications());
    }

    public function getComponents(): \Generator
    {
        yield from $this->upload_builder->getDropZone();
        yield $this->buildTable();
        // Modals must be rendered after the presentation table, otherwise there are no modals
        yield from $this->action_builder->getModals();
    }
}
