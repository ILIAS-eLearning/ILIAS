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

declare(strict_types=1);

/**
 * Class ilCmiXapiExporter
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiExporter extends ilXmlExporter
{
    public const ENTITY = 'cmix';
    public const SCHEMA_VERSION = '5.1.0';

    //    private $main_object = null;
    private ?ilCmiXapiDataSet $_dataset = null;

    public function __construct()
    {
        parent::__construct();
        $this->_dataset = new ilCmiXapiDataSet();
        $this->_dataset->initByExporter($this);
        $this->_dataset->setDSPrefix("ds");

        /*
        $this->main_object = $a_main_object;
        include_once("./components/ILIAS/CmiXapi/classes/class.ilCmiXapiDataSet.php");
        $this->dataset = new ilCmiXapiDataSet($this->main_object->getRefId());
        $this->getXmlRepresentation(self::ENTITY, self::SCHEMA_VERSION, $this->main_object->getRefId());
        */
    }

    public function init(): void
    {
    }

    /**
     * Get xml representation
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        return $this->_dataset->getCmiXapiXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }

    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ): array {
        $dependencies = [];

        $md_ids = [];
        foreach ($a_ids as $id) {
            $md_ids[] = $id . ":0:cmix";
        }
        if ($md_ids !== []) {
            $dependencies[] = [
                "component" => "components/ILIAS/MetaData",
                "entity" => "md",
                "ids" => $md_ids
            ];
        }

        return $dependencies;
    }

    /**
     * @return array<string, array<string, string|bool>>
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "5.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/CmiXapi/cmix/5_1",
                "xsd_file" => "xml/ilias_cmix_5_1.xsd",
                "uses_dataset" => true,
                "min" => "5.1.0",
                "max" => "")
        );
    }
}
