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
 * Glossary definition page GUI class
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilObjectMetaDataGUI
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilGlossaryDefPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 */
class ilGlossaryDefPageGUI extends ilPageObjectGUI
{
    protected ilObjGlossary $glossary;
    
    public function __construct(
        int $a_id = 0,
        int $a_old_nr = 0
    ) {
        parent::__construct("gdf", $a_id, $a_old_nr);
    }
    
    public function setGlossary(ilObjGlossary $a_val) : void
    {
        $this->glossary = $a_val;
    }
    
    public function getGlossary() : ilObjGlossary
    {
        return $this->glossary;
    }

    public function postOutputProcessing(string $a_output) : string
    {
        if ($this->getOutputMode() == "print") {
            $term_id = ilGlossaryDefinition::_lookupTermId($this->getId());
            $mdgui = new ilObjectMetaDataGUI($this->glossary, "term", $term_id);
            $md = $mdgui->getKeyValueList();
            if ($md != "") {
                $a_output = str_replace("<!--COPage-PageTop-->", "<p>" . $md . "</p>", $a_output);
            }
        }

        return $a_output;
    }

    public function finishEditing() : void
    {
        $this->ctrl->redirectByClass("ilObjGlossaryGUI", "listTerms");
    }
}
