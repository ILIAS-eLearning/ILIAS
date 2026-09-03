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

namespace ILIAS\components\ResourceStorage\Collections\View;

use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Regression for Mantis 0045055: the duplicate behaviour of a collection view
 * is configurable via the OnDuplicate enum. Verifies the backwards compatible
 * default and the passthrough of every mode.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class ConfigurationTest extends TestCase
{
    public function testDefaultOnDuplicateIsAllow(): void
    {
        $configuration = new Configuration(
            $this->createStub(ResourceCollection::class),
            $this->createStub(ResourceStakeholder::class),
            'title'
        );

        $this->assertSame(OnDuplicate::ALLOW, $configuration->getOnDuplicate());
    }

    #[DataProvider('onDuplicateProvider')]
    public function testOnDuplicateIsPassedThrough(OnDuplicate $on_duplicate): void
    {
        $configuration = new Configuration(
            $this->createStub(ResourceCollection::class),
            $this->createStub(ResourceStakeholder::class),
            'title',
            Mode::DATA_TABLE,
            100,
            true,
            true,
            $on_duplicate
        );

        $this->assertSame($on_duplicate, $configuration->getOnDuplicate());
    }

    public static function onDuplicateProvider(): \Iterator
    {
        yield 'allow' => [OnDuplicate::ALLOW];
        yield 'replace' => [OnDuplicate::REPLACE];
        yield 'append revision' => [OnDuplicate::APPEND_REVISION];
        yield 'reject' => [OnDuplicate::REJECT];
    }
}
