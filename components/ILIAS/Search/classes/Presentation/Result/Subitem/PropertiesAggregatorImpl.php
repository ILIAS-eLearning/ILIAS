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

namespace ILIAS\Search\Presentation\Result\Subitem;

use ILIAS\Data\URI;
use ILIAS\Search\Setup\BuildSubitemPresentationReadersObjective;
use ILIAS\DI\Container;
use Generator;

class PropertiesAggregatorImpl implements PropertiesAggregator
{
    public function __construct(
        protected Container $dic,
        protected PropertiesFactory $factory,
    ) {
    }

    /**
     * @var PropertiesReader[]
     */
    protected array $readers_by_type = [];

    /**
     * @return Properties[]
     */
    public function getSubitemProperties(
        int $parent_ref_id,
        string $parent_type,
        ID ...$subitem_ids
    ): Generator {
        if ($subitem_ids === []) {
            yield from [];
        }
        yield from $this->getReader($parent_type)?->getSubitemProperties(
            $this->factory,
            $parent_ref_id,
            ...$subitem_ids
        ) ?? [];
    }

    protected function getReader(string $parent_type): ?PropertiesReader
    {
        if (isset($this->readers_by_type[$parent_type])) {
            return $this->readers_by_type[$parent_type];
        }

        $class_name = (include BuildSubitemPresentationReadersObjective::PATH())[$parent_type] ?? null;
        if ($class_name === null || !class_exists((string) $class_name)) {
            return null;
        }
        $reader = new $class_name();
        if (!$reader instanceof PropertiesReader) {
            return null;
        }
        $reader->init($this->dic);
        return $this->readers_by_type[$parent_type] = $reader;
    }
}
