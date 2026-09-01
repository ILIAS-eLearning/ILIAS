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

namespace ILIAS\WOPI\Handler;

use PHPUnit\Framework\TestCase;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Information\Information;

/**
 * @see https://mantis.ilias.de/view.php?id=48246
 */
final class GetFileInfoResponseTest extends TestCase
{
    protected function setUp(): void
    {
        if (!defined('ILIAS_HTTP_PATH')) {
            define('ILIAS_HTTP_PATH', 'https://ilias.example.org');
        }

        $GLOBALS['DIC'] ??= new \ILIAS\DI\Container();
        if (!isset($GLOBALS['DIC']['ilDB'])) {
            $db = $this->createMock(\ilDBInterface::class);
            $db->method('queryF')->willReturn($this->createStub(\ilDBStatement::class));
            $db->method('fetchAssoc')->willReturn(
                ['firstname' => 'Ada', 'lastname' => 'Lovelace', 'title' => '', 'login' => 'ada']
            );
            $GLOBALS['DIC']['ilDB'] = static fn(): \ilDBInterface => $db;
        }
    }

    private function responseFor(bool $editable, ?bool $user_can_write = null): array
    {
        $information = $this->createMock(Information::class);
        $information->method('getSuffix')->willReturn('odt');
        $information->method('getSize')->willReturn(1234);
        $information->method('getCreationDate')->willReturn(new \DateTimeImmutable('2026-01-01 12:00:00'));

        $revision = $this->createMock(Revision::class);
        $revision->method('getTitle')->willReturn('minutes.odt');
        $revision->method('getVersionNumber')->willReturn(3);
        $revision->method('getOwnerId')->willReturn(6);
        $revision->method('getInformation')->willReturn($information);

        return (new GetFileInfoResponse($revision, 6, $editable, $user_can_write))->jsonSerialize();
    }

    /**
     * RestrictedWebViewOnly restricts what the user may do with the file and its effect is
     * up to the client; UserCanAttend is about viewing a broadcast. Neither describes the
     * fact that this session opened a viewer, so neither is sent any more.
     */
    public function testViewSessionDoesNotClaimRestrictionsItDoesNotMean(): void
    {
        $response = $this->responseFor(false);

        $this->assertArrayNotHasKey('RestrictedWebViewOnly', $response);
        $this->assertArrayNotHasKey('UserCanAttend', $response);
    }

    public function testEditSessionDoesNotClaimRestrictionsItDoesNotMean(): void
    {
        $response = $this->responseFor(true);

        $this->assertArrayNotHasKey('RestrictedWebViewOnly', $response);
        $this->assertArrayNotHasKey('UserCanAttend', $response);
    }

    public function testViewSessionStaysReadOnly(): void
    {
        $response = $this->responseFor(false);

        $this->assertTrue($response['ReadOnly']);
        $this->assertFalse($response['UserCanWrite']);
        $this->assertFalse($response['SupportsUpdate']);
    }

    public function testEditSessionMayWrite(): void
    {
        $response = $this->responseFor(true);

        $this->assertFalse($response['ReadOnly']);
        $this->assertTrue($response['UserCanWrite']);
        $this->assertTrue($response['SupportsUpdate']);
    }

    /**
     * The content tab of a file object is a viewer, also for someone who holds the
     * permission to edit the file. The session stays read-only, but the client is told
     * that the user is not the reason for it - otherwise it explains the viewer with
     * missing access rights.
     */
    public function testViewSessionOfAUserWhoMayWriteStaysReadOnlyButDoesNotBlameTheirRights(): void
    {
        $response = $this->responseFor(false, true);

        $this->assertTrue($response['ReadOnly']);
        $this->assertFalse($response['SupportsUpdate']);
        $this->assertTrue($response['UserCanWrite']);
    }

    public function testViewSessionOfAUserWhoMayNotWrite(): void
    {
        $response = $this->responseFor(false, false);

        $this->assertTrue($response['ReadOnly']);
        $this->assertFalse($response['SupportsUpdate']);
        $this->assertFalse($response['UserCanWrite']);
    }

    /**
     * ILIAS never handles the UI_Edit message, so it must not announce that it does.
     */
    public function testTheHostDoesNotOfferAWayIntoEditMode(): void
    {
        $this->assertFalse($this->responseFor(false, true)['EditModePostMessage']);
        $this->assertFalse($this->responseFor(true)['EditModePostMessage']);
    }
}
