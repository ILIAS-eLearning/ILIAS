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

namespace ILIAS\BookingManager\BookingProcess;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ilTemplate;

class SlotGUI
{
    protected UIFactory $ui_factory;
    protected Renderer $ui_renderer;

    public function __construct(
        protected string $link,
        protected string $from,
        protected string $to,
        protected int $from_ts,
        protected int $to_ts,
        protected string $title,
        protected int $available,
        protected int $color_nr
    ) {

        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
    }

    public function render(): string
    {
        $tpl = new ilTemplate('tpl.slot.html', true, true, 'components/ILIAS/BookingManager/BookingProcess');

        $modal = $this->ui_factory->modal()->roundtrip('', $this->ui_factory->legacy()->content(''));
        $url = "{$this->link}&replaceSignal={$modal->getReplaceSignal()->getId()}";
        $modal = $modal->withAsyncRenderUrl($url);
        $button = $this->ui_factory->button()->shy($this->title, '#')->withOnClick($modal->getShowSignal());

        $tpl->setVariable('OBJECT_LINK', $this->ui_renderer->render([$button, $modal]));
        $tpl->setVariable('TIME', ($this->to_ts - $this->from_ts) !== 86400 ? "{$this->from}-{$this->to}" : '');
        $tpl->setVariable('COLOR_NR', $this->color_nr);
        $tpl->setVariable('AVAILABILITY', "({$this->available}) ");

        return $tpl->get();
    }
}
