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
 * Learning history page content
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCLearningHistory extends ilPageContent
{
    protected php4DOMElement $lhist_node;
    protected ilObjUser $user;

    public function init() : void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setType("lhist");
    }

    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->lhist_node = $a_node->first_child();		// this is the skill node
    }

    /**
     * Create learning history node
     */
    public function create(ilPageObject $a_pg_obj, string $a_hier_id, string $a_pc_id = "") : void
    {
        $this->node = $this->createPageContentNode();
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->lhist_node = $this->dom->create_element("LearningHistory");
        $this->lhist_node = $this->node->append_child($this->lhist_node);
    }
    
    public function setFrom(int $a_val) : void
    {
        $this->lhist_node->set_attribute("From", (string) $a_val);
    }
    
    public function getFrom() : int
    {
        return (int) $this->lhist_node->get_attribute("From");
    }
    
    public function setTo(int $a_val) : void
    {
        $this->lhist_node->set_attribute("To", $a_val);
    }

    public function getTo() : int
    {
        return (int) $this->lhist_node->get_attribute("To");
    }

    public function setClasses(array $a_val) : void
    {
        // delete properties
        $children = $this->lhist_node->child_nodes();
        foreach ($children as $iValue) {
            $this->lhist_node->remove_child($iValue);
        }
        // set classes
        foreach ($a_val as $key => $class) {
            $prop_node = $this->dom->create_element("LearningHistoryProvider");
            $prop_node = $this->lhist_node->append_child($prop_node);
            $prop_node->set_attribute("Name", $class);
        }
    }
    
    public function getClasses() : array
    {
        $classes = [];
        // delete properties
        $children = $this->lhist_node->child_nodes();
        foreach ($children as $iValue) {
            $classes[] = $iValue->get_attribute("Name");
        }
        return $classes;
    }

    public static function afterPageUpdate(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        string $a_xml,
        bool $a_creation
    ) : void {
    }
    
    public static function beforePageDelete(
        ilPageObject $a_page
    ) : void {
    }

    /**
     * After page history entry has been created
     * @param ilPageObject $a_page       page object
     * @param DOMDocument  $a_old_domdoc old dom document
     * @param string       $a_old_xml    old xml
     * @param int          $a_old_nr     history number
     */
    public static function afterPageHistoryEntry(
        ilPageObject $a_page,
        DOMDocument $a_old_domdoc,
        string $a_old_xml,
        int $a_old_nr
    ) : void {
    }

    /**
     * Get lang vars needed for editing
     */
    public static function getLangVars() : array
    {
        return array("ed_insert_learning_history", "pc_learning_history");
    }

    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ) : string {
        $start = strpos($a_output, "{{{{{LearningHistory");
        $end = 0;
        if (is_int($start)) {
            $end = strpos($a_output, "}}}}}", $start);
        }

        while ($end > 0) {
            $param = substr($a_output, $start + 5, $end - $start - 5);
            $param = str_replace(' xmlns:xhtml="http://www.w3.org/1999/xhtml"', "", $param);
            $param = explode("#", $param);
            $from = $param[1];
            $to = $param[2];
            $classes = explode(";", $param[3]);
            $classes = array_map(static function ($i) {
                return trim($i);
            }, $classes);


            $a_output = substr($a_output, 0, $start) .
                $this->getPresentation($from, $to, $classes, $a_mode) .
                substr($a_output, $end + 5);

            if (strlen($a_output) > $start + 5) {
                $start = strpos($a_output, "{{{{{LearningHistory", $start + 5);
            } else {
                $start = false;
            }
            $end = 0;
            if (is_int($start)) {
                $end = strpos($a_output, "}}}}}", $start);
            }
        }

        return $a_output;
    }

    /**
     * Get presentation
     * @throws ilCtrlException
     * @throws ilDateTimeException
     */
    protected function getPresentation(
        string $from,
        string $to,
        array $classes,
        string $a_mode
    ) : string {
        $user_id = 0;
        if ($a_mode === "preview" || $a_mode === "presentation" || $a_mode === "print") {
            if ($this->getPage()->getParentType() === "prtf") {
                $user_id = ilObject::_lookupOwner($this->getPage()->getPortfolioId());
            }
        }
        if ($user_id > 0) {
            $tpl = new ilTemplate("tpl.pc_lhist.html", true, true, "Services/LearningHistory");
            $hist_gui = new ilLearningHistoryGUI();
            $hist_gui->setUserId($user_id);
            $from_unix = ($from != "")
                ? (new ilDateTime($from . " 00:00:00", IL_CAL_DATETIME))->get(IL_CAL_UNIX)
                : null;
            $to_unix = ($to != "")
                ? (new ilDateTime($to . " 23:59:59", IL_CAL_DATETIME))->get(IL_CAL_UNIX)
                : null;
            $classes = (is_array($classes))
                ? array_filter($classes, static function ($i) : bool {
                    return ($i != "");
                })
                : [];
            if (count($classes) === 0) {
                $classes = null;
            }
            $tpl->setVariable("LHIST", $hist_gui->getEmbeddedHTML($from_unix, $to_unix, $classes, $a_mode));
            return $tpl->get();
        }

        return ilPCLearningHistoryGUI::getPlaceholderPresentation();
    }
}
