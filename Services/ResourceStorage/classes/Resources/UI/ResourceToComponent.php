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

namespace ILIAS\Services\ResourceStorage\Resources\UI;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Services\ResourceStorage\Resources\UI\Actions\ActionGenerator;
use ILIAS\UI\Component\Card\Card;
use ILIAS\UI\Component\Table\PresentationRow;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ResourceToComponent extends BaseToComponent implements ToComponent
{
    private \ILIAS\ResourceStorage\Revision\Revision $current_revision;
    private RevisionToComponent $revision_to_component;

    public function __construct(
        protected StorableResource $resource,
        ?ActionGenerator $action_generator = null
    ) {
        parent::__construct($action_generator);
        $this->current_revision = $this->resource->getCurrentRevision();
        $this->revision_to_component = new RevisionToComponent(
            $this->current_revision,
            $action_generator
        );
    }

    public function getAsRowMapping(): \Closure
    {
        return function (
            PresentationRow $row,
            ResourceIdentification $resource_identification
        ): PresentationRow {
            /** @var PresentationRow $row */
            $row = $this->revision_to_component->getAsRowMapping()($row, $resource_identification);
            return $row
                ->withImportantFields($this->getImportantProperties())
                ->withContent(
                    $this->ui_factory->listing()->descriptive($this->getCommonProperties())
                )
                ->withFurtherFields(
                    $this->getDetailedProperties()
                );
        };
    }


    public function getAsItem(): \ILIAS\UI\Component\Item\Standard
    {
        $properties = array_merge(
            $this->getCommonProperties(),
            $this->getDetailedProperties()
        );
        return $this->revision_to_component->getAsItem()
            ->withProperties($properties);
    }

    public function getAsCard(): Card
    {
        return $this->revision_to_component->getAsCard();
    }

    public function getImportantProperties(): array
    {
        return array_merge(
            [],
            $this->revision_to_component->getImportantProperties()
        );
    }

    public function getCommonProperties(): array
    {
        $stakeholders = implode(
            ', ',
            array_map(function (ResourceStakeholder $stakeholder): string {
                return $stakeholder->getConsumerNameForPresentation();
            }, $this->resource->getStakeholders())
        );


        return array_merge(
            [
                $this->language->txt('stakeholders') => $stakeholders,
                $this->language->txt('full_size') => $this->formatSize($this->resource->getFullSize()),
            ],
            $this->revision_to_component->getCommonProperties()
        );
    }

    public function getDetailedProperties(): array
    {
        return array_merge(
            [
                $this->language->txt('revisions') => (string)count($this->resource->getAllRevisions()),
            ],
            $this->revision_to_component->getImportantProperties()
        );
    }
}
