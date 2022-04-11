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
 * Booking definition
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesBooking
 */
class ilWebResourceExporter extends ilXmlExporter
{
    private ?ilWebLinkXmlWriter $writer = null;

    protected ilLogger $logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->logger = $DIC->logger()->webr();
    }

    /**
     * @inheritDoc
     */
    public function getXmlRepresentation(
        string $a_entity,
        string $a_schema_version,
        string $a_id
    ) : string {
        try {
            $this->writer = new ilWebLinkXmlWriter(false);
            $this->writer->setObjId((int) $a_id);
            $this->writer->write();
            return $this->writer->xmlDumpMem(false);
        } catch (UnexpectedValueException $e) {
            $this->logger->warning("Caught error: " . $e->getMessage());
            return '';
        }
    }

    /**
     * @inheritDoc
     */
    public function getXmlExportTailDependencies(
        string $a_entity,
        string $a_target_release,
        array $a_ids
    ) : array {
        $deps = [];
        // service settings
        $deps[] = [
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids
        ];

        return $deps;
    }

    /**
     * @inheritDoc
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.1.0" => array(
                "namespace" => "http://www.ilias.de/Modules/WebResource/webr/4_1",
                "xsd_file" => "ilias_webr_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => ""
            )
        );
    }

    public function init() : void
    {
    }
}
