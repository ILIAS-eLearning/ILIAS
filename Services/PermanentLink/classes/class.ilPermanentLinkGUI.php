<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class for permanent links
 *
 * @ilCtrl_Calls ilPermanentLinkGUI: ilNoteGUI, ilColumnGUI, ilPublicUserProfileGUI
 */
class ilPermanentLinkGUI
{
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilObjectDataCache $obj_data_cache;
    protected bool $align_center = true;
    protected bool $includepermanentlinktext = false;
    protected string $type = "";
    protected string $id = "";
    protected string $append = "";
    protected string $target = "";
    protected string $title = "";

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
    
    // Set Include permanent link text.
    public function setIncludePermanentLinkText(bool $a_includepermanentlinktext) : void
    {
        $this->includepermanentlinktext = $a_includepermanentlinktext;
    }

    // Include permanent link text
    public function getIncludePermanentLinkText() : bool
    {
        return $this->includepermanentlinktext;
    }

    public function setType(string $a_type) : void
    {
        $this->type = $a_type;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setId(string $a_id) : void
    {
        $this->id = $a_id;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function setAppend(string $a_append) : void
    {
        $this->append = $a_append;
    }

    public function getAppend() : string
    {
        return $this->append;
    }

    public function setTarget(string $a_target) : void
    {
        $this->target = $a_target;
    }

    public function getTarget() : string
    {
        return $this->target;
    }

    public function setTitle(string $a_val) : void
    {
        $this->title = $a_val;
    }
    
    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function getHTML() : string
    {
        $lng = $this->lng;
        $ilObjDataCache = $this->obj_data_cache;
        
        $tpl = new ilTemplate(
            "tpl.permanent_link.html",
            true,
            true,
            "Services/PermanentLink"
        );
        
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
        
        if ($this->getTitle() != "") {
            $title = $this->getTitle();
        } elseif (is_numeric($this->getId())) {
            $obj_id = $ilObjDataCache->lookupObjId($this->getId());
            $title = $ilObjDataCache->lookupTitle($obj_id);
        }

        $tpl->setVariable("TXT_BOOKMARK_DEFAULT", $title);

        $tpl->setVariable("LINK", $href);
        
        $tpl->setVariable("ALIGN", "left");

        if ($this->getTarget() != "") {
            $tpl->setVariable("TARGET", 'target="' . $this->getTarget() . '"');
        }

        return $tpl->get();
    }
}
