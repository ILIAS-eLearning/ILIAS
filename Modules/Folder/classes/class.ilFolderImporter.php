<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * folder xml importer
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilFolderImporter extends ilXmlImporter
{
    private ?ilObject $folder = null;


    public function init(): void
    {
    }

    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping): void
    {
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $this->folder = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
        } elseif ($new_id = $a_mapping->getMapping('Services/Container', 'refs', '0')) {
            $this->folder = ilObjectFactory::getInstanceByRefId((int) $new_id, false);
        } elseif (!$this->folder instanceof ilObjFolder) {
            $this->folder = new ilObjFolder();
            $this->folder->create();
        }

        try {
            $parser = new ilFolderXmlParser($this->folder, $a_xml);
            $parser->start();
            $a_mapping->addMapping('Modules/Folder', 'fold', $a_id, (string) $this->folder->getId());
        } catch (ilSaxParserException $e) {
            $GLOBALS['ilLog']->write(__METHOD__ . ': Parsing failed with message, "' . $e->getMessage() . '".');
        }
    }
}
