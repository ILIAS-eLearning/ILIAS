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

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;
use ILIAS\UI\Help;

class Factory implements FactoryInternal
{
    public function __construct(
        protected I\Counter\Factory $counter_factory,
        protected I\Button\Factory $button_factory,
        protected I\Listing\Factory $listing_factory,
        protected I\Image\Factory $image_factory,
        protected I\Player\Factory $player_factory,
        protected I\Panel\Factory $panel_factory,
        protected I\Modal\Factory $modal_factory,
        protected I\Progress\Factory $progress_factory,
        protected I\Dropzone\Factory $dropzone_factory,
        protected I\Popover\Factory $popover_factory,
        protected I\Divider\Factory $divider_factory,
        protected I\Link\Factory $link_factory,
        protected I\Dropdown\Factory $dropdown_factory,
        protected I\Item\Factory $item_factory,
        protected I\ViewControl\Factory $viewcontrol_factory,
        protected I\Chart\Factory $chart_factory,
        protected I\Input\Factory $input_factory,
        protected I\Table\Factory $table_factory,
        protected I\MessageBox\Factory $messagebox_factory,
        protected I\Card\Factory $card_factory,
        protected I\Layout\Factory $layout_factory,
        protected I\MainControls\Factory $maincontrols_factory,
        protected I\Tree\Factory $tree_factory,
        protected I\Menu\Factory $menu_factory,
        protected I\Symbol\Factory $symbol_factory,
        protected I\Toast\Factory $toast_factory,
        protected I\Legacy\Factory $legacy_factory,
        protected I\launcher\Factory $launcher_factory,
        protected I\Entity\Factory $entity_factory,
        protected I\Prompt\Factory $prompt_factory,
        protected I\Navigation\Factory $navigation_factory,
    ) {
    }

    public function counter(): I\Counter\Factory
    {
        return $this->counter_factory;
    }

    public function button(): I\Button\Factory
    {
        return $this->button_factory;
    }

    public function card(): I\Card\Factory
    {
        return $this->card_factory;
    }

    public function deck(array $cards): I\Deck\Deck
    {
        return new I\Deck\Deck($cards, C\Deck\Deck::SIZE_S);
    }

    public function listing(): I\Listing\Factory
    {
        return $this->listing_factory;
    }

    public function image(): I\Image\Factory
    {
        return $this->image_factory;
    }

    public function player(): I\Player\Factory
    {
        return $this->player_factory;
    }

    public function legacy(): I\Legacy\Factory
    {
        return $this->legacy_factory;
    }

    public function panel(): I\Panel\Factory
    {
        return $this->panel_factory;
    }

    public function modal(): I\Modal\Factory
    {
        return $this->modal_factory;
    }

    public function progress(): I\Progress\Factory
    {
        return $this->progress_factory;
    }

    public function dropzone(): I\Dropzone\Factory
    {
        return $this->dropzone_factory;
    }

    public function popover(): I\Popover\Factory
    {
        return $this->popover_factory;
    }

    public function divider(): I\Divider\Factory
    {
        return $this->divider_factory;
    }

    public function link(): I\Link\Factory
    {
        return $this->link_factory;
    }

    public function dropdown(): I\Dropdown\Factory
    {
        return $this->dropdown_factory;
    }

    public function item(): I\Item\Factory
    {
        return $this->item_factory;
    }

    public function viewControl(): I\ViewControl\Factory
    {
        return $this->viewcontrol_factory;
    }

    public function breadcrumbs(array $crumbs): I\Breadcrumbs\Breadcrumbs
    {
        return new Component\Breadcrumbs\Breadcrumbs($crumbs);
    }

    public function chart(): I\Chart\Factory
    {
        return $this->chart_factory;
    }

    public function input(): I\Input\Factory
    {
        return $this->input_factory;
    }

    public function table(): I\Table\Factory
    {
        return $this->table_factory;
    }

    public function messageBox(): I\MessageBox\Factory
    {
        return $this->messagebox_factory;
    }

    public function layout(): I\Layout\Factory
    {
        return $this->layout_factory;
    }

    public function mainControls(): I\MainControls\Factory
    {
        return $this->maincontrols_factory;
    }

    public function tree(): I\Tree\Factory
    {
        return $this->tree_factory;
    }

    public function menu(): I\Menu\Factory
    {
        return $this->menu_factory;
    }

    public function symbol(): I\Symbol\Factory
    {
        return $this->symbol_factory;
    }

    public function toast(): I\Toast\Factory
    {
        return $this->toast_factory;
    }

    public function helpTopics(string ...$topics): array
    {
        return array_map(
            fn($t) => new Help\Topic($t),
            $topics
        );
    }

    public function launcher(): I\Launcher\Factory
    {
        return $this->launcher_factory;
    }

    public function entity(): I\Entity\Factory
    {
        return $this->entity_factory;
    }

    public function prompt(): I\Prompt\Factory
    {
        return $this->prompt_factory;
    }

    public function navigation(): I\Navigation\Factory
    {
        return $this->navigation_factory;
    }

}
