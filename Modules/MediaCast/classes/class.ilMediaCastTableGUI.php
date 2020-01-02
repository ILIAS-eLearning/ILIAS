<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for table NewsForContext
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesNews
*/
class ilMediaCastTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    protected $downloadable = false;
    protected $edit_order;
    
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd = "",
        $a_edit_order = false,
        $a_presentation_mode = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->edit_order = (bool) $a_edit_order;
        $this->presentation_mode = (bool) $a_presentation_mode;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        // Check whether download-buttons will be displayed
        $mediacast = new ilObjMediaCast($a_parent_obj->id);
        $this->downloadable = $mediacast->getDownloadable();

        if (!$this->presentation_mode) {
            $this->addColumn("", "", "1");
        }
        $this->addColumn($lng->txt("title"));
        $this->addColumn($lng->txt("properties"));
        if (!$this->edit_order) {
            $this->addColumn($lng->txt("mcst_play"), "", "320px");
        }
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.table_media_cast_row.html",
            "Modules/MediaCast"
        );
        
        $this->setShowRowsSelector(true);

        // this messes up the db ordering, where the id is also taken into
        // account, if the creation date is the same (this happens e.g. on import)
//		$this->setDefaultOrderField("creation_date");
//		$this->setDefaultOrderDirection("desc");
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        
        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");

        if ($this->presentation_mode) {
            $ilCtrl->setParameterByClass("ilobjmediacastgui", "presentation", "1");
        }
        
        // access
        if ($enable_internal_rss && !$this->presentation_mode) {
            $this->tpl->setCurrentBlock("access");
            $this->tpl->setVariable("TXT_ACCESS", $lng->txt("news_news_item_visibility"));
            if ($a_set["visibility"] == NEWS_PUBLIC) {
                $this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_public"));
            } else {
                $this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_users"));
            }
            $this->tpl->parseCurrentBlock();
        }
        
        $ilCtrl->setParameterByClass("ilobjmediacastgui", "item_id", "");

        if (ilObject::_exists($a_set["mob_id"])) {
            if ($a_set["update_date"] != "") {
                $this->tpl->setCurrentBlock("last_update");
                $this->tpl->setVariable(
                    "TXT_LAST_UPDATE",
                    $lng->txt("last_update")
                );
                $this->tpl->setVariable(
                    "VAL_LAST_UPDATE",
                    ilDatePresentation::formatDate(new ilDateTime($a_set["update_date"], IL_CAL_DATETIME))
                );
                $this->tpl->parseCurrentBlock();
            }
            
            $mob = new ilObjMediaObject($a_set["mob_id"]);
            $med = $mob->getMediaItem("Standard");
            
            $this->tpl->setVariable(
                "VAL_TITLE",
                $a_set["title"]
            );
            $this->tpl->setVariable(
                "VAL_DESCRIPTION",
                $a_set["content"]
            );
            $this->tpl->setVariable(
                "TXT_CREATED",
                $lng->txt("created")
            );
            $this->tpl->setVariable(
                "VAL_CREATED",
                ilDatePresentation::formatDate(new ilDateTime($a_set["creation_date"], IL_CAL_DATETIME))
            );
            
            $this->tpl->setVariable(
                "TXT_DURATION",
                $lng->txt("mcst_play_time")
            );
            
            if ($a_set["playtime"] != "00:00:00") {
                $this->tpl->setVariable(
                    "VAL_DURATION",
                    $a_set["playtime"]
                );
            } else {
                $this->tpl->setVariable("VAL_DURATION", "-");
            }

            if (!$this->edit_order) {
                if ($this->downloadable) {
                    $ilCtrl->setParameterByClass("ilobjmediacastgui", "item_id", $a_set["id"]);
                    // to keep always the order of the purposes
                    // iterate through purposes and display the according mediaitems
                    foreach (ilObjMediaCast::$purposes as $purpose) {
                        $a_mob = $mob->getMediaItem($purpose);
                        if (!is_object($a_mob)) {
                            continue;
                        }
                        $ilCtrl->setParameterByClass("ilobjmediacastgui", "purpose", $a_mob->getPurpose());
                        $file = ilObjMediaObject::_lookupItemPath($a_mob->getMobId(), false, false, $a_mob->getPurpose());
                        if (is_file($file)) {
                            $size = filesize($file);
                            $size = ", " . sprintf("%.1f MB", $size/1024/1024);
                        }
                        $format = ($a_mob->getFormat()!= "")?$a_mob->getFormat():"audio/mpeg";
                        $this->tpl->setCurrentBlock("downloadable");
                        $this->tpl->setVariable("TXT_DOWNLOAD", $lng->txt("mcst_download_" . strtolower($a_mob->getPurpose())));
                        $this->tpl->setVariable("CMD_DOWNLOAD", $ilCtrl->getLinkTargetByClass("ilobjmediacastgui", "downloadItem"));
                        $this->tpl->setVariable("TITLE_DOWNLOAD", "(" . $format . $size . ")");
                        $this->tpl->parseCurrentBlock();
                    }
                }
                
                include_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");
                
                // the news id will be used as player id, see also ilObjMediaCastGUI
                $event_url =  ($this->presentation_mode)
                    ? $ilCtrl->getLinkTarget($this->parent_obj, "handlePlayerEvent", "", true, false)
                    : "";
                $mpl = new ilMediaPlayerGUI($a_set["id"], $event_url);
                if (is_object($med)) {
                    require_once('./Services/WebAccessChecker/classes/class.ilWACSignedPath.php');
                    if (strcasecmp("Reference", $med->getLocationType()) == 0) {
                        $a_file = $med->getLocation();
                    } else {
                        $a_file = ilObjMediaObject::_getURL($mob->getId()) . "/" . $med->getLocation();
                    }
                    $mpl->setFile(ilWACSignedPath::signFile($a_file));
                    $mpl->setMimeType($med->getFormat());
                    //$mpl->setDisplayHeight($med->getHeight());
                    $mpl->setDisplayWidth("640");
                    $mpl->setDisplayHeight("480");
                    $mpl->setVideoPreviewPic(ilWACSignedPath::signFile($mob->getVideoPreviewPic()));
                    $mpl->setTitle($a_set["title"]);
                    $mpl->setDescription($a_set["content"]);
                    
                    $med_alt = $mob->getMediaItem("VideoAlternative");
                    if (is_object($med_alt)) {
                        $a_val = ilObjMediaObject::_getURL($mob->getId()) . "/" . $med_alt->getLocation();
                        $mpl->setAlternativeVideoFile(ilWACSignedPath::signFile($a_val));
                        $mpl->setAlternativeVideoMimeType($med_alt->getFormat());
                    }
                }

                //$this->tpl->setVariable("PLAYER", $mpl->getMp3PlayerHtml());
                $this->tpl->setVariable("PLAYER", $mpl->getPreviewHtml());

                // edit link
                $ilCtrl->setParameterByClass("ilobjmediacastgui", "item_id", $a_set["id"]);
                if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]) &&
                    !$this->presentation_mode) {
                    $this->tpl->setCurrentBlock("edit");
                    $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
                    $this->tpl->setVariable(
                        "CMD_EDIT",
                        $ilCtrl->getLinkTargetByClass("ilobjmediacastgui", "editCastItem")
                    );
                    
                    if (!is_int(strpos($med->getFormat(), "image/"))) {
                        $this->tpl->setVariable("TXT_DET_PLAYTIME", $lng->txt("mcst_det_playtime"));
                        $this->tpl->setVariable(
                            "CMD_DET_PLAYTIME",
                            $ilCtrl->getLinkTargetByClass("ilobjmediacastgui", "determinePlaytime")
                        );
                    }
                    $this->tpl->parseCurrentBlock();
                    
                    $this->tpl->setCurrentBlock("edit_checkbox");
                    $this->tpl->setVariable("VAL_ID", $a_set["id"]);
                    $this->tpl->parseCurrentBlock();
                    //					$this->tpl->touchBlock("contrl_col");
                }
            } else {
                $this->tpl->setCurrentBlock("edit_order");
                $this->tpl->setVariable("VAL_ID", $a_set["id"]);
                $this->tpl->setVariable("VAL_ORDER", $a_set["order"]);
                $this->tpl->parseCurrentBlock();
                //				$this->tpl->touchBlock("contrl_col");
            }
            
            // download and play counter
            if (!$this->presentation_mode) {
                if ($a_set["mob_cnt_download"] > 0) {
                    $this->tpl->setCurrentBlock("prop");
                    $this->tpl->setVariable("TXT_PROP", $lng->txt("mcst_download_cnt"));
                    $this->tpl->setVariable("VAL_PROP", $a_set["mob_cnt_download"]);
                    $this->tpl->parseCurrentBlock();
                }
                if ($a_set["mob_cnt_play"] > 0) {
                    $this->tpl->setCurrentBlock("prop");
                    $this->tpl->setVariable("TXT_PROP", $lng->txt("mcst_play_cnt"));
                    $this->tpl->setVariable("VAL_PROP", $a_set["mob_cnt_play"]);
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
    }
}
