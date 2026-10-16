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

namespace ILIAS\Setup\Activities;

use ILIAS\Component\Activities\ActivityType;
use ILIAS\Data\Description\Factory as DescriptionFactory;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GetStatusTest extends TestCase
{
    public function testItIsAQuery(): void
    {
        $this->assertSame(ActivityType::Query, (new GetStatus())->getType());
    }

    public function testItDescribesItselfWithoutFailing(): void
    {
        $activity = new GetStatus();

        $this->assertStringContainsString('status', $activity->getDescription()->getRawRepresentation());
        $this->assertNotNull($activity->getOutputDescription(new DescriptionFactory()));
    }

    /**
     * @return array<string, array{int}>
     */
    public static function users(): array
    {
        return [
            'nobody' => [0],
            'the root user' => [6],
            'somebody' => [42],
        ];
    }

    /**
     * What the setup knows is nothing for everybody with a session - as long
     * as there is no check for administrators, nobody gets it.
     */
    #[DataProvider('users')]
    public function testNobodyIsAllowedYet(int $usr_id): void
    {
        $activity = new GetStatus();

        $this->assertFalse($activity->isAllowedToPerform($usr_id, []));

        $result = $activity->maybePerformAs($this->createStub(InputFactory::class), $usr_id, []);

        $this->assertTrue($result->isError());
        $this->assertSame(403, $result->error()->getCode());
    }
}
