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

namespace ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots;

use ILIAS\LegalDocuments\test\ContainerMock;
use ilRbacReview;
use ilDBInterface;
use ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots\OnlineStatusFilter;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../ContainerMock.php';

class OnlineStatusFilterTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(OnlineStatusFilter::class, new OnlineStatusFilter(
            $this->fail(...),
            $this->mock(ilRbacReview::class)
        ));
    }

    public function testInvoke(): void
    {
        if (!defined('SYSTEM_ROLE_ID')) {
            define('SYSTEM_ROLE_ID', 14);
        }
        if (!defined('SYSTEM_USER_ID')) {
            define('SYSTEM_USER_ID', 9);
        }

        $rbac = $this->mock(ilRbacReview::class);
        $rbac->method('isAssigned')->willReturnCallback(function (int $user, int $role) {
            $this->assertSame(SYSTEM_ROLE_ID, $role);
            return $user === 7;
        });

        $instance = new OnlineStatusFilter(
            fn($ids) => array_intersect($ids, [3, 4, 7]),
            $rbac
        );

        $this->assertSame([1, 2, 5, 6, 7, 8], $instance([1, 2, 3, 4, 5, 6, 7, 8]));
    }
}
