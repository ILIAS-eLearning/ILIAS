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
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\Services\ResourceStorage\Resources\UI\Actions\ActionGenerator;
use ILIAS\UI\Component\Card\Card;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Table\PresentationRow;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RevisionToComponent extends BaseToComponent implements ToComponent
{
    private \ILIAS\ResourceStorage\Information\Information $information;

    public function __construct(
        private Revision $revision,
        ?ActionGenerator $action_generator = null
    ) {
        parent::__construct($action_generator);
        $this->information = $this->revision->getInformation();
    }

    public function getAsItem(): \ILIAS\UI\Component\Item\Standard
    {
        $properties = array_merge(
            $this->getCommonProperties(),
            $this->getDetailedProperties()
        );
        return $this->ui_factory->item()->standard($this->revision->getTitle())
            ->withDescription($this->information->getTitle())
            ->withProperties($properties);
    }

    public function getAsCard(): Card
    {
        return $this->ui_factory->card()->repositoryObject(
            $this->information->getTitle(),
            $this->getImage()
        )->withSections([$this->ui_factory->listing()->descriptive($this->getCommonProperties())]);
    }

    public function getAsRowMapping(): \Closure
    {
        return function (
            PresentationRow $row,
            ResourceIdentification $resource_identification
        ): PresentationRow {
            $actions = $this->action_generator->getActionsForRevision($this->revision);
            if ($actions !== []) {
                $row = $row->withAction(
                    $this->ui_factory->dropdown()->standard(
                        $actions
                    )
                );
            }

            return $row
                ->withHeadline($this->information->getTitle())
                ->withSubheadline($this->revision->getTitle())
                ->withImportantFields($this->getImportantProperties())
                ->withContent(
                    $this->ui_factory->listing()->descriptive($this->getCommonProperties())
                )
                ->withFurtherFields(
                    $this->getDetailedProperties()
                );
        };
    }


    private function getImage(): Image
    {
        // We could use Flavours in the Future
        return $this->ui_factory->image()->standard(
            "./templates/default/images/icon_file.svg",
            $this->information->getTitle()
        );
    }

    public function getImportantProperties(): array
    {
        return [
            $this->formatDate($this->information->getCreationDate()),
            $this->formatSize($this->information->getSize()),
        ];
    }

    public function getCommonProperties(): array
    {
        return [
            $this->language->txt('file_size') => $this->formatSize($this->information->getSize()),
            $this->language->txt('type') => $this->information->getMimeType(),

        ];
    }

    public function getDetailedProperties(): array
    {
        return [
            $this->language->txt('create_date') => $this->formatDate($this->information->getCreationDate()),
        ];
    }
}
