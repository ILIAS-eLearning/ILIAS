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

use OrgUnit\PublicApi\OrgUnitUserService;

class ilMailTemplatePlaceholderToEmptyResolverTest extends ilMailBaseTestCase
{
    protected function tearDown(): void
    {
        $lang_property = new ReflectionProperty(ilDatePresentation::class, 'lang');
        $lang_property->setValue(null, null);

        parent::tearDown();
    }

    public function testNullRecipientResolvesContextPlaceholdersAndKeepsRecipientDependentNames(): void
    {
        $lng = $this->createMock(ilLanguage::class);
        $this->setGlobalVariable('lng', $lng);
        $env_helper = $this->createMock(ilMailEnvironmentHelper::class);
        $env_helper->method('getClientId')->willReturn('phpunit_client');
        $env_helper->method('getHttpPath')->willReturn('https://ilias.example/');

        $lng_helper = $this->createMock(ilMailLanguageHelper::class);
        $lng_helper->method('getCurrentLanguage')->willReturn($lng);

        $context = new class (
            new OrgUnitUserService(),
            $env_helper,
            new ilMailUserHelper(),
            $lng_helper
        ) extends ilMailTemplateContext {
            public function getId(): string
            {
                return 'phpunit_context';
            }

            public function getTitle(): string
            {
                return 'phpunit';
            }

            public function getDescription(): string
            {
                return 'phpunit';
            }

            public function getSpecificPlaceholders(): array
            {
                return [
                    'course_title' => [
                        'placeholder' => 'COURSE_TITLE',
                        'label' => 'Course Title',
                    ],
                ];
            }

            public function resolveSpecificPlaceholder(
                string $placeholder_id,
                array $context_parameters,
                ilObjUser $recipient = null
            ): string {
                if ('course_title' === $placeholder_id) {
                    return 'My Course';
                }

                return '';
            }
        };

        $resolver = new ilMailTemplatePlaceholderResolver(new Mustache_Engine());
        $message = 'Hello {{FIRST_NAME}} {{LAST_NAME}}, welcome to {{COURSE_TITLE}} at {{ILIAS_URL}}{{INSTALLATION_NAME}}';

        $resolved = $resolver->resolve($context, $message, null, ['ref_id' => 123]);

        $this->assertStringContainsString('FIRST_NAME', $resolved);
        $this->assertStringContainsString('LAST_NAME', $resolved);
        $this->assertStringNotContainsString('{{FIRST_NAME}}', $resolved);
        $this->assertStringNotContainsString('{{LAST_NAME}}', $resolved);
        $this->assertStringContainsString('My Course', $resolved);
        $this->assertStringContainsString('https://ilias.example/', $resolved);
        $this->assertStringContainsString('phpunit_client', $resolved);
    }
}
