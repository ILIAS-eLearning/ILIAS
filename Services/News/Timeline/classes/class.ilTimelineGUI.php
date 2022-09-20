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

/**
 *  Timline (temporary implementation)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTimelineGUI
{
    /** @var ilTimelineItemInt[] */
    protected array $items = [];
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;

    protected function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public function addItem(ilTimelineItemInt $a_item): void
    {
        $this->items[] = $a_item;
    }

    public function render(
        bool $a_items_only = false
    ): string {
        $this->tpl->addJavaScript("./Services/News/Timeline/js/Timeline.js");
        $this->tpl->addJavaScript("./Services/News/Timeline/libs/jquery-dynamic-max-height-master/src/jquery.dynamicmaxheight.js");

        $t = new ilTemplate("tpl.timeline.html", true, true, "Services/News/Timeline");
        if (!$a_items_only) {
            $t->touchBlock("list_start");
            $t->touchBlock("list_end");
        }
        $keys = array_keys($this->items);
        foreach ($this->items as $k => $i) {
            $next = null;
            if (isset($keys[$k + 1], $this->items[$keys[$k + 1]])) {
                $next = $this->items[$keys[$k + 1]];
            }

            $dt = $i->getDateTime();
            if (is_null($next) || $dt->get(IL_CAL_FKT_DATE, "Y-m-d") !== $next->getDateTime()->get(IL_CAL_FKT_DATE, "Y-m-d")) {
                $t->setCurrentBlock("badge");
                $t->setVariable("DAY", $dt->get(IL_CAL_FKT_DATE, "d"));
                $t->setVariable("MONTH", $this->lng->txt("month_" . $dt->get(IL_CAL_FKT_DATE, "m") . "_short"));
                $t->parseCurrentBlock();
            }

            $t->setCurrentBlock("item");
            $t->setVariable("CONTENT", $i->render());
            $t->setVariable("FOOTER", $i->renderFooter());
            $t->parseCurrentBlock();
        }
        return $t->get();
    }
}
