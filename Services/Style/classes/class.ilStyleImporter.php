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
 * Importer class for style
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilStyleImporter extends ilXmlImporter
{
    /**
     * @var ilLogger
     */
    protected $log;

    public function init() : void
    {
        $this->log = ilLoggerFactory::getLogger('styl');

        $this->ds = new ilStyleDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());

        $this->log->debug("initialized");
    }

    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        $this->log->debug("import xml " . $a_entity);

        if (true) {
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
                    ilFileUtils::makeDirParents($target);
                }
                ilFileUtils::rCopy($source, $target);
            }
        }
        
        $a_mapping->addMapping("Services/Style", "sty", $a_id, $new_id);
    }
}
