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

namespace ILIAS\Scripts\Privacy\Documentation;

use ILIAS\Data\Privacy\Types\PersonName;
use ILIAS\Data\Privacy\Types\PostalAddress;
use PHPUnit\Framework\TestCase;

class MarkdownRendererTest extends TestCase
{
    /**
     * @param array<string, string> $args
     */
    private function entry(
        string $type,
        string $class,
        array $args,
        EntryCategory $category,
        string $location = 'ilMap::render()'
    ): ResolveEntry {
        return new ResolveEntry($type, $class, $args, $category, '/repo/components/ILIAS/Maps/A.php', $location);
    }

    private function render(ComponentReport $report, bool $has_privacy_md = true): string
    {
        return new MarkdownRenderer()->render('Maps', $report, static fn(string $c): bool => $has_privacy_md && $c !== 'Unknown');
    }

    public function testHasTheStructureOfAPrivacyMd(): void
    {
        $md = $this->render(new ComponentReport());

        $this->assertStringContainsString('# Maps Privacy', $md);
        $this->assertStringContainsString('> **Disclaimer:', $md);
        foreach (['Integrated components', 'Data being stored', 'Data being presented', 'Data being deleted', 'Data being exported'] as $section) {
            $this->assertStringContainsString("## {$section}\n\n_This section has not been documented here yet.", $md);
        }
        $this->assertStringContainsString('see the handwritten [PRIVACY.md](PRIVACY.md)', $md);
        $this->assertStringNotContainsString('## General information', $md);
    }

    public function testUsesWithTheSamePhrasesBecomeOneSentence(): void
    {
        $marker = ['where' => 'in the marker', 'to' => 'every person who can see the map', 'in_order_to' => 'show who is where', 'condition' => 'the person has published it'];
        $report = new ComponentReport();
        $report->add($this->entry(PostalAddress::class, 'DisplayToUser', $marker, EntryCategory::Display));
        $report->add($this->entry(PersonName::class, 'DisplayToUser', $marker, EntryCategory::Display, 'ilMap::name()'));
        $report->add($this->entry(PersonName::class, 'DisplayToUser', $marker, EntryCategory::Display, 'ilMap::other()'));

        $this->assertStringContainsString(
            "## Data being presented\n\n- If the person has published it, the name and the postal address are presented in"
            . " the marker to every person who can see the map, to show who is where.\n",
            $this->render($report)
        );
    }

    public function testEverySectionIsBuiltFromItsRequirement(): void
    {
        $report = new ComponentReport();
        $report->add($this->entry(PostalAddress::class, 'StoreInTable', ['target' => 'usr_data.(street)', 'in_order_to' => 'keep it'], EntryCategory::Store));
        $report->add($this->entry(PostalAddress::class, 'Export', ['where' => 'as vCard', 'by' => 'persons who can see the profile', 'in_order_to' => 'add it to an address book'], EntryCategory::Export));
        $report->add($this->entry(PostalAddress::class, 'PassToComponent', ['component' => 'Mail', 'in_order_to' => 'send it'], EntryCategory::Pass));
        $report->add($this->entry(PostalAddress::class, 'PassToComponent', ['component' => 'Unknown', 'in_order_to' => 'use it'], EntryCategory::Pass));

        $md = $this->render($report);

        $this->assertStringContainsString("## Data being stored\n\n- The postal address is stored to keep it.\n", $md);
        $this->assertStringContainsString(
            "## Data being exported\n\n- Persons who can see the profile can export the postal address as vCard, to add it to an address book.\n",
            $md
        );
        $this->assertStringContainsString('- [Mail](../Mail/PRIVACY.md): receives the postal address to send it', $md);
        $this->assertStringContainsString('- Unknown: receives the postal address to use it', $md);
        $this->assertStringContainsString("## Data being deleted\n\n_This section has not been documented here yet.", $md);
        $this->assertStringContainsString('| Data being stored | `PostalAddress` | `usr_data.(street)` |', $md);
    }

    public function testPhrasesNotKnownFromTheCodeAreMarked(): void
    {
        $report = new ComponentReport();
        $report->add($this->entry(PostalAddress::class, 'DisplayToUser', ['where' => '{string}', 'to' => 'the reader', 'in_order_to' => 'read it'], EntryCategory::Display));

        $md = $this->render($report);

        $this->assertStringContainsString('- The postal address is presented … to the reader, to read it.', $md);
        $this->assertStringContainsString('### Phrases not known from the code', $md);
    }

    public function testCodeFactsAreNotesForAuthors(): void
    {
        $report = new ComponentReport();
        $report->add($this->entry(PostalAddress::class, 'TechnicalProcessing', ['in_order_to' => 'compare it'], EntryCategory::Technical));
        $report->add($this->entry(PostalAddress::class, 'LegacyAccess', [0 => 'profile_data_getter'], EntryCategory::Legacy));
        $report->add($this->entry(PostalAddress::class, 'dynamic', [0 => 'TechnicalRequirement'], EntryCategory::Unclassified));

        $md = $this->render($report, false);
        $notes = substr($md, (int) strpos($md, 'Notes for authors'));

        $this->assertStringContainsString('- The component has no handwritten PRIVACY.md yet.', $notes);
        $this->assertStringContainsString('### Technical processing', $notes);
        $this->assertStringContainsString('`TechnicalProcessing · compare it`', $notes);
        $this->assertStringContainsString('### Not yet migrated', $notes);
        $this->assertStringContainsString('### Unclassified', $notes);
        $this->assertStringEndsWith("-->\n", $md);
    }
}
