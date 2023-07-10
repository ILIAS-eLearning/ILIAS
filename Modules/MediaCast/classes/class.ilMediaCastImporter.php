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
 * Importer class for media casts
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaCastImporter extends ilXmlImporter
{
    protected ilMediaCastDataSet $ds;

    public function init(): void
    {
        $this->ds = new ilMediaCastDataSet();
        $this->ds->setDSPrefix("ds");
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }

    public function finalProcessing(
        ilImportMapping $a_mapping
    ): void {
        // restore manual order
        $order = $this->ds->getOrder();
        if (sizeof($order)) {
            foreach ($order as $obj_id => $items) {
                $map = array();
                foreach ($items as $old_id) {
                    $map[] = $a_mapping->getMapping("Services/News", "news", $old_id);
                }

                $mcst = new ilObjMediaCast($obj_id, false);
                $mcst->saveOrder($map);
            }
        }
    }
}
