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

interface FactoryInternal extends \ILIAS\UI\Factory
{
    public function counter(): I\Counter\Factory;

    public function button(): I\Button\Factory;

    public function card(): I\Card\Factory;

    public function deck(array $cards): I\Deck\Deck;

    public function listing(): I\Listing\Factory;

    public function image(): I\Image\Factory;

    public function player(): I\Player\Factory;

    public function legacy(): I\Legacy\Factory;

    public function panel(): I\Panel\Factory;

    public function modal(): I\Modal\Factory;

    public function progress(): I\Progress\Factory;

    public function dropzone(): I\Dropzone\Factory;

    public function popover(): I\Popover\Factory;

    public function divider(): I\Divider\Factory;

    public function link(): I\Link\Factory;

    public function dropdown(): I\Dropdown\Factory;

    public function item(): I\Item\Factory;

    public function viewControl(): I\ViewControl\Factory;

    public function breadcrumbs(array $crumbs): I\Breadcrumbs\Breadcrumbs;

    public function chart(): I\Chart\Factory;

    public function input(): I\Input\Factory;

    public function table(): I\Table\Factory;

    public function messageBox(): I\MessageBox\Factory;

    public function layout(): I\Layout\Factory;

    public function mainControls(): I\MainControls\Factory;

    public function tree(): I\Tree\Factory;

    public function menu(): I\Menu\Factory;

    public function symbol(): I\Symbol\Factory;

    public function toast(): I\Toast\Factory;

    public function launcher(): I\Launcher\Factory;

    public function entity(): I\Entity\Factory;

    public function prompt(): I\Prompt\Factory;
}
