<?php

declare(strict_types=1);

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
 * folder xml importer
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
abstract class ilContainerReferenceImporter extends ilXmlImporter
{
    protected ?ilContainerReference $ref = null;

    public function init(): void
    {
    }

    protected function initReference(int $a_ref_id = 0): void
    {
        /** @var ilContainerReference $ref */
        $ref = ilObjectFactory::getInstanceByRefId($a_ref_id, true);
        $this->ref = $ref;
    }

    /**
     * Get reference type
     */
    abstract protected function getType(): string;

    abstract protected function initParser(string $a_xml): ilContainerReferenceXmlParser;

    protected function getReference(): ilContainerReference
    {
        return $this->ref;
    }

    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        global $DIC;

        $objDefinition = $DIC["objDefinition"];
        $log = $DIC->logger()->root();

        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $refs = ilObject::_getAllReferences((int) $new_id);
            $this->initReference(end($refs));
        }
        // Mapping for containers without subitems
        elseif ($new_id = $a_mapping->getMapping('Services/Container', 'refs', '0')) {
            $this->initReference((int) $new_id);
        } elseif (!$this->getReference() instanceof ilContainerReference) {
            $this->initReference();
            $this->getReference()->create();
        }

        try {
            /** @var ilContainerReferenceXmlParser $parser */
            $parser = $this->initParser($a_xml);
            $parser->setImportMapping($a_mapping);
            $parser->setReference($this->getReference());
            $parser->setMode(ilContainerReferenceXmlParser::MODE_UPDATE);
            $parser->startParsing();

            $a_mapping->addMapping(
                $objDefinition->getComponentForType($this->getType()),
                $this->getType(),
                $a_id,
                (string) $this->getReference()->getId()
            );
        } catch (ilSaxParserException | Exception $e) {
            $log->error(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }
}
