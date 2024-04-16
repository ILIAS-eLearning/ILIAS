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
    protected Setting $settings;
    protected string $current_sort_option = self::SORT_BY_LOCATION;
    protected string $current_presentation_option = self::PRESENTATION_LIST;

    public function __construct(
        protected readonly ilObjUser $actor,
        protected readonly int $view = self::VIEW_SELECTED_ITEMS,
        Setting $settings = null,
        protected readonly DashboardAccess $access = new DashboardAccess()
    ) {
        global $DIC;
        $this->settings = $settings ?? $DIC->settings();
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

    final public function getLearningSequenceView(): int
    {
        return self::VIEW_LEARNING_SEQUENCES;
    }

    final public function getRecommendedContentView(): int
    {
        return self::VIEW_RECOMMENDED_CONTENT;
    }

    final public function getListPresentationMode(): string
    {
        return self::PRESENTATION_LIST;
    }

    final public function getTilePresentationMode(): string
    {
        return self::PRESENTATION_TILE;
    }

    public function isMembershipsViewActive(): bool
    {
        return $this->view === $this->getMembershipsView();
    }

    final public function isRecommendedContentViewActive(): bool
    {
        return $this->view === self::VIEW_RECOMMENDED_CONTENT;
    }

    public function isSelectedItemsViewActive(): bool
    {
        return $this->view === $this->getSelectedItemsView();
    }

    public function isStudyProgrammeViewActive(): bool
    {
        return $this->view === $this->getStudyProgrammeView();
    }

    final public function isLearningSequenceViewActive(): bool
    {
        return $this->view === self::VIEW_LEARNING_SEQUENCES;
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

    /**
     * @return string[]
     */
    final public function getAvailableSortOptionsByView(int $view): array
    {
        return self::AVAILABLE_SORT_OPTIONS_BY_VIEW[$view] ?? [];
    }

    public function getDefaultSortingByView(int $view): string
    {
        $sorting = $this->settings->get('pd_def_sort_view_' . $view, self::SORT_BY_LOCATION);
        if (!in_array($sorting, $this->getAvailableSortOptionsByView($view), true)) {
            return $this->getAvailableSortOptionsByView($view)[0];
        }
        return $sorting;
    }

    /**
     * @return int[]
     */
    final public function getPresentationViews(): array
    {
        return self::AVAILABLE_VIEWS;
    }

    /**
     * @return string[]
     */
    final public function getAvailablePresentationsByView(int $view): array
    {
        return self::AVAILABLE_PRESENTATION_BY_VIEW[$view];
    }

    public function storeViewSorting(int $view, string $type, array $active): void
    {
        if (!in_array($type, $active, true)) {
            $active[] = $type;
        }

        assert(in_array($type, $this->getAvailableSortOptionsByView($view), true));

        $this->settings->set('pd_def_sort_view_' . $view, $type);
        $this->settings->set('pd_active_sort_view_' . $view, serialize($active));
    }

    /**
     * @return string[]
     */
    public function getActiveSortingsByView(int $view): array
    {
        $val = $this->settings->get('pd_active_sort_view_' . $view);
        if ($val === '' || $val === null) {
            $active_sortings = $this->getAvailableSortOptionsByView($view);
        } else {
            $active_sortings = unserialize($val, ['allowed_classes' => false]);
        }
        return array_filter(
            $active_sortings,
            fn(string $sorting): bool => in_array(
                $sorting,
                $this->getAvailableSortOptionsByView($view),
                true
            )
        );
    }

    /**
     * @param string[] $active
     */
    public function storeViewPresentation(int $view, string $default, array $active): void
    {
        if (!in_array($default, $active, true)) {
            $active[] = $default;
        }
        $this->settings->set('pd_def_pres_view_' . $view, $default);
        $this->settings->set('pd_active_pres_view_' . $view, serialize($active));
    }

    public function getDefaultPresentationByView(int $view): string
    {
        return $this->settings->get('pd_def_pres_view_' . $view, 'list');
    }

    /**
     * @return string[]
     */
    public function getActivePresentationsByView(int $view): array
    {
        $val = $this->settings->get('pd_active_pres_view_' . $view, '');

        return (!$val)
            ? $this->getAvailablePresentationsByView($view)
            : unserialize($val, ['allowed_classes' => false]);
    }

    /**
     * @param int[] $positions
     */
    public function setViewPositions(array $positions): void
    {
        $this->settings->set('pd_view_positions', serialize($positions));
    }

    /**
     * @return int[]
     */
    public function getViewPositions(): array
    {
        $val = $this->settings->get('pd_view_positions', '');
        return (!$val)
            ? self::AVAILABLE_VIEWS
            : unserialize($val, ['allowed_classes' => false]);
    }

    public function isViewEnabled(int $view): bool
    {
        switch ($view) {
            case $this->getMembershipsView():
                return $this->enabledMemberships();
            case $this->getSelectedItemsView():
                return $this->enabledSelectedItems();
            case $this->getStudyProgrammeView():
                return $this->enabledStudyProgrammes();
            case $this->getRecommendedContentView():
                return $this->enabledRecommendedContent();
            case $this->getLearningSequenceView():
                return $this->enabledLearningSequences();
            default:
                return false;
        }
    }

    public function enableView(int $view, bool $status): void
    {
        switch ($view) {
            case $this->getMembershipsView():
                $this->enableMemberships($status);
                break;
            case $this->getSelectedItemsView():
                $this->enableSelectedItems($status);
                break;
            case $this->getStudyProgrammeView():
                $this->enableStudyProgrammes($status);
                break;
            case $this->getRecommendedContentView():
                break;
            case $this->getLearningSequenceView():
                $this->enableLearningSequences($status);
                break;
            default:
                throw new InvalidArgumentException('Unknown view: $view');
        }
    }

    public function enabledMemberships(): bool
    {
        return (int) $this->settings->get('disable_my_memberships', '0') === 0;
    }

    public function enabledSelectedItems(): bool
    {
        return (int) $this->settings->get('disable_my_offers', '0') === 0;
    }

    public function enableMemberships(bool $status): void
    {
        $this->settings->set('disable_my_memberships', $status ? '0' : '1');
    }

    public function enableSelectedItems(bool $status): void
    {
        $this->settings->set('disable_my_offers', $status ? '0' : '1');
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

    public function storeDefaultView(int $view): void
    {
        $this->settings->set('personal_items_default_view', (string) $view);
    }

    public function parse(): void
    {
        $this->current_sort_option = $this->getEffectiveSortingMode();
        $this->current_presentation_option = $this->getEffectivePresentationMode();
    }

    public function getEffectivePresentationMode(): string
    {
        $mode = $this->actor->getPref('pd_view_pres_' . $this->view);

        if (!in_array($mode, $this->getSelectablePresentationModes(), true)) {
            $mode = $this->getDefaultPresentationByView($this->view);
        }

        return $mode;
    }

    public function getEffectiveSortingMode(): string
    {
        $mode = $this->actor->getPref('pd_order_items_' . $this->view);

        if (!in_array($mode, $this->getSelectableSortingModes(), true)) {
            $mode = $this->getDefaultSortingByView($this->view);
        }

        return $mode;
    }

    /**
     * @return string[]
     */
    public function getSelectableSortingModes(): array
    {
        return array_intersect(
            $this->getActiveSortingsByView($this->view),
            $this->getAvailableSortOptionsByView($this->view)
        );
    }

    /**
     * @return string[]
     */
    public function getSelectablePresentationModes(): array
    {
        if (!$this->access->canChangePresentation($this->actor->getId())) {
            return [$this->getDefaultPresentationByView($this->view)];
        }
        return array_intersect(
            $this->getActivePresentationsByView($this->view),
            $this->getAvailablePresentationsByView($this->view)
        );
    }

    public function storeActorPresentationMode(string $presentationMode): void
    {
        if (in_array($presentationMode, $this->getSelectablePresentationModes())) {
            $this->actor->writePref(
                'pd_view_pres_' . $this->view,
                $presentationMode
            );
        }
    }

    public function storeActorSortingMode(string $sortingMode): void
    {
        if (in_array($sortingMode, $this->getSelectableSortingModes())) {
            $this->actor->writePref(
                'pd_order_items_' . $this->view,
                $sortingMode
            );
        }
    }

    final public function getActor(): ilObjUser
    {
        return $this->actor;
    }

    final public function getView(): int
    {
        return $this->view;
    }

    final public function getCurrentSortOption(): string
    {
        return $this->current_sort_option;
    }

    final public function isValidView(int $view): bool
    {
        return in_array($view, self::AVAILABLE_VIEWS, true);
    }

    public function getDefaultSorting(): string
    {
        return $this->settings->get('dash_def_sort', $this->getSortByLocationMode());
    }

    public function isSortedByType(): bool
    {
        return $this->current_sort_option === $this->getSortByTypeMode();
    }

    public function isSortedByAlphabet(): bool
    {
        return $this->current_sort_option === $this->getSortByAlphabetMode();
    }

    public function isSortedByLocation(): bool
    {
        return $this->current_sort_option === $this->getSortByLocationMode();
    }

    public function isSortedByStartDate(): bool
    {
        return $this->current_sort_option === $this->getSortByStartDateMode();
    }

    public function isTilePresentation(): bool
    {
        return $this->current_presentation_option === $this->getTilePresentationMode();
    }

    public function isListPresentation(): bool
    {
        return $this->current_presentation_option === $this->getListPresentationMode();
    }

    final public function enabledRecommendedContent(): bool
    {
        return true;
    }

    public function enabledLearningSequences(): bool
    {
        return (int) $this->settings->get('disable_learning_sequences', '1') === 0;
    }

    public function enabledStudyProgrammes(): bool
    {
        return (int) $this->settings->get('disable_study_programmes', '1') === 0;
    }

    public function enableLearningSequences(bool $status): void
    {
        $this->settings->set('disable_learning_sequences', $status ? '0' : '1');
    }

    public function enableStudyProgrammes(bool $status): void
    {
        $this->settings->set('disable_study_programmes', $status ? '0' : '1');
    }

    final public function getViewName(int $view): string
    {
        return self::VIEW_NAMES[$view];
    }
}
