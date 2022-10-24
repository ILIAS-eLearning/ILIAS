<?php

declare(strict_types=1);

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

use OrgUnit\PublicApi\OrgUnitUserService;
use OrgUnit\User\ilOrgUnitUser;

/**
 * Class ilMailTemplateContextTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTemplateContextTest extends ilMailBaseTest
{
    public function getAnonymousTemplateContext(
        OrgUnitUserService $orgUnitUserService,
        ilMailEnvironmentHelper $envHelper,
        ilMailUserHelper $usernameHelper,
        ilMailLanguageHelper $languageHelper
    ): ilMailTemplateContext {
        return new class ($orgUnitUserService, $envHelper, $usernameHelper, $languageHelper) extends ilMailTemplateContext {
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
                ilObjUser $recipient = null,
                bool $html_markup = false
            ): string {
                return '';
            }
        };
    }

    /**
     * @throws ReflectionException
     */
    private function generateOrgUnitUsers(int $amount): array
    {
        $users = [];

        for ($i = 1; $i <= $amount; $i++) {
            $user = $this->getMockBuilder(ilOrgUnitUser::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getUserId',])
                ->getMock();
            $user->expects($this->atLeastOnce())->method('getUserId')->willReturn($i);

            $users[$i] = $user;
        }

        return $users;
    }

    /**
     * @throws ReflectionException
     */
    public function userProvider(): array
    {
        $testUsers = [];

        foreach ([
                     ['gender' => 'm', 'num_superiors' => 2,],
                     ['gender' => 'n', 'num_superiors' => 1,],
                     ['gender' => 'f', 'num_superiors' => 0,],
                     ['gender' => '', 'num_superiors' => 3,],
                 ] as $definition) {
            $user = $this->getMockBuilder(ilObjUser::class)
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

            $ouUser = $this->getMockBuilder(ilOrgUnitUser::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getSuperiors',])
                ->getMock();

            $superiors = $this->generateOrgUnitUsers($definition['num_superiors']);
            $ouUser->expects($this->atLeastOnce())->method('getSuperiors')->willReturn($superiors);

            $testUsers[sprintf(
                'User with gender "%s" and %s superiors',
                $definition['gender'],
                $definition['num_superiors']
            )] = [$user, $ouUser, $superiors,];
        }

        return $testUsers;
    }

    /**
     * @dataProvider userProvider
     * @param ilOrgUnitUser[] $superiors
     * @throws ReflectionException
     */
    public function testGlobalPlaceholdersCanBeResolvedWithCorrespondingValues(
        ilObjUser $user,
        ilOrgUnitUser $ouUser,
        array $superiors
    ): void {
        $ouService = $this->getMockBuilder(OrgUnitUserService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUsers',])
            ->getMock();

        $lng = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'loadLanguageModule',])
            ->getMock();

        $envHelper = $this->getMockBuilder(ilMailEnvironmentHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClientId', 'getHttpPath',])
            ->getMock();

        $lngHelper = $this->getMockBuilder(ilMailLanguageHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLanguageByIsoCode', 'getCurrentLanguage',])
            ->getMock();

        $userHelper = $this->getMockBuilder(ilMailUserHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUsernameMapForIds',])
            ->getMock();

        $ouService->expects($this->atLeastOnce())->method('getUsers')->willReturn([$ouUser,]);
        $lng->expects($this->atLeastOnce())->method('txt')->will($this->returnArgument(0));
        $envHelper->expects($this->atLeastOnce())->method('getClientId')->willReturn('###phpunit_client###');
        $envHelper->expects($this->atLeastOnce())->method('getHttpPath')->willReturn('###http_ilias###');
        $lngHelper->expects($this->atLeastOnce())->method('getLanguageByIsoCode')->willReturn($lng);
        $lngHelper->expects($this->atLeastOnce())->method('getCurrentLanguage')->willReturn($lng);

        if ($superiors === []) {
            $expectedIdsConstraint = [];
        } else {
            $expectedIdsConstraint = self::logicalAnd(...array_map(static function (ilOrgUnitUser $user) {
                return self::containsEqual($user->getUserId());
            }, $superiors));
        }

        $firstAndLastnames = array_map(static function (ilOrgUnitUser $user, int $key): string {
            return "PhpSup$key UnitSup$key";
        }, $superiors, array_keys($superiors));

        $userHelper->expects($this->atLeastOnce())->method('getUsernameMapForIds')
            ->with($expectedIdsConstraint)
            ->willReturn($firstAndLastnames);

        $context = $this->getAnonymousTemplateContext(
            $ouService,
            $envHelper,
            $userHelper,
            $lngHelper
        );

        $placeholderResolver = new ilMailTemplatePlaceholderResolver($context, implode('', [
            '[MAIL_SALUTATION]',
            '[FIRST_NAME]',
            '[LAST_NAME]',
            '[LOGIN]',
            '[TITLE]',
            '[FIRSTNAME_LASTNAME_SUPERIOR]',
            '[ILIAS_URL]',
            '[INSTALLATION_NAME]',
        ]));

        $replaceMessage = $placeholderResolver->resolve($user);

        $this->assertStringContainsString('###Dr. Ing###', $replaceMessage);
        $this->assertStringContainsString('###phpunit###', $replaceMessage);
        $this->assertStringContainsString('###Unit###', $replaceMessage);
        $this->assertStringContainsString('###PHP###', $replaceMessage);
        $this->assertStringContainsString('###phpunit_client###', $replaceMessage);
        $this->assertStringContainsString('###http_ilias###', $replaceMessage);
        $this->assertStringContainsString('mail_salutation_' . $user->getGender(), $replaceMessage);

        foreach ($firstAndLastnames as $firstAndLastname) {
            $this->assertStringContainsString($firstAndLastname, $replaceMessage);
        }
    }
}
