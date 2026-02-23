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

namespace ILIAS\Forum\Search;

use ILIAS\Search\Presentation\Result\Subitem\PropertiesReader;
use ILIAS\DI\Container;
use ILIAS\Data\Factory as DataFactory;
use ilLanguage;
use ILIAS\Search\Presentation\Result\Subitem\PropertiesFactory;
use ILIAS\Search\Presentation\Result\Subitem\ID;
use Generator;
use ilObjForum;
use ILIAS\StaticURL\Services as StaticURL;

class SubitemPropertiesReader implements PropertiesReader
{
    protected ilLanguage $lng;
    protected DataFactory $data_factory;
    protected StaticURL $static_url;

    public static function type(): string
    {
        return 'frm';
    }

    public function init(Container $dic): void
    {
        $this->lng = $dic->language();
        $this->data_factory = new DataFactory();
        $this->static_url = $dic['static_url'];
    }

    public function getSubitemProperties(
        PropertiesFactory $factory,
        int $parent_ref_id,
        ID ...$subitem_ids
    ): Generator {
        foreach ($subitem_ids as $subitem_id) {
            $link = $this->static_url->builder()->build(
                'frm',
                $this->data_factory->refId($parent_ref_id),
                [$subitem_id->id()]
            );
            yield $factory->get(
                $subitem_id,
                ilObjForum::_lookupThreadSubject((int) $subitem_id->id()),
                $link,
                false,
                $this->lng->txt('thread')
            );
        }
    }
}
