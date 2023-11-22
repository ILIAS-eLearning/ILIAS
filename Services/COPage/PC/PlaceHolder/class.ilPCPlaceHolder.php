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
 * Class ilPlaceHolder
 *
 * List content object (see ILIAS DTD)
 */
class ilPCPlaceHolder extends ilPageContent
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    public string $content_class;
    public string $height;

    public function init(): void
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->setType("plach");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ): void {
        $this->createInitialChildNode($a_hier_id, $a_pc_id, "PlaceHolder");
    }

    public function setContentClass(string $a_class): void
    {
        if (is_object($this->getChildNode())) {
            $this->getChildNode()->setAttribute("ContentClass", $a_class);
        }
    }

    public function getContentClass(): string
    {
        if (is_object($this->getChildNode())) {
            return $this->getChildNode()->getAttribute("ContentClass");
        }
        return "";
    }

    public function setHeight(string $a_height): void
    {
        if (is_object($this->getChildNode())) {
            $this->getChildNode()->setAttribute("Height", $a_height);
        }
    }

    public function getHeight(): string
    {
        if (is_object($this->getChildNode())) {
            return $this->getChildNode()->getAttribute("Height");
        }
        return "";
    }

    public function getClass(): string
    {
        return "";
    }

    public static function getLangVars(): array
    {
        return array("question_placeh","media_placeh","text_placeh",
            "ed_insert_plach","question_placehl","media_placehl","text_placehl",
            "verification_placeh", "verification_placehl");
    }

    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ): string {
        $lng = $this->lng;

        //
        // Note: this standard output is "overwritten", e.g. by ilPortfolioPageGUI::postOutputProcessing
        //

        $end = 0;
        $start = strpos($a_output, "{{{{{PlaceHolder#");
        if (is_int($start)) {
            $end = strpos($a_output, "}}}}}", $start);
        }
        $i = 1;
        while ($end > 0) {
            $param = substr($a_output, $start + 17, $end - $start - 17);
            $param = explode("#", $param);

            $html = $param[2];
            switch ($param[2]) {
                case "Text":
                    $html = $lng->txt("cont_text_placeh");
                    break;

                case "Media":
                    $html = $lng->txt("cont_media_placeh");
                    break;

                case "Question":
                    $html = $lng->txt("cont_question_placeh");
                    break;

                case "Verification":
                    $html = $lng->txt("cont_verification_placeh");
                    break;
            }

            $h2 = substr($a_output, 0, $start) .
                $html .
                substr($a_output, $end + 5);
            $a_output = $h2;
            $i++;

            $start = strpos($a_output, "{{{{{PlaceHolder#", $start + 5);
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_output, "}}}}}", $start);
            }
        }
        return $a_output;
    }

    /**
     * @inheritDoc
     */
    public function getModel(): ?stdClass
    {
        $model = new \stdClass();
        $model->contentClass = $this->getContentClass();
        return $model;
    }

    public function getCssFiles(string $a_mode): array
    {
        return [];
    }

    public static function handleCopiedContent(
        DOMDocument $a_domdoc,
        bool $a_self_ass = true,
        bool $a_clone_mobs = false,
        int $new_parent_id = 0,
        int $obj_copy_id = 0
    ): void {
        // remove question placholders
        if (!$a_self_ass) {
            // Get question IDs
            $path = "//PlaceHolder[@ContentClass = 'Question']";
            $xpath = new DOMXPath($a_domdoc);
            $nodes = $xpath->query($path);

            foreach ($nodes as $node) {
                $parent = $node->parentNode;
                $parent->parentNode->removeChild($parent);
            }
        }
    }
}
