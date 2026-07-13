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

use ILIAS\Mail\TemplateEngine\MailTemplateContextAdapter;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use OrgUnit\PublicApi\OrgUnitUserService;
use OrgUnit\User\ilOrgUnitUser;
use PHPUnit\Framework\Attributes\DataProvider;

class ilMailTemplateContextTest extends ilMailBaseTestCase
{
    public function getAnonymousTemplateContext(
        OrgUnitUserService $org_unit_user_service,
        ilMailEnvironmentHelper $il_mail_environment_helper,
        ilMailUserHelper $mail_user_helper,
        ilMailLanguageHelper $language_helper
    ): ilMailTemplateContext {
        return new class ($org_unit_user_service, $il_mail_environment_helper, $mail_user_helper, $language_helper) extends
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
                ?ilObjUser $recipient = null
            ): string {
                return '';
            }
        };
    }

    /**
     * @param Closure(): MockBuilder<ilOrgUnitUser> $mock_builder
     * @return array<int, ilOrgUnitUser&MockObject>
     */
    private function generateOrgUnitUsers(Closure $mock_builder, int $amount): array
    {
        $users = [];

        for ($i = 1; $i <= $amount; $i++) {
            $user = $mock_builder()
                ->disableOriginalConstructor()
                ->onlyMethods(['getUserId',])
                ->getMock();
            $user->expects($this->atLeastOnce())->method('getUserId')->willReturn($i);

            $users[$i] = $user;
        }

        return $users;
    }

    /**
     * @return array<string, array{0: Closure(Closure(): MockBuilder<ilObjUser>): ilObjUser, 1: Closure(Closure(): MockBuilder<ilOrgUnitUser>): array{0: ilOrgUnitUser, 1: list<ilOrgUnitUser>}}>
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
            $user_callable = function (Closure $mock_builder) use ($definition): ilObjUser&MockObject {
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

                $user->expects($this->atLeastOnce())->method('getLanguage')->willReturn('de');
                $user->expects($this->atLeastOnce())->method('getUTitle')->willReturn('###Dr. Ing###');
                $user->expects($this->atLeastOnce())->method('getLogin')->willReturn('###phpunit###');
                $user->expects($this->atLeastOnce())->method('getLastname')->willReturn('###Unit###');
                $user->expects($this->atLeastOnce())->method('getFirstname')->willReturn('###PHP###');
                $user->expects($this->atLeastOnce())->method('getGender')->willReturn($definition['gender']);
                $user->expects($this->atLeastOnce())->method('getId')->willReturn(4711);

                return $user;
            };

            /**
             * @param Closure(): MockBuilder<ilOrgUnitUser> $mock_builder
             * @return array{0: ilOrgUnitUser&MockObject, 1: list<ilOrgUnitUser&MockObject>}
             */
            $ou_user_callable = function (Closure $mock_builder) use ($definition): array {
                $ou_user = $mock_builder()
                    ->disableOriginalConstructor()
                    ->onlyMethods(['getSuperiors',])
                    ->getMock();

                $superiors = $this->generateOrgUnitUsers($mock_builder, $definition['num_superiors']);
                $ou_user->expects($this->atLeastOnce())->method('getSuperiors')->willReturn($superiors);

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
     * @param Closure(Closure(): MockBuilder<ilObjUser>): ilObjUser                                           $user_callable
     * @param Closure(Closure(): MockBuilder<ilOrgUnitUser>): array{0: ilOrgUnitUser, 1: list<ilOrgUnitUser>} $ou_user_callable
     */
    #[DataProvider('userProvider')]
    public function testGlobalPlaceholdersCanBeResolvedWithCorrespondingValues(
        callable $user_callable,
        callable $ou_user_callable
    ): void {
        $mock_builder_user_callable = fn(): MockBuilder => $this->getMockBuilder(ilObjUser::class);
        $mock_builder_ou_user_callable = fn(): MockBuilder => $this->getMockBuilder(ilOrgUnitUser::class);

        $user_callable = Closure::bind($user_callable, $this, self::class);
        $ou_user_callable = Closure::bind($ou_user_callable, $this, self::class);

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
        $lng->expects($this->atLeastOnce())->method('txt')->willReturnArgument(0);
        $env_helper->expects($this->atLeastOnce())->method('getClientId')->willReturn('###phpunit_client###');
        $env_helper->expects($this->atLeastOnce())->method('getHttpPath')->willReturn('###http_ilias###');
        $lng_helper->expects($this->atLeastOnce())->method('getLanguageByIsoCode')->willReturn($lng);
        $lng_helper->method('getCurrentLanguage')->willReturn($lng);

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

        $mail_salutation_expected = $user->getGender() === ''
            ? 'mail_salutation_n'
            : 'mail_salutation_' . $user->getGender();
        $expected_values = [
            'MAIL_SALUTATION' => $mail_salutation_expected,
            'FIRST_NAME' => '###PHP###',
            'LAST_NAME' => '###Unit###',
            'LOGIN' => '###phpunit###',
            'TITLE' => '###Dr. Ing###',
            'FIRSTNAME_LASTNAME_SUPERIOR' => implode(', ', $first_and_last_names),
            'ILIAS_URL' => '###http_ilias### ',
            'INSTALLATION_NAME' => '###phpunit_client###',
        ];

        $engine = new class ($this, $expected_values) implements \ILIAS\Mail\TemplateEngine\TemplateEngineInterface {
            public function __construct(
                private readonly \PHPUnit\Framework\TestCase $test_case,
                /** @var array<string, string> */
                private readonly array $expected_values
            ) {
            }

            public function render(string $template, array|object $context): string
            {
                $this->test_case->assertInstanceOf(MailTemplateContextAdapter::class, $context);
                if (!str_contains($template, '{{')) {
                    return $template;
                }

                foreach ($this->expected_values as $placeholder => $expected_value) {
                    $this->test_case->assertSame(
                        $expected_value,
                        $context->{$placeholder},
                        sprintf('Context value for placeholder "%s" does not match.', $placeholder)
                    );
                }

                return '';
            }
        };

        $placeholder_resolver = new ilMailTemplatePlaceholderResolver($engine);

        $message = implode('', array_map(
            static fn(string $key): string => '{{' . $key . '}}',
            array_keys($expected_values)
        ));
        $placeholder_resolver->resolve($context, $message, $user);
    }
}
