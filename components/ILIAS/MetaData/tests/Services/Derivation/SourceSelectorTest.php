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

namespace ILIAS\MetaData\Services\Derivation;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Repository\NullRepository;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Elements\NullSet;
use ILIAS\MetaData\Services\Derivation\Creation\NullCreator;

class SourceSelectorTest extends TestCase
{
    protected function getSourceSelector(): SourceSelector
    {
        $repo = new class () extends NullRepository {
            public function getMD(int $obj_id, int $sub_id, string $type): SetInterface
            {
                return new class ($obj_id, $sub_id, $type) extends NullSet {
                    public function __construct(
                        public int $obj_id,
                        public int $sub_id,
                        public string $type,
                    ) {
                    }
                };
            }
        };

        $creator = new class () extends NullCreator {
            public function createSet(
                string $title,
                string $description = '',
                string $language = ''
            ): SetInterface {
                return new class ($title, $description, $language) extends NullSet {
                    public function __construct(
                        public string $title,
                        public string $description,
                        public string $language
                    ) {
                    }
                };
            }
        };

        return new class ($repo, $creator) extends SourceSelector {
            protected function getDerivator(SetInterface $from_set): DerivatorInterface
            {
                return new class ($from_set) extends NullDerivator {
                    public function __construct(public SetInterface $from_set)
                    {
                    }
                };
            }
        };
    }

    public function testFromObject(): void
    {
        $source_selector = $this->getSourceSelector();
        $derivator = $source_selector->fromObject(7, 33, 'type');

        $this->assertSame(7, $derivator->from_set->obj_id);
        $this->assertSame(33, $derivator->from_set->sub_id);
        $this->assertSame('type', $derivator->from_set->type);
    }

    public function testFromObjectWithSubIDZero(): void
    {
        $source_selector = $this->getSourceSelector();
        $derivator = $source_selector->fromObject(67, 0, 'type');

        $this->assertSame(67, $derivator->from_set->obj_id);
        $this->assertSame(67, $derivator->from_set->sub_id);
        $this->assertSame('type', $derivator->from_set->type);
    }

    public function testFromBasicProperties(): void
    {
        $source_selector = $this->getSourceSelector();

        $derivator = $source_selector->fromBasicProperties(
            'great title',
            'amazing description',
            'best language'
        );

        $this->assertSame('great title', $derivator->from_set->title);
        $this->assertSame('amazing description', $derivator->from_set->description);
        $this->assertSame('best language', $derivator->from_set->language);
    }
}
