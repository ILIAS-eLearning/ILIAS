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

namespace ILIAS\WebResource\Search;

use ILIAS\Search\Presentation\Result\Subitem\PropertiesReader;
use ILIAS\DI\Container;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ilObject;
use ilWebLinkDatabaseRepository;
use ilWebLinkDatabaseRepositoryException;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesFactory;
use ILIAS\Search\Presentation\Result\Subitem\ID;
use Generator;

class SubitemPropertiesReader implements PropertiesReader
{
    protected ilLanguage $lng;
    protected DataFactory $data_factory;

    public static function type(): string
    {
        return 'webr';
    }

    public function init(Container $dic): void
    {
        $this->lng = $dic->language();
        $this->data_factory = new DataFactory();
    }

    public function getSubitemProperties(
        PropertiesFactory $factory,
        int $parent_ref_id,
        ID ...$subitem_ids
    ): Generator {
        $obj_id = ilObject::_lookupObjId($parent_ref_id);
        $repo = new ilWebLinkDatabaseRepository($obj_id);
        foreach ($subitem_ids as $subitem_id) {
            try {
                $item = $repo->getItemByLinkId((int) $subitem_id->id());
            } catch (ilWebLinkDatabaseRepositoryException $e) {
                continue;
            }
            yield $factory->get(
                $subitem_id,
                $item->getTitle(),
                $this->data_factory->uri($item->getResolvedLink(false)),
                true,
                $this->lng->txt('webr')
            );
        }
    }
}
