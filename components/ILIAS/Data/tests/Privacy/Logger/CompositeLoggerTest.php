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

namespace ILIAS\Data\Privacy\Logger;

use ILIAS\Data\Privacy\Fixtures\ConcretePrivacyDataType;
use ILIAS\Data\Privacy\Fixtures\InMemoryPrivacyLogger;
use ILIAS\Data\Privacy\Purpose\TechnicalProcessing;
use ILIAS\Data\Privacy\Source\SessionData;
use PHPUnit\Framework\TestCase;

class CompositeLoggerTest extends TestCase
{
    public function testEmptyCompositeIsANoOp(): void
    {
        $type = new ConcretePrivacyDataType(
            'raw',
            new SessionData('key'),
            new CompositeLogger([])
        );

        $this->assertSame('raw', $type->resolve(new TechnicalProcessing('noop')));
    }

    public function testFansOutToAllBackends(): void
    {
        $first = new InMemoryPrivacyLogger();
        $second = new InMemoryPrivacyLogger();

        $type = new ConcretePrivacyDataType(
            'raw',
            new SessionData('key'),
            new CompositeLogger([$first, $second])
        );
        $type->resolve(new TechnicalProcessing('comparison'));

        $first->assertLoggedOnce();
        $second->assertLoggedOnce();
        $first->assertLastPurposeIs('technical:comparison');
        $second->assertLastPurposeIs('technical:comparison');
    }

    public function testAcceptsGenerators(): void
    {
        $backend = new InMemoryPrivacyLogger();
        $generator = (static function () use ($backend): \Generator {
            yield $backend;
        })();

        $type = new ConcretePrivacyDataType(
            'raw',
            new SessionData('key'),
            new CompositeLogger($generator)
        );
        $type->resolve(new TechnicalProcessing('comparison'));

        $backend->assertLoggedOnce();
    }
}
