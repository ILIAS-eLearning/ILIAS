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

namespace ILIAS\LegalDocuments\test\Condition;

use ILIAS\LegalDocuments\Condition;
use ilObjUser;
use ILIAS\UI\Component\Legacy;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\LegalDocuments\Value\CriterionContent;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Condition\UserLanguage;
use ILIAS\LegalDocuments\Condition\Definition\UserLanguageDefinition;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;

require_once __DIR__ . '/../ContainerMock.php';

class UserLanguageTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(UserLanguage::class, new UserLanguage(
            $this->stub(CriterionContent::class),
            $this->stub(UserLanguageDefinition::class),
            $this->stub(UI::class)
        ));
    }

    public function testAsComponent(): void
    {
        $legacy = $this->stub(Legacy\Content::class);
        $instance = new UserLanguage(
            $this->stubTree(CriterionContent::class, ['arguments' => ['lng' => 'foo']]),
            $this->stub(UserLanguageDefinition::class),
            $this->stubTree(UI::class, ['create' => ['legacy' => ['content' => $legacy]]])
        );

        $this->assertSame($legacy, $instance->asComponent());
    }

    public function testEval(): void
    {
        $instance = new UserLanguage(
            $this->stubTree(CriterionContent::class, ['arguments' => ['lng' => 'foo']]),
            $this->stub(UserLanguageDefinition::class),
            $this->stub(UI::class)
        );

        $this->assertTrue($instance->eval($this->stubTree(ilObjUser::class, ['getLanguage' => 'foo'])));
    }

    public function testDefinition(): void
    {
        $definition = $this->stub(UserLanguageDefinition::class);
        $instance = new UserLanguage(
            $this->stub(CriterionContent::class),
            $definition,
            $this->stub(UI::class)
        );

        $this->assertSame($definition, $instance->definition());
    }

    public function testKnownToNeverMatchWith(): void
    {
        $instance = new UserLanguage(
            $this->stub(CriterionContent::class),
            $this->stub(UserLanguageDefinition::class),
            $this->stub(UI::class)
        );

        $second = new UserLanguage(
            $this->stub(CriterionContent::class),
            $this->stub(UserLanguageDefinition::class),
            $this->stub(UI::class)
        );

        $this->assertTrue($instance->knownToNeverMatchWith($second));
        $this->assertFalse($instance->knownToNeverMatchWith($this->stub(Condition::class)));
    }
}
