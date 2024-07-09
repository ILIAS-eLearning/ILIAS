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

namespace ILIAS\Skill\Profile;

use PHPUnit\Framework\TestCase;


class SkillProfileCompletionManagerTest extends TestCase
{
    protected function getManagerMock(): SkillProfileCompletionManager
    {
        $s_manager = $this->getProfileManagerMock();
        $repo = $this->getMockBuilder(SkillProfileCompletionDBRepository::class)->disableOriginalConstructor()->getMock();

        return new class ($s_manager, $repo) extends SkillProfileCompletionManager {
            public function __construct(
                protected SkillProfileManager $profile_manager,
                protected SkillProfileCompletionDBRepository $profile_completion_repo
            ) {
            }

            public function getActualMaxLevels(
                int $user_id,
                array $skills,
                string $gap_mode = "",
                string $gap_mode_type = "",
                int $gap_mode_obj_id = 0
            ): array {
                $actual_levels = [];

                if ($user_id === 101) {
                    $actual_levels[301][0] = 501;
                    $actual_levels[302][0] = 504;
                    $actual_levels[303][0] = 503;
                    $actual_levels[304][0] = 502;
                    $actual_levels[305][0] = 502;
                } elseif ($user_id === 102) {
                    $actual_levels[301][0] = 505;
                    $actual_levels[302][0] = 505;
                    $actual_levels[303][0] = 505;
                    $actual_levels[304][0] = 505;
                    $actual_levels[305][0] = 505;
                } elseif ($user_id === 103) {
                    $actual_levels[301][0] = 505;
                    $actual_levels[302][0] = 501;
                    $actual_levels[303][0] = 505;
                    $actual_levels[304][0] = 501;
                    $actual_levels[305][0] = 501;
                } elseif ($user_id === 104) {
                    $actual_levels[301][0] = 505;
                    $actual_levels[305][0] = 501;
                } elseif ($user_id === 105) {
                    // no levels
                }

                return $actual_levels;
            }
        };
    }

    protected function getProfileManagerMock(): SkillProfileManager
    {
        $p_repo = $this->getMockBuilder(SkillProfileDBRepository::class)->disableOriginalConstructor()->getMock();
        $pl_repo = $this->getMockBuilder(SkillProfileLevelsDBRepository::class)->disableOriginalConstructor()->getMock();
        $pu_repo = $this->getMockBuilder(SkillProfileUserDBRepository::class)->disableOriginalConstructor()->getMock();
        $pr_repo = $this->getMockBuilder(SkillProfileRoleDBRepository::class)->disableOriginalConstructor()->getMock();

        return new class ($p_repo, $pl_repo, $pu_repo, $pr_repo) extends SkillProfileManager {
            public function __construct(
                protected SkillProfileDBRepository $profile_repo,
                protected SkillProfileLevelsDBRepository $profile_levels_repo,
                protected SkillProfileUserDBRepository $profile_user_repo,
                protected SkillProfileRoleDBRepository $profile_role_repo
            ) {
            }

            public function getSkillLevels(int $profile_id): array
            {
                $levels = [];

                if ($profile_id > 0) {
                    $levels[] = new SkillProfileLevel($profile_id, 301, 0, 502, 10);
                    $levels[] = new SkillProfileLevel($profile_id, 302, 0, 505, 20);
                    $levels[] = new SkillProfileLevel($profile_id, 303, 0, 505, 30);
                    $levels[] = new SkillProfileLevel($profile_id, 304, 0, 504, 40);
                    $levels[] = new SkillProfileLevel($profile_id, 305, 0, 505, 50);
                }

                return $levels;
            }
        };
    }

    public function testGetProfileProgress(): void
    {
        $manager = $this->getManagerMock();

        $this->assertSame(0, $manager->getProfileProgress(101, 201));
        $this->assertSame(100, $manager->getProfileProgress(102, 201));
        $this->assertSame(40, $manager->getProfileProgress(103, 201));
        $this->assertSame(20, $manager->getProfileProgress(104, 201));
        $this->assertSame(0, $manager->getProfileProgress(105, 201));
        $this->assertSame(0, $manager->getProfileProgress(101, 0));
        $this->assertSame(0, $manager->getProfileProgress(0, 201));
        $this->assertSame(0, $manager->getProfileProgress(0, 0));
    }
}
