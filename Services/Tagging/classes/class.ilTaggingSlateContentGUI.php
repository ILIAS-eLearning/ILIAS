<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tagging slate UI
 *
 * @author killing@leifos.de
 */
class ilTaggingSlateContentGUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();

        $this->lng->loadLanguageModule("tagging");

        $this->tags = ilTagging::getTagsForUser($this->user->getId(), 1000000);
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, array("showResourcesForTag", "showTagCloud"))) {
                    $this->$cmd();
                }
        }
    }
    /**
     * Render
     *
     * @return string
     * @throws ilTemplateException
     */
    public function render()
    {
        $tag_cloud_html = $this->renderTagCloud();
        return "<div id='il-tag-slate-container'>" . $tag_cloud_html . "</div>";
    }
    
    /**
     * Get tag cloud
     *
     * @return string
     * @throws ilTemplateException
     */
    protected function renderTagCloud() : string
    {
        $ilCtrl = $this->ctrl;

        $tpl = new ilTemplate(
            "tpl.tag_cloud.html",
            true,
            true,
            "Services/Tagging"
        );
        $max = 1;
        foreach ($this->tags as $tag) {
            $max = max($tag["cnt"], $max);
        }
        reset($this->tags);

        if (count($this->tags) > 0) {
            foreach ($this->tags as $tag) {
                $tpl->setCurrentBlock("linked_tag");
                $ilCtrl->setParameter($this, "tag", rawurlencode($tag["tag"]));
                $list_cmd = $ilCtrl->getLinkTarget($this, "showResourcesForTag");
                $tpl->setVariable("ON_CLICK",
                    "il.Util.ajaxReplaceInner('$list_cmd', 'il-tag-slate-container'); return false;");
                $tpl->setVariable("TAG_TITLE", $tag["tag"]);
                $tpl->setVariable("HREF_TAG", "#");
                $tpl->setVariable(
                    "REL_CLASS",
                    ilTagging::getRelevanceClass($tag["cnt"], $max)
                );
                $tpl->parseCurrentBlock();
            }
            return $tpl->get();
        } else {
            return $this->ui->renderer()->render($this->getNoTagsUsedMessage());
        }
    }
    

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
        $back_button = $ui->factory()->button()->bulky($ui->factory()->symbol()->glyph()->back(), $lng->txt("back"), "#")->withOnLoadCode(function ($id) use ($tag_cmd) {
            return
                "$(\"#$id\").click(function() { il.Util.ajaxReplaceInner('$tag_cmd', 'il-tag-slate-container'); return false;});";
        });

        $tag = str_replace("-->", "", $_GET["tag"]);

        // resource list
        include_once("./Services/Tagging/classes/class.ilTagging.php");
        $objs = ilTagging::getObjectsForTagAndUser($ilUser->getId(), $_GET["tag"]);

        $unaccessible = false;
        $f = $this->ui->factory();
        $item_groups = [];
        foreach ($objs as $key => $obj) {
            $ref_ids = ilObject::_getAllReferences($obj["obj_id"]);
            foreach ($ref_ids as $ref_id) {
                $type = $obj["obj_type"];

                if ($type == "" || (!$access->checkAccess("visible", "", $ref_id) &&
                        !$access->checkAccess("read", "", $ref_id))) {
                    $unaccessible = true;
                    continue;
                }

                $title = ilObject::_lookupTitle($obj["obj_id"]);
                $items[] = $f->item()->standard(
                    $f->link()->standard($title, ilLink::_getLink($ref_id))
                )->withLeadIcon($f->symbol()->icon()->custom(ilObject::_getIcon($obj["obj_id"]), $title));
            }
        }
        $item_groups[] = $f->item()->group(sprintf(
            $lng->txt("tagging_resources_for_tag"),
            "<i>" . $tag . "</i>"
        ), $items);
        $panel = $f->panel()->secondary()->listing("", $item_groups);

        /*
        if ($unaccessible)
        {
            $tpl->setCurrentBlock("no_access");
            $tpl->setVariable("SOME_OBJ_WITHOUT_ACCESS", $lng->txt("tag_some_obj_tagged_without_access"));
            $ilCtrl->saveParameter($this, "tag");
            $tpl->setVariable("HREF_REMOVE_TAGS", $ilCtrl->getLinkTarget($this, "removeTagsWithoutAccess"));
            $tpl->setVariable("REMOVE_TAGS", $lng->txt("tag_remove_tags_of_obj_without_access"));
            $tpl->parseCurrentBlock();
        }*/

        echo $ui->renderer()->renderAsync([$back_button, $panel]);
        exit;
    }

    /**
     * Show tag cloud
     * @throws ilTemplateException
     */
    protected function showTagCloud()
    {
        echo $this->renderTagCloud();
        exit;
    }


    /**
     * Remove tasg without access
     */
    public function removeTagsWithoutAccess()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilUser = $this->user;
        $lng = $this->lng;

        // get resources
        include_once("./Services/Tagging/classes/class.ilTagging.php");
        $objs = ilTagging::getObjectsForTagAndUser($ilUser->getId(), $_GET["tag"]);

        foreach ($objs as $key => $obj) {
            $ref_ids = ilObject::_getAllReferences($obj["obj_id"]);
            if (count($ref_ids) == 0) {
                $inaccessible = true;
            } else {
                $inaccessible = false;
            }
            foreach ($ref_ids as $ref_id) {
                $type = $obj["obj_type"];

                if ($type == "") {
                    $inaccessible = true;
                    continue;
                }
                if (!$ilAccess->checkAccess("visible", "", $ref_id) &&
                    !$ilAccess->checkAccess("read", "", $ref_id) &&
                    !$ilAccess->checkAccess("write", "", $ref_id)) {
                    $inaccessible = true;
                }
                if ($inaccessible) {
                    ilTagging::deleteTagOfObjectForUser($ilUser->getId(), $obj["obj_id"], $obj["obj_type"], $obj["sub_obj_id"], $obj["sub_obj_type"], $_GET["tag"]);
                }
            }
        }

        ilUtil::sendSuccess($lng->txt("tag_tags_deleted"), true);

        $ilCtrl->returnToParent($this);
    }

    /**
     * No tags used message box
     *
     * @return ILIAS\UI\Component\MessageBox\MessageBox
     */
    public function getNoTagsUsedMessage() : ILIAS\UI\Component\MessageBox\MessageBox
    {

        $txt = $this->lng->txt("no_tag_text_1") . "<br>";
        $txt .= sprintf(
                $this->lng->txt('no_tag_text_2'),
                $this->getRepositoryTitle()
            ) . "<br>";
        $txt .= $this->lng->txt("no_tag_text_3");
        $mbox = $this->ui->factory()->messageBox()->info($txt);
        $mbox = $mbox->withLinks([$this->ui->factory()->link()->standard($this->getRepositoryTitle(), ilLink::_getStaticLink(1, 'root', true))]);

        return $mbox;
    }

    /**
     * @return string
     */
    protected function getRepositoryTitle()
    {
        $nd = $this->tree->getNodeData($this->tree->getRootId());
        $title = $nd['title'];

        if ($title == 'ILIAS') {
            $title = $this->lng->txt('repository');
        }

        return $title;
    }
}
