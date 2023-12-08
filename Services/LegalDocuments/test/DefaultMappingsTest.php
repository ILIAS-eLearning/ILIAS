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

namespace ILIAS\LegalDocuments\test;

use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\LegalDocuments\Condition\Definition\UserCountryDefinition;
use ILIAS\LegalDocuments\Condition\Definition\UserLanguageDefinition;
use ILIAS\LegalDocuments\Condition\Definition\RoleDefinition;
use ILIAS\DI\RBACServices;
use ilRbacReview;
use ilGlobalTemplateInterface;
use ILIAS\UI\Factory as UI;
use ilLanguage;
use ILIAS\DI\UIServices;
use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\DefaultMappings;
use ilObjectDataCache;

require_once __DIR__ . '/ContainerMock.php';

class DefaultMappingsTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(DefaultMappings::class, new DefaultMappings('foo', $this->mock(Container::class)));
    }

    public function testConditionDefinitions(): void
    {
        $container = $this->mockTree(Container::class, [
            'ui' => [
                'factory' => $this->mock(UI::class),
                'mainTemplate' => $this->mock(ilGlobalTemplateInterface::class),
            ],
            'language' => $this->mockMethod(ilLanguage::class, 'getInstalledLanguages', [], []),
            'rbac' => $this->mockMethod(RBACServices::class, 'review', [], $this->mock(ilRbacReview::class)),
        ]);
        $container->method('offsetGet')->with('ilObjDataCache')->willReturn($this->mock(ilObjectDataCache::class));

        $instance = new DefaultMappings('foo', $container);
        $result = $instance->conditionDefinitions();
        $definitions = $result->choices();
        $this->assertSame('usr_country', $result->defaultSelection());
        $this->assertSame(3, count($definitions));
        $this->assertInstanceOf(RoleDefinition::class, $definitions['usr_global_role']);
        $this->assertInstanceOf(UserLanguageDefinition::class, $definitions['usr_language']);
        $this->assertInstanceOf(UserCountryDefinition::class, $definitions['usr_country']);
    }

    public function testContentAsComponent(): void
    {
        $legacy = $this->mock(Legacy::class);

        $container = $this->mockTree(Container::class, [
            'ui' => ['factory' => $this->mockMethod(UIFactory::class, 'legacy', ['bar'], $legacy)],
        ]);

        $instance = new DefaultMappings('foo', $container);
        $map = $instance->contentAsComponent();
        $this->assertSame(['html'], array_keys($map));
        $this->assertSame($legacy, $map['html']($this->mockTree(DocumentContent::class, ['value' => 'bar'])));
    }
}
