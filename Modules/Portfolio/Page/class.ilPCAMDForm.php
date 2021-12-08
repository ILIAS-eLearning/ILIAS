<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * AMD Form Page element
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCAMDForm extends ilPageContent
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public $dom;

    /**
     * Init page content component.
     */
    public function init()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->setType("amdfrm");

        $this->ref_id = (int) $_GET["ref_id"];
    }

    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array("ed_insert_amdfrm", "pc_amdfrm");
    }

    /**
     * Set node
     */
    public function setNode($a_node)
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->amdfrm_node = $a_node->first_child();		// this is the courses node
    }

    /**
     * Is template
     * @return bool
     */
    protected function isTemplate() : bool
    {
        return ($this->getPage()->getParentType() == "prtt");
    }

    /**
     * Create list node in xml.
     *
     * @param	object	$a_pg_obj		Page Object
     * @param	string	$a_hier_id		Hierarchical ID
     */
    public function create($a_pg_obj, $a_hier_id, $a_pc_id = "")
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->amdfrm_node = $this->dom->create_element("AMDForm");
        $this->amdfrm_node = $this->node->append_child($this->amdfrm_node);
    }

    public function setRecordIds(array $record_ids)
    {
        $this->amdfrm_node->set_attribute("RecordIds", implode(",", $record_ids));
    }

    public function getRecordIds() : array
    {
        if (is_object($this->amdfrm_node)) {
            return explode(",", $this->amdfrm_node->get_attribute("RecordIds"));
        }
    }


    /**
     * @inheritDoc
     */
    public function modifyPageContentPostXsl($a_html, $a_mode, $a_abstract_only = false)
    {
        $c_pos = 0;
        $start = strpos($a_html, "[[[[[AMDForm;");
        if (is_int($start)) {
            $end = strpos($a_html, "]]]]]", $start);
        }
        $i = 1;
        while ($end > 0) {
            $parts = explode(";", substr($a_html, $start + 13, $end - $start - 13));
            if ($this->isTemplate()) {
                $portfolio = new ilObjPortfolioTemplate($this->getPage()->getPortfolioId(), false);
            } else {
                $portfolio = new ilObjPortfolio($this->getPage()->getPortfolioId(), false);
            }

            $mdgui = new ilObjectMetaDataGUI($portfolio, "pfpg", $this->getPage()->getId(), false);
            $mdgui->setRecordFilter(explode(",", $parts[0]));
            $insert_html = $mdgui->getBlockHTML();

            $a_html = substr($a_html, 0, $start) .
                $insert_html .
                substr($a_html, $end + 5);

            $start = strpos($a_html, "[[[[[AMDForm;", $start + 5);
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_html, "]]]]]", $start);
            }
        }

        return $a_html;
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
                    $new_ids = array_map(function ($i) use ($mappings, $key) {
                        return $mappings[$key][(int) $i];
                    }, $old_ids);
                    $new_ids = implode(",", $new_ids);
                    if ($new_ids != "") {
                        $node->setAttribute("RecordIds", $new_ids);
                    }
                }
            }
        }
    }
}
