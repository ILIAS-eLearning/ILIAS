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

namespace ILIAS\components\ResourceStorage\Container\View;

use ILIAS\UI\Factory;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\components\ResourceStorage\URLSerializer;
use ILIAS\components\ResourceStorage\Container\Wrapper\ContainerWrapper;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class Request
{
    use URLSerializer;

    /**
     * @var int
     */
    public const MODE_AS_DATA_TABLE = 1;
    /**
     * @var int
     */
    public const MODE_AS_PRESENTATION_TABLE = 2;
    /**
     * @var int
     */
    public const MODE_AS_ITEMS = 3;
    /**
     * @var int
     */
    public const MODE_AS_DECK = 4;
    /**
     * @var string
     */
    public const P_PAGE = 'page';
    /**
     * @var string
     */
    public const P_SORTATION = 'sort';
    /**
     * @var string
     */
    public const BY_CREATION_DATE_DESC = 'by_creation_date_desc';
    /**
     * @var string
     */
    public const BY_CREATION_DATE_ASC = 'by_creation_date_asc';
    /**
     * @var string
     */
    public const BY_TITLE_DESC = 'by_title_desc';
    /**
     * @var string
     */
    public const BY_TITLE_ASC = 'by_title_asc';
    /**
     * @var string
     */
    public const BY_SIZE_DESC = 'by_size_desc';
    /**
     * @var string
     */
    public const BY_SIZE_ASC = 'by_size_asc';
    /**
     * @var string
     */
    public const BY_TYPE_DESC = 'by_type_desc';
    /**
     * @var string
     */
    public const BY_TYPE_ASC = 'by_type_asc';
    /**
     * @var string
     */
    public const P_MODE = 'mode';
    /**
     * @var string
     */
    public const P_PATH = 'path';
    /**
     * @var string
     */
    private const BASE = './';
    private Mode $mode;
    private int $page;
    private string $sortation;
    private Factory $ui_factory;
    private array $actions = [];
    private \ilLanguage $language;
    private \ILIAS\Refinery\Factory $refinery;
    private int $items_per_page = 20;
    private string $path = self::BASE;
    private ContainerWrapper $wrapper;

    public function __construct(
        private \ilCtrlInterface $ctrl,
        private ArrayBasedRequestWrapper $query,
        private Configuration $view_configuration,
    ) {
        global $DIC;
        $irss = $DIC->resourceStorage();
        $this->ctrl = $DIC->ctrl();
        $this->refinery = $DIC->refinery();

        $this->mode = $this->determineMode();
        $this->page = $this->determinePage();
        $this->path = $this->determinePath();
        $this->sortation = $this->determineSortation();
        $this->items_per_page = $this->view_configuration->getItemsPerPage();

        $this->wrapper = new ContainerWrapper(
            $view_configuration->getContainer()->getIdentification(),
            $this->path
        );
    }

    public function init(
        \ilContainerResourceGUI $container_resource_gui
    ): void {
        $this->ctrl->saveParameter($container_resource_gui, self::P_SORTATION);
        $this->ctrl->saveParameter($container_resource_gui, self::P_PAGE);
        $this->ctrl->saveParameter($container_resource_gui, self::P_MODE);
        $this->ctrl->saveParameter($container_resource_gui, self::P_PATH);
    }

    public function buildURI(string $cmd): void
    {
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

    private function determinePath(): string
    {
        return $this->query->has(self::P_PATH)
            ? $this->unhash($this->query->retrieve(self::P_PATH, $this->refinery->kindlyTo()->string()))
            : self::BASE;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getItemsPerPage(): int
    {
        return $this->items_per_page;
    }

    public function getPath(): string
    {
        return $this->path;
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

    public function getWrapper(): ContainerWrapper
    {
        return $this->wrapper;
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
