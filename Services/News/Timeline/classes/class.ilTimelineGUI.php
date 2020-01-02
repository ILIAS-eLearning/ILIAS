<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/Timeline/interfaces/interface.ilTimelineItemInt.php");

/**
 *  Timline (temporary implementation)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup
 */
class ilTimelineGUI
{
    protected $items = array();

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * Construct
     *
     * @param
     * @return
     */
    protected function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
    }

    /**
     * Get instance
     *
     * @param
     * @return
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Add item
     *
     * @param
     * @return
     */
    public function addItem(ilTimelineItemInt $a_item)
    {
        $this->items[] = $a_item;
    }

    /**
     * Render
     *
     * @param
     * @return
     */
    public function render($a_items_only = false)
    {
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
            if (isset($this->items[$keys[$k+1]])) {
                $next = $this->items[$keys[$k+1]];
            }

            $dt = $i->getDateTime();
            if (is_null($next) || $dt->get(IL_CAL_FKT_DATE, "Y-m-d") != $next->getDateTime()->get(IL_CAL_FKT_DATE, "Y-m-d")) {
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
