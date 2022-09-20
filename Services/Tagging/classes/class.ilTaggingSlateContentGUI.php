<?php

declare(strict_types=1);

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

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\Sender\ResponseSendingException;

/**
 * Tagging slate UI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaggingSlateContentGUI implements ilCtrlBaseClassInterface
{
    public const CURRENT_TAG_KEY = "tag_current_tag";

    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected \ILIAS\DI\UIServices $ui;
    protected \ilLanguage $lng;
    protected \ilAccessHandler $access;
    protected ilTree $tree;
    protected \ILIAS\HTTP\Services $http;
    protected string $requested_tag;
    protected ilSessionIStorage $store;
    protected array $tags;
    private \ilGlobalTemplateInterface $main_tpl;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

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

        $params = $this->http->request()->getQueryParams();
        $this->requested_tag = str_replace(
            "-->",
            "",
            $params["tag"] ?? ""
        );
        if ($this->requested_tag == "") {
            $this->requested_tag = $this->getCurrentTag();
        }
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        // PHP8 Review: 'switch' with single 'case'
        switch ($next_class) {
            default:
                if (in_array($cmd, array("showResourcesForTag", "showTagCloud"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Render
     * @return string
     * @throws ilTemplateException
     */
    public function render(): string
    {
        if ($this->getCurrentTag() != "") {
            $content = $this->renderResourcesForTag();
        } else {
            $content = $this->renderTagCloud();
        }
        return "<div id='il-tag-slate-container'>" . $content . "</div>";
    }

    // Get tag cloud
    protected function renderTagCloud(): string
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
                    ilTagging::getRelevanceClass((int) $tag["cnt"], (int) $max)
                );
                $tpl->parseCurrentBlock();
            }
            return $tpl->get();
        }

        return $this->ui->renderer()->render($this->getNoTagsUsedMessage());
    }


    // Render resources
    protected function renderResourcesForTag(): string
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
        $objs = ilTagging::getObjectsForTagAndUser($ilUser->getId(), $tag);

        $f = $this->ui->factory();
        $item_groups = [];
        $items = [];
        foreach ($objs as $key => $obj) {
            $ref_ids = ilObject::_getAllReferences((int) $obj["obj_id"]);
            foreach ($ref_ids as $ref_id) {
                $type = $obj["obj_type"];

                if ($type == "" || (!$access->checkAccess("visible", "", $ref_id) &&
                        !$access->checkAccess("read", "", $ref_id))) {
                    continue;
                }

                $title = ilObject::_lookupTitle((int) $obj["obj_id"]);
                $items[] = $f->item()->standard(
                    $f->link()->standard($title, ilLink::_getLink($ref_id))
                )->withLeadIcon($f->symbol()->icon()->custom(ilObject::_getIcon((int) $obj["obj_id"]), $title));
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
     * @throws ResponseSendingException
     */
    protected function showResourcesForTag(): void
    {
        $this->send($this->renderResourcesForTag());
    }

    /**
     * Show tag cloud
     * @throws ResponseSendingException
     */
    protected function showTagCloud(): void
    {
        $this->send($this->renderTagCloud());
    }


    // Remove tags without access
    public function removeTagsWithoutAccess(): void
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

        $this->main_tpl->setOnScreenMessage('success', $lng->txt("tag_tags_deleted"), true);

        $ilCtrl->returnToParent($this);
    }

    public function getNoTagsUsedMessage(): ILIAS\UI\Component\MessageBox\MessageBox
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

    protected function getRepositoryTitle(): string
    {
        $nd = $this->tree->getNodeData($this->tree->getRootId());
        $title = $nd['title'];

        if ($title === 'ILIAS') {
            $title = $this->lng->txt('repository');
        }

        return $title;
    }

    /**
     * Send
     * @param string $output
     * @throws ResponseSendingException
     */
    protected function send(string $output): void
    {
        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($output)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }

    protected function setCurrentTag(string $tag): void
    {
        $this->store->set(self::CURRENT_TAG_KEY, $tag);
    }

    protected function getCurrentTag(): string
    {
        // PHP8 Review: Type cast is unnecessary
        return (string) $this->store->get(self::CURRENT_TAG_KEY);
    }

    protected function clearCurrentTag(): void
    {
        $this->store->set(self::CURRENT_TAG_KEY, "");
    }
}
