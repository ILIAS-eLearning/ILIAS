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
 * Class for permanent links
 * @author Alexander Killing <killing@leifos.de>
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
    public function __construct(
        string $a_type,
        int $a_id,
        string $a_append = "",
        string $a_target = ""
    ) {
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
     */
    public function setIncludePermanentLinkText(bool $a_includepermanentlinktext) : void
    {
        $this->includepermanentlinktext = $a_includepermanentlinktext;
    }
    
    /**
     * Include permanent link text
     */
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
            $obj_id = $ilObjDataCache->lookupObjId((int) $this->getId());
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
