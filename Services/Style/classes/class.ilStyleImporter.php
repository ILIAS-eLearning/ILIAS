<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for style
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: $
 * @ingroup ServicesStyle
 */
class ilStyleImporter extends ilXmlImporter
{
    /**
     * @var ilLogger
     */
    protected $log;

    public function init()
    {
        $this->log = ilLoggerFactory::getLogger('styl');

        include_once("./Services/Style/classes/class.ilStyleDataSet.php");
        $this->ds = new ilStyleDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());

        $this->log->debug("initialized");
    }

    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $this->log->debug("import xml " . $a_entity);

        if (true) {
            include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
            $parser = new ilDataSetImportParser(
                $a_entity,
                $this->getSchemaVersion(),
                $a_xml,
                $this->ds,
                $a_mapping
            );
            return;
        }

        // see ilStyleExporter::getXmlRepresentation()
        if (preg_match("/<StyleSheetExport><ImagePath>(.+)<\/ImagePath>/", $a_xml, $hits)) {
            $path = $hits[1];
            $a_xml = str_replace($hits[0], "", $a_xml);
            $a_xml = str_replace("</StyleSheetExport>", "", $a_xml);
        }
        
        // temp xml-file
        $tmp_file = $this->getImportDirectory() . "/sty_" . $a_id . ".xml";
        file_put_contents($tmp_file, $a_xml);
                
        include_once "./Services/Style/Content/classes/class.ilObjStyleSheet.php";
        $style = new ilObjStyleSheet();
        $style->createFromXMLFile($tmp_file);
        $new_id = $style->getId();
        
        unlink($tmp_file);
        
        // images
        if ($path) {
            $source = $this->getImportDirectory() . "/" . $path;
            if (is_dir($source)) {
                $target = $style->getImagesDirectory();
                if (!is_dir($target)) {
                    ilUtil::makeDirParents($target);
                }
                ilUtil::rCopy($source, $target);
            }
        }
        
        $a_mapping->addMapping("Services/Style", "sty", $a_id, $new_id);
    }
}
