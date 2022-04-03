<?php

namespace ILIAS\OrgUnit\Webservices\SOAP;

use ilOrgUnitSimpleImport;

/**
 * Class ImportOrgUnitTree
 * @author Martin Studer ms@studer-raimann.ch
 */
class ImportOrgUnitTree extends Base
{
    public const ORG_UNIT_TREE = 'OrgUnitTree';

    /**
     * @param array $params
     * @return void
     * @throws \ilSoapPluginException
     */
    protected function run(array $params) : bool
    {
        global $DIC;
        $DIC->language()->loadLanguageModule('orgu');

        $importer = new ilOrgUnitSimpleImport();

        $xml = simplexml_load_string($params['OrgUnitTree']);

        if ($xml) {
            foreach ($xml->children() as $ou_id => $node) {
                $importer->simpleImportElement(simplexml_load_string($node->asXML()));
            }
        } else {
            throw new \ilSoapPluginException("Could not Read the XML File");
        }

        if (count($importer->getErrors()) || count($importer->getWarnings())) {
            $arr_msg = [];
            if ($importer->hasWarnings()) {
                $arr_msg[] = $DIC->language()->txt("import_terminated_with_warnings");
                foreach ($importer->getWarnings() as $warning) {
                    $arr_msg[$warning["import_id"]] = $DIC->language()->txt($warning["lang_var"]) . " (Import ID: " . $warning["import_id"] . ")";
                }
            }
            if ($importer->hasErrors()) {
                $arr_msg[] = $DIC->language()->txt("import_terminated_with_errors");
                foreach ($importer->getErrors() as $error) {
                    $arr_msg[$error["import_id"]] = $DIC->language()->txt($error["lang_var"]) . " (Import ID: " . $error["import_id"] . ")";
                }
            }

            throw new \ilSoapPluginException(implode(" / ", $arr_msg));
        }

        return true;
    }

    public function getName() : string
    {
        return "importOrgUnitsSimpleXML";
    }

    final protected function getAdditionalInputParams(): array
    {
        return array(self::ORG_UNIT_TREE => Base::TYPE_STRING);
    }

    final public function getOutputParams() : array
    {
        return [];
    }

    public function getDocumentation() : string
    {
        return "Imports ILIAS Organisational Units (SimpleXML)";
    }
}
