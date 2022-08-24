<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Component as C;

// TODO: This might cache the created factories.
use ILIAS\UI\Implementation\Component\SignalGenerator;

class Factory implements \ILIAS\UI\Factory
{
    protected C\Counter\Factory $counter_factory;
    protected C\Button\Factory $button_factory;
    protected C\Listing\Factory $listing_factory;
    protected C\Image\Factory $image_factory;
    protected C\Panel\Factory $panel_factory;
    protected C\Modal\Factory $modal_factory;
    protected C\Dropzone\Factory $dropzone_factory;
    protected C\Popover\Factory $popover_factory;
    protected C\Divider\Factory $divider_factory;
    protected C\Link\Factory $link_factory;
    protected C\Dropdown\Factory $dropdown_factory;
    protected C\Item\Factory $item_factory;
    protected C\ViewControl\Factory $viewcontrol_factory;
    protected C\Chart\Factory $chart_factory;
    protected C\Input\Factory $input_factory;
    protected C\Table\Factory $table_factory;
    protected C\MessageBox\Factory $messagebox_factory;
    protected C\Card\Factory $card_factory;
    protected C\Menu\Factory $menu_factory;
    protected C\Layout\Factory $layout_factory;
    protected C\MainControls\Factory $maincontrols_factory;
    protected C\Tree\Factory $tree_factory;
    protected C\Symbol\Factory $symbol_factory;
    protected C\Toast\Factory $toast_factory;
    protected C\Legacy\Factory $legacy_factory;

    public function __construct(
        C\Counter\Factory $counter_factory,
        C\Button\Factory $button_factory,
        C\Listing\Factory $listing_factory,
        C\Image\Factory $image_factory,
        C\Panel\Factory $panel_factory,
        C\Modal\Factory $modal_factory,
        C\Dropzone\Factory $dropzone_factory,
        C\Popover\Factory $popover_factory,
        C\Divider\Factory $divider_factory,
        C\Link\Factory $link_factory,
        C\Dropdown\Factory $dropdown_factory,
        C\Item\Factory $item_factory,
        C\ViewControl\Factory $viewcontrol_factory,
        C\Chart\Factory $chart_factory,
        C\Input\Factory $input_factory,
        C\Table\Factory $table_factory,
        C\MessageBox\Factory $messagebox_factory,
        C\Card\Factory $card_factory,
        C\Layout\Factory $layout_factory,
        C\MainControls\Factory $maincontrols_factory,
        C\Tree\Factory $tree_factory,
        C\Menu\Factory $menu_factory,
        C\Symbol\Factory $symbol_factory,
        C\Toast\Factory $toast_factory,
        C\Legacy\Factory $legacy_factory
    ) {
        $this->counter_factory = $counter_factory;
        $this->button_factory = $button_factory;
        $this->listing_factory = $listing_factory;
        $this->image_factory = $image_factory;
        $this->panel_factory = $panel_factory;
        $this->modal_factory = $modal_factory;
        $this->dropzone_factory = $dropzone_factory;
        $this->popover_factory = $popover_factory;
        $this->divider_factory = $divider_factory;
        $this->link_factory = $link_factory;
        $this->dropdown_factory = $dropdown_factory;
        $this->item_factory = $item_factory;
        $this->viewcontrol_factory = $viewcontrol_factory;
        $this->chart_factory = $chart_factory;
        $this->input_factory = $input_factory;
        $this->table_factory = $table_factory;
        $this->messagebox_factory = $messagebox_factory;
        $this->card_factory = $card_factory;
        $this->layout_factory = $layout_factory;
        $this->maincontrols_factory = $maincontrols_factory;
        $this->tree_factory = $tree_factory;
        $this->menu_factory = $menu_factory;
        $this->symbol_factory = $symbol_factory;
        $this->toast_factory = $toast_factory;
        $this->legacy_factory = $legacy_factory;
    }

    /**
     * @inheritdoc
     */
    public function counter(): C\Counter\Factory
    {
        return $this->counter_factory;
    }

    /**
     * @inheritdoc
     */
    public function button(): C\Button\Factory
    {
        return $this->button_factory;
    }

    /**
     * @inheritdoc
     */
    public function card(): C\Card\Factory
    {
        return $this->card_factory;
    }

    /**
     * @inheritdoc
     */
    public function deck(array $cards): C\Deck\Deck
    {
        return new Component\Deck\Deck($cards, C\Deck\Deck::SIZE_S);
    }

    /**
     * @inheritdoc
     */
    public function listing(): C\Listing\Factory
    {
        return $this->listing_factory;
    }

    /**
     * @inheritdoc
     */
    public function image(): C\Image\Factory
    {
        return $this->image_factory;
    }

    public function player(): C\Player\Factory
    {
        return new Component\Player\Factory();
    }

    /**
     * @inheritdoc
     */
    public function legacy(string $content): C\Legacy\Legacy
    {
        return $this->legacy_factory->legacy($content);
    }

    /**
     * @inheritdoc
     */
    public function panel(): C\Panel\Factory
    {
        return $this->panel_factory;
    }

    /**
     * @inheritdoc
     */
    public function modal(): C\Modal\Factory
    {
        return $this->modal_factory;
    }

    /**
     * @inheritdoc
     */
    public function dropzone(): C\Dropzone\Factory
    {
        return $this->dropzone_factory;
    }

    /**
     * @inheritdoc
     */
    public function popover(): C\Popover\Factory
    {
        return $this->popover_factory;
    }

    /**
     * @inheritdoc
     */
    public function divider(): C\Divider\Factory
    {
        return $this->divider_factory;
    }

    /**
     * @inheritdoc
     */
    public function link(): C\Link\Factory
    {
        return $this->link_factory;
    }

    /**
     * @inheritdoc
     */
    public function dropdown(): C\Dropdown\Factory
    {
        return $this->dropdown_factory;
    }

    /**
     * @inheritdoc
     */
    public function item(): C\Item\Factory
    {
        return $this->item_factory;
    }


    /**
     * @inheritdoc
     */
    public function viewControl(): C\ViewControl\Factory
    {
        return $this->viewcontrol_factory;
    }

    /**
     * @inheritdoc
     */
    public function breadcrumbs(array $crumbs): C\Breadcrumbs\Breadcrumbs
    {
        return new Component\Breadcrumbs\Breadcrumbs($crumbs);
    }

    /**
     * @inheritdoc
     */
    public function chart(): C\Chart\Factory
    {
        return $this->chart_factory;
    }

    /**
     * @inheritdoc
     */
    public function input(): C\Input\Factory
    {
        return $this->input_factory;
    }

    /**
     * @inheritdoc
     */
    public function table(): C\Table\Factory
    {
        return $this->table_factory;
    }

    /**
     * @inheritdoc
     */
    public function messageBox(): C\MessageBox\Factory
    {
        return $this->messagebox_factory;
    }

    /**
     * @inheritdoc
     */
    public function layout(): C\Layout\Factory
    {
        return $this->layout_factory;
    }

    /**
     * @inheritdoc
     */
    public function mainControls(): C\MainControls\Factory
    {
        return $this->maincontrols_factory;
    }

    /**
    * @inheritdoc
    */
    public function tree(): C\Tree\Factory
    {
        return $this->tree_factory;
    }

    /**
     * @inheritdoc
     */
    public function menu(): C\Menu\Factory
    {
        return $this->menu_factory;
    }

    /**
     * @inheritdoc
     */
    public function symbol(): C\Symbol\Factory
    {
        return $this->symbol_factory;
    }

    public function toast(): C\Toast\Factory
    {
        return $this->toast_factory;
    }
}
