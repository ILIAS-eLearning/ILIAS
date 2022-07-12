<?php declare(strict_types=1);

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
 * Class for category export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCategoryExporter extends ilXmlExporter
{
    /**
     * Get head dependencies
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        // always trigger container because of co-page(s)
        return [
            [
                'component' => 'Services/Container',
                'entity' => 'struct',
                'ids' => $a_ids
            ]
        ];
    }
    
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        if ($a_entity === "cat") {
            $tax_ids = [];
            foreach ($a_ids as $id) {
                $t_ids = ilObjTaxonomy::getUsageOfObject((int) $id);
                if (count($t_ids) > 0) {
                    $tax_ids[$t_ids[0]] = $t_ids[0];
                }
            }

            return [
                [
                    "component" => "Services/Taxonomy",
                    "entity" => "tax",
                    "ids" => $tax_ids
                ]
            ];
        }
        return [];
    }

    /**
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $all_ref = ilObject::_getAllReferences((int) $a_id);
        $cat_ref_id = end($all_ref);
        $category = ilObjectFactory::getInstanceByRefId($cat_ref_id, false);

        if (!$category instanceof ilObjCategory) {
            $GLOBALS['ilLog']->write(__METHOD__ . $a_id . ' is not instance of type category!');
            return '';
        }


        $writer = new ilCategoryXmlWriter($category);
        $writer->setMode(ilCategoryXmlWriter::MODE_EXPORT);
        $writer->export(false);
        return $writer->getXml();
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return [
            "4.3.0" => [
                "namespace" => "https://www.ilias.de/Modules/Category/cat/4_3",
                "xsd_file" => "ilias_cat_4_3.xsd",
                "uses_dataset" => false,
                "min" => "4.3.0",
                "max" => ""
            ]
        ];
    }

    /**
     * Init method
     */
    public function init() : void
    {
    }
}
