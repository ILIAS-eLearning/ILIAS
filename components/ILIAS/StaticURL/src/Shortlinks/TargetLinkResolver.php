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

namespace ILIAS\StaticURL\Shortlinks;

use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Button\Button;
use ILIAS\StaticURL\Shortlinks\Shortlink\Shortlink;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\StaticURL\Builder\URIBuilder;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Signal;
use ILIAS\StaticURL\Shortlinks\Shortlink\Target\Type;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class TargetLinkResolver
{
    public function __construct(
        private URIBuilder $uri_builder,
        private DataFactory $data_factory,
        private ?UIFactory $ui_factory = null,
    ) {
    }

    public function resolve(Shortlink $shortlink): URI|Signal|string|null
    {
        $data = $shortlink->getTargetData();

        return match ($shortlink->getTargetType()) {
            Type::REPO => $this->uri_builder->build(
                $data['type'],
                $this->data_factory->refId($data['ref_id'])
            ),
            Type::CUSTOM => '#',
            default => null
        };
    }

    protected function buildName(Shortlink $shortlink): string
    {
        $type_data = $shortlink->getTargetData();
        return match ($shortlink->getTargetType()) {
            Type::REPO => \ilObject2::_lookupTitle(\ilObject2::_lookupObjId($type_data['ref_id'])),
            Type::CUSTOM => $shortlink->getAlias(),
            default => '-'
        };
    }

    public function resolveLink(Shortlink $shortlink): ?Link
    {
        if ($this->ui_factory === null) {
            return null;
        }

        return $this->ui_factory->link()->standard(
            $this->buildName($shortlink),
            (string) $this->resolve($shortlink),
        );
    }

    public function resolveStandardButton(Shortlink $shortlink): ?Button
    {
        return $this->ui_factory->button()->standard(
            $this->buildName($shortlink),
            $this->resolve($shortlink),
        );
    }

}
