<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilPageContent
*
* Content object of ilPageObject (see ILIAS DTD). Every concrete object
* should be an instance of a class derived from ilPageContent (e.g. ilParagraph,
* ilMediaObject, ...)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
abstract class ilPageContent
{
    //var $type;		// type
    public $hier_id; 		// hierarchical editing id
    public $node;			// node in page xml
    public $dom;			// dom object
    public $page_lang;

    /**
     * @var string needed for post processing (e.g. content includes)
     */
    protected $file_download_link;

    /**
     * @var string needed for post processing (e.g. content includes)
     */
    protected $fullscreen_link;

    /**
     * @var string needed for post processing (e.g. content includes)
     */
    protected $sourcecode_download_script;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
    * Constructor.
    *
    * All initialisation in derived classes should go to the
    * init() function
    */
    final public function __construct($a_pg_obj)
    {
        $this->log = ilLoggerFactory::getLogger('copg');
        $this->setPage($a_pg_obj);
        $this->dom = $a_pg_obj->getDom();
        $this->init();
        if ($this->getType() == "") {
            die("Error: ilPageContent::init() did not set type");
        }
    }
    
    /**
     * Set page
     *
     * @param object $a_val page object
     */
    public function setPage($a_val)
    {
        $this->pg_obj = $a_val;
    }
    
    /**
     * Get page
     *
     * @return object page object
     */
    public function getPage()
    {
        return $this->pg_obj;
    }
    
    /**
    * Init object. This function must be overwritten and at least set
    * the content type.
    */
    abstract public function init();

    /**
    * Set Type. Must be called in constructor.
    *
    * @param	string	$a_type		type of page content component
    */
    final protected function setType($a_type)
    {
        $this->type = $a_type;
    }

    /**
    * Get type of page content
    *
    * @return	string		Type as defined by the page content component
    */
    public function getType()
    {
        return $this->type;
    }

    /**
    * Set xml node of page content.
    *
    * @param	object	$a_node		node object
    */
    public function setNode($a_node)
    {
        $this->node = $a_node;
    }
    

    /**
    * Get xml node of page content.
    *
    * @return	object				node object
    */
    public function &getNode()
    {
        return $this->node;
    }

    /**
     * Get Javascript files
     */
    public function getJavascriptFiles($a_mode)
    {
        return array();
    }

    /**
     * Get css files
     */
    public function getCssFiles($a_mode)
    {
        return array();
    }

    /**
     * Get on load code
     */
    public function getOnloadCode($a_mode)
    {
        return array();
    }

    /**
    * Set hierarchical ID in xml structure
    *
    * @param	string		$a_hier_id		Hierarchical ID.
    */
    public function setHierId($a_hier_id)
    {
        $this->hier_id = $a_hier_id;
    }

    /**
    * Get hierarchical id
    */
    public function getHierId()
    {
        return $this->hier_id;
    }
    
    
    /**
    * Get hierarchical id from dom
    */
    public function lookupHierId()
    {
        return $this->node->get_attribute("HierId");
    }

    /**
    * Read PC Id.
    *
    * @return	string	PC Id
    */
    public function readHierId()
    {
        if (is_object($this->node)) {
            return $this->node->get_attribute("HierId");
        }
    }

    /**
    * Set PC Id.
    *
    * @param	string	$a_pcid	PC Id
    */
    public function setPcId($a_pcid)
    {
        $this->pcid = $a_pcid;
    }

    /**
    * Get PC Id.
    *
    * @return	string	PC Id
    */
    public function getPCId()
    {
        return $this->pcid;
    }

    /**
     * Set file download link
     *
     * @param string $a_download_link download link
     */
    public function setFileDownloadLink($a_download_link)
    {
        $this->file_download_link = $a_download_link;
    }

    /**
     * Get file download link
     *
     * @return string
     */
    public function getFileDownloadLink()
    {
        return $this->file_download_link;
    }

    /**
     * Set fullscreen link
     *
     * @param string $a_download_link download link
     */
    public function setFullscreenLink($a_fullscreen_link)
    {
        $this->fullscreen_link = $a_fullscreen_link;
    }

    /**
     * Get fullscreen link
     *
     * @return string
     */
    public function getFullscreenLink()
    {
        return $this->fullscreen_link;
    }

    /**
     * Set sourcecode download script
     *
     * @param string $script_name
     */
    public function setSourcecodeDownloadScript($script_name)
    {
        $this->sourcecode_download_script = $script_name;
    }

