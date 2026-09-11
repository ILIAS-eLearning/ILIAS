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

namespace ILIAS\Data\Privacy\PHPStan\Generator;

use PHPUnit\Framework\TestCase;

class MarkdownRendererTest extends TestCase
{
    public function testEmptyReport(): void
    {
        $md = new MarkdownRenderer()->render('Mail', new ComponentReport());

        $this->assertStringContainsString('# Privacy Data Usage – Mail', $md);
        $this->assertStringContainsString('does not resolve any privacy-protected data', $md);
        $this->assertStringNotContainsString('## Summary', $md);
    }

    public function testFullReport(): void
    {
        $report = new ComponentReport();
        $report->add(new ResolveEntry(
            'PostalAddress',
            'StoreInTable',
            ['usr_data.(street,city,zipcode,country)'],
            EntryCategory::Store,
            '/repo/components/ILIAS/User/src/Profile/DatabaseDataRepository.php',
            123
        ));
        $report->add(new ResolveEntry(
            'PostalAddress',
            'DisplayToUser',
            ['public_profile'],
            EntryCategory::Display,
            '/repo/components/ILIAS/User/src/Profile/class.PublicProfileGUI.php',
            371
        ));
        $report->add(new ResolveEntry(
            'PostalAddress',
            'PassToComponent',
            ['Mail', 'profile_mail_body'],
            EntryCategory::Pass,
            '/repo/components/ILIAS/User/classes/class.ilObjUser.php',
            1580
        ));
        $report->add(new ResolveEntry(
            'PostalAddress',
            'TechnicalProcessing',
            ['comparison'],
            EntryCategory::Technical,
            '/repo/components/ILIAS/User/classes/class.ilSomething.php',
            10
        ));
        $report->add(new ResolveEntry(
            'PostalAddress',
            'LegacyAccess',
            ['profile_data_getter'],
            EntryCategory::Legacy,
            '/repo/components/ILIAS/User/src/Profile/Data.php',
            266
        ));

        $md = new MarkdownRenderer()->render('User', $report);

        $this->assertStringContainsString('# Privacy Data Usage – User', $md);
        // summary counts
        $this->assertStringContainsString('| Stored in DB | 1 |', $md);
        $this->assertStringContainsString('| Displayed to user | 1 |', $md);
        $this->assertStringContainsString('| Passed to component | 1 |', $md);
        $this->assertStringContainsString('| Technical processing | 1 |', $md);
        $this->assertStringContainsString('| Unmigrated (legacy access) | 1 |', $md);
        // sections
        $this->assertStringContainsString('## Stored data', $md);
        $this->assertStringContainsString('`usr_data.(street,city,zipcode,country)`', $md);
        $this->assertStringContainsString('## Displayed to user', $md);
        $this->assertStringContainsString('`public_profile`', $md);
        $this->assertStringContainsString('## Passed to other components', $md);
        $this->assertStringContainsString('| `PostalAddress` | `Mail` | profile_mail_body |', $md);
        $this->assertStringContainsString('## Technical processing', $md);
        $this->assertStringContainsString('## Unmigrated (legacy access)', $md);
        // file links are shortened to component-relative paths
        $this->assertStringContainsString('`User/src/Profile/DatabaseDataRepository.php:123`', $md);
        $this->assertStringNotContainsString('/repo/components', $md);
        // GDPR block
        $this->assertStringContainsString('| Personal data categories | `PostalAddress` |', $md);
        $this->assertStringContainsString('| Storage locations | `usr_data.(street,city,zipcode,country)` |', $md);
        $this->assertStringContainsString('| Data recipients | `Mail` |', $md);
        $this->assertStringContainsString('| Legal basis | _To be filled in manually_ |', $md);
        $this->assertStringContainsString('| Retention period | _To be filled in manually_ |', $md);
    }

    public function testGdprBlockOmitsEmptyStorageAndRecipients(): void
    {
        $report = new ComponentReport();
        $report->add(new ResolveEntry(
            'PostalAddress',
            'DisplayToUser',
            ['map_user_info'],
            EntryCategory::Display,
            '/repo/components/ILIAS/Maps/classes/class.ilGoogleMapGUI.php',
            97
        ));

        $md = new MarkdownRenderer()->render('Maps', $report);

        $this->assertStringNotContainsString('Storage locations', $md);
        $this->assertStringNotContainsString('Data recipients', $md);
        $this->assertStringNotContainsString('## Stored data', $md);
    }
}
