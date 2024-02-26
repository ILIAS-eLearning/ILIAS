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

use ILIAS\ResourceStorage\Collection\ResourceCollection;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class Request
{
    public const MODE_AS_DATA_TABLE = 1;
    public const MODE_AS_PRESENTATION_TABLE = 2;
    public const MODE_AS_ITEMS = 3;
    public const MODE_AS_DECK = 4;
    public const P_PAGE = 'page';
    public const P_SORTATION = 'sort';
    public const BY_CREATION_DATE_DESC = 'by_creation_date_desc';
    public const BY_CREATION_DATE_ASC = 'by_creation_date_asc';
    public const BY_TITLE_DESC = 'by_title_desc';
    public const BY_TITLE_ASC = 'by_title_asc';
    public const BY_SIZE_DESC = 'by_size_desc';
    public const BY_SIZE_ASC = 'by_size_asc';
    public const P_MODE = 'mode';
    private Mode $mode;
    private int $page;
    private string $sortation;
    private \ILIAS\UI\Factory $ui_factory;
    private array $actions = [];
    private \ilLanguage $language;
    private \ILIAS\Refinery\Factory $refinery;
    private int $items_per_page = 20;

    public function __construct(
        private \ilCtrlInterface $ctrl,
        private \ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $query,
        private Configuration $view_configuration,
    ) {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->refinery = $DIC->refinery();

        $this->mode = $this->determineMode();
        $this->page = $this->determinePage();
        $this->sortation = $this->determineSortation();
        $this->items_per_page = $this->view_configuration->getItemsPerPage();
    }

    public function init(
        \ilResourceCollectionGUI $collection_gui
    ): void {
        $this->ctrl->saveParameter($collection_gui, self::P_SORTATION);
        $this->ctrl->saveParameter($collection_gui, self::P_PAGE);
        $this->ctrl->saveParameter($collection_gui, self::P_MODE);
        $this->ctrl->saveParameter($collection_gui, 'tsort_f');
        $this->ctrl->saveParameter($collection_gui, 'tsort_d');
    }

    public function handleViewTitle(): bool
    {
        return false;
    }

    private function determinePage(): int
    {
        return $this->query->has(self::P_PAGE)
            ? $this->query->retrieve(self::P_PAGE, $this->refinery->kindlyTo()->int())
            : 0;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getItemsPerPage(): int
    {
        return $this->items_per_page;
    }

    public function setItemsPerPage(int $items_per_page): void
    {
        $this->items_per_page = $items_per_page;
    }

    public function getTitle(): ?string
    {
        return $this->view_configuration->getTitle();
    }

    public function getDescription(): ?string
    {
        return $this->view_configuration->getDescription();
    }

    private function determineSortation(): string
    {
        return $this->query->has(self::P_SORTATION)
            ? $this->query->retrieve(self::P_SORTATION, $this->refinery->kindlyTo()->string())
            : self::BY_TITLE_ASC;
    }

    public function setSortation(string $sortation): void
    {
        $this->sortation = $sortation;
    }

    public function getCollection(): ResourceCollection
    {
        return $this->view_configuration->getCollection();
    }

    private function determineMode(): Mode
    {
        return $this->query->has(self::P_MODE)
            ? Mode::from($this->query->retrieve(self::P_MODE, $this->refinery->kindlyTo()->int()))
            : $this->view_configuration->getMode();
    }

    public function getMode(): Mode
    {
        return $this->mode;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getSortation(): string
    {
        return $this->sortation;
    }

    public function canUserUplaod(): bool
    {
        return $this->view_configuration->canUserUpload();
    }

    public function canUserAdministrate(): bool
    {
        return $this->view_configuration->canUserAdministrate();
    }
}
