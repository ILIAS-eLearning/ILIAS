<?php

declare(strict_types=1);

use ILIAS\UI\Component\MessageBox\MessageBox;

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
abstract class ilDashboardBlockGUI extends ilBlockGUI
{
    private string $content;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\UI\Factory $factory;
    protected ILIAS\UI\Renderer $renderer;
    protected ilPDSelectedItemsBlockViewSettings $viewSettings;
    protected ilPDSelectedItemsBlockViewGUI $blockView;
    /** @var array<string, array>  */
    protected array $data;

    public function __construct()
    {
        parent::__construct();
        global $DIC;

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();

        $this->new_rendering = true;
        $this->initViewSettings();
        $this->viewSettings->parse();
        $this->blockView = ilPDSelectedItemsBlockViewGUI::bySettings($this->viewSettings);
        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
        if ($this->viewSettings->isTilePresentation()) {
            $this->setPresentation(self::PRES_MAIN_LEG);
        } else {
            $this->setPresentation(self::PRES_MAIN_LIST);
        }

        $this->initData();
    }

    abstract public function initViewSettings(): void;

    abstract public function initData(): void;

    abstract public function emptyHandling(): string;

    abstract public function getCardForData(array $data): ?\ILIAS\UI\Component\Card\RepositoryObject;

    abstract public function getItemForData(array $data): ?\ILIAS\UI\Component\Item\Item;

    protected function getListItemGroups(): array
    {
        $data = $this->loadData();
        $groupedCards = [];
        foreach ($data as $title => $group) {
            $items = [];
            foreach ($group as $datum) {
                $item = $this->getListItemForData($datum);
                if ($item !== null) {
                    $items[] = $item;
                }
            }
            $groupedCards[] = $this->factory->item()->group((string) $title, $items);
        }


        return $groupedCards;
    }


    protected function getListItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        return $this->getItemForData($data);
    }

    protected function isRepositoryObject(): bool
    {
        return false;
    }

    protected function getLegacyContent(): string
    {
        $groupedCards = [];
        foreach ($this->loadData() as $title => $group) {
            $cards = [];
            foreach ($group as $datum) {
                $cards[] = $this->getCardForData($datum);
            }
            if ($cards) {
                $groupedCards[] = $this->ui->factory()->panel()->sub(
                    $title,
                    $this->factory->deck($cards)->withNormalCardsSize()
                );
            }
        }

        if ($groupedCards) {
            return $this->renderer->render($groupedCards);
        }

        return $this->getNoItemFoundContent();
    }

    public function getNoItemFoundContent(): string
    {
        return $this->emptyHandling();
    }

    public function getViewSettings(): ilPDSelectedItemsBlockViewSettings
    {
        return $this->viewSettings;
    }

    public function getBlockType(): string
    {
        return 'pditems';
    }

    protected function initAndShow(): void
    {
        $this->initViewSettings();
        $this->viewSettings->parse();

        if ($this->viewSettings->isTilePresentation()) {
            $this->setPresentation(self::PRES_MAIN_LEG);
        } else {
            $this->setPresentation(self::PRES_MAIN_LIST);
        }

        if ($this->ctrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        }

        $this->returnToContext();
    }

    public function getHTML(): string
    {
        if (!$this->data) {
            return $this->emptyHandling();
        }

        $this->setTitle(
            $this->lng->txt('dash_' . $this->viewSettings->getViewName($this->viewSettings->getCurrentView()))
        );
        $this->addCommandActions();

        // sort
        $data = $this->getData();
        switch ($this->viewSettings->getEffectiveSortingMode()) {
            case ilPDSelectedItemsBlockConstants::SORT_BY_ALPHABET:
                usort($data, static function ($a, $b) {
                    return strcmp($a['title'], $b['title']);
                });
                break;
            case ilPDSelectedItemsBlockConstants::SORT_BY_START_DATE:
                usort($data, static function ($a, $b) {
                    return $a['lso_obj']->getCreateDate() <=> $b['lso_obj']->getCreateDate();
                });
                break;
        }
        $this->setData($data);

        return parent::getHTML();
    }

    public function addCommandActions(): void
    {
        $sortings = $this->viewSettings->getActiveSortingsByView($this->viewSettings->getCurrentView());
        foreach ($sortings as $sorting) {
            $this->ctrl->setParameter($this, 'sorting', $sorting);
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, 'changePDItemSorting'),
                $this->lng->txt('dash_sort_by_' . $sorting),
                $this->ctrl->getLinkTarget($this, 'changePDItemSorting', '', true)
            );
            $this->ctrl->setParameter($this, 'sorting', null);
        }

        $presentations = $this->viewSettings->getActivePresentationsByView($this->viewSettings->getCurrentView());
        foreach ($presentations as $presentation) {
            $this->ctrl->setParameter($this, 'presentation', $presentation);
            $this->addBlockCommand(
                $this->ctrl->getLinkTarget($this, 'changePDItemPresentation'),
                $this->lng->txt('pd_presentation_mode_' . $presentation),
                $this->ctrl->getLinkTarget($this, 'changePDItemPresentation', '', true)
            );
            $this->ctrl->setParameter($this, 'presentation', null);
        }

        $this->addBlockCommand(
            $this->ctrl->getLinkTarget($this, 'manage'),
            $this->viewSettings->isSelectedItemsViewActive() ?
                $this->lng->txt('pd_remove_multiple') :
                $this->lng->txt('pd_unsubscribe_multiple_memberships')
        );
    }

    public function executeCommand(): string
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd('getHTML');

        switch ($next_class) {
            default:
                if (method_exists($this, $cmd)) {
                    return $this->$cmd();
                }
        }
        return "";
    }

    public function changePDItemSorting(): void
    {
        $this->viewSettings->storeActorSortingMode(
            ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['sorting'] ?? ''))
        );

        $this->initAndShow();
    }

    public function changePDItemPresentation(): void
    {
        $this->viewSettings->storeActorPresentationMode(
            \ilUtil::stripSlashes((string) ($this->http->request()->getQueryParams()['presentation'] ?? ''))
        );
        $this->initAndShow();
    }

    protected function cancel(): void
    {
        $this->ctrl->returnToParent($this);
    }

    protected function returnToContext(): void
    {
        $this->ctrl->setParameterByClass('ildashboardgui', 'view', $this->viewSettings->getCurrentView());
        $this->ctrl->redirectByClass('ildashboardgui', 'show');
    }
}
