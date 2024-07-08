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

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use OrgUnit\PublicApi\OrgUnitUserService;
use OrgUnit\User\ilOrgUnitUser;

class ilMailTemplateContextTest extends ilMailBaseTestCase
{
    public function getAnonymousTemplateContext(
        OrgUnitUserService $orgUnitUserService,
        ilMailEnvironmentHelper $envHelper,
        ilMailUserHelper $usernameHelper,
        ilMailLanguageHelper $languageHelper
    ): ilMailTemplateContext {
        return new class ($orgUnitUserService, $envHelper, $usernameHelper, $languageHelper) extends
            ilMailTemplateContext {
            public function getId(): string
            {
                return 'phpunuit';
            }

            public function getTitle(): string
            {
                return 'phpunuit';
            }

            public function getDescription(): string
            {
                return 'phpunuit';
            }

            public function getSpecificPlaceholders(): array
            {
                return [];
            }

            public function resolveSpecificPlaceholder(
                string $placeholder_id,
                array $context_parameters,
                ilObjUser $recipient = null
            ): string {
                return '';
            }
        };
    }

    /**
     * @param Closure(): MockBuilder<ilOrgUnitUser> $mock_builder
     * @return array<int, ilOrgUnitUser&MockObject>
     * @throws ReflectionException
     */
    private static function generateOrgUnitUsers(Closure $mock_builder, int $amount): array
    {
        $users = [];

        for ($i = 1; $i <= $amount; $i++) {
            $user = $mock_builder()
                ->disableOriginalConstructor()
                ->onlyMethods(['getUserId',])
                ->getMock();
            $user->expects(self::atLeastOnce())->method('getUserId')->willReturn($i);

            $users[$i] = $user;
        }

        return $users;
    }

    /**
     * @return array<string, array{0: callable, 1: callable}>
     * @throws ReflectionException
     */
    public static function userProvider(): array
    {
        $test_users = [];

        foreach (
            [
                ['gender' => 'm', 'num_superiors' => 2,],
                ['gender' => 'n', 'num_superiors' => 1,],
                ['gender' => 'f', 'num_superiors' => 0,],
                ['gender' => '', 'num_superiors' => 3,],
            ] as $definition
        ) {
            /**
             * @param Closure(): MockBuilder<ilObjUser> $mock_builder $mock_builder
             */
            $user_callable = static function (Closure $mock_builder) use ($definition): ilObjUser&MockObject {
                $user = $mock_builder()
                    ->disableOriginalConstructor()
                    ->onlyMethods([
                        'getLanguage',
                        'getUTitle',
                        'getLogin',
                        'getLastname',
                        'getFirstname',
                        'getGender',
                        'getId',
                    ])
                    ->getMock();

                $user->expects(self::atLeastOnce())->method('getLanguage')->willReturn('de');
                $user->expects(self::atLeastOnce())->method('getUTitle')->willReturn('###Dr. Ing###');
                $user->expects(self::atLeastOnce())->method('getLogin')->willReturn('###phpunit###');
                $user->expects(self::atLeastOnce())->method('getLastname')->willReturn('###Unit###');
                $user->expects(self::atLeastOnce())->method('getFirstname')->willReturn('###PHP###');
                $user->expects(self::atLeastOnce())->method('getGender')->willReturn($definition['gender']);
                $user->expects(self::atLeastOnce())->method('getId')->willReturn(4711);

                return $user;
            };

            /**
             * @param Closure(): MockBuilder<ilOrgUnitUser> $mock_builder
             * @return array{0: ilOrgUnitUser&MockObject, 1: list<ilOrgUnitUser&MockObject>}
             * @throws ReflectionException
             */
            $ou_user_callable = static function (Closure $mock_builder) use ($definition): array {
                $ou_user = $mock_builder()
                    ->disableOriginalConstructor()
                    ->onlyMethods(['getSuperiors',])
                    ->getMock();

                $superiors = self::generateOrgUnitUsers($mock_builder, $definition['num_superiors']);
                $ou_user->expects(self::atLeastOnce())->method('getSuperiors')->willReturn($superiors);

                return [$ou_user, $superiors];
            };

            $test_users[sprintf(
                'User with gender "%s" and %s superiors',
                $definition['gender'],
                $definition['num_superiors']
            )] = [$user_callable, $ou_user_callable];
        }

        return $test_users;
    }

