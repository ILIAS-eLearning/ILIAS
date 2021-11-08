<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exporter class for sessions
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesForum
 */
class ilForumExporter extends ilXmlExporter
{
    private $ds;

    public function init() : void
    {
    }


    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $xml = '';

        if (ilObject::_lookupType($a_id) === 'frm') {
            $writer = new ilForumXMLWriter();
            $writer->setForumId($a_id);
            ilUtil::makeDirParents($this->getAbsoluteExportDirectory());
            $writer->setFileTargetDirectories($this->getRelativeExportDirectory(), $this->getAbsoluteExportDirectory());
            $writer->start();
            $xml .= $writer->getXml();
        }

        return $xml;
    }

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        $deps = [];

        if ('frm' === $a_entity) {
            $deps[] = [
                'component' => 'Services/Object',
                'entity' => 'common',
                'ids' => $a_ids
            ];

            // news settings
            $deps[] = [
                "component" => "Services/News",
                "entity" => "news_settings",
                "ids" => $a_ids
            ];
        }

        return $deps;
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return [
            "4.1.0" => [
                "namespace" => "http://www.ilias.de/Modules/Forum/frm/4_1",
                "xsd_file" => "ilias_frm_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "4.4.999"
            ],
            "4.5.0" => [
                "namespace" => "http://www.ilias.de/Modules/Forum/frm/4_5",
                "xsd_file" => "ilias_frm_4_5.xsd",
                "uses_dataset" => false,
                "min" => "4.5.0",
                "max" => "5.0.999"
            ],
            "5.1.0" => [
                "namespace" => "http://www.ilias.de/Modules/Forum/frm/5_1",
                "xsd_file" => "ilias_frm_5_1.xsd",
                "uses_dataset" => false,
                "min" => "5.1.0",
                "max" => ""
            ]
        ];
    }
}
