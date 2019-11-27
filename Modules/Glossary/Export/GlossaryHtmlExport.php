<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Glossary\Export;

/**
 * Glossary HTML Export
 *
 * @author killing@leifos.de
 */
class GlossaryHtmlExport
{
    /**
     * @var \ilObjGlossary
     */
    protected $glossary;

    /**
     * @var string
     */
    protected $export_dir;

    /**
     * @var string
     */
    protected $sub_dir;

    /**
     * @var string
     */
    protected $target_dir;

    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    protected $global_screen;

    /**
     * @var \ILIAS\Services\Export\HTML\Util
     */
    protected $export_util;

    /**
     * @var \ilCOPageHTMLExport
     */
    protected $co_page_html_export;

    /**
     * GlossaryHtmlExport constructor.
     * @param \ilObjGlossary $glo
     * @param string $exp_dir
     * @param string $sub_dir
     */
    public function __construct(\ilObjGlossary $glo, string $exp_dir, string $sub_dir)
    {
        global $DIC;

        $this->glossary = $glo;
        $this->export_dir = $exp_dir;
        $this->sub_dir = $sub_dir;
        $this->target_dir = $exp_dir."/".$sub_dir;

        $this->global_screen = $DIC->globalScreen();
        $this->export_util = new \ILIAS\Services\Export\HTML\Util();
    }

    /**
     * Initialize directories
     */
    protected function initDirectories()
    {
        // initialize temporary target directory
        \ilUtil::delDir($this->target_dir);
        \ilUtil::makeDir($this->target_dir);
    }


    /**
     * export html package
     */
    function exportHTML()
    {
        $this->initDirectories();
        $this->export_util->exportSystemStyle();
        $this->export_util->exportCOPageFiles($this->glossary->getStyleSheetId());


        // get glossary presentation gui class
        $_GET["cmd"] = "nop";
        $glo_gui = new ilGlossaryPresentationGUI();
        $glo_gui->setOfflineMode(true);
        $glo_gui->setOfflineDirectory($a_target_dir);


        // export terms
        $this->exportHTMLGlossaryTerms($glo_gui, $a_target_dir);

        // export all media objects
        foreach ($this->offline_mobs as $mob)
        {
            $this->exportHTMLMOB($a_target_dir, $glo_gui, $mob, "_blank");
        }
        $_GET["obj_type"]  = "MediaObject";
        $_GET["obj_id"]  = $a_mob_id;
        $_GET["cmd"] = "";

        // export all file objects
        foreach ($this->offline_files as $file)
        {
            $this->exportHTMLFile($a_target_dir, $file);
        }

        // export images
        $image_dir = $a_target_dir."/images";
        ilUtil::makeDir($image_dir);
        ilUtil::makeDir($image_dir."/browser");
        copy(ilUtil::getImagePath("enlarge.svg", false, "filesystem"),
            $image_dir."/enlarge.svg");
        copy(ilUtil::getImagePath("browser/blank.png", false, "filesystem"),
            $image_dir."/browser/plus.png");
        copy(ilUtil::getImagePath("browser/blank.png", false, "filesystem"),
            $image_dir."/browser/minus.png");
        copy(ilUtil::getImagePath("browser/blank.png", false, "filesystem"),
            $image_dir."/browser/blank.png");
        copy(ilUtil::getImagePath("icon_st.svg", false, "filesystem"),
            $image_dir."/icon_st.svg");
        copy(ilUtil::getImagePath("icon_pg.svg", false, "filesystem"),
            $image_dir."/icon_pg.svg");
        copy(ilUtil::getImagePath("nav_arr_L.png", false, "filesystem"),
            $image_dir."/nav_arr_L.png");
        copy(ilUtil::getImagePath("nav_arr_R.png", false, "filesystem"),
            $image_dir."/nav_arr_R.png");

        // template workaround: reset of template
        $tpl = new ilGlobalTemplate("tpl.main.html", true, true);
        $tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);
        $tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

