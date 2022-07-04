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

namespace ILIAS\LearningModule\Editing;

use ILIAS\Repository;

class EditingGUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    public function getObjId() : int
    {
        return $this->int("obj_id");
    }

    public function getActiveNode() : int
    {
        return $this->int("active_node");
    }

    public function getToPage() : bool
    {
        return (bool) $this->int("to_page");
    }

    public function getToProps() : bool
    {
        return (bool) $this->int("to_props");
    }

    public function getRootId() : int
    {
        return $this->int("root_id");
    }

    public function getGlossaryId() : int
    {
        return $this->int("glo_id");
    }

    public function getGlossaryRefId() : int
    {
        return $this->int("glo_ref_id");
    }

    public function getMenuEntry() : int
    {
        return $this->int("menu_entry");
    }

    /** @return int[] */
    public function getMenuEntries() : array
    {
        return $this->intArray("menu_entries");
    }

    public function getLMMenuExpand() : int
    {
        return $this->int("lm_menu_expand");
    }

    public function getLMExpand() : int
    {
        return $this->int("lmexpand");
    }

    public function getSearchRootExpand() : int
    {
        return $this->int("search_root_expand");
    }

    public function getNewType() : string
    {
        return $this->str("new_type");
    }

    public function getBaseClass() : string
    {
        return $this->str("baseClass");
    }

    public function getTranslation() : string
    {
        return $this->str("transl");
    }

    public function getToTranslation() : string
    {
        return $this->str("totransl");
    }


    public function getBackCmd() : string
    {
        return $this->str("backcmd");
    }

    public function getHierarchy() : bool
    {
        return (bool) $this->int("hierarchy");
    }

    public function getLangSwitchMode() : string
    {
        return $this->str("lang_switch_mode");
    }

    public function getLinkRefId() : int
    {
        return $this->int("link_ref_id");
    }

    public function getLMMoveCopy() : bool
    {
        return (bool) $this->int("lmmovecopy");
    }

    public function getTarget() : string
    {
        return $this->str("target");
    }

    public function getFileId() : string
    {
        return $this->str("file_id");
    }

    public function getStyleId() : int
    {
        return $this->int("style_id");
    }

    /** @return int[] */
    public function getIds() : array
    {
        return $this->intArray("id");
    }

    public function getLayout() : string
    {
        return $this->str("layout");
    }

    /** @return string[] */
    public function getTitles() : array
    {
        return $this->strArray("title");
    }

    public function getFormat() : string
    {
        return $this->str("format");
    }

    /** @return string[] */
    public function getUserQuestionIds() : array
    {
        return $this->strArray("userquest_id");
    }

    public function getLMPublicMode() : string
    {
        return $this->str("lm_public_mode");
    }

    /** @return int[] */
    public function getPublicPages() : array
    {
        return $this->intArray("pages");
    }

    public function getHFormPar(string $par) : string
    {
        return $this->str("il_hform_" . $par);
    }

    public function getHelpChap() : string
    {
        return $this->str("help_chap");
    }

    /** @return string[] */
    public function getExportIds() : array
    {
        return $this->strArray("exportid");
    }

    /** @return string[] */
    public function getScreenIds() : array
    {
        return $this->strArray("screen_ids");
    }

    public function getTooltipId() : string
    {
        return $this->str("tooltip_id");
    }

    /** @return string[] */
    public function getTooltipIds() : array
    {
        return $this->strArray("tt_id");
    }

    public function getTooltipComponent() : string
    {
        return $this->str("help_tt_comp");
    }

    /** @return string[] */
    public function getTooltipTexts() : array
    {
        return $this->strArray("text");
    }

    /** @return string[] */
    public function getShortTitles() : array
    {
        return $this->strArray("short_title");
    }

    public function getImportLang() : string
    {
        return $this->str("import_lang");
    }

    public function getNodeId() : int
    {
        return $this->int("node_id");
    }

    public function getMulti() : int
    {
        return $this->int("multi");
    }

    public function getFirstChild() : bool
    {
        return (bool) $this->int("first_child");
    }
}
