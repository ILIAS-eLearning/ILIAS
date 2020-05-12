<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

/**
 * Class ilPDBaseObjectsRenderer
 */
abstract class ilPDBaseObjectsRenderer implements ilPDObjectsRenderer
{
    /** @var ilPDSelectedItemsBlockViewGUI */
    protected $blockView;
    
    /** @var Factory */
    protected $uiFactory;

    /** @var Renderer */
    protected $uiRenderer;

    /** @var ilObjUser */
    protected $user;

    /** @var ilLanguage */
    protected $lng;

    /** @var \ilObjectService */
    protected $objectService;

    /** @var ilCtrl */
    protected $ctrl;

    /** @var ilPDSelectedItemsBlockListGUIFactory */
    protected $listItemFactory;

    /** @var \ilTemplate $*/
    protected $tpl;

    /** @var string */
    protected $currentRowType = '';

    /**
     * ilPDSelectedItemsTileRenderer constructor.
     * @param ilPDSelectedItemsBlockViewGUI $blockView
     * @param Factory $uiFactory
     * @param Renderer $uiRenderer
     * @param ilPDSelectedItemsBlockListGUIFactory $listItemFactory
     * @param ilObjUser $user
     * @param ilLanguage $lng
     * @param ilObjectService $objectService
     * @param ilCtrl $ctrl
     */
    public function __construct(
        ilPDSelectedItemsBlockViewGUI $blockView,
        Factory $uiFactory,
        Renderer $uiRenderer,
        ilPDSelectedItemsBlockListGUIFactory $listItemFactory,
        ilObjUser $user,
        ilLanguage $lng,
        ilObjectService $objectService,
        ilCtrl $ctrl
    ) {
        $this->blockView = $blockView;
        $this->uiFactory = $uiFactory;
        $this->uiRenderer = $uiRenderer;
        $this->listItemFactory = $listItemFactory;
        $this->user = $user;
        $this->lng = $lng;
        $this->objectService = $objectService;
        $this->ctrl = $ctrl;
    }
}
