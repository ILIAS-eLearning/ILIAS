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
 * Webresource xml importer
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup components\ILIASWebResource
 */
class ilWebResourceImporter extends ilXmlImporter
{
    protected ?ilObjLinkResource $link = null;

    protected ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->logger = $DIC->logger()->webr();
    }

    /**
     * @inheritDoc
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        if ($new_id = $a_mapping->getMapping(
            'components/ILIAS/Container',
            'objs',
            $a_id
        )) {
            $this->link = ilObjectFactory::getInstanceByObjId((int) $new_id, false);
            if (!$this->link instanceof ilObjLinkResource) {
                throw new ilObjectNotFoundException(
                    'Invalid id given ' . $a_id
                );
            }
        } else {
            $this->link = new ilObjLinkResource();
            $this->link->setType('webr');
            $this->link->create(true);
        }

        try {
            $parser = new ilWebLinkXmlParser($this->link, $a_xml);
            $parser->setMode(ilWebLinkXmlParser::MODE_CREATE);
            $parser->start();
            $a_mapping->addMapping(
                'components/ILIAS/WebResource',
                'webr',
                $a_id,
                (string) $this->link->getId()
            );

            $a_mapping->addMapping(
                'components/ILIAS/MetaData',
                'md',
                $a_id . ':0:webr',
                $this->link->getId() . ':0:webr'
            );
        } catch (ilSaxParserException $e) {
            $this->logger->error(
                ': Parsing failed with message, "' . $e->getMessage() . '".'
            );
        } catch (ilWebLinkXMLParserException $e) {
            $this->logger->error(
                ': Parsing failed with message, "' . $e->getMessage() . '".'
            );
        }
    }
}
