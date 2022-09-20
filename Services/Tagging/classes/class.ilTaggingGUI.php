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

use Psr\Http\Message\RequestInterface;

/**
 * Class ilTaggingGUI. User interface class for tagging engine.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaggingGUI
{
    protected ilCtrl $ctrl;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected \ILIAS\DI\UIServices $ui;
    protected int $obj_id = 0;
    protected string $obj_type = "";
    protected int $sub_obj_id = 0;
    protected string $sub_obj_type;
    protected array $forbidden = [];    // forbidden tags
    protected int $userid = 0;
    protected string $savecmd = "";
    protected string $inputfieldname = "";
    protected RequestInterface $request;
    protected string $mess = "";
    protected string $requested_mess = "";
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
        $this->lng = $DIC->language();
        $this->ui = $DIC->ui();
        $this->request = $DIC->http()->request();

        $params = $this->request->getQueryParams();
        $this->requested_mess = ($params["mess"] ?? "");
    }


    /**
     * Execute command
     */
    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        // PHP8 Review: 'switch' with single 'case'
        switch ($next_class) {
            default:
                $cmd = $ilCtrl->getCmd();
                $this->$cmd();
                break;
        }
    }


    /**
     * Set Object.
     * @param int    $a_obj_id          Object ID
     * @param string $a_obj_type        Object Type
     * @param int    $a_sub_obj_id      Sub-object ID
     * @param string $a_sub_obj_type    Sub-object Type
     */
    public function setObject(
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id = 0,
        string $a_sub_obj_type = ""
    ): void {
        $ilUser = $this->user;

        $this->obj_id = $a_obj_id;
        $this->obj_type = $a_obj_type;
        $this->sub_obj_id = $a_sub_obj_id;
        $this->sub_obj_type = $a_sub_obj_type;

        $this->setSaveCmd("saveTags");
        $this->setUserId($ilUser->getId());
        $this->setInputFieldName("il_tags");

        $tags_set = new ilSetting("tags");
        $forbidden = $tags_set->get("forbidden_tags");
        if ($forbidden != "") {
            $this->forbidden = unserialize((string) $forbidden, ['allowed_classes' => false]);
        } else {
            $this->forbidden = array();
        }
    }

    public function setUserId(int $a_userid): void
    {
        $this->userid = $a_userid;
    }

    public function getUserId(): int
    {
        return $this->userid;
    }

    public function setSaveCmd(string $a_savecmd): void
    {
        $this->savecmd = $a_savecmd;
    }

    public function getSaveCmd(): string
    {
        return $this->savecmd;
    }

    public function setInputFieldName(string $a_inputfieldname): void
    {
        $this->inputfieldname = $a_inputfieldname;
    }

    public function getInputFieldName(): string
    {
        return $this->inputfieldname;
    }

    public function getTaggingInputHTML(): string
    {
        $lng = $this->lng;

        $ttpl = new ilTemplate("tpl.tags_input.html", true, true, "Services/Tagging");
        $tags = ilTagging::getTagsForUserAndObject(
            $this->obj_id,
            $this->obj_type,
            $this->sub_obj_id,
            $this->sub_obj_type,
            $this->getUserId()
        );
        $ttpl->setVariable(
            "VAL_TAGS",
            ilLegacyFormElementsUtil::prepareFormOutput(implode(", ", $tags))
        );
        $ttpl->setVariable("TAG_LABEL", $lng->txt("tagging_my_tags"));
        $ttpl->setVariable("TXT_SAVE", $lng->txt("save"));
        $ttpl->setVariable("TXT_COMMA_SEPARATED", $lng->txt("comma_separated"));
        $ttpl->setVariable("CMD_SAVE", $this->savecmd);
        $ttpl->setVariable("NAME_TAGS", $this->getInputFieldName());

        return $ttpl->get();
    }

    protected function getTagsFromInput(string $input): array
    {
        $input = ilUtil::stripSlashes($input);
        $input = str_replace("\r", "\n", $input);
        $input = str_replace("\n\n", "\n", $input);
        $input = str_replace("\n", ",", $input);
        $itags = explode(",", $input);
        return $itags;
    }

    public function saveInput(): void
    {
        $lng = $this->lng;
        $request = $this->request;
        $body = $request->getParsedBody();

        $itags = $this->getTagsFromInput($body[$this->getInputFieldName()]);
        $tags = array();
        foreach ($itags as $itag) {
            $itag = trim($itag);
            if (!in_array($itag, $tags) && $itag != "") {
                if (!$this->isForbidden($itag)) {
                    $tags[] = $itag;
                }
            }
        }

        ilTagging::writeTagsForUserAndObject(
            $this->obj_id,
            $this->obj_type,
            $this->sub_obj_id,
            $this->sub_obj_type,
            $this->getUserId(),
            $tags
        );
        $this->main_tpl->setOnScreenMessage('success', $lng->txt('msg_obj_modified'));
    }

    // Check whether a tag is forbiddens
    public function isForbidden(string $a_tag): bool
    {
        foreach ($this->forbidden as $f) {
            if (is_int(strpos(strtolower(
                str_replace(array("+", "§", '"', "'", "*", "%", "&", "/", "\\", "(", ")", "=", ":", ";", ":", "-", "_", "\$",
                    "£" . "!" . "¨", "^", "`", "@", "<", ">"), "", $a_tag)
            ), $f))) {
                return true;
            }
        }
        return false;
    }

    // Get Input HTML for Tagging of an object (and a user)
    public function getAllUserTagsForObjectHTML(): string
    {
        $ttpl = new ilTemplate("tpl.tag_cloud.html", true, true, "Services/Tagging");
        $tags = ilTagging::getTagsForObject(
            $this->obj_id,
            $this->obj_type,
            $this->sub_obj_id,
            $this->sub_obj_type
        );

        $max = 1;
        foreach ($tags as $tag) {
            $max = max($max, $tag["cnt"]);
        }
        reset($tags);
        foreach ($tags as $tag) {
            if (!$this->isForbidden($tag["tag"])) {
                $ttpl->setCurrentBlock("unlinked_tag");
                $ttpl->setVariable(
                    "REL_CLASS",
                    ilTagging::getRelevanceClass((int) $tag["cnt"], $max)
                );
                $ttpl->setVariable("TAG_TITLE", $tag["tag"]);
                $ttpl->parseCurrentBlock();
            }
        }

        return $ttpl->get();
    }


    ////
    //// Ajax related methods
    ////

    public static function initJavascript(
        string $a_ajax_url,
        ilGlobalTemplateInterface $a_main_tpl = null
    ): void {
        global $DIC;

        if ($a_main_tpl != null) {
            $tpl = $a_main_tpl;
        } else {
            $tpl = $DIC["tpl"];
        }

        $lng = $DIC->language();

        $lng->loadLanguageModule("tagging");
        $lng->toJS("tagging_tags", $tpl);

        iljQueryUtil::initjQuery($tpl);
        $tpl->addJavaScript("./Services/Tagging/js/ilTagging.js");

        $tpl->addOnLoadCode("ilTagging.setAjaxUrl('" . $a_ajax_url . "');");
    }

    // Get tagging js call
    public static function getListTagsJSCall(
        string $a_hash,
        string $a_update_code = null
    ): string {
        if ($a_update_code === null) {
            $a_update_code = "null";
        } else {
            $a_update_code = "'" . $a_update_code . "'";
        }
        return "ilTagging.listTags(event, '" . $a_hash . "', " . $a_update_code . ");";
    }

    /**
     * Get HTML
     */
    public function getHTML(): string
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ui = $this->ui;

        $lng->loadLanguageModule("tagging");
        $tpl = new ilTemplate("tpl.edit_tags.html", true, true, "Services/Tagging");
        $tpl->setVariable("TXT_TAGS", $lng->txt("tagging_tags"));

        $mtxt = "";
        $mtype = "";
        $mess = $this->requested_mess != ""
            ? $this->requested_mess
            : $this->mess;
        // PHP8 Review: 'switch' with single 'case'
        switch ($mess) {
            case "mod":
                $mtype = "success";
                $mtxt = $lng->txt("msg_obj_modified");
                break;
        }
        if ($mtxt != "") {
            $tpl->setVariable("MESS", ilUtil::getSystemMessageHTML($mtxt, $mtype));
        } else {
            $tpl->setVariable("MESS", "");
        }

        $title = ilObject::_lookupTitle($this->obj_id);
        $icon = $ui->factory()->symbol()->icon()->custom(
            ilObject::_getIcon($this->obj_id),
            $title,
            "medium"
        );
        $tpl->setVariable("ICON", $ui->renderer()->render($icon));
        $tpl->setVariable("TXT_OBJ_TITLE", ilObject::_lookupTitle($this->obj_id));
        $tags = ilTagging::getTagsForUserAndObject(
            $this->obj_id,
            $this->obj_type,
            $this->sub_obj_id,
            $this->sub_obj_type,
            $this->getUserId()
        );
        $tpl->setVariable(
            "VAL_TAGS",
            ilLegacyFormElementsUtil::prepareFormOutput(implode(", ", $tags))
        );
        $tpl->setVariable("TXT_SAVE", $lng->txt("save"));
        $tpl->setVariable("TXT_COMMA_SEPARATED", $lng->txt("comma_separated"));
        $tpl->setVariable("CMD_SAVE", "saveJS");

        $os = "ilTagging.cmdAjaxForm(event, '" .
            $ilCtrl->getFormActionByClass("iltagginggui", "", "", true) .
            "');";
        $tpl->setVariable("ON_SUBMIT", $os);

        $tags_set = new ilSetting("tags");
        if ($tags_set->get("enable_all_users")) {
            $tpl->setVariable("TAGS_TITLE", $lng->txt("tagging_my_tags"));

            $all_obj_tags = ilTagging::_getListTagsForObjects(array($this->obj_id));
            $all_obj_tags = $all_obj_tags[$this->obj_id] ?? null;
            if (is_array($all_obj_tags) &&
                sizeof($all_obj_tags) != sizeof($tags)) {
                $tpl->setVariable("TITLE_OTHER", $lng->txt("tagging_other_users"));
                $tpl->setCurrentBlock("tag_other_bl");
                foreach ($all_obj_tags as $tag => $is_owner) {
                    if (!$is_owner) {
                        $tpl->setVariable("OTHER_TAG", $tag);
                        $tpl->parseCurrentBlock();
                    }
                }
            }
        }

        echo $tpl->get();
        exit;
    }

    /**
     * Save JS
     */
    public function saveJS(): void
    {
        $request = $this->request;
        $body = $request->getParsedBody();
        $itags = $this->getTagsFromInput($body["tags"]);
        $tags = array();
        foreach ($itags as $itag) {
            $itag = trim($itag);
            if (!in_array($itag, $tags) && $itag != "") {
                if (!$this->isForbidden($itag)) {
                    $tags[] = $itag;
                }
            }
        }

        ilTagging::writeTagsForUserAndObject(
            $this->obj_id,
            $this->obj_type,
            $this->sub_obj_id,
            $this->sub_obj_type,
            $this->getUserId(),
            $tags
        );

        $this->mess = "mod";

        $this->getHTML();
    }
}