    /**
     * Get sourcecode download script
     *
     * @return string
     */
    public function getSourcecodeDownloadScript()
    {
        return $this->sourcecode_download_script;
    }


    /**
    * Read PC Id.
    *
    * @return	string	PC Id
    */
    public function readPCId()
    {
        if (is_object($this->node)) {
            return $this->node->get_attribute("PCID");
        }
    }

    /**
     * Write pc id
     */
    public function writePCId($a_pc_id)
    {
        if (is_object($this->node)) {
            $this->node->set_attribute("PCID", $a_pc_id);
        }
    }

    /**
    * Increases an hierarchical editing id at lowest level (last number)
    *
    * @param	string	$ed_id		hierarchical ID
    *
    * @return	string				hierarchical ID (increased)
    */
    final public static function incEdId($ed_id)
    {
        $id = explode("_", $ed_id);
        $id[count($id) - 1]++;
        
        return implode($id, "_");
    }

    /**
    * Decreases an hierarchical editing id at lowest level (last number)
    *
    * @param	string	$ed_id		hierarchical ID
    *
    * @return	string				hierarchical ID (decreased)
    */
    final public static function decEdId($ed_id)
    {
        $id = explode("_", $ed_id);
        $id[count($id) - 1]--;

        return implode($id, "_");
    }

    /**
    * Check, if two ids are in same container.
    *
    * @param	string	$ed_id1		hierachical ID 1
    * @param	string	$ed_id2		hierachical ID 2
    *
    * @return	boolean				true/false
    */
    final public static function haveSameContainer($ed_id1, $ed_id2)
    {
        $id1 = explode("_", $ed_id1);
        $id2 = explode("_", $ed_id1);
        if (count($id1) == count($id2)) {
            array_pop($id1);
            array_pop($id2);
            foreach ($id1 as $key => $id) {
                if ($id != $id2[$key]) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
    * Sort an array of Hier IDS in ascending order
    */
    public static function sortHierIds($a_array)
    {
        uasort($a_array, array("ilPageContent", "isGreaterHierId"));
        
        return $a_array;
    }
    
    /**
    * Check whether Hier ID $a is greater than Hier ID $b
    */
    public static function isGreaterHierId($a, $b)
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
    *
    * @param	string	$value		"True" | "False"
    *
    */
    public function setEnabled($value)
    {
        if (is_object($this->node)) {
            $this->node->set_attribute("Enabled", $value);
        }
    }
     
    /**
    * Enable page content.
    */
    public function enable()
    {
        $this->setEnabled("True");
    }
      
    /**
    * Disable page content.
    */
    public function disable()
    {
        $this->setEnabled("False");
    }

    /**
    * Check whether page content is enabled.
    *
    * @return	boolean			true/false
    */
    final public function isEnabled()
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
    public function createPageContentNode($a_set_this_node = true)
    {
        $node = $this->dom->create_element("PageContent");
        if ($a_set_this_node) {
            $this->node = $node;
        }
        return $node;
    }
    
    /**
     * Get lang vars needed for editing
     * @return array array of lang var keys
     */
    public static function getLangVars()
    {
        return array();
    }

    /**
     * Handle copied content. This function must, e.g. create copies of
     * objects referenced within the content (e.g. question objects)
     *
     * @param DOMDocument $a_domdoc dom document
     */
    public static function handleCopiedContent(DOMDocument $a_domdoc, $a_self_ass = true, $a_clone_mobs = false)
    {
    }
    
    /**
     * Modify page content after xsl
     *
     * @param string $a_output
     * @return string
     */
    public function modifyPageContentPostXsl($a_output, $a_mode)
    {
        return $a_output;
    }

    /**
     * After page has been updated (or created)
     *
     * @param object $a_page page object
     * @param DOMDocument $a_domdoc dom document
     * @param string $a_xml xml
     * @param bool $a_creation true on creation, otherwise false
     */
    public static function afterPageUpdate($a_page, DOMDocument $a_domdoc, $a_xml, $a_creation)
    {
    }
    
    /**
     * Before page is being deleted
     *
     * @param object $a_page page object
     */
    public static function beforePageDelete($a_page)
    {
    }

    /**
     * After page history entry has been created
     *
     * @param object $a_page page object
     * @param DOMDocument $a_old_domdoc old dom document
     * @param string $a_old_xml old xml
     * @param integer $a_old_nr history number
     */
    public static function afterPageHistoryEntry($a_page, DOMDocument $a_old_domdoc, $a_old_xml, $a_old_nr)
    {
    }
}
