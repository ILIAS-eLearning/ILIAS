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

use ILIAS\Administration\Setting;
use ILIAS\Dashboard\Access\DashboardAccess;

class ilPDSelectedItemsBlockViewSettings implements ilPDSelectedItemsBlockConstants
{
    /**
     * @var int[]
     */
    protected static array $availableViews = [
        self::VIEW_SELECTED_ITEMS,
        self::VIEW_MY_MEMBERSHIPS,
        self::VIEW_MY_STUDYPROGRAMME
    ];

    /**
     * @var string[]
     */
    protected static array $availableSortOptions = [
        self::SORT_BY_LOCATION,
        self::SORT_BY_TYPE,
        self::SORT_BY_START_DATE
    ];

    /**
     * @var string[]
     */
    protected static array $availablePresentations = [
        self::PRESENTATION_LIST,
        self::PRESENTATION_TILE
    ];

    /**
     * @var <int, string[]>
     */
    protected static array $availableSortOptionsByView = [
        self::VIEW_SELECTED_ITEMS => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE,
            self::SORT_BY_ALPHABET,
        ],
        self::VIEW_MY_MEMBERSHIPS => [
            self::SORT_BY_LOCATION,
            self::SORT_BY_TYPE,
            self::SORT_BY_START_DATE,
            self::SORT_BY_ALPHABET,
        ],
        self::VIEW_MY_STUDYPROGRAMME => []
    ];

    /**
     * @var <int, string[]>
     */
    protected static array $availablePresentationsByView = [
        self::VIEW_SELECTED_ITEMS => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_MY_MEMBERSHIPS => [
            self::PRESENTATION_LIST,
            self::PRESENTATION_TILE
        ],
        self::VIEW_MY_STUDYPROGRAMME => []
    ];

    protected array $validViews = [];
    protected string $currentSortOption = self::SORT_BY_LOCATION;
    protected string $currentPresentationOption = self::PRESENTATION_LIST;
    protected Setting $settings;
    protected DashboardAccess $access;

    public function __construct(
        protected ilObjUser $actor,
        protected int $currentView = self::VIEW_SELECTED_ITEMS,
        Setting $settings = null,
        DashboardAccess $access = null
    ) {
        global $DIC;

        $this->settings = $settings ?? $DIC->settings();
        $this->access = $access ?? new DashboardAccess();
    }

    final public function getMembershipsView(): int
    {
        return self::VIEW_MY_MEMBERSHIPS;
    }

    final public function getSelectedItemsView(): int
    {
        return self::VIEW_SELECTED_ITEMS;
    }

    final public function getStudyProgrammeView(): int
    {
        return self::VIEW_MY_STUDYPROGRAMME;
    }

    final public function getListPresentationMode(): string
    {
        return self::PRESENTATION_LIST;
    }

    final public function getTilePresentationMode(): string
    {
        return self::PRESENTATION_TILE;
    }

    final public function isMembershipsViewActive(): bool
    {
        return $this->currentView === $this->getMembershipsView();
    }

    final public function isSelectedItemsViewActive(): bool
    {
        return $this->currentView === $this->getSelectedItemsView();
    }

    final public function isStudyProgrammeViewActive(): bool
    {
        return $this->currentView === $this->getStudyProgrammeView();
    }

    final public function getSortByStartDateMode(): string
    {
        return self::SORT_BY_START_DATE;
    }

    final public function getSortByLocationMode(): string
    {
        return self::SORT_BY_LOCATION;
    }

    final public function getSortByTypeMode(): string
    {
        return self::SORT_BY_TYPE;
    }

    final public function getSortByAlphabetMode(): string
    {
        return self::SORT_BY_ALPHABET;
    }

    final public function getAvailableSortOptionsByView(int $view): array
    {
        return self::$availableSortOptionsByView[$view];
    }

    final public function getAvailablePresentationsByView(int $view): array
    {
        return self::$availablePresentationsByView[$view];
    }

    public function getDefaultSortingByView(int $view): string
    {
        switch ($view) {
            case $this->getSelectedItemsView():
                return $this->settings->get('selected_items_def_sort', $this->getSortByLocationMode());

            default:
                return $this->settings->get('my_memberships_def_sort', $this->getSortByLocationMode());
        }
    }

    final public function isSortedByType(): bool
    {
        return $this->currentSortOption === $this->getSortByTypeMode();
    }

    final public function isSortedByAlphabet(): bool
    {
        return $this->currentSortOption === $this->getSortByAlphabetMode();
    }

    final public function isSortedByLocation(): bool
    {
        return $this->currentSortOption === $this->getSortByLocationMode();
    }

    final public function isSortedByStartDate(): bool
    {
        return $this->currentSortOption === $this->getSortByStartDateMode();
    }

    final public function isTilePresentation(): bool
    {
        return $this->currentPresentationOption === $this->getTilePresentationMode();
    }

    final public function isListPresentation(): bool
    {
        return $this->currentPresentationOption === $this->getListPresentationMode();
    }

    public function storeViewSorting(int $view, string $type, array $active): void
    {
        if (!in_array($type, $active, true)) {
            $active[] = $type;
        }

        assert(in_array($type, $this->getAvailableSortOptionsByView($view), true));

        switch ($view) {
            case $this->getSelectedItemsView():
                $this->settings->set('selected_items_def_sort', $type);
                break;

            default:
                $this->settings->set('my_memberships_def_sort', $type);
                break;
        }

        $this->settings->set('pd_active_sort_view_' . $view, serialize($active));
    }

    public function getActiveSortingsByView(int $view): array
    {
        $val = $this->settings->get('pd_active_sort_view_' . $view);
        return ($val === '')
            ? []
            : unserialize($val, ['allowed_classes' => false]);
    }

    public function storeViewPresentation(int $view, string $default, array $active): void
    {
        if (!in_array($default, $active, true)) {
            $active[] = $default;
        }
        $this->settings->set('pd_def_pres_view_' . $view, $default);
        $this->settings->set('pd_active_pres_view_' . $view, serialize($active));
    }

    final public function getDefaultPresentationByView(int $view): string
    {
        return $this->settings->get('pd_def_pres_view_' . $view, 'list');
    }

    public function getActivePresentationsByView(int $view): array
    {
        $val = $this->settings->get('pd_active_pres_view_' . $view, '');

        return ('' === $val)
            ? []
            : unserialize($val, ['allowed_classes' => false]);
    }

    final public function enabledMemberships(): bool
    {
        return $this->settings->get('disable_my_memberships', '0') === '0';
    }

    final public function enabledSelectedItems(): bool
    {
        return $this->settings->get('disable_my_offers', '0') === '0';
    }

    final public function enableMemberships(bool $status): void
    {
        $this->settings->set('disable_my_memberships', ($status) ? '0' : '1');
    }

    final public function enableSelectedItems(bool $status): void
    {
        $this->settings->set('disable_my_offers', ($status) ? '0' : '1');
    }

    public function allViewsEnabled(): bool
    {
        return $this->enabledMemberships() && $this->enabledSelectedItems();
    }

    protected function allViewsDisabled(): bool
    {
        return !$this->enabledMemberships() && !$this->enabledSelectedItems();
    }

    public function getDefaultView(): int
    {
        return (int) $this->settings->get('personal_items_default_view', (string) $this->getSelectedItemsView());
    }

    final public function storeDefaultView(int $view): void
    {
        $this->settings->set('personal_items_default_view', (string) $view);
    }

    public function parse(): void
    {
        $this->validViews = self::$availableViews;

        $this->currentSortOption = $this->getEffectiveSortingMode();
        $this->currentPresentationOption = $this->getEffectivePresentationMode();
    }

    public function getEffectivePresentationMode(): string
    {
        $mode = $this->actor->getPref('pd_view_pres_' . $this->currentView);

        if (!in_array($mode, $this->getSelectablePresentationModes(), true)) {
            $mode = $this->getDefaultPresentationByView($this->currentView);
        }

        return $mode;
    }

    public function getEffectiveSortingMode(): string
    {
        $mode = $this->actor->getPref('pd_order_items_' . $this->currentView);

        if (!in_array($mode, $this->getSelectableSortingModes(), true)) {
            $mode = $this->getDefaultSortingByView($this->currentView);
        }

        return $mode;
    }

    /**
     * @return string[]
     */
    public function getSelectableSortingModes(): array
    {
        return array_intersect(
            $this->getActiveSortingsByView($this->currentView),
            $this->getAvailableSortOptionsByView($this->currentView)
        );
    }

    /**
     * @return string[]
     */
    public function getSelectablePresentationModes(): array
    {
        if (!$this->access->canChangePresentation($this->actor->getId())) {
            return [$this->getDefaultSortingByView($this->currentView)];
        }
        return array_intersect(
            $this->getActivePresentationsByView($this->currentView),
            $this->getAvailablePresentationsByView($this->currentView)
        );
    }

    public function storeActorPresentationMode(string $presentationMode): void
    {
        if (in_array($presentationMode, $this->getSelectablePresentationModes())) {
            $this->actor->writePref(
                'pd_view_pres_' . $this->currentView,
                $presentationMode
            );
        }
    }

    public function storeActorSortingMode(string $sortingMode): void
    {
        if (in_array($sortingMode, $this->getSelectableSortingModes())) {
            $this->actor->writePref(
                'pd_order_items_' . $this->currentView,
                $sortingMode
            );
        }
    }

    final public function getActor(): ilObjUser
    {
        return $this->actor;
    }

    final public function getCurrentView(): int
    {
        return $this->currentView;
    }

    public function isValidView(int $view): bool
    {
        return in_array($view, $this->validViews, true);
    }
}
