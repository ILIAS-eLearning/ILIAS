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

use ILIAS\COPage\PC\EditGUIRequest;
use ILIAS\COPage\PC\MapEditorSessionRepository;

/**
 * User interface class for page content map editor
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPCImageMapEditorGUI: ilInternalLinkGUI
 */
class ilPCImageMapEditorGUI extends ilImageMapEditorGUI
{
    protected MapEditorSessionRepository $map_repo;
    protected ilMediaAliasItem $std_alias_item;
    protected ilPageObject $page;
    /**
     * @var ilPCInteractiveImage|ilPCMediaObject
     */
    protected $content_obj;
    protected EditGUIRequest $edit_request;

    /**
     * @param ilPCMediaObject|ilPCInteractiveImage $a_content_obj
     */
    public function __construct(
        $a_content_obj,
        ilPageObject $a_page,
        EditGUIRequest $request
    ) {
        global $DIC;

        $this->content_obj = $a_content_obj;
        $this->page = $a_page;
        $this->edit_request = $request;
        parent::__construct($a_content_obj->getMediaObject());
                
        $this->std_alias_item = new ilMediaAliasItem(
            $this->content_obj->dom,
            $this->content_obj->hier_id,
            "Standard",
            $this->content_obj->getPCId(),
            $this->getParentNodeName()
        );
        $this->map_repo = $DIC
            ->copage()
            ->internal()
            ->repo()
            ->pc()
            ->mediaMap();
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
    public function saveArea() : string
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        switch ($this->map_repo->getMode()) {
            // save edited link
            case "edit_link":
//				$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//					$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());

                $area_link_type = $this->edit_request->getString("area_link_type");
                if ($area_link_type == IL_INT_LINK) {
                    $this->std_alias_item->setAreaIntLink(
                        $this->map_repo->getAreaNr(),
                        $this->map_repo->getLinkType(),
                        $this->map_repo->getLinkTarget(),
                        $this->map_repo->getLinkFrame()
                    );
                } elseif ($area_link_type == IL_NO_LINK) {
                    $this->std_alias_item->setAreaExtLink(
                        $this->map_repo->getAreaNr(),
                        ""
                    );
                } else {
                    $this->std_alias_item->setAreaExtLink(
                        $this->map_repo->getAreaNr(),
                        $this->edit_request->getString("area_link_ext")
                    );
                }
                $this->page->update();
                break;

            // save edited shape
            case "edit_shape":
                $this->std_alias_item->setShape(
                    $this->map_repo->getAreaNr(),
                    $this->map_repo->getAreaType(),
                    $this->map_repo->getCoords()
                );
                $this->page->update();
                break;

            // save new area
            default:
                $area_type = $this->map_repo->getAreaType();
                $coords = $this->map_repo->getCoords();

                $area_link_type = $this->edit_request->getString("area_link_type");
                switch ($area_link_type) {
                    case IL_EXT_LINK:
                        $link = array(
                            "LinkType" => IL_EXT_LINK,
                            "Href" => $this->edit_request->getString("area_link_ext"));
                        break;

                    case IL_NO_LINK:
                        $link = array(
                            "LinkType" => IL_EXT_LINK,
                            "Href" => "");
                        break;

                    case IL_INT_LINK:
                        $link = array(
                            "LinkType" => IL_INT_LINK,
                            "Type" => $this->map_repo->getLinkType(),
                            "Target" => $this->map_repo->getLinkTarget(),
                            "TargetFrame" => $this->map_repo->getLinkFrame());
                        break;
                }

//				$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
//					$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());
                $this->std_alias_item->addMapArea(
                    $area_type,
                    $coords,
                    $this->edit_request->getString("area_name"),
                    []
                );
                $this->page->update();
                break;
        }

        //$this->initMapParameters();
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("cont_saved_map_area"), true);
        $ilCtrl->redirect($this, "editMapAreas");
        return "";
    }

    /**
     * Delete map areas
     */
    public function deleteAreas() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $areas = $this->edit_request->getStringArray("area");
        if (count($areas) == 0) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        }

        //		$std_alias_item = new ilMediaAliasItem($this->content_obj->dom,
        //			$this->content_obj->hier_id, "Standard", $this->content_obj->getPcId());

        $i = 0;
        arsort($areas);
        foreach ($areas as $area_nr) {
            $this->std_alias_item->deleteMapArea($area_nr);
        }
        $this->page->update();
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("cont_areas_deleted"), true);

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
            $name = $this->edit_request->getString("name_" . $area["Nr"]);
            $hl_mode = $this->edit_request->getString("hl_mode_" . $area["Nr"]);
            $hl_class = $this->edit_request->getString("hl_class_" . $area["Nr"]);
            if ($name == "") {
                $name = " ";
            }
            $this->std_alias_item->setAreaTitle(
                $area["Nr"],
                $name
            );
            $this->std_alias_item->setAreaHighlightMode(
                $area["Nr"],
                $hl_mode
            );
            $this->std_alias_item->setAreaHighlightClass(
                $area["Nr"],
                $hl_class
            );
        }
        $this->page->update();
        
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("cont_saved_map_data"), true);
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
