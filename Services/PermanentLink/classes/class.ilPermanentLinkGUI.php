<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesPermanentLink Services/PermanentLink
 */

/**
* Class for permanent links
*
* @version $Id$
*
* @ilCtrl_Calls ilPermanentLinkGUI: ilNoteGUI, ilColumnGUI, ilPublicUserProfileGUI
*
* @ingroup ServicesPermanentLink
*/
class ilPermanentLinkGUI
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjectDataCache
     */
    protected $obj_data_cache;

    protected $align_center = true;
    
    /**
    * Example: type = "wiki", id (ref_id) = "234", append = "_Start_Page"
    */
    public function __construct($a_type, $a_id, $a_append = "", $a_target = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->setType($a_type);
        $this->setId($a_id);
        $this->setAppend($a_append);
        $this->setIncludePermanentLinkText(true);
        $this->setTarget($a_target);
    }
    
    /**
    * Set Include permanent link text.
    *
    * @param	boolean	$a_includepermanentlinktext	Include permanent link text
    */
    public function setIncludePermanentLinkText($a_includepermanentlinktext)
    {
        $this->includepermanentlinktext = $a_includepermanentlinktext;
    }

    /**
    * Get Include permanent link text.
    *
    * @return	boolean	Include permanent link text
    */
    public function getIncludePermanentLinkText()
    {
        return $this->includepermanentlinktext;
    }

    /**
    * Set Type.
    *
    * @param	string	$a_type	Type
    */
    public function setType($a_type)
    {
        $this->type = $a_type;
    }

    /**
    * Get Type.
    *
    * @return	string	Type
    */
    public function getType()
    {
        return $this->type;
    }

    /**
    * Set Id.
    *
    * @param	string	$a_id	Id
    */
    public function setId($a_id)
    {
        $this->id = $a_id;
    }

    /**
    * Get Id.
    *
    * @return	string	Id
    */
    public function getId()
    {
        return $this->id;
    }

    /**
    * Set Append.
    *
    * @param	string	$a_append	Append
    */
    public function setAppend($a_append)
    {
        $this->append = $a_append;
    }

    /**
    * Get Append.
    *
    * @return	string	Append
    */
    public function getAppend()
    {
        return $this->append;
    }

    /**
    * Set Target.
    *
    * @param	string	$a_target	Target
    */
    public function setTarget($a_target)
    {
        $this->target = $a_target;
    }

    /**
    * Get Target.
    *
    * @return	string	Target
    */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set title
     *
     * @param	string	title
     */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }
    
    /**
     * Get title
     *
     * @return	string	title
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set center alignment
     *
     * @param	boolean	align the link at center
     */
    public function setAlignCenter($a_val)
    {
        $this->align_center = $a_val;
    }
    
    /**
     * Get center alignment
     *
     * @return	boolean	align the link at center
     */
    public function getAlignCenter()
    {
        return $this->align_center;
    }

    /**
    * Get HTML for link
    */
    public function getHTML()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilObjDataCache = $this->obj_data_cache;
        
        $tpl = new ilTemplate(
            "tpl.permanent_link.html",
            true,
            true,
            "Services/PermanentLink"
        );
        
        include_once('./Services/Link/classes/class.ilLink.php');
        $href = ilLink::_getStaticLink(
            $this->getId(),
            $this->getType(),
            true,
            $this->getAppend()
        );
        if ($this->getIncludePermanentLinkText()) {
            $tpl->setVariable("TXT_PERMA", $lng->txt("perma_link") . ":");
        }

        $title = '';
        
        // fetch default title for bookmark

        if ($this->getTitle() != "") {
            $title = $this->getTitle();
        } elseif (is_numeric($this->getId())) {
            $obj_id = $ilObjDataCache->lookupObjId($this->getId());
            $title = $ilObjDataCache->lookupTitle($obj_id);
        }
        #if (!$title)
        #	$bookmark->setTitle("untitled");

        $tpl->setVariable("TXT_BOOKMARK_DEFAULT", $title);

        $tpl->setVariable("LINK", $href);
        
        if ($this->getAlignCenter()) {
            $tpl->setVariable("ALIGN", "center");
        } else {
            $tpl->setVariable("ALIGN", "left");
        }
        
        if ($this->getTarget() != "") {
            $tpl->setVariable("TARGET", 'target="' . $this->getTarget() . '"');
        }

        $bm_html = self::getBookmarksSelectionList($title, $href);
        if ($bm_html) {
            $tpl->setVariable('SELECTION_LIST', $bm_html);
        }

        return $tpl->get();
    }
    
    /**
     * @return string
     */
    protected static function getBookmarksSelectionList($title, $href)
    {
        require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

        $current_selection_list = new ilAdvancedSelectionListGUI();
        $random = new \ilRandom();
        $current_selection_list->setId('socialbm_actions_' . md5(uniqid($random->int(), true)));

        $html = '';

        if (!$GLOBALS['DIC']['ilUser']->isAnonymous() && !$GLOBALS['DIC']['ilSetting']->get('disable_bookmarks')) {
            $GLOBALS['DIC']->ctrl()->setParameterByClass(
                'ilbookmarkadministrationgui',
                'bmf_id',
                1
            );
            $GLOBALS['DIC']->ctrl()->setParameterByClass(
                'ilbookmarkadministrationgui',
                'return_to',
                'true'
            );
            $GLOBALS['DIC']->ctrl()->setParameterByClass(
                'ilbookmarkadministrationgui',
                'bm_title',
                urlencode($title)
            );
            $GLOBALS['DIC']->ctrl()->setParameterByClass(
                'ilbookmarkadministrationgui',
                'bm_link',
                urlencode($href)
            );
            $GLOBALS['DIC']->ctrl()->setParameterByClass(
                'ilbookmarkadministrationgui',
                'return_to_url',
                urlencode($_SERVER['REQUEST_URI'])
            );
            $link = $GLOBALS['DIC']->ctrl()->getLinkTargetByClass(
                ['ilPersonalDesktopGUI', 'ilbookmarkadministrationgui'],
                'newFormBookmark'
            );
            $current_selection_list->addItem($GLOBALS['DIC']['lng']->txt("bm_add_to_ilias"), '', $link, '', $GLOBALS['DIC']['lng']->txt('bm_add_to_ilias'), '_top');
            $html = $current_selection_list->getHTML();
        }

        return $html;
    }
}
