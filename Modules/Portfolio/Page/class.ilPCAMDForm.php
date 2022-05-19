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
 * AMD Form Page element
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCAMDForm extends ilPageContent
{
    protected php4DOMElement $amdfrm_node;
    protected int $ref_id;
    protected ilDBInterface $db;
    protected ilLanguage $lng;

    public function init() : void
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->setType("amdfrm");

        $request = $DIC->portfolio()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->ref_id = $request->getRefId();
    }

    public static function getLangVars() : array
    {
        return array("ed_insert_amdfrm", "pc_amdfrm");
    }

    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->amdfrm_node = $a_node->first_child();		// this is the courses node
    }

    protected function isTemplate() : bool
    {
        return ($this->getPage()->getParentType() === "prtt");
    }

    public function create(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) : void {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->amdfrm_node = $this->dom->create_element("AMDForm");
        $this->amdfrm_node = $this->node->append_child($this->amdfrm_node);
    }

    public function setRecordIds(array $record_ids) : void
    {
        $this->amdfrm_node->set_attribute("RecordIds", implode(",", $record_ids));
    }

    public function getRecordIds() : array
    {
        if (is_object($this->amdfrm_node)) {
            return explode(",", $this->amdfrm_node->get_attribute("RecordIds"));
        }
        return [];
    }


    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ) : string {
        $end = 0;
        $start = strpos($a_output, "[[[[[AMDForm;");
        if (is_int($start)) {
            $end = strpos($a_output, "]]]]]", $start);
        }
        while ($end > 0) {
            $parts = explode(";", substr($a_output, $start + 13, $end - $start - 13));
            if ($this->isTemplate()) {
                $portfolio = new ilObjPortfolioTemplate($this->getPage()->getPortfolioId(), false);
            } else {
                $portfolio = new ilObjPortfolio($this->getPage()->getPortfolioId(), false);
            }

            $mdgui = new ilObjectMetaDataGUI($portfolio, "pfpg", $this->getPage()->getId(), false);
            $mdgui->setRecordFilter(explode(",", $parts[0]));
            $insert_html = $mdgui->getBlockHTML();

            $a_output = substr($a_output, 0, $start) .
                $insert_html .
                substr($a_output, $end + 5);

            $start = strpos($a_output, "[[[[[AMDForm;", $start + 5);
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_output, "]]]]]", $start);
            }
        }

        return $a_output;
    }

    public static function handleCopiedContent(
        DOMDocument $a_domdoc,
        bool $a_self_ass = true,
        bool $a_clone_mobs = false,
        int $new_parent_id = 0,
        int $obj_copy_id = 0
    ) : void {
        if ($obj_copy_id > 0) {
            $cp_options = ilCopyWizardOptions::_getInstance($obj_copy_id);
            $mappings = $cp_options->getMappings();
            $key = $new_parent_id . "_adv_rec";
            if (is_array($mappings) && isset($mappings[$key])) {
                $xpath = new DOMXPath($a_domdoc);
                $nodes = $xpath->query("//AMDForm");
                foreach ($nodes as $node) {
                    $old_ids = explode(",", (string) $node->getAttribute("RecordIds"));
                    $new_ids = array_map(static function ($i) use ($mappings, $key) {
                        return $mappings[$key][(int) $i];
                    }, $old_ids);
                    $new_ids = implode(",", $new_ids);
                    if ($new_ids !== "") {
                        $node->setAttribute("RecordIds", $new_ids);
                    }
                }
            }
        }
    }
}
