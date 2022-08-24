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

use ILIAS\MediaObjects\ImageMap\ImageMapManager;
use ILIAS\MediaObjects\ImageMap\ImageMapGUIRequest;

/**
 * User interface class for map editor
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilImageMapEditorGUI: ilInternalLinkGUI
 */
class ilImageMapEditorGUI
{
    protected ilObjMediaObject $media_object;
    protected ImageMapGUIRequest $request;
    protected ImageMapManager $map;
    protected ilTemplate $tpl;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;

    public function __construct(
        ilObjMediaObject $a_media_object
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->media_object = $a_media_object;

        $this->map = $DIC->mediaObjects()
            ->internal()
            ->domain()
            ->imageMap();

        $this->request = $DIC->mediaObjects()
            ->internal()
            ->gui()
            ->imageMap()
            ->request();
    }

    /**
     * @return mixed
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            case "ilinternallinkgui":
                $link_gui = new ilInternalLinkGUI("Media_Media", 0);
                $link_gui->setSetLinkTargetScript(
                    $ilCtrl->getLinkTarget(
                        $this,
                        "setInternalLink"
                    )
                );
                $link_gui->filterLinkType("File");
                $ret = $ilCtrl->forwardCommand($link_gui);
                break;

            default:
                ilObjMediaObjectGUI::includePresentationJS();
                if ($this->request->getX() != "" &&
                    $this->request->getY() != "") {
                    $cmd = "editImagemapForward";
                }
                $ret = $this->$cmd();
                break;
        }
        return $ret;
    }

    public function editMapAreas(): string
    {
        $ilCtrl = $this->ctrl;

        $this->map->setTargetScript(
            $ilCtrl->getLinkTarget(
                $this,
                "addArea",
                "",
                false,
                false
            )
        );
        $this->handleMapParameters();

        $this->tpl = new ilTemplate("tpl.map_edit.html", true, true, "Services/MediaObjects");
        $this->tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));

        // create/update imagemap work copy
        $this->makeMapWorkCopy();

        $output = $this->getImageMapOutput();
        $this->tpl->setVariable("IMAGE_MAP", $output);

        $this->tpl->setVariable("TOOLBAR", $this->getToolbar()->getHTML());

        // table
        $this->tpl->setVariable("MAP_AREA_TABLE", $this->getImageMapTableHTML());

        return $this->tpl->get();
    }

    public function getToolbar(): ilToolbarGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        // toolbar
        $tb = new ilToolbarGUI();
        $tb->setFormAction($ilCtrl->getFormAction($this));
        $options = array(
            "WholePicture" => $lng->txt("cont_WholePicture"),
            "Rect" => $lng->txt("cont_Rect"),
            "Circle" => $lng->txt("cont_Circle"),
            "Poly" => $lng->txt("cont_Poly"),
            );
        $si = new ilSelectInputGUI($lng->txt("cont_shape"), "shape");
        $si->setOptions($options);
        $tb->addInputItem($si, true);
        $tb->addFormButton($lng->txt("cont_add_area"), "addNewArea");

        return $tb;
    }

    public function getEditorTitle(): string
    {
        $lng = $this->lng;
        return $lng->txt("cont_imagemap");
    }


    public function getImageMapTableHTML(): string
    {
        $image_map_table = new ilImageMapTableGUI($this, "editMapAreas", $this->media_object);
        return $image_map_table->getHTML();
    }

    public function handleMapParameters(): void
    {
        if ($this->request->getRefId() > 0) {
            $this->map->setRefId($this->request->getRefId());
        }

        if ($this->request->getObjId() > 0) {
            $this->map->setObjId($this->request->getObjId());
        }

        if ($this->request->getHierId() != "") {
            $this->map->setHierId($this->request->getHierId());
        }

        if ($this->request->getPCId() != "") {
            $this->map->setPCId($this->request->getPCId());
        }
    }

    public function showImageMap(): void
    {
        $item = new ilMediaItem($this->request->getItemId());
        $item->outputMapWorkCopy();
    }

    public function updateAreas(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $st_item = $this->media_object->getMediaItem("Standard");
        $max = ilMapArea::_getMaxNr($st_item->getId());
        for ($i = 1; $i <= $max; $i++) {
            $area = new ilMapArea($st_item->getId(), $i);
            $area->setTitle(
                $this->request->getAreaTitle($i)
            );
            $area->setHighlightMode(
                $this->request->getAreaHighlightMode($i)
            );
            $area->setHighlightClass(
                $this->request->getAreaHighlightClass($i)
            );
            $area->update();
        }

        $this->main_tpl->setOnScreenMessage('success', $lng->txt("cont_saved_map_data"), true);
        $ilCtrl->redirect($this, "editMapAreas");
    }

    public function addNewArea(): string
    {
        switch ($this->request->getAreaShape()) {
            case "WholePicture": return $this->linkWholePicture();
            case "Rect": return $this->addRectangle();
            case "Circle": return $this->addCircle();
            case "Poly": return $this->addPolygon();
        }
        return "";
    }

    public function linkWholePicture(): string
    {
        $this->clearSessionVars();
        $this->map->setAreaType("WholePicture");

        return $this->editMapArea(false, false, true);
    }

    public function addRectangle(): string
    {
        $this->clearSessionVars();
        $this->map->setAreaType("Rect");
        return $this->addArea(false);
    }

    public function addCircle(): string
    {
        $this->clearSessionVars();
        $this->map->setAreaType("Circle");
        return $this->addArea(false);
    }

    public function addPolygon(): string
    {
        $this->clearSessionVars();
        $this->map->setAreaType("Poly");
        return $this->addArea(false);
    }

    public function clearSessionVars(): void
    {
        $this->map->clear();
    }

    public function addArea(
        bool $a_handle = true
    ): string {
        // handle map parameters
        if ($a_handle) {
            $this->handleMapParameters();
        }

        $area_type = $this->map->getAreaType();
        $coords = $this->map->getCoords();
        $cnt_coords = ilMapArea::countCoords($coords);

        // decide what to do next
        switch ($area_type) {
            // Rectangle
            case "Rect":
                if ($cnt_coords < 2) {
                    $html = $this->editMapArea(true, false, false);
                    return $html;
                } elseif ($cnt_coords == 2) {
                    return $this->editMapArea(false, true, true);
                }
                break;

                // Circle
            case "Circle":
                if ($cnt_coords <= 1) {
                    return $this->editMapArea(true, false, false);
                } else {
                    if ($cnt_coords == 2) {
                        $c = explode(",", $coords);
                        $coords = $c[0] . "," . $c[1] . ",";	// determine radius
                        $coords .= round(sqrt(pow(abs($c[3] - $c[1]), 2) + pow(abs($c[2] - $c[0]), 2)));
                    }
                    $this->map->setCoords($coords);

                    return $this->editMapArea(false, true, true);
                }

                // Polygon
                // no break
            case "Poly":
                if ($cnt_coords < 1) {
                    return $this->editMapArea(true, false, false);
                } elseif ($cnt_coords < 3) {
                    return $this->editMapArea(true, true, false);
                } else {
                    return $this->editMapArea(true, true, true);
                }

                // Whole picture
                // no break
            case "WholePicture":
                return $this->editMapArea(false, false, true);
        }
        return "";
    }

    /**
     * Edit a single map area
     * @param bool   $a_get_next_coordinate enable next coordinate input
     * @param bool   $a_output_new_area     output the new area
     * @param bool   $a_save_form           output save form
     * @param string $a_edit_property       "" | "link" | "shape"
     * @param int    $a_area_nr
     * @return string
     */
    public function editMapArea(
        bool $a_get_next_coordinate = false,
        bool $a_output_new_area = false,
        bool $a_save_form = false,
        string $a_edit_property = "",
        int $a_area_nr = 0
    ): string {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $area_type = $this->map->getAreaType();
        $coords = $this->map->getCoords();
        $cnt_coords = ilMapArea::countCoords($coords);

        $this->tpl = new ilTemplate("tpl.map_edit.html", true, true, "Services/MediaObjects");

        $this->tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));

        if ($a_edit_property != "link") {
            switch ($area_type) {
                // rectangle
                case "Rect":
                    if ($cnt_coords == 0) {
                        $this->main_tpl->setOnScreenMessage('info', $lng->txt("cont_click_tl_corner"));
                    }
                    if ($cnt_coords == 1) {
                        $this->main_tpl->setOnScreenMessage('info', $lng->txt("cont_click_br_corner"));
                    }
                    break;

                    // circle
                case "Circle":
                    if ($cnt_coords == 0) {
                        $this->main_tpl->setOnScreenMessage('info', $lng->txt("cont_click_center"));
                    }
                    if ($cnt_coords == 1) {
                        $this->main_tpl->setOnScreenMessage('info', $lng->txt("cont_click_circle"));
                    }
                    break;

                    // polygon
                case "Poly":
                    if ($cnt_coords == 0) {
                        $this->main_tpl->setOnScreenMessage('info', $lng->txt("cont_click_starting_point"));
                    } elseif ($cnt_coords < 3) {
                        $this->main_tpl->setOnScreenMessage('info', $lng->txt("cont_click_next_point"));
                    } else {
                        $this->main_tpl->setOnScreenMessage('info', $lng->txt("cont_click_next_or_save"));
                    }
                    break;
            }
        }


        // map properties input fields (name and link)
        if ($a_save_form) {
            if ($a_edit_property != "shape") {
                // prepare link gui
                $ilCtrl->setParameter($this, "linkmode", "map");
                $this->tpl->setCurrentBlock("int_link_prep");
                $this->tpl->setVariable("INT_LINK_PREP", ilInternalLinkGUI::getInitHTML(
                    $ilCtrl->getLinkTargetByClass(
                        "ilinternallinkgui",
                        "",
                        false,
                        true,
                        false
                    )
                ));
                $this->tpl->parseCurrentBlock();
            }
            $form = $this->initAreaEditingForm($a_edit_property);
            $this->tpl->setVariable("FORM", $form->getHTML());
        }

        $this->makeMapWorkCopy(
            $a_edit_property,
            $a_area_nr,
            $a_output_new_area,
            $area_type,
            $coords
        );

        $edit_mode = ($a_get_next_coordinate)
            ? "get_coords"
            : (($a_output_new_area)
                ? "new_area"
                : "");
        $output = $this->getImageMapOutput($edit_mode);
        $this->tpl->setVariable("IMAGE_MAP", $output);

        return $this->tpl->get();
    }

    public function initAreaEditingForm(
        string $a_edit_property
    ): ilPropertyFormGUI {
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();
        $form->setOpenTag(false);
        $form->setCloseTag(false);

        // link
        if ($a_edit_property != "shape") {
            //
            $radg = new ilRadioGroupInputGUI($lng->txt("cont_link"), "area_link_type");
            if ($this->map->getLinkType() != "int") {
                if ($this->map->getExternalLink() == "") {
                    $radg->setValue("no");
                } else {
                    $radg->setValue("ext");
                }
            } else {
                $radg->setValue("int");
            }

            // external link
            $ext = new ilRadioOption($lng->txt("cont_link_ext"), "ext");
            $radg->addOption($ext);

            $ti = new ilTextInputGUI("", "area_link_ext");
            $ti->setMaxLength(800);
            $ti->setSize(50);
            if ($this->map->getExternalLink() != "") {
                $ti->setValue($this->map->getExternalLink());
            } else {
                $ti->setValue("https://");
            }
            $ext->addSubItem($ti);

            // internal link
            $int = new ilRadioOption($lng->txt("cont_link_int"), "int");
            $radg->addOption($int);

            $ne = new ilNonEditableValueGUI("", "", true);
            $link_str = "";
            $int_link = $this->map->getInternalLink();
            if ($int_link["target"] != "") {
                $link_str = $this->getMapAreaLinkString(
                    $int_link["target"],
                    $int_link["type"],
                    $int_link["target_frame"]
                );
            }
            $ne->setValue(
                $link_str .
                    '&nbsp;<a id="iosEditInternalLinkTrigger" href="#">' .
                    "[" . $lng->txt("cont_get_link") . "]" .
                    '</a>'
            );
            $int->addSubItem($ne);

            // no link
            $no = new ilRadioOption($lng->txt("cont_link_no"), "no");
            $radg->addOption($no);

            $form->addItem($radg);
        }


        // name
        if ($a_edit_property != "link" && $a_edit_property != "shape") {
            $ti = new ilTextInputGUI($lng->txt("cont_name"), "area_name");
            $ti->setMaxLength(200);
            $ti->setSize(20);
            $form->addItem($ti);
        }

        // save and cancel commands
        $form->setTitle($lng->txt("cont_new_area"));
        $form->addCommandButton("saveArea", $lng->txt("save"));

        //		$form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
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
    ): void {
        // create/update imagemap work copy
        $st_item = $this->media_object->getMediaItem("Standard");

        if ($a_edit_property == "shape") {
            $st_item->makeMapWorkCopy($a_area_nr, true);	// exclude area currently being edited
        } else {
            $st_item->makeMapWorkCopy($a_area_nr, false);
        }

        if ($a_output_new_area) {
            $st_item->addAreaToMapWorkCopy($a_area_type, $a_coords);
        }
    }

    /**
     * Render the image map.
     */
    public function getImageMapOutput(
        string $a_map_edit_mode = ""
    ): string {
        $ilCtrl = $this->ctrl;

        $st_item = $this->media_object->getMediaItem("Standard");

        // output image map
        $xml = "<dummy>";
        $xml .= $this->getAliasXML();
        $xml .= $this->media_object->getXML(IL_MODE_OUTPUT);
        $xml .= $this->getAdditionalPageXML();
        $xml .= "</dummy>";
        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        //echo htmlentities($xml); exit;
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();
        $wb_path = ilFileUtils::getWebspaceDir("output") . "/";
        $mode = "media";
        //echo htmlentities($ilCtrl->getLinkTarget($this, "showImageMap"));

        $random = new \ilRandom();
        $params = array('map_edit_mode' => $a_map_edit_mode,
            'map_item' => $st_item->getId(),
            'map_mob_id' => $this->media_object->getId(),
            'mode' => $mode,
            'media_mode' => 'enable',
            'image_map_link' => $ilCtrl->getLinkTarget($this, "showImageMap", "", false, false),
            'link_params' => "ref_id=" . $this->request->getRefId() . "&rand=" . $random->int(1, 999999),
            'ref_id' => $this->request->getRefId(),
            'pg_frame' => "",
            'enlarge_path' => ilUtil::getImagePath("enlarge.svg"),
            'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        xslt_error($xh);
        xslt_free($xh);

        $output = $this->outputPostProcessing($output);

        return $output;
    }

    /**
     * Get additional page xml (to be overwritten)
     * @return string additional page xml
     */
    public function getAdditionalPageXML(): string
    {
        return "";
    }

    public function outputPostProcessing(
        string $a_output
    ): string {
        return $a_output;
    }

    public function getAliasXML(): string
    {
        return $this->media_object->getXML(IL_MODE_ALIAS);
    }

    /**
     * Get text name of internal link
     * @param	string		$a_target		target object link id
     * @param	string		$a_type			type
     * @param	string		$a_frame		target frame
     */
    public function getMapAreaLinkString(
        string $a_target,
        string $a_type,
        string $a_frame
    ): string {
        $lng = $this->lng;
        $frame_str = "";
        $link_str = "";
        $t_arr = explode("_", $a_target);
        if ($a_frame != "") {
            $frame_str = " (" . $a_frame . " Frame)";
        }
        switch ($a_type) {
            case "StructureObject":
                $title = ilLMObject::_lookupTitle($t_arr[count($t_arr) - 1]);
                $link_str = $lng->txt("chapter") .
                    ": " . $title . " [" . $t_arr[count($t_arr) - 1] . "]" . $frame_str;
                break;

            case "PageObject":
                $title = ilLMObject::_lookupTitle($t_arr[count($t_arr) - 1]);
                $link_str = $lng->txt("page") .
                    ": " . $title . " [" . $t_arr[count($t_arr) - 1] . "]" . $frame_str;
                break;

            case "GlossaryItem":
                $term = new ilGlossaryTerm($t_arr[count($t_arr) - 1]);
                $link_str = $lng->txt("term") .
                    ": " . $term->getTerm() . " [" . $t_arr[count($t_arr) - 1] . "]" . $frame_str;
                break;

            case "MediaObject":
                $mob = new ilObjMediaObject($t_arr[count($t_arr) - 1]);
                $link_str = $lng->txt("mob") .
                    ": " . $mob->getTitle() . " [" . $t_arr[count($t_arr) - 1] . "]" . $frame_str;
                break;

            case "RepositoryItem":
                $title = ilObject::_lookupTitle(
                    ilObject::_lookupObjId($t_arr[count($t_arr) - 1])
                );
                $link_str = $lng->txt("obj_" . $t_arr[count($t_arr) - 2]) .
                    ": " . $title . " [" . $t_arr[count($t_arr) - 1] . "]" . $frame_str;
                break;
        }

        return $link_str;
    }

    /**
     * Get image map coordinates.
     */
    public function editImagemapForward(): void
    {
        ilImageMapEditorGUI::_recoverParameters();

        $coords = $this->map->getCoords();
        if ($coords != "") {
            $coords .= ",";
        }

        $this->map->setCoords($coords . $this->request->getX() . "," .
            $this->request->getY());

        // call editing script
        ilUtil::redirect($this->map->getTargetScript());
    }

    /**
     * Recover parameters from session variables (static)
     */
    public static function _recoverParameters(): void
    {
        global $DIC;

        $map = $DIC->mediaObjects()->internal()->domain()->imageMap();
        /*
        $_GET["ref_id"] = $map->getRefId();
        $_GET["obj_id"] = $map->getObjId();
        $_GET["hier_id"] = $map->getHierId();
        $_GET["pc_id"] = $map->getPCId();*/
    }

    /**
     * Save new or updated map area
     */
    public function saveArea(): string
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        switch ($this->map->getMode()) {
            // save edited link
            case "edit_link":
                $st_item = $this->media_object->getMediaItem("Standard");
                $max = ilMapArea::_getMaxNr($st_item->getId());
                $area = new ilMapArea($st_item->getId(), $this->map->getAreaNr());

                if ($this->request->getAreaLinkType() == IL_INT_LINK) {
                    $area->setLinkType(IL_INT_LINK);
                    $int_link = $this->map->getInternalLink();
                    $area->setType($int_link["type"]);
                    $area->setTarget($int_link["target"]);
                    $area->setTargetFrame($int_link["target_frame"]);
                } else {
                    $area->setLinkType(IL_EXT_LINK);
                    if ($this->request->getAreaLinkType() != IL_NO_LINK) {
                        $area->setHref(
                            $this->request->getExternalLink()
                        );
                    } else {
                        $area->setHref("");
                    }
                }
                $area->update();
                break;

                // save edited shape
            case "edit_shape":
                $st_item = $this->media_object->getMediaItem("Standard");
                $max = ilMapArea::_getMaxNr($st_item->getId());
                $area = new ilMapArea(
                    $st_item->getId(),
                    $this->map->getAreaNr()
                );

                $area->setShape($this->map->getAreaType());
                $area->setCoords($this->map->getCoords());
                $area->update();
                break;

                // save new area
            default:
                $area_type = $this->map->getAreaType();
                $coords = $this->map->getCoords();

                $st_item = $this->media_object->getMediaItem("Standard");
                $max = ilMapArea::_getMaxNr($st_item->getId());

                // make new area object
                $area = new ilMapArea();
                $area->setItemId($st_item->getId());
                $area->setShape($area_type);
                $area->setCoords($coords);
                $area->setNr($max + 1);
                $area->setTitle($this->request->getAreaName());
                switch ($this->request->getAreaLinkType()) {
                    case "ext":
                        $area->setLinkType(IL_EXT_LINK);
                        $area->setHref($this->request->getExternalLink());
                        break;

                    case "int":
                        $area->setLinkType(IL_INT_LINK);
                        $int_link = $this->map->getInternalLink();
                        $area->setType($int_link["type"]);
                        $area->setTarget($int_link["target"]);
                        $area->setTargetFrame($int_link["type_frame"]);
                        break;
                }

                // put area into item and update media object
                $st_item->addMapArea($area);
                $this->media_object->update();
                break;
        }

        //$this->initMapParameters();
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("cont_saved_map_area"), true);
        $ilCtrl->redirect($this, "editMapAreas");
        return "";
    }

    public function setInternalLink(): string
    {
        $this->map->setLinkType("int");
        $this->map->setInternalLink(
            $this->request->getLinkType(),
            $this->request->getLinkTarget(),
            $this->request->getLinkTargetFrame(),
            $this->request->getLinkAnchor()
        );

        switch ($this->map->getMode()) {
            case "edit_link":
                return $this->setLink();

            default:
                return $this->addArea();
        }
    }

    public function setLink(
        bool $a_handle = true
    ): string {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $area = null;
        if ($a_handle) {
            $this->handleMapParameters();
        }
        if ($this->map->getAreaNr() != "") {
            $area_nr = $this->map->getAreaNr();
        } else {
            $area = $this->request->getArea();
            $area_nr = $area[0] ?? "";
        }
        if ($area_nr == "") {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        }

        if (count($area) > 1) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("cont_select_max_one_item"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        }


        if ($this->map->getMode() != "edit_link") {
            $this->map->setAreaNr($area_nr);
            $this->map->setLinkType($this->getLinkTypeOfArea($area_nr));
            $this->map->setMode("edit_link");
            $this->map->setTargetScript($ilCtrl->getLinkTarget($this, "setLink"));
            if ($this->map->getLinkType() == IL_INT_LINK) {
                $this->map->setInternalLink(
                    $this->getTypeOfArea($area_nr),
                    $this->getTargetOfArea($area_nr),
                    $this->getTargetFrameOfArea($area_nr),
                    ""
                );
            } else {
                $this->map->setExternalLink($this->getHrefOfArea($area_nr));
            }
        }

        return $this->editMapArea(false, false, true, "link", $area_nr);
    }

    public function getLinkTypeOfArea(
        int $a_nr
    ): string {
        $st_item = $this->media_object->getMediaItem("Standard");
        $area = $st_item->getMapArea($a_nr);
        return $area->getLinkType();
    }

    /**
     * Get Type of Area (only internal link)
     */
    public function getTypeOfArea(
        int $a_nr
    ): string {
        $st_item = $this->media_object->getMediaItem("Standard");
        $area = $st_item->getMapArea($a_nr);
        return $area->getType();
    }

    /**
     * Get Target of Area (only internal link)
     */
    public function getTargetOfArea(
        int $a_nr
    ): string {
        $st_item = $this->media_object->getMediaItem("Standard");
        $area = $st_item->getMapArea($a_nr);
        return $area->getTarget();
    }

    /**
     * Get TargetFrame of Area (only internal link)
     */
    public function getTargetFrameOfArea(
        int $a_nr
    ): string {
        $st_item = $this->media_object->getMediaItem("Standard");
        $area = $st_item->getMapArea($a_nr);
        return $area->getTargetFrame();
    }

    /**
     * Get Href of Area (only external link)
     */
    public function getHrefOfArea(
        int $a_nr
    ): string {
        $st_item = $this->media_object->getMediaItem("Standard");
        $area = $st_item->getMapArea($a_nr);
        return $area->getHref();
    }

    /**
     * Delete map areas
     */
    public function deleteAreas(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $area = $this->request->getArea();
        if (count($area) == 0) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        }

        $st_item = $this->media_object->getMediaItem("Standard");
        $max = ilMapArea::_getMaxNr($st_item->getId());

        if (count($area) > 0) {
            $i = 0;

            foreach ($area as $area_nr) {
                $st_item->deleteMapArea($area_nr - $i);
                $i++;
            }

            $this->media_object->update();
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("cont_areas_deleted"), true);
        }

        $ilCtrl->redirect($this, "editMapAreas");
    }

    /**
     * Edit existing link
     */
    public function editLink(): string
    {
        $this->map->clear();
        return $this->setLink(false);
    }

    /**
     * Edit an existing shape (make it a whole picture link)
     */
    public function editShapeWholePicture(): string
    {
        $this->clearSessionVars();
        $this->map->setAreaType("WholePicture");
        return $this->setShape(false);
    }

    /**
     * Edit an existing shape (make it a rectangle)
     */
    public function editShapeRectangle(): string
    {
        $this->clearSessionVars();
        $this->map->setAreaType("Rect");
        return $this->setShape(false);
    }

    /**
     * Edit an existing shape (make it a circle)
     */
    public function editShapeCircle(): string
    {
        $this->clearSessionVars();
        $this->map->setAreaType("Circle");
        return $this->setShape(false);
    }

    /**
     * Edit an existing shape (make it a polygon)
     */
    public function editShapePolygon(): string
    {
        $this->clearSessionVars();
        $this->map->setAreaType("Poly");
        return $this->setShape(false);
    }

    /**
     * edit shape of existing map area
     */
    public function setShape(
        bool $a_handle = true
    ): string {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $area = [];
        if ($a_handle) {
            $this->handleMapParameters();
        }
        /* this seems to be obsolete
        if ($_POST["areatype2"] != "") {
            $this->map->setAreaType($_POST["areatype2"]);
        }*/
        if ($this->map->getAreaNr() != "") {
            $area_nr = $this->map->getAreaNr();
        } else {
            $area = $this->request->getArea();
            $area_nr = $area[0] ?? "";
        }
        if ($area_nr == "") {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        }

        if (count($area) > 1) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("cont_select_max_one_item"), true);
            $ilCtrl->redirect($this, "editMapAreas");
        }

        if ($this->map->getMode() != "edit_shape") {
            $this->map->setAreaNr($area_nr);
            $this->map->setMode("edit_shape");
            $this->map->setTargetScript(
                $ilCtrl->getLinkTarget($this, "setShape", "", false, false)
            );
        }


        $area_type = $this->map->getAreaType();
        $coords = $this->map->getCoords();
        $cnt_coords = ilMapArea::countCoords($coords);

        // decide what to do next
        switch ($area_type) {
            // Rectangle
            case "Rect":
                if ($cnt_coords < 2) {
                    return $this->editMapArea(true, false, false, "shape", $area_nr);
                } elseif ($cnt_coords == 2) {
                    return $this->saveArea();
                }
                break;

                // Circle
            case "Circle":
                if ($cnt_coords <= 1) {
                    return $this->editMapArea(true, false, false, "shape", $area_nr);
                } else {
                    if ($cnt_coords == 2) {
                        $c = explode(",", $coords);
                        $coords = $c[0] . "," . $c[1] . ",";	// determine radius
                        $coords .= round(sqrt(pow(abs($c[3] - $c[1]), 2) + pow(abs($c[2] - $c[0]), 2)));
                    }
                    $this->map->setCoords($coords);
                    return $this->saveArea();
                }

                // Polygon
                // no break
            case "Poly":
                if ($cnt_coords < 1) {
                    return $this->editMapArea(true, false, false, "shape", $area_nr);
                } elseif ($cnt_coords < 3) {
                    return $this->editMapArea(true, true, false, "shape", $area_nr);
                } else {
                    return $this->editMapArea(true, true, true, "shape", $area_nr);
                }

                // Whole Picture
                // no break
            case "WholePicture":
                return $this->saveArea();
        }
        return "";
    }

    /**
     * Set highlight settings
     */
    public function setHighlight(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $st_item = $this->media_object->getMediaItem("Standard");
        // seems to be obsolete, methods don't exist
        //$st_item->setHighlightMode($this->request->getHighlightMode());
        //$st_item->setHighlightClass($this->request->getHighlightClass());
        $st_item->update();

        $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "editMapAreas");
    }
}
