<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use OrgUnit\PublicApi\OrgUnitUserService;
use OrgUnit\User\ilOrgUnitUser;

/**
 * Class ilMailTemplateContextTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTemplateContextTest extends ilMailBaseTest
{
    /**
     * @param OrgUnitUserService $orgUnitUserService
     * @return ilMailTemplateContext
     */
    public function getAnonymousTemplateContext(OrgUnitUserService $orgUnitUserService) : ilMailTemplateContext
    {
        return new class($orgUnitUserService) extends ilMailTemplateContext
        {
            public function getId() : string
            {
                return 'phpunuit';
            }

            public function getTitle() : string
            {
                return 'phpunuit';
            }

            public function getDescription() : string
            {
                return 'phpunuit';
            }

            public function getSpecificPlaceholders() : array
            {
                return [];
            }

            public function resolveSpecificPlaceholder(
                string $placeholder_id,
                array $context_parameters,
                ilObjUser $recipient = null,
                bool $html_markup = false
            ) : string {
                return '';
            }
        };
    }

    /**
     * @throws ReflectionException
     */
    public function testGenericContextPlaceholders() : void
    {
        $ouSuperiorUser = $this
            ->getMockBuilder(ilOrgUnitUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId', 'getSuperiors'])
            ->getMock();
        
        $ouUser = $this
            ->getMockBuilder(ilOrgUnitUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId', 'getSuperiors'])
            ->getMock();

        $ouService = $this
            ->getMockBuilder(OrgUnitUserService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUsers',])
            ->getMock();

        $lng = $this
            ->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->setMethods(['txt',])
            ->getMock();

        $ouSuperiorUser->expects($this->atLeastOnce())->method('getUserId')->willReturn(4712);
        $ouUser->expects($this->atLeastOnce())->method('getSuperiors')->willReturn([$ouSuperiorUser]);
        $ouUser->expects($this->atLeastOnce())->method('getUserId')->willReturn(4711);
        $ouService->expects($this->atLeastOnce())->method('getUsers')->willReturn([$ouUser]);
        
        $context = $this->getAnonymousTemplateContext($ouService);
        $lng->expects($this->atLeastOnce())->method('txt')->willReturn($this->returnArgument(0));

        $context->setLanguage($lng);
        $this->setGlobalVariable('lng', $lng);
        
        $placeholderResolver = new ilMailTemplatePlaceholderResolver($context, implode('', [
            '[MAIL_SALUTATION]',
            '[FIRST_NAME]',
            '[LAST_NAME]',
            '[LOGIN]',
            '[TITLE]',
            '[FIRSTNAME_LASTNAME_SUPERIOR]',
            '[ILIAS_URL]',
            '[CLIENT_NAME]',
        ]));

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods([
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
        $user->expects($this->atLeastOnce())->method('getGender')->willReturn('m');
        $user->expects($this->atLeastOnce())->method('getId')->willReturn(4711);

        $replaceMessage = $placeholderResolver->resolve($user, []);

        $this->assertStringContainsString('###Dr. Ing###', $replaceMessage);
        $this->assertStringContainsString('###phpunit###', $replaceMessage);
        $this->assertStringContainsString('###Unit###', $replaceMessage);
        $this->assertStringContainsString('###PHP###', $replaceMessage);
    }
}