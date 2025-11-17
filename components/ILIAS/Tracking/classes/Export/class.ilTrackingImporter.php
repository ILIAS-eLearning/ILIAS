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

use ILIAS\Tracking\Export\Exception as TrackingExportException;
use ILIAS\Tracking\Export\InfoInterface;
use ILIAS\Tracking\Factory as TrackingFactory;
use ILIAS\Tracking\FactoryInterface as TrackingFactoryInterface;

class ilTrackingImporter extends ilXmlImporter
{
    protected TrackingFactoryInterface $tracking_factory;

    public function init(): void
    {
        $this->tracking_factory = new TrackingFactory();
    }

    /**
     * @throws TrackingExportException
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        if (strcmp($a_entity, "lpsettings") === 0) {
            $this->importLPSettings($a_id, $a_xml, $a_mapping);
        }
    }

    /**
     * @throws TrackingExportException
     */
    protected function importLPSettings(
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $new_id = $this->getNewId($a_id, $a_mapping);
        $export_factory = $this->tracking_factory->export();
        $db_factory = $this->tracking_factory->db();
        $reader = $export_factory->xml()->reader();
        $info = $export_factory->info();
        $info = $reader->readLPSettings($a_xml, $info);
        $info = $reader->readLPCollection($a_xml, $info);
        $info = $reader->readLPStatusCollection($a_xml, $info);
        $info = $this->applyMappings($new_id, $a_mapping, $info);
        $db_factory->lpSettings()->repository()->writeLPSettings($info->getLPSettings());
        $db_factory->lpCollection()->repository()->writeLPCollection($info->getLPCollection());
        foreach ($reader->readAdditionalContentRootsIdentifierMap($a_xml) as $lp_status_id => $xml_root) {
            $info->getLPStatusCollection()->getElementByStatusId((string) $lp_status_id)
                ->importCustomLPSettingsExportXML(
                    $new_id,
                    $a_mapping,
                    $xml_root
                );
        }
    }

    /**
     * @throws TrackingExportException
     */
    protected function getNewId(
        string $id,
        ilImportMapping $a_mapping
    ): int {
        $new_id = $a_mapping->getMapping("components/ILIAS/Tracking", "obj", $id);
        if (is_null($new_id)) {
            throw new TrackingExportException(sprintf("Object id (%s) during tracking import not found in mapping", $id));
        }
        return (int) $new_id;
    }

    protected function applyMappings(
        int $new_id,
        ilImportMapping $mapping,
        InfoInterface $info
    ): InfoInterface {
        $lp_collection = $info->getLPCollection();
        $elements = [];
        foreach ($lp_collection as $collection) {
            $new_item_id = $mapping->getMapping("components/ILIAS/Container", "refs", (string) $collection->getItemId());
            if (is_null($new_item_id)) {
                # Element is not exported, the element is ignored.
                continue;
            }
            $new_item_id = (int) $new_item_id;
            $elements[] = $collection
                ->withItemId($new_item_id);
        }
        $new_lp_collection = $this->tracking_factory->db()->lpCollection()->element()->lpCollection(...$elements)
            ->withObjectId($new_id)
            ->withFixedNumObligatory();
        return $info
            ->withLPSettings($info->getLPSettings()->withObjectId($new_id))
            ->withLPCollection($new_lp_collection);
    }
}