        // zip everything
            // zip it all
            $date = time();
            $zip_file = $this->getExportDirectory("html")."/".$date."__".IL_INST_ID."__".
                $this->getType()."_".$this->getId().".zip";
            ilUtil::zip($a_target_dir, $zip_file);
            ilUtil::delDir($a_target_dir);
    }


    /**
     * export glossary terms
     */
    function exportHTMLGlossaryTerms(&$a_glo_gui, $a_target_dir)
    {
        $copage_export = new ilCOPageHTMLExport($a_target_dir);
        $copage_export->exportSupportScripts();

        // index.html file
        $a_glo_gui->tpl = new ilGlobalTemplate("tpl.main.html", true, true);
        $style_name = $this->user->prefs["style"].".css";;
        $a_glo_gui->tpl->setVariable("LOCATION_STYLESHEET","./".$style_name);
        $a_glo_gui->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
        $a_glo_gui->tpl->setTitle($this->getTitle());

        $content = $a_glo_gui->listTerms();
        $file = $a_target_dir."/index.html";

        // open file
        if (!($fp = @fopen($file,"w+")))
        {
            die ("<b>Error</b>: Could not open \"".$file."\" for writing".
                " in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
        }
        chmod($file, 0770);
        fwrite($fp, $content);
        fclose($fp);

        $terms = $this->getTermList();

        $this->offline_mobs = array();
        $this->offline_files = array();

        foreach($terms as $term)
        {
            $a_glo_gui->tpl = new ilGlobalTemplate("tpl.main.html", true, true);
            $a_glo_gui->tpl = $copage_export->getPreparedMainTemplate();
            //$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");

            // set style
            $style_name = $this->user->prefs["style"].".css";;
            $a_glo_gui->tpl->setVariable("LOCATION_STYLESHEET","./".$style_name);

            $_GET["term_id"] = $term["id"];
            $_GET["frame"] = "_blank";
            $content = $a_glo_gui->listDefinitions($_GET["ref_id"],$term["id"],false);
            $file = $a_target_dir."/term_".$term["id"].".html";

            // open file
            if (!($fp = @fopen($file,"w+")))
            {
                die ("<b>Error</b>: Could not open \"".$file."\" for writing".
                    " in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
            }
            chmod($file, 0770);
            fwrite($fp, $content);
            fclose($fp);

            // store linked/embedded media objects of glosssary term
            $defs = ilGlossaryDefinition::getDefinitionList($term["id"]);
            foreach($defs as $def)
            {
                $def_mobs = ilObjMediaObject::_getMobsOfObject("gdf:pg", $def["id"]);
                foreach($def_mobs as $def_mob)
                {
                    $this->offline_mobs[$def_mob] = $def_mob;
                }

                // get all files of page
                $def_files = ilObjFile::_getFilesOfObject("gdf:pg", $def["id"]);
                $this->offline_files = array_merge($this->offline_files, $def_files);

            }
        }
    }

    /**
     * export media object to html
     */
    function exportHTMLMOB($a_target_dir, &$a_glo_gui, $a_mob_id)
    {
        $tpl = $this->tpl;

        $mob_dir = $a_target_dir."/mobs";

        $source_dir = ilUtil::getWebspaceDir()."/mobs/mm_".$a_mob_id;
        if (@is_dir($source_dir))
        {
            ilUtil::makeDir($mob_dir."/mm_".$a_mob_id);
            ilUtil::rCopy($source_dir, $mob_dir."/mm_".$a_mob_id);
        }

        $tpl = new ilGlobalTemplate("tpl.main.html", true, true);
        $tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
        $_GET["obj_type"]  = "MediaObject";
        $_GET["mob_id"]  = $a_mob_id;
        $_GET["cmd"] = "";
        $content = $a_glo_gui->media();
        $file = $a_target_dir."/media_".$a_mob_id.".html";

        // open file
        if (!($fp = @fopen($file,"w+")))
        {
            die ("<b>Error</b>: Could not open \"".$file."\" for writing".
                " in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
        }
        chmod($file, 0770);
        fwrite($fp, $content);
        fclose($fp);

        // fullscreen
        $mob_obj = new ilObjMediaObject($a_mob_id);
        if ($mob_obj->hasFullscreenItem())
        {
            $tpl = new ilGlobalTemplate("tpl.main.html", true, true);
            $tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
            $_GET["mob_id"]  = $a_mob_id;
            $_GET["cmd"] = "fullscreen";
            $content = $a_glo_gui->fullscreen();
            $file = $a_target_dir."/fullscreen_".$a_mob_id.".html";

            // open file
            if (!($fp = @fopen($file,"w+")))
            {
                die ("<b>Error</b>: Could not open \"".$file."\" for writing".
                    " in <b>".__FILE__."</b> on line <b>".__LINE__."</b><br />");
            }
            chmod($file, 0770);
            fwrite($fp, $content);
            fclose($fp);
        }
    }

    /**
     * export file object
     */
    function exportHTMLFile($a_target_dir, $a_file_id)
    {
        $file_dir = $a_target_dir."/files/file_".$a_file_id;
        ilUtil::makeDir($file_dir);
        $file_obj = new ilObjFile($a_file_id, false);
        $source_file = $file_obj->getDirectory($file_obj->getVersion())."/".$file_obj->getFileName();
        if (!is_file($source_file))
        {
            $source_file = $file_obj->getDirectory()."/".$file_obj->getFileName();
        }
        copy($source_file, $file_dir."/".$file_obj->getFileName());
    }

}