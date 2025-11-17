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

use ILIAS\Tracking\Factory as TrackingFactory;
use ILIAS\Tracking\FactoryInterface as TrackingFactoryInterface;

class ilTrackingExporter extends ilXmlExporter
{
    protected TrackingFactoryInterface $tracking_factory;

    public function init(): void
    {
        $this->tracking_factory = new TrackingFactory();
    }

    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ): string {
        $db_factory = $this->tracking_factory->db();
        $export_factory = $this->tracking_factory->export();
        $lp_status_factory = $this->tracking_factory->status();
        $lp_settings = $db_factory->lpSettings()->repository()->readLPSettings((int) $a_id);
        $lp_collection = $db_factory->lpCollection()->repository()->readLPCollection((int) $a_id);
        $lp_status_collection = is_null($lp_settings) ? null : $lp_status_factory->allLPStatusImplementations()->getElementsByStatusIds(((string) $lp_settings->getUMode()));
        $info = $export_factory->info()
            ->withLPSettings($lp_settings)
            ->withLPCollection($lp_collection)
            ->withLPStatusCollection($lp_status_collection);
        $writer = $export_factory->xml()->writer();
        $writer->writeXMLByExportInfo($info);
        return $writer->__toString();
    }

    public function getValidSchemaVersions(
        string $a_entity
    ): array {
        return [
            "12.0" => [
                "namespace" => 'http://www.ilias.de/Components/Tracking/trac/12',
                "xsd_file" => 'ilias_trac_12_0.xsd',
                "uses_dataset" => false,
                "min" => "12.0",
                "max" => ""
            ]
        ];
    }
}
