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

namespace ILIAS\Tracking\Export\XML;

use Exception;
use ILIAS\Tracking\DB\LPCollection\Element\FactoryInterface as LPCollectionElementFactoryInterface;
use ILIAS\Tracking\DB\LPSettings\Element\FactoryInterface as LPSettingsElementFactoryInterface;
use ILIAS\Tracking\Export\InfoInterface;
use ILIAS\Tracking\Status\FactoryInterface as LPStatusFactoryInterface;
use SimpleXMLElement;

class Reader implements ReaderInterface
{
    public function __construct(
        protected LPStatusFactoryInterface $lp_status_factory,
        protected LPSettingsElementFactoryInterface $lp_settings_element_factory,
        protected LPCollectionElementFactoryInterface $lp_collection_element_factory
    ) {
    }

    /**
     * @throws Exception
     */
    public function readLPSettings(
        string $xml,
        InfoInterface $target_info
    ): InfoInterface {
        $xml_root = new SimpleXMLElement($xml);
        $lp_settings = $this->lp_settings_element_factory->lpSettings()
            ->withObjectId((int) $xml_root->attributes()->object_id)
            ->withObjType((string) $xml_root->attributes()->object_type)
            ->withUMode((int) $xml_root->attributes()->u_mode)
            ->withVisits((int) $xml_root->attributes()->visits);
        return $target_info
            ->withLPSettings($lp_settings);
    }

    /**
     * @throws Exception
     */
    public function readLPCollection(
        string $xml,
        InfoInterface $target_info
    ): InfoInterface {
        $xml_root = new SimpleXMLElement($xml);
        $xml_lp_collection = $xml_root->LPCollection;
        $elements = [];
        foreach($xml_lp_collection->children() as $xml_lp_collection_element) {
            $lp_collection_element = $this->lp_collection_element_factory->lpCollectionElement()
                ->withNumObligatory((int) $xml_lp_collection_element->attributes()->num_obligatory)
                ->withLPMode((int) $xml_lp_collection_element->attributes()->lp_mode)
                ->withItemId((int) $xml_lp_collection_element->attributes()->item_id)
                ->withIsActive((bool) ((int)$xml_lp_collection_element->attributes()->active))
                ->withGroupingId((int) $xml_lp_collection_element->attributes()->grouping_id);
            $elements[] = $lp_collection_element;
        }
        $lp_collection = $this->lp_collection_element_factory->lpCollection(...$elements)
            ->withObjectId((int) $xml_root->attributes()->object_id);
        return $target_info
            ->withLPCollection($lp_collection);
    }

    /**
     * @throws Exception
     */
    public function readLPStatusCollection(
        string $xml,
        InfoInterface $target_info
    ): InfoInterface {
        $xml_root = new SimpleXMLElement($xml);
        $xml_lp_status_collection = $xml_root->LPStatusCollection;
        $lp_status_ids = [];
        foreach ($xml_lp_status_collection->children() as $xml_lp_status_element) {
            $lp_status_ids[] = (string) $xml_lp_status_element->attributes()->lp_status_id;
        }
        return $target_info
            ->withLPStatusCollection($this->lp_status_factory->allLPStatusImplementations()->getElementsByStatusIds(...$lp_status_ids));
    }

    /**
     * @return array<string, SimpleXMLElement>
     * @throws Exception
     */
    public function readAdditionalContentRootsIdentifierMap(
        string $xml
    ): array {
        $xml_root = new SimpleXMLElement($xml);
        $xml_lp_status_collection = $xml_root->LPStatusCollection;
        $map = [];
        foreach ($xml_lp_status_collection->children() as $xml_lp_status_element) {
            $lp_status_id = (string) $xml_lp_status_element->attributes()->lp_status_id;
            $content = count($xml_lp_status_element->children()) > 0 ? $xml_lp_status_element->children()[0] : null;
            if (is_null($content)) {
                continue;
            }
            $map[$lp_status_id] = $content;
        }
        return $map;
    }
}
