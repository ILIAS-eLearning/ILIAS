<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Filesystem\Stream\Streams;

/**
 * Tagging slate UI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaggingSlateContentGUI
{
    const CURRENT_TAG_KEY = "tag_current_tag";

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
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;

    /**
     * @var string
     */
    protected $requested_tag;

    /**
     * @var ilSessionIStorage
     */
    protected $store;

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
        $this->http = $DIC->http();
        $this->store = new ilSessionIStorage("tag");

        $this->lng->loadLanguageModule("tagging");

        $this->tags = ilTagging::getTagsForUser($this->user->getId(), 1000000);

        $this->requested_tag = str_replace(
            "-->",
            "",
            $this->http->request()->getQueryParams()["tag"]
        );
        if ($this->requested_tag == "") {
            $this->requested_tag = $this->getCurrentTag();
        }
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
        if ($this->getCurrentTag() != "") {
            $content = $this->renderResourcesForTag();
        } else {
            $content = $this->renderTagCloud();
        }
        return "<div id='il-tag-slate-container'>" . $content . "</div>";
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
        $this->clearCurrentTag();

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
                $tpl->setVariable(
                    "ON_CLICK",
                    "il.Util.ajaxReplaceInner('$list_cmd', 'il-tag-slate-container'); return false;"
                );
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
     * Render resources
     *
     * @return string
     */
    protected function renderResourcesForTag()
    {
        $ui = $this->ui;
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $access = $this->access;
        $ilUser = $this->user;
        $tag = $this->requested_tag;
        $this->setCurrentTag($tag);

        // back button
        $tag_cmd = $ctrl->getLinkTarget($this, "showTagCloud", "", true, false);
        $back_button = $ui->factory()->button()->bulky($ui->factory()->symbol()->glyph()->back(), $lng->txt("back"), "#")->withOnLoadCode(function ($id) use ($tag_cmd) {
            return
                "$(\"#$id\").click(function() { il.Util.ajaxReplaceInner('$tag_cmd', 'il-tag-slate-container'); return false;});";
        });

        // resource list
        include_once("./Services/Tagging/classes/class.ilTagging.php");
        $objs = ilTagging::getObjectsForTagAndUser($ilUser->getId(), $tag);

        $f = $this->ui->factory();
        $item_groups = [];
        $items = [];
        foreach ($objs as $key => $obj) {
            $ref_ids = ilObject::_getAllReferences($obj["obj_id"]);
            foreach ($ref_ids as $ref_id) {
                $type = $obj["obj_type"];

                if ($type == "" || (!$access->checkAccess("visible", "", $ref_id) &&
                        !$access->checkAccess("read", "", $ref_id))) {
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
            "<i>" . ilUtil::secureString($tag) . "</i>"
        ), $items);
        $panel = $f->panel()->secondary()->listing("", $item_groups);

        $components = [$back_button, $panel];
        if (count($items) == 0) {
            $mbox = $f->messageBox()->info(
                sprintf($lng->txt("tagging_no_obj_for_tag"), ilUtil::secureString($tag))
            );
            $components = [$back_button, $mbox];
        }

        return $ui->renderer()->renderAsync($components);
    }

    /**
     * show resources
     *
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    protected function showResourcesForTag()
    {
        $this->send($this->renderResourcesForTag());
    }
    /**
     * Show tag cloud
     * @throws ilTemplateException
     */
    protected function showTagCloud()
    {
        $this->send($this->renderTagCloud());
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
        $tag = $this->requested_tag;

        // get resources
        $objs = ilTagging::getObjectsForTagAndUser($ilUser->getId(), $tag);

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
                    ilTagging::deleteTagOfObjectForUser($ilUser->getId(), $obj["obj_id"], $obj["obj_type"], $obj["sub_obj_id"], $obj["sub_obj_type"], $tag);
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

    /**
     * Send
     * @param string $output
     * @throws \ILIAS\HTTP\Response\Sender\ResponseSendingException
     */
    protected function send(string $output)
    {
        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($output)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * Set current tag
     * @param string
     */
    protected function setCurrentTag(string $tag)
    {
        $this->store->set(self::CURRENT_TAG_KEY, $tag);
    }

    /**
     * Get current tag
     * @return string
     */
    protected function getCurrentTag()
    {
        return $this->store->get(self::CURRENT_TAG_KEY);
    }

    /**
     * Clear current tag
     */
    protected function clearCurrentTag()
    {
        $this->store->set(self::CURRENT_TAG_KEY, "");
    }
}
