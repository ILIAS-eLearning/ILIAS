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

namespace ILIAS\components\ResourceStorage\Collections\View;

use ILIAS\UI\Factory;
use ILIAS\components\ResourceStorage\Collections\DataProvider\TableDataProvider;
use ILIAS\components\ResourceStorage\Collections\DataProvider\DataTableDataProviderAdapter;
use ILIAS\HTTP\Services;
use ILIAS\components\ResourceStorage\Resources\UI\RevisionToComponent;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class RequestToDeckOfCards implements RequestToComponents
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

    protected function buildDeck(): \ILIAS\UI\Component\Deck\Deck
    {
        $this->initCardsPerPage();

        $cards = [];
        foreach ($this->data_provider->getIdentifications() as $resource_identification) {
            $revision_to_card = new RevisionToComponent($this->getCurrentRevision($resource_identification));
            $cards[] = $revision_to_card->getAsCard()
                                        ->withActions(
                                            $this->action_builder->buildDropDownForResource($resource_identification)
                                        );
        }

        return $this->ui_factory->deck($cards)->withSmallCardsSize();
    }

    protected function initCardsPerPage(): void
    {
        $cards_per_row = 6;
        $items_per_page = (int) ceil($this->request->getItemsPerPage() / ($cards_per_row ** 1)) * ($cards_per_row ** 1);
        $this->request->setItemsPerPage($items_per_page);
    }

    public function getComponents(): \Generator
    {
        yield from $this->upload_builder->getDropZone();

        yield $this->ui_factory->panel()
                               ->standard($this->request->getTitle(), $this->buildDeck())
                               ->withViewControls([
                                   $this->view_control_builder->getPagination(),
                                   $this->view_control_builder->getSortation()
                               ]);
        // Modals must be rendered after the deck, otherwise there are no modals
        yield from $this->action_builder->getModals();
    }
}
