<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/Glossary/classes/class.ilGlossaryDefPage.php");

/**
 * Glossary definition page GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilObjectMetaDataGUI
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ModulesGlossary
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
        if ($this->getOutputMode() == "print"  && $this->glossary instanceof ilObjGlossary) {
            include_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
            $term_id = ilGlossaryDefinition::_lookupTermId($this->getId());
            include_once("./Services/Object/classes/class.ilObjectMetaDataGUI.php");
            $mdgui = new ilObjectMetaDataGUI($this->glossary, "term", $term_id);
            $md = $mdgui->getKeyValueList();
            if ($md != "") {
                $a_output = str_replace("<!--COPage-PageTop-->", "<p>" . $md . "</p>", $a_output);
            }
        }

        return $a_output;
    }
}
