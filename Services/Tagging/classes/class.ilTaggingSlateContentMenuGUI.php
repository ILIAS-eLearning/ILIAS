<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tagging slate UI
 *
 * @author killing@leifos.de
 */
class ilTaggingSlateContentMenuGUI extends ilTaggingSlateContentGUI
{
    /**
     * show resources
     *
     * @param
     * @return string
     */
    protected function showResourcesForTag()
    {
        $ui = $this->ui;
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $access = $this->access;
        $ilUser = $this->user;

        // back button
        $tag_cmd = $ctrl->getLinkTarget($this, "showTagCloud", "", true, false);
        $back_button = $ui->factory()->button()->bulky(
            $ui->factory()->symbol()->glyph()->back(),
            $lng->txt("back"), "#")->withOnLoadCode(function ($id) use ($tag_cmd) {
            return "$(\"#$id\").click(function() { 
                il.Util.ajaxReplaceInner('$tag_cmd', 'il-tag-slate-container'); return false;
            });";
        });

        //$tag = str_replace("-->", "", $_GET["tag"]);

        // resource list
        include_once("./Services/Tagging/classes/class.ilTagging.php");

        $f = $this->ui->factory();
        $item_groups = [];
        foreach (ilTagging::getObjectsForTagAndUser($ilUser->getId(), $_GET["tag"]) as $key => $obj) {
            $ref_ids = ilObject::_getAllReferences($obj["obj_id"]);
            foreach ($ref_ids as $ref_id) {
                $type = $obj["obj_type"];

                if ($type != "" && ($access->checkAccess("visible", "", $ref_id) ||
                        $access->checkAccess("read", "", $ref_id))) {
                    $title = ilObject::_lookupTitle($obj["obj_id"]);
                    $items[] = $this->ui->factory()->link()->bulky(
                        $this->ui->factory()->symbol()->icon()->custom(ilObject::_getIcon($obj_id), $nav_item["title"]),
                        $nav_item["title"],
                        new URI($url)
                    );




                        $f->item()->standard(
                        $f->link()->standard($title, ilLink::_getLink($ref_id))
                    )->withLeadIcon($f->symbol()->icon()->custom(ilObject::_getIcon($obj["obj_id"]), $title));
                }
            }
        }
        $item_groups[] = $f->item()->group(sprintf(
            $lng->txt("tagging_resources_for_tag"),
            "<i>" . $tag . "</i>"
        ), $items);
        $panel = $f->panel()->secondary()->listing("", $item_groups);

        echo $ui->renderer()->renderAsync([$back_button, $panel]);
        exit;
    }
}
