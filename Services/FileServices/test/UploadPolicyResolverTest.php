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

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class UploadPolicyResolverTest extends TestCase
{
    public function testLimitedPolicy(): void
    {
        $rbac_mock = $this->createMock(\ilRbacReview::class);
        $user_mock = $this->createMock(\ilObjUser::class);

        $general_policy = new \UploadPolicy(
            1,
            'General',
            10,
            [],
            \UploadPolicy::AUDIENCE_TYPE_ALL_USERS,
            \UploadPolicy::SCOPE_DEFINITION_GLOBAL,
            true,
            null,
            null,
            6,
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $specifiy_policy = new \UploadPolicy(
            1,
            'Specific',
            1000,
            [],
            \UploadPolicy::AUDIENCE_TYPE_ALL_USERS,
            \UploadPolicy::SCOPE_DEFINITION_GLOBAL,
            true,
            new \DateTimeImmutable('today midnight'),
            new \DateTimeImmutable('tomorrow midnight'),
            6,
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $resolver = new \UploadPolicyResolver(
            $rbac_mock,
            $user_mock,
            [$general_policy, $specifiy_policy]
        );

        $this->assertEquals(1000, $resolver->getUserUploadSizeLimitInBytes() / 1000 / 1000);

        $specifiy_policy_expired = new \UploadPolicy(
            1,
            'Specific Expired',
            1000,
            [],
            \UploadPolicy::AUDIENCE_TYPE_ALL_USERS,
            \UploadPolicy::SCOPE_DEFINITION_GLOBAL,
            true,
            null,
            new \DateTimeImmutable('yesterday midnight'),
            6,
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );

        $resolver = new \UploadPolicyResolver(
            $rbac_mock,
            $user_mock,
            [$general_policy, $specifiy_policy_expired]
        );

        $this->assertEquals(10, $resolver->getUserUploadSizeLimitInBytes() / 1000 / 1000);
    }
}
