<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\URI;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\Sender\ResponseSendingException;

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
     * @throws ResponseSendingException
     */
    protected function showResourcesForTag()
    {
        // back button
        $tag_cmd = $this->ctrl->getLinkTarget($this, "showTagCloud", "", true, false);
        $items[] = $this->ui->factory()->button()->bulky(
            $this->ui->factory()->symbol()->glyph()->back(),
            $this->lng->txt("back"), "#")->withOnLoadCode(function ($id) use ($tag_cmd) {
            return "$(\"#$id\").click(function() { 
                il.Util.ajaxReplaceInner('$tag_cmd', 'il-tag-slate-container'); return false;
            });";
        });

        $items[] = $this->ui->factory()->item()->standard(sprintf(
            $this->lng->txt("tagging_resources_for_tag"),
            "<i>" . ilUtil::secureString($this->http->request()->getQueryParams()["tag"]) . "</i>"
        ));

        // resource list
        include_once("./Services/Tagging/classes/class.ilTagging.php");

        foreach (ilTagging::getObjectsForTagAndUser($this->user->getId(), $_GET["tag"]) as $key => $obj) {
            $ref_ids = ilObject::_getAllReferences($obj["obj_id"]);
            foreach ($ref_ids as $ref_id) {
                if ($obj["obj_type"] != "" && ($this->access->checkAccess("visible", "", $ref_id) ||
                        $this->access->checkAccess("read", "", $ref_id))) {
                    $title = ilObject::_lookupTitle($obj["obj_id"]);
                    $items[] = $this->ui->factory()->link()->bulky(
                        $this->ui->factory()->symbol()->icon()->custom(ilObject::_getIcon($obj["obj_id"]), $title),
                        $title,
                        new URI(ilLink::_getLink($ref_id))
                    );
                }
            }
        }

        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($this->ui->renderer()->renderAsync($items))
        ));
        $this->http->sendResponse();
        $this->http->close();
    }
}
