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
 * User interface class for page content map editor
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPCImageMapEditorGUI: ilInternalLinkGUI
 */
class ilPCImageMapEditorGUI extends ilImageMapEditorGUI
{
    protected ilMediaAliasItem $std_alias_item;
    protected ilPageObject $page;
    /**
     * @var ilPCInteractiveImage|ilPCMediaObject
     */
    protected $content_obj;

    /**
     * @param ilPCMediaObject|ilPCInteractiveImage $a_content_obj
     */
    public function __construct(
        $a_content_obj,
        ilPageObject $a_page
    ) {
        $this->content_obj = $a_content_obj;
        $this->page = $a_page;
        parent::__construct($a_content_obj->getMediaObject());
                
        $this->std_alias_item = new ilMediaAliasItem(
            $this->content_obj->dom,
            $this->content_obj->hier_id,
            "Standard",
            $this->content_obj->getPCId(),
            $this->getParentNodeName()
        );
    }

    public function getParentNodeName() : string
    {
        return "MediaObject";
    }

    public function getImageMapTableHTML() : string
    {
        $image_map_table = new ilPCImageMapTableGUI(
            $this,
            "editMapAreas",
            $this->content_obj,
            $this->getParentNodeName()
        );
        return $image_map_table->getHTML();
    }

    /**
     * Save new or updated map area
     */
    public function saveArea() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        switch ($_SESSION["il_map_edit_mode"]) {
            // save edited link
            case "edit_link":
//				$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//					$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());

                if ($_POST["area_link_type"] == IL_INT_LINK) {
                    $this->std_alias_item->setAreaIntLink(
                        $_SESSION["il_map_area_nr"],
                        $_SESSION["il_map_il_type"],
                        $_SESSION["il_map_il_target"],
                        $_SESSION["il_map_il_targetframe"]
                    );
                } elseif ($_POST["area_link_type"] == IL_NO_LINK) {
                    $this->std_alias_item->setAreaExtLink(
                        $_SESSION["il_map_area_nr"],
                        ""
                    );
                } else {
                    $this->std_alias_item->setAreaExtLink(
                        $_SESSION["il_map_area_nr"],
                        ilUtil::stripSlashes($_POST["area_link_ext"])
                    );
                }
                $this->page->update();
                break;

            // save edited shape
            case "edit_shape":
//				$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//					$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
                $this->std_alias_item->setShape(
                    $_SESSION["il_map_area_nr"],
                    $_SESSION["il_map_edit_area_type"],
                    $_SESSION["il_map_edit_coords"]
                );
                $this->page->update();
                break;

            // save new area
            default:
                $area_type = $_SESSION["il_map_edit_area_type"];
                $coords = $_SESSION["il_map_edit_coords"];

                switch ($_POST["area_link_type"]) {
                    case IL_EXT_LINK:
                        $link = array(
                            "LinkType" => IL_EXT_LINK,
                            "Href" => ilUtil::stripSlashes($_POST["area_link_ext"]));
                        break;

                    case IL_NO_LINK:
                        $link = array(
                            "LinkType" => IL_EXT_LINK,
                            "Href" => "");
                        break;

                    case IL_INT_LINK:
                        $link = array(
                            "LinkType" => IL_INT_LINK,
                            "Type" => $_SESSION["il_map_il_type"],
                            "Target" => $_SESSION["il_map_il_target"],
                            "TargetFrame" => $_SESSION["il_map_il_targetframe"]);
                        break;
                }

//				$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//					$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
                $this->std_alias_item->addMapArea(
                    $area_type,
                    $coords,
                    ilUtil::stripSlashes($_POST["area_name"]),
                    []
                );
                $this->page->update();
                break;
        }

