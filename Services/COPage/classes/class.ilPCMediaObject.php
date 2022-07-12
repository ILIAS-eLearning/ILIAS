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

use ILIAS\MediaObjects\Usage\UsageDBRepository;

/**
 * Class ilPCMediaObject
 * Media content object (see ILIAS DTD)
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCMediaObject extends ilPageContent
{
    protected UsageDBRepository $mob_usage_repo;
    protected php4DOMElement $mal_node;
    protected ilObjUser $user;
    protected php4DOMElement $mob_node;
    protected \ILIAS\DI\UIServices $ui;
    protected ?ilObjMediaObject $mediaobject = null;
    protected ilLanguage $lng;
    protected ilGlobalPageTemplate $global_tpl;

    public function init() : void
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->setType("media");
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->global_tpl = $DIC['tpl'];
        $this->mob_usage_repo = $DIC->mediaObjects()
            ->internal()
            ->repo()
            ->usage();
    }

    public function readMediaObject(int $a_mob_id = 0) : void
    {
        if ($a_mob_id > 0) {
            $mob = new ilObjMediaObject($a_mob_id);
            $this->setMediaObject($mob);
        }
    }
    
    public function setNode(php4DOMElement $a_node) : void
    {
        parent::setNode($a_node);		// this is the PageContent node
        $this->mob_node = $a_node->first_child();
    }

    public function setDom(php4DOMDocument $a_dom) : void
    {
        $this->dom = $a_dom;
    }

    public function setHierId(string $a_hier_id) : void
    {
        $this->hier_id = $a_hier_id;
    }

    public function setMediaObject(ilObjMediaObject $a_mediaobject) : void
    {
        $this->mediaobject = $a_mediaobject;
    }

    public function getMediaObject() : ?ilObjMediaObject
    {
        return $this->mediaobject;
    }
    
    public function createMediaObject() : void
    {
        $this->setMediaObject(new ilObjMediaObject());
    }

    public function create() : void
    {
        $this->node = $this->createPageContentNode();
    }

    public function createAlias(
        ilPageObject $a_pg_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) : void {
        $this->node = $this->dom->create_element("PageContent");
        $a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
        $this->mob_node = $this->dom->create_element("MediaObject");
        $this->mob_node = $this->node->append_child($this->mob_node);
        $this->mal_node = $this->dom->create_element("MediaAlias");
        $this->mal_node = $this->mob_node->append_child($this->mal_node);
        $this->mal_node->set_attribute("OriginId", "il__mob_" . $this->getMediaObject()->getId());

        // standard view
        $item_node = $this->dom->create_element("MediaAliasItem");
        $item_node = $this->mob_node->append_child($item_node);
        $item_node->set_attribute("Purpose", "Standard");
        $media_item = $this->getMediaObject()->getMediaItem("Standard");
        if (is_null($media_item)) {
            return;
        }

        $layout_node = $this->dom->create_element("Layout");
        $layout_node = $item_node->append_child($layout_node);
        if ($media_item->getWidth() > 0) {
            //$layout_node->set_attribute("Width", $media_item->getWidth());
        }
        if ($media_item->getHeight() > 0) {
            //$layout_node->set_attribute("Height", $media_item->getHeight());
        }
        $layout_node->set_attribute("HorizontalAlign", "Left");

        // caption
        if ($media_item->getCaption() != "") {
            $cap_node = $this->dom->create_element("Caption");
            $cap_node = $item_node->append_child($cap_node);
            $cap_node->set_attribute("Align", "bottom");
            $cap_node->set_content($media_item->getCaption());
        }

        // text representation
        if ($media_item->getTextRepresentation() != "") {
            $tr_node = $this->dom->create_element("TextRepresentation");
            $tr_node = $item_node->append_child($tr_node);
            $tr_node->set_content($media_item->getTextRepresentation());
        }

        $pars = $media_item->getParameters();
        foreach ($pars as $par => $val) {
            $par_node = $this->dom->create_element("Parameter");
            $par_node = $item_node->append_child($par_node);
            $par_node->set_attribute("Name", $par);
            $par_node->set_attribute("Value", $val);
        }

        // fullscreen view
        $fullscreen_item = $this->getMediaObject()->getMediaItem("Fullscreen");
        if (is_object($fullscreen_item)) {
            $item_node = $this->dom->create_element("MediaAliasItem");
            $item_node = $this->mob_node->append_child($item_node);
            $item_node->set_attribute("Purpose", "Fullscreen");

            // width and height
            $layout_node = $this->dom->create_element("Layout");
            $layout_node = $item_node->append_child($layout_node);
            if ($fullscreen_item->getWidth() > 0) {
                $layout_node->set_attribute("Width", $fullscreen_item->getWidth());
            }
            if ($fullscreen_item->getHeight() > 0) {
                $layout_node->set_attribute("Height", $fullscreen_item->getHeight());
            }

            // caption
            if ($fullscreen_item->getCaption() != "") {
                $cap_node = $this->dom->create_element("Caption");
                $cap_node = $item_node->append_child($cap_node);
                $cap_node->set_attribute("Align", "bottom");
                $cap_node->set_content($fullscreen_item->getCaption());
            }

            // text representation
            if ($fullscreen_item->getTextRepresentation() != "") {
                $tr_node = $this->dom->create_element("TextRepresentation");
                $tr_node = $item_node->append_child($tr_node);
                $tr_node->set_content($fullscreen_item->getTextRepresentation());
            }

            $pars = $fullscreen_item->getParameters();
            foreach ($pars as $par => $val) {
                $par_node = $this->dom->create_element("Parameter");
                $par_node = $item_node->append_child($par_node);
                $par_node->set_attribute("Name", $par);
                $par_node->set_attribute("Value", $val);
            }
        }
    }
    
    /**
     * Updates the media object referenced by the media alias.
     * This makes only sense, after the media object has changed.
     * (-> change object reference function)
     */
    public function updateObjectReference() : void
    {
        if (is_object($this->mob_node)) {
            $this->mal_node = $this->mob_node->first_child();
            if (is_object($this->mal_node) && $this->mal_node->node_name() == "MediaAlias") {
                $this->mal_node->set_attribute("OriginId", "il__mob_" . $this->getMediaObject()->getId());
            }
        }
    }

    public function dumpXML() : string
    {
        $xml = $this->dom->dump_node($this->node);
        return $xml;
    }
    
    public function setClass(string $a_class) : void
    {
        if (is_object($this->mob_node)) {
            $mal_node = $this->mob_node->first_child();
            if (is_object($mal_node)) {
                if (!empty($a_class)) {
                    $mal_node->set_attribute("Class", $a_class);
                } else {
                    if ($mal_node->has_attribute("Class")) {
                        $mal_node->remove_attribute("Class");
                    }
                }
            }
        }
    }

    /**
     * Get characteristic of section.
     */
    public function getClass() : string
    {
        if (is_object($this->mob_node)) {
            $mal_node = $this->mob_node->first_child();
            if (is_object($mal_node)) {
                $class = $mal_node->get_attribute("Class");
                return $class;
            }
        }
        return "";
    }

    /**
     * Set caption style class of media object
     */
    public function setCaptionClass(string $a_class) : void
    {
        if (is_object($this->mob_node)) {
            $mal_node = $this->mob_node->first_child();
            if (is_object($mal_node)) {
                if (!empty($a_class)) {
                    $mal_node->set_attribute("CaptionClass", $a_class);
                } else {
                    if ($mal_node->has_attribute("CaptionClass")) {
                        $mal_node->remove_attribute("CaptionClass");
                    }
                }
            }
        }
    }

    public function getCaptionClass() : string
    {
        if (is_object($this->mob_node)) {
            $mal_node = $this->mob_node->first_child();
            if (is_object($mal_node)) {
                $class = $mal_node->get_attribute("CaptionClass");
                return $class;
            }
        }
        return "";
    }

    public static function getLangVars() : array
    {
        return array("pc_mob");
    }

    /**
     * After page has been updated (or created)
     * @param ilPageObject $a_page     page object
     * @param DOMDocument  $a_domdoc   dom document
     * @param string       $a_xml      xml
     * @param bool         $a_creation true on creation, otherwise false
     */
    public static function afterPageUpdate(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        string $a_xml,
        bool $a_creation
    ) : void {
        if (!$a_page->getImportMode()) {
            $mob_ids = ilObjMediaObject::_getMobsOfObject(
                $a_page->getParentType() . ":pg",
                $a_page->getId(),
                0,
                $a_page->getLanguage()
            );
            self::saveMobUsage($a_page, $a_domdoc);
            foreach ($mob_ids as $mob) {	// check, whether media object can be deleted
                if (ilObject::_exists($mob) && ilObject::_lookupType($mob) == "mob") {
                    $mob_obj = new ilObjMediaObject($mob);
                    $usages = $mob_obj->getUsages(false);
                    if (count($usages) == 0) {	// delete, if no usage exists
                        $mob_obj->delete();
                    }
                }
            }
        }
    }
    
    public static function beforePageDelete(
        ilPageObject $a_page
    ) : void {
        $mob_ids = ilObjMediaObject::_getMobsOfObject(
            $a_page->getParentType() . ":pg",
            $a_page->getId(),
            0,
            $a_page->getLanguage()
        );

        ilObjMediaObject::_deleteAllUsages(
            $a_page->getParentType() . ":pg",
            $a_page->getId(),
            false,
            $a_page->getLanguage()
        );

        foreach ($mob_ids as $mob) {	// check, whether media object can be deleted
            if (ilObject::_exists($mob) && ilObject::_lookupType($mob) == "mob") {
                $mob_obj = new ilObjMediaObject($mob);
                $usages = $mob_obj->getUsages(false);
                if (count($usages) == 0) {	// delete, if no usage exists
                    $mob_obj->delete();
                }
            }
        }
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
        self::saveMobUsage($a_page, $a_old_domdoc, $a_old_nr);
    }

    public static function saveMobUsage(
        ilPageObject $a_page,
        DOMDocument $a_domdoc,
        int $a_old_nr = 0
    ) : array {
        $usages = array();
        
        // media aliases
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query('//MediaAlias');
        foreach ($nodes as $node) {
            $id_arr = explode("_", $node->getAttribute("OriginId"));
            $mob_id = $id_arr[count($id_arr) - 1];
            if ($mob_id > 0 && $id_arr[1] == "") {
                $usages[$mob_id] = true;
            }
        }

        // media objects
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query('//MediaObject/MetaData/General/Identifier');
        foreach ($nodes as $node) {
            $mob_entry = $node->getAttribute("Entry");
            $mob_arr = explode("_", $mob_entry);
            $mob_id = $mob_arr[count($mob_arr) - 1];
            if ($mob_id > 0 && $mob_arr[1] == "") {
                $usages[$mob_id] = true;
            }
        }

        // internal links
        $xpath = new DOMXPath($a_domdoc);
        $nodes = $xpath->query("//IntLink[@Type='MediaObject']");
        foreach ($nodes as $node) {
            $mob_target = $node->getAttribute("Target");
            $mob_arr = explode("_", $mob_target);
            //echo "<br>3<br>";
            //echo $mob_target."<br>";
            //var_dump($mob_arr);
            $mob_id = $mob_arr[count($mob_arr) - 1];
            if ($mob_id > 0 && $mob_arr[1] == "") {
                $usages[$mob_id] = true;
            }
        }

        ilObjMediaObject::_deleteAllUsages(
            $a_page->getParentType() . ":pg",
            $a_page->getId(),
            $a_old_nr,
            $a_page->getLanguage()
        );
        foreach ($usages as $mob_id => $val) {
            // save usage, if object exists...
            if (ilObject::_lookupType($mob_id) == "mob") {
                ilObjMediaObject::_saveUsage(
                    $mob_id,
                    $a_page->getParentType() . ":pg",
                    $a_page->getId(),
                    $a_old_nr,
                    $a_page->getLanguage()
                );
            }
        }
        
        return $usages;
    }

    public function modifyPageContentPostXsl(
        string $a_output,
        string $a_mode,
        bool $a_abstract_only = false
    ) : string {
        $ilUser = $this->user;

        if ($a_mode == "offline") {
            $page = $this->getPage();

            $mob_ids = ilObjMediaObject::_getMobsOfObject(
                $page->getParentType() . ":pg",
                $page->getId(),
                0,
                $page->getLanguage()
            );
            foreach ($mob_ids as $mob_id) {
                $mob = new ilObjMediaObject($mob_id);
                $srts = $mob->getSrtFiles();
                foreach ($srts as $srt) {
                    if ($ilUser->getLanguage() == $srt["language"]) {
                        $srt_content = file_get_contents(ilObjMediaObject::_getDirectory($mob->getId()) . "/" . $srt["full_path"]);
                        $a_output = str_replace("[[[[[mobsubtitle;il__mob_" . $mob->getId() . "_Standard]]]]]", $srt_content, $a_output);
                    }
                }
            }
        }

        if ($a_abstract_only) {
            return $a_output;
        }

        // add fullscreen modals
        $page = $this->getPage();
        $suffix = "-" . $page->getParentType() . "-" . $page->getId();
        $modal = $this->ui->factory()->modal()->roundtrip(
            $this->lng->txt("cont_fullscreen"),
            $this->ui->factory()->legacy("<iframe class='il-copg-mob-fullscreen' id='il-copg-mob-fullscreen" . $suffix . "'></iframe>")
        );
        $show_signal = $modal->getShowSignal();

        $js = "
            $(function () {
                il.COPagePres.setFullscreenModalShowSignal('$show_signal', '$suffix');
            });
        ";

        $this->global_tpl->addOnloadCode($js);

        return $a_output . "<div class='il-copg-mob-fullscreen-modal'>" . $this->ui->renderer()->render($modal) . "</div>";
    }

    public function getJavascriptFiles(
        string $a_mode
    ) : array {
        $js_files = ilPlayerUtil::getJsFilePaths();
        $js_files[] = iljQueryUtil::getLocalMaphilightPath();
        return $js_files;
    }

    public function getCssFiles(
        string $a_mode
    ) : array {
        $js_files = ilPlayerUtil::getCssFilePaths();

        return $js_files;
    }

    public function getStandardMediaAliasItem() : ilMediaAliasItem
    {
        $std_alias_item = new ilMediaAliasItem(
            $this->dom,
            $this->getHierId(),
            "Standard",
            $this->getPCId()
        );
        return $std_alias_item;
    }

    public function getFullscreenMediaAliasItem() : ilMediaAliasItem
    {
        $std_alias_item = new ilMediaAliasItem(
            $this->dom,
            $this->getHierId(),
            "Fullscreen",
            $this->getPCId()
        );
        return $std_alias_item;
    }

    public function checkInstanceEditing() : bool
    {
        // if any properties are set on the instance,
        // that are not accessible through the quick editing screen
        // -> offer instance editing
        $std_alias_item = $this->getStandardMediaAliasItem();
        if ($std_alias_item->hasAnyPropertiesSet()) {
            return true;
        }
        if ($this->getMediaObject()->hasFullscreenItem()) {
            $full_alias_item = $this->getFullscreenMediaAliasItem();
            if ($full_alias_item->hasAnyPropertiesSet()) {
                return true;
            }
        }

        // if the media object has any other use outside of the current page
        // -> offer instance editing
        /** @var $mob ilObjMediaObject */
        $mob = $this->getMediaObject();
        $page = $this->getPage();
        if (is_object($mob)) {
            $usages = $mob->getUsages();
            $other_usages = array_filter($usages, function ($usage) use ($page) {
                return ($usage["type"] != $page->getParentType() . ":pg" || $usage["id"] != $page->getId());
            });
            if (count($other_usages) > 0) {
                return true;
            }
        }
        return false;
    }

    public static function deleteHistoryLowerEqualThan(
        string $parent_type,
        int $page_id,
        string $lang,
        int $delete_lower_than_nr
    ) : void {
        global $DIC;

        $mob_usage_repo = $DIC->mediaObjects()
            ->internal()
            ->repo()
            ->usage();

        $log = ilLoggerFactory::getLogger("copg");

        $mob_ids = $mob_usage_repo->getHistoryUsagesLowerEqualThan(
            $parent_type . ":pg",
            $page_id,
            $delete_lower_than_nr,
            $lang
        );

        $mob_usage_repo->deleteHistoryUsagesLowerEqualThan(
            $parent_type . ":pg",
            $page_id,
            $delete_lower_than_nr,
            $lang
        );

        foreach ($mob_ids as $mob_id) {
            $usages = ilObjMediaObject::lookupUsages($mob_id, true);
            $log->debug("...check deletion of mob $mob_id. Usages: " . count($usages));
            if (count($usages) == 0) {
                $mob = new ilObjMediaObject($mob_id);
                $log->debug("Deleting Mob ID: " . $mob_id);
                $mob->delete();
            }
        }
    }
}
