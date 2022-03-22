<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Glossary definition page GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilObjectMetaDataGUI
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 */
class ilGlossaryDefPageGUI extends ilPageObjectGUI
{
    /**
     * @var ilObjGlossary
     */
    protected $glossary;
    
    /**
    * Constructor
    */
    public function __construct($a_id = 0, $a_old_nr = 0)
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $tpl = $DIC["tpl"];

        parent::__construct("gdf", $a_id, $a_old_nr);
    }
    
    /**
     * Set glossary
     *
     * @param ilObjGlossary $a_val glossary
     */
    public function setGlossary($a_val)
    {
        $this->glossary = $a_val;
    }
    
    /**
     * Get glossary
     *
     * @return ilObjGlossary glossary
     */
    public function getGlossary()
    {
        return $this->glossary;
    }

    /**
     * Output metadata
     */
    public function postOutputProcessing($a_output)
    {
        if ($this->getOutputMode() == "print" && $this->glossary instanceof ilObjGlossary) {
            $term_id = ilGlossaryDefinition::_lookupTermId($this->getId());
            $mdgui = new ilObjectMetaDataGUI($this->glossary, "term", $term_id);
            $md = $mdgui->getKeyValueList();
            if ($md != "") {
                $a_output = str_replace("<!--COPage-PageTop-->", "<p>" . $md . "</p>", $a_output);
            }
        }

        return $a_output;
    }

    public function finishEditing()
    {
        $this->ctrl->redirectByClass("ilObjGlossaryGUI", "listTerms");
    }
}
