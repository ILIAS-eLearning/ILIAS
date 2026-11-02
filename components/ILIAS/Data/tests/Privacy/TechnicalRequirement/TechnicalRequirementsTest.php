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

namespace ILIAS\Data\Privacy\TechnicalRequirement;

use ILIAS\Data\Privacy\Source\DbTableColumn;
use ILIAS\Data\Privacy\Source\DbTableColumns;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TechnicalRequirementsTest extends TestCase
{
    public function testFactoryBuildsAllTechnicalRequirements(): void
    {
        $requirements = new TechnicalRequirements();

        $store = $requirements->storeInTable(new DbTableColumns('usr_data', 'street', 'city'), 'keep it', 'it is on');
        $this->assertSame('store_in:usr_data.(street,city)', $store->describe());
        $this->assertSame(['keep it', 'it is on'], [$store->in_order_to, $store->condition]);

        $display = $requirements->displayToUser('on the profile', 'the reader', 'show it');
        $this->assertSame(['on the profile', 'the reader', 'show it', ''], [$display->where, $display->to, $display->in_order_to, $display->condition]);

        $export = $requirements->export('as vCard', 'the reader', 'keep a copy', 'it is published');
        $this->assertSame(['as vCard', 'the reader', 'keep a copy', 'it is published'], [$export->where, $export->by, $export->in_order_to, $export->condition]);

        $pass = $requirements->passToComponent('Mail', 'send it');
        $this->assertSame(['Mail', 'send it'], [$pass->component, $pass->in_order_to]);

        $this->assertSame('technical:compare it', $requirements->technicalProcessing('compare it')->describe());
        $this->assertSame('store_in:usr_data.street', new StoreInTable(new DbTableColumn('usr_data', 'street'), 'keep it')->describe());
    }

    /**
     * @return array<string, array{\Closure(): TechnicalRequirement}>
     */
    public static function emptyPhraseProvider(): array
    {
        return [
            'store in_order_to' => [static fn() => new StoreInTable(new DbTableColumn('t', 'c'), ' ')],
            'display where' => [static fn() => new DisplayToUser('', 'to', 'in_order_to')],
            'display to' => [static fn() => new DisplayToUser('where', '', 'in_order_to')],
            'display in_order_to' => [static fn() => new DisplayToUser('where', 'to', '')],
            'export by' => [static fn() => new Export('where', '', 'in_order_to')],
            'pass component' => [static fn() => new PassToComponent('', 'in_order_to')],
            'technical in_order_to' => [static fn() => new TechnicalProcessing('')],
        ];
    }

    /**
     * @param \Closure(): TechnicalRequirement $create
     */
    #[DataProvider('emptyPhraseProvider')]
    public function testEveryPhraseIsRequired(\Closure $create): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $create();
    }

    public function testLegacyAccess(): void
    {
        $this->assertSame('legacy:profile_data_getter', new LegacyAccess('profile_data_getter')->describe());
        $this->assertSame('legacy:unclassified', new LegacyAccess()->describe());
    }
}
