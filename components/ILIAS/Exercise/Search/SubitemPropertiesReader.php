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

namespace ILIAS\Exercise\Search;

use ILIAS\Search\Presentation\Result\Subitem\PropertiesReader;
use ILIAS\DI\Container;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesFactory;
use ILIAS\Search\Presentation\Result\Subitem\ID;
use Generator;
use ilExAssignment;
use ilAccess;
use ILIAS\Exercise\PermanentLink\PermanentLinkManager;

class SubitemPropertiesReader implements PropertiesReader
{
    protected ilLanguage $lng;
    protected DataFactory $data_factory;
    protected ilAccess $access;
    protected PermanentLinkManager $permanent_link;

    public static function type(): string
    {
        return 'exc';
    }

    public function init(Container $dic): void
    {
        $this->lng = $dic->language();
        $this->lng->loadLanguageModule('exc');
        $this->data_factory = new DataFactory();
        $this->access = $dic->access();
        $this->permanent_link = $dic->exercise()->internal()->gui()->permanentLink();
    }

    public function getSubitemProperties(
        PropertiesFactory $factory,
        int $parent_ref_id,
        ID ...$subitem_ids
    ): Generator {
        foreach ($subitem_ids as $subitem_id) {
            if (!$this->isAssignmentVisible($parent_ref_id, (int) $subitem_id->id())) {
                continue;
            }
            $link = $this->data_factory->uri(
                $this->permanent_link->getPermanentLink($parent_ref_id, (int) $subitem_id->id())
            );
            yield $factory->get(
                $subitem_id,
                ilExAssignment::lookupTitle((int) $subitem_id->id()),
                $link,
                false,
                $this->lng->txt('exc_assignment')
            );
        }
    }

    protected function isAssignmentVisible(
        int $ref_id,
        int $subitem_id
    ): bool {
        if ($this->access->checkAccess('write', '', $ref_id)) {
            return true;
        }
        return ilExAssignment::lookupAssignmentOnline($subitem_id);
    }
}