        //$this->initMapParameters();
        ilUtil::sendSuccess($lng->txt("cont_saved_map_area"), true);
        $ilCtrl->redirect($this, "editMapAreas");
    }

    /**
     * Delete map areas
     */
    public function deleteAreas() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if (!isset($_POST["area"])) {
            ilUtil::sendFailure($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        }

        //		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
        //			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());

        if (count($_POST["area"]) > 0) {
            $i = 0;
            arsort($_POST["area"]);
            foreach ($_POST["area"] as $area_nr) {
                $this->std_alias_item->deleteMapArea($area_nr);
            }
            $this->page->update();
            ilUtil::sendSuccess($lng->txt("cont_areas_deleted"), true);
        }

        $ilCtrl->redirect($this, "editMapAreas");
    }

    /**
     * Get Link Type of Area
     */
    public function getLinkTypeOfArea(int $a_nr) : string
    {
        return $this->std_alias_item->getLinkTypeOfArea($a_nr);
    }

    /**
     * Get Type of Area (only internal link)
     */
    public function getTypeOfArea(int $a_nr) : string
    {
        return $this->std_alias_item->getTypeOfArea($a_nr);
    }

    /**
     * Get Target of Area (only internal link)
     */
    public function getTargetOfArea(int $a_nr) : string
    {
        return $this->std_alias_item->getTargetOfArea($a_nr);
    }

    /**
     * Get TargetFrame of Area (only internal link)
     */
    public function getTargetFrameOfArea(int $a_nr) : string
    {
        return $this->std_alias_item->getTargetFrameOfArea($a_nr);
    }

    /**
     * Get Href of Area (only external link)
     */
    public function getHrefOfArea(int $a_nr) : string
    {
        return $this->std_alias_item->getHrefOfArea($a_nr);
    }

    /**
     * Update map areas
     */
    public function updateAreas() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        //		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
        //			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
        $areas = $this->std_alias_item->getMapAreas();
        foreach ($areas as $area) {
            // fix #26032 empty values lead to "empty text node" errors on page update
            $name = ilUtil::stripSlashes($_POST["name_" . $area["Nr"]]);
            if ($name == "") {
                $name = " ";
            }
            $this->std_alias_item->setAreaTitle(
                $area["Nr"],
                $name
            );
            $this->std_alias_item->setAreaHighlightMode(
                $area["Nr"],
                ilUtil::stripSlashes($_POST["hl_mode_" . $area["Nr"]])
            );
            $this->std_alias_item->setAreaHighlightClass(
                $area["Nr"],
                ilUtil::stripSlashes($_POST["hl_class_" . $area["Nr"]])
            );
        }
        $this->page->update();
        
        ilUtil::sendSuccess($lng->txt("cont_saved_map_data"), true);
        $ilCtrl->redirect($this, "editMapAreas");
    }
    
    /**
     * Make work file for editing
     */
    public function makeMapWorkCopy(
        string $a_edit_property = "",
        int $a_area_nr = 0,
        bool $a_output_new_area = false,
        string $a_area_type = "",
        string $a_coords = ""
    ) : void {
        // old for pc media object
        //		$media_object = $this->media_object->getMediaItem("Standard");
        $media_object = $this->content_obj->getMediaObject();
        
        // create/update imagemap work copy
        $st_item = $media_object->getMediaItem("Standard");
        $st_alias_item = new ilMediaAliasItem(
            $this->content_obj->dom,
            $this->content_obj->hier_id,
            "Standard",
            $this->content_obj->getPCId(),
            $this->getParentNodeName()
        );

        if ($a_edit_property == "shape") {
            $st_alias_item->makeMapWorkCopy(
                $st_item,
                $a_area_nr,
                true,
                $a_output_new_area,
                $a_area_type,
                $a_coords
            );	// exclude area currently being edited
        } else {
            $st_alias_item->makeMapWorkCopy(
                $st_item,
                $a_area_nr,
                false,
                $a_output_new_area,
                $a_area_type,
                $a_coords
            );
        }
    }

    public function getAliasXML() : string
    {
        return $this->content_obj->dumpXML();
    }
}
