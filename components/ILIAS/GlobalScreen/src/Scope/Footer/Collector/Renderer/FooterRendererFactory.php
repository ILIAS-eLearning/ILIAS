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

namespace ILIAS\GlobalScreen\Scope\Footer\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\Footer\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Group;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Link;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Modal;
use ILIAS\DI\UIServices;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Permanent;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Text;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class FooterRendererFactory
{
    private readonly GroupItemRenderer $group_renderer;
    private readonly LinkItemRenderer $link_renderer;
    private readonly ModalItemRenderer $modal_renderer;
    private readonly NullItemRenderer $null_renderer;

    public function __construct(private readonly UIServices $ui)
    {
        $this->group_renderer = new GroupItemRenderer($this->ui, $this);
        $this->link_renderer = new LinkItemRenderer($this->ui);
        $this->modal_renderer = new ModalItemRenderer($this->ui);
        $this->null_renderer = new NullItemRenderer($this->ui);
    }

    public function getRendererFor(isItem $item): FooterItemRenderer
    {
        return match (true) {
            $item instanceof Group => $this->group_renderer,
            $item instanceof Link => $this->link_renderer,
            $item instanceof Modal => $this->modal_renderer,
            default => $this->null_renderer,
        };
    }

    public function addToFooter(isItem $item, Footer $footer): Footer
    {
        $renderer = $this->getRendererFor($item);
        $component = $renderer->getComponentForItem($item);

        return match (true) {
            $item instanceof Group => $footer->withAdditionalLinkGroup($item->getTitle(), $component),
            $item instanceof Link => $item->hasParent() ? $footer : $footer->withAdditionalLink($component),
            $item instanceof Modal => $footer->withAdditionalModal($item->getModal()),
            $item instanceof Permanent => $footer->withPermanentURL($item->getURI()),
            $item instanceof Text => $footer->withAdditionalText($item->getText()),
            default => $footer,
        };
    }

}
