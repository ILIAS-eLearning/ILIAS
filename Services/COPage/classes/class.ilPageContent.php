<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Content object of ilPageObject (see ILIAS DTD). Every concrete object
 * should be an instance of a class derived from ilPageContent (e.g. ilParagraph,
 * ilMediaObject, ...)
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilPageContent
{
    protected string $pcid;
    protected string $type = "";
    protected ilPageObject $pg_obj;
    public string $hier_id = "";
    public ?php4DOMElement $node = null;
    public ?php4DOMDocument $dom = null;
    public string $page_lang = "";
    // needed for post processing (e.g. content includes)
    protected string $file_download_link;
    // needed for post processing (e.g. content includes)
    protected string $fullscreen_link;
    // needed for post processing (e.g. content includes)
    protected string $sourcecode_download_script;
    protected ilLogger $log;

    final public function __construct(ilPageObject $a_pg_obj)
    {
        $this->log = ilLoggerFactory::getLogger('copg');
        $this->setPage($a_pg_obj);
        $this->dom = $a_pg_obj->getDom();
        $this->init();
        if ($this->getType() == "") {
            die("Error: ilPageContent::init() did not set type");
        }
    }
    
    public function setPage(ilPageObject $a_val) : void
    {
        $this->pg_obj = $a_val;
    }
    
    public function getPage() : ilPageObject
    {
        return $this->pg_obj;
    }
    
    /**
     * Init object. This function must be overwritten and at least set
     * the content type.
     */
    abstract public function init() : void;

    /**
     * Set Type. Must be called in constructor.
     * @param string $a_type type of page content component
     */
    final protected function setType(string $a_type) : void
    {
        $this->type = $a_type;
    }

    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Set xml node of page content.
     * @param	php4DOMElement $a_node node object
     */
    public function setNode(php4DOMElement $a_node) : void
    {
        $this->node = $a_node;
    }

    // Get PageContent node
    public function getNode() : ?php4DOMElement
    {
        return $this->node;
    }

    public function getJavascriptFiles(string $a_mode) : array
    {
        return array();
    }

    public function getCssFiles(string $a_mode) : array
    {
        return [];
    }

    public function getOnloadCode(string $a_mode) : array
    {
        return array();
    }

    public function setHierId(string $a_hier_id) : void
    {
        $this->hier_id = $a_hier_id;
    }

    public function getHierId() : string
    {
        return $this->hier_id;
    }
    
    // Get hierarchical id from dom
    public function lookupHierId() : string
    {
        return $this->node->get_attribute("HierId");
    }

    public function readHierId() : string
    {
        if (is_object($this->node)) {
            return $this->node->get_attribute("HierId");
        }
        return "";
    }

    public function setPcId(string $a_pcid) : void
    {
        $this->pcid = $a_pcid;
    }

    public function getPCId() : string
    {
        return $this->pcid;
    }

    public function setFileDownloadLink(string $a_download_link) : void
    {
        $this->file_download_link = $a_download_link;
    }

    public function getFileDownloadLink() : string
    {
        return $this->file_download_link;
    }

    public function setFullscreenLink(string $a_fullscreen_link) : void
    {
        $this->fullscreen_link = $a_fullscreen_link;
    }

    public function getFullscreenLink() : string
    {
        return $this->fullscreen_link;
    }

    public function setSourcecodeDownloadScript(string $script_name) : void
    {
        $this->sourcecode_download_script = $script_name;
    }

    public function getSourcecodeDownloadScript() : string
    {
        return $this->sourcecode_download_script;
    }

    public function readPCId() : string
    {
        if (is_object($this->node)) {
            return $this->node->get_attribute("PCID");
        }
        return "";
    }

    public function writePCId(string $a_pc_id) : void
    {
        if (is_object($this->node)) {
            $this->node->set_attribute("PCID", $a_pc_id);
        }
    }

    /**
     * Increases an hierarchical editing id at lowest level (last number)
     * @param string $ed_id hierarchical ID
     * @return string hierarchical ID (increased)
     */
    final public static function incEdId(string $ed_id) : string
    {
        $id = explode("_", $ed_id);
        $id[count($id) - 1]++;
        return implode("_", $id);
    }

    /**
     * Decreases an hierarchical editing id at lowest level (last number)
     * @param string $ed_id hierarchical ID
     * @return string hierarchical ID (decreased)
     */
    final public static function decEdId(string $ed_id) : string
    {
        $id = explode("_", $ed_id);
        $id[count($id) - 1]--;
        return implode("_", $id);
    }

    /**
     * Sort an array of Hier IDS in ascending order
     */
    public static function sortHierIds(array $a_array) : array
    {
        uasort($a_array, array("ilPageContent", "isGreaterHierId"));
        return $a_array;
    }
    
    /**
     * Check whether Hier ID $a is greater than Hier ID $b
     */
    public static function isGreaterHierId(string $a, string $b) : bool
    {
        $a_arr = explode("_", $a);
        $b_arr = explode("_", $b);
        for ($i = 0; $i < count($a_arr); $i++) {
            if ((int) $a_arr[$i] > (int) $b_arr[$i]) {
                return true;
            } elseif ((int) $a_arr[$i] < (int) $b_arr[$i]) {
                return false;
            }
        }
        return false;
    }
    
    /**
    * Set Enabled value for page content component.
    * @param string $value "True" | "False"
    */
    public function setEnabled(string $value) : void
    {
        if (is_object($this->node)) {
            $this->node->set_attribute("Enabled", $value);
        }
    }
     
    public function enable() : void
    {
        $this->setEnabled("True");
    }
      
    public function disable() : void
    {
        $this->setEnabled("False");
    }

    final public function isEnabled() : bool
    {
        if (is_object($this->node) && $this->node->has_attribute("Enabled")) {
            $compare = $this->node->get_attribute("Enabled");
        } else {
            $compare = "True";
        }
        
        return strcasecmp($compare, "true") == 0;
    }
    
    /**
     * Create page content node (always use this method first when adding a new element)
     */
    public function createPageContentNode(bool $a_set_this_node = true) : php4DOMElement
    {
        $node = $this->dom->create_element("PageContent");
        if ($a_set_this_node) {
            $this->node = $node;
        }
        return $node;
    }
    
    /**
     * Get lang vars needed for editing
     * @return string[] array of lang var keys
     */
    public static function getLangVars() : array
    {
        return array();
    }

    /**
     * Handle copied content. This function must, e.g. create copies of
     * objects referenced within the content (e.g. question objects)
     * @param DOMDocument $a_domdoc
     * @param bool        $a_self_ass
     * @param bool        $a_clone_mobs
     * @param int         $new_parent_id
     * @param int         $obj_copy_id
     */
    public static function handleCopiedContent(
        DOMDocument $a_domdoc,
        bool $a_self_ass = true,
        bool $a_clone_mobs = false,
        int $new_parent_id = 0,
        int $obj_copy_id = 0
    ) : void {
    }
    
    /**
     * Modify page content after xsl
     */
    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ) : string {
        return $a_output;
    }

    /**
     * After page has been updated (or created)
     */
    public static function afterPageUpdate(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        string $a_xml,
        bool $a_creation
    ) : void {
    }
    
    /**
     * Before page is being deleted
     * @param ilPageObject $a_page page object
     */
    public static function beforePageDelete(
        ilPageObject $a_page
    ) : void {
    }

    /**
     * After repository (container) copy action
     */
    public static function afterRepositoryCopy(ilPageObject $page, array $mapping, int $source_ref_id) : void
    {
    }

    /**
     * After page history entry has been created
     */
    public static function afterPageHistoryEntry(
        ilPageObject $a_page,
        DOMDocument $a_old_domdoc,
        string $a_old_xml,
        int $a_old_nr
    ) : void {
    }

    /**
     * Get model as needed for the front-end editor
     */
    public function getModel() : ?stdClass
    {
        return null;
    }
}