    /**
     * @dataProvider userProvider
     * @param callable(Closure(): MockBuilder<ilObjUser>): ilObjUser                                           $user_callable
     * @param callable(Closure(): MockBuilder<ilOrgUnitUser>): array{0: ilOrgUnitUser, 1: list<ilOrgUnitUser>} $ou_user_callable
     * @throws ReflectionException
     */
    public function testGlobalPlaceholdersCanBeResolvedWithCorrespondingValues(
        callable $user_callable,
        callable $ou_user_callable
    ): void {
        $mock_builder_user_callable = function (): MockBuilder {
            return $this->getMockBuilder(ilObjUser::class);
        };
        $mock_builder_ou_user_callable = function (): MockBuilder {
            return $this->getMockBuilder(ilOrgUnitUser::class);
        };

        $user = $user_callable($mock_builder_user_callable);
        [$ou_user, $ou_superiors] = $ou_user_callable($mock_builder_ou_user_callable);

        $ou_service = $this->getMockBuilder(OrgUnitUserService::class)
                           ->disableOriginalConstructor()
                           ->onlyMethods(['getUsers',])
                           ->getMock();

        $lng = $this->getMockBuilder(ilLanguage::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods(['txt', 'loadLanguageModule',])
                    ->getMock();

        $env_helper = $this->getMockBuilder(ilMailEnvironmentHelper::class)
                           ->disableOriginalConstructor()
                           ->onlyMethods(['getClientId', 'getHttpPath',])
                           ->getMock();

        $lng_helper = $this->getMockBuilder(ilMailLanguageHelper::class)
                           ->disableOriginalConstructor()
                           ->onlyMethods(['getLanguageByIsoCode', 'getCurrentLanguage',])
                           ->getMock();

        $user_helper = $this->getMockBuilder(ilMailUserHelper::class)
                            ->disableOriginalConstructor()
                            ->onlyMethods(['getUsernameMapForIds',])
                            ->getMock();

        $ou_service->expects($this->atLeastOnce())->method('getUsers')->willReturn([$ou_user,]);
        $lng->expects($this->atLeastOnce())->method('txt')->will($this->returnArgument(0));
        $env_helper->expects($this->atLeastOnce())->method('getClientId')->willReturn('###phpunit_client###');
        $env_helper->expects($this->atLeastOnce())->method('getHttpPath')->willReturn('###http_ilias###');
        $lng_helper->expects($this->atLeastOnce())->method('getLanguageByIsoCode')->willReturn($lng);
        $lng_helper->expects($this->atLeastOnce())->method('getCurrentLanguage')->willReturn($lng);

        $expected_ids_constraint = [];
        if ($ou_superiors !== []) {
            $expected_ids_constraint = self::logicalAnd(
                ...array_map(
                    static function (ilOrgUnitUser $user): \PHPUnit\Framework\Constraint\TraversableContainsEqual {
                        return self::containsEqual($user->getUserId());
                    },
                    $ou_superiors
                )
            );
        }

        $first_and_last_names = array_map(static function (ilOrgUnitUser $user, int $key): string {
            return "PhpSup$key UnitSup$key";
        }, $ou_superiors, array_keys($ou_superiors));

        $user_helper->expects($this->atLeastOnce())->method('getUsernameMapForIds')
                    ->with($expected_ids_constraint)
                    ->willReturn($first_and_last_names);

        $context = $this->getAnonymousTemplateContext(
            $ou_service,
            $env_helper,
            $user_helper,
            $lng_helper
        );

        $mustache = new Mustache_Engine();
        $placeholder_resolver = new ilMailTemplatePlaceholderResolver($mustache);

        $message = implode('', [
            '{{MAIL_SALUTATION}}',
            '{{FIRST_NAME}}',
            '{{LAST_NAME}}',
            '{{LOGIN}}',
            '{{TITLE}}',
            '{{FIRSTNAME_LASTNAME_SUPERIOR}}',
            '{{ILIAS_URL}}',
            '{{INSTALLATION_NAME}}',
        ]);
        $replace_message = $placeholder_resolver->resolve($context, $message, $user);

        $this->assertStringContainsString('###Dr. Ing###', $replace_message);
        $this->assertStringContainsString('###phpunit###', $replace_message);
        $this->assertStringContainsString('###Unit###', $replace_message);
        $this->assertStringContainsString('###PHP###', $replace_message);
        $this->assertStringContainsString('###phpunit_client###', $replace_message);
        $this->assertStringContainsString('###http_ilias###', $replace_message);
        $this->assertStringContainsString('mail_salutation_' . $user->getGender(), $replace_message);

        foreach ($first_and_last_names as $firstAndLastname) {
            $this->assertStringContainsString($firstAndLastname, $replace_message);
        }
    }
}
