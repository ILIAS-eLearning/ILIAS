<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\BookingManager\BookingProcess;

use ILIAS\BookingManager\getObjectSettingsCommand;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class SlotGUI
{
    protected int $color_nr;
    protected $from;
    protected string $to;
    protected int $from_ts;
    protected int $to_ts;
    protected string $title;
    protected int $available;
    protected string $link;

    public function __construct(
        string $link,
        string $from,
        string $to,
        int $from_ts,
        int $to_ts,
        string $title,
        int $available,
        int $color_nr
    ) {
        $this->from = $from;
        $this->to = $to;
        $this->from_ts = $from_ts;
        $this->to_ts = $to_ts;
        $this->title = $title;
        $this->available = $available;
        $this->link = $link;
        $this->color_nr = $color_nr;
    }

    public function render() : string
    {
        global $DIC;
        $ui = $DIC->ui();
        $tpl = new \ilTemplate("tpl.slot.html", true, true, "Modules/BookingManager/BookingProcess");

        $modal = $ui->factory()->modal()->roundtrip("", $ui->factory()->legacy(""));
        $url = $this->link . '&replaceSignal=' . $modal->getReplaceSignal()->getId();
        $modal = $modal->withAsyncRenderUrl($url);
        $button = $ui->factory()->button()->shy($this->title, "#")
            ->withOnClick($modal->getShowSignal());

        $tpl->setVariable("OBJECT_LINK", $ui->renderer()->render([$button, $modal]));
        $tpl->setVariable("TIME", $this->from . "-" . $this->to);
        $tpl->setVariable("COLOR_NR", $this->color_nr);
        $tpl->setVariable("AVAILABILITY", "(" . $this->available . ") ");

        return $tpl->get();
    }
}
