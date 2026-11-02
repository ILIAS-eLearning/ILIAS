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

namespace ILIAS\LegalDocuments\test\ConsumerToolbox;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Error;
use ILIAS\Data\Result\Ok;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Provide\ProvideHistory;
use ILIAS\LegalDocuments\Provide\ProvideDocument;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\LegalDocuments\test\ContainerMock;
use ILIAS\Data\Clock\ClockInterface as Clock;
use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\ConsumerToolbox\UserSettings;
use ILIAS\LegalDocuments\ConsumerToolbox\Settings;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ilObjUser;
use DateTimeImmutable;
use ilAuthUtils;
use PHPUnit\Framework\Attributes\DataProvider;

require_once __DIR__ . '/../ContainerMock.php';

class UserTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(User::class, new User(
            $this->stub(ilObjUser::class),
            $this->stub(Settings::class),
            $this->stub(UserSettings::class),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        ));
    }

    public function testIsLoggedIn(): void
    {
        $this->ensureDefined('ANONYMOUS_USER_ID', 13);

        $user = $this->mock(ilObjUser::class);
        $user->expects($this->exactly(5))->method('getId')->willReturnOnConsecutiveCalls(0, ANONYMOUS_USER_ID, 9, 1, 68);

        $instance = new User(
            $user,
            $this->stub(Settings::class),
            $this->stub(UserSettings::class),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $this->assertFalse($instance->isLoggedIn());
        $this->assertFalse($instance->isLoggedIn());
        $this->assertTrue($instance->isLoggedIn());
        $this->assertTrue($instance->isLoggedIn());
        $this->assertTrue($instance->isLoggedIn());
    }

    public function testCannotAgree(): void
    {
        $this->ensureDefined('ANONYMOUS_USER_ID', 13);
        $this->ensureDefined('SYSTEM_USER_ID', 9);

        $user = $this->mock(ilObjUser::class);
        $user->expects($this->exactly(5))->method('getId')->willReturnOnConsecutiveCalls(0, ANONYMOUS_USER_ID, SYSTEM_USER_ID, 1, 68);

        $instance = new User(
            $user,
            $this->stub(Settings::class),
            $this->stub(UserSettings::class),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $this->assertTrue($instance->cannotAgree());
        $this->assertTrue($instance->cannotAgree());
        $this->assertTrue($instance->cannotAgree());
        $this->assertFalse($instance->cannotAgree());
        $this->assertFalse($instance->cannotAgree());
    }

    public function testNeverAgreed(): void
    {
        $setting = $this->mock(Setting::class);
        $setting->expects($this->exactly(2))->method('value')->willReturnOnConsecutiveCalls(null, new DateTimeImmutable());

        $user_settings = $this->mock(UserSettings::class);
        $user_settings->expects($this->exactly(2))->method('agreeDate')->willReturn($setting);

        $instance = new User(
            $this->stub(ilObjUser::class),
            $this->stub(Settings::class),
            $user_settings,
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $this->assertTrue($instance->neverAgreed());
        $this->assertFalse($instance->neverAgreed());
    }

    public function testWithdrawalRequested(): void
    {
        $setting = $this->stub(Setting::class);

        $instance = new User(
            $this->stub(ilObjUser::class),
            $this->stub(Settings::class),
            $this->mockMethod(UserSettings::class, 'withdrawalRequested', [], $setting),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $this->assertSame($setting, $instance->withdrawalRequested());
    }

    public function testAgreeDate(): void
    {
        $setting = $this->stub(Setting::class);

        $instance = new User(
            $this->stub(ilObjUser::class),
            $this->stub(Settings::class),
            $this->mockMethod(UserSettings::class, 'agreeDate', [], $setting),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $this->assertSame($setting, $instance->agreeDate());
    }

    public function testDidNotAcceptCurrentVersion(): void
    {
        $user = $this->stub(ilObjUser::class);
        $document = $this->stub(Document::class);

        $instance = new User(
            $user,
            $this->stubTree(Settings::class, ['validateOnLogin' => ['value' => true]]),
            $this->stub(UserSettings::class),
            $this->stubTree(Provide::class, [
                'document' => $this->mockMethod(ProvideDocument::class, 'chooseDocumentFor', [$user], new Ok($document)),
                'history' => $this->mockMethod(ProvideHistory::class, 'alreadyAccepted', [$user, $document], false),
            ]),
            $this->stub(Clock::class)
        );

        $this->assertTrue($instance->didNotAcceptCurrentVersion());
    }

    public function testNeedsToAcceptNewDocumentWhereNeverAgreed(): void
    {
        $instance = new User(
            $this->stub(ilObjUser::class),
            $this->stub(Settings::class),
            $this->stubTree(UserSettings::class, ['agreeDate' => ['value' => null]]),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $this->assertTrue($instance->needsToAcceptNewDocument());
    }

    public function testNeedsToAcceptNewDocumentReturnsTrue(): void
    {
        $user = $this->stub(ilObjUser::class);
        $history = $this->mockMethod(ProvideHistory::class, 'currentDocumentOfAcceptedVersion', [$user], new Error('Not found.'));

        $instance = new User(
            $user,
            $this->stubTree(Settings::class, ['validateOnLogin' => ['value' => true]]),
            $this->stubTree(UserSettings::class, ['agreeDate' => ['value' => new DateTimeImmutable()]]),
            $this->stubTree(Provide::class, ['history' => $history]),
            $this->stub(Clock::class)
        );

        $this->assertTrue($instance->needsToAcceptNewDocument());
    }

    public function testNeedsToAcceptNewDocumentReturnsFalse(): void
    {
        $user = $this->stub(ilObjUser::class);
        $document = $this->stub(Document::class);
        $history = $this->mockMethod(ProvideHistory::class, 'currentDocumentOfAcceptedVersion', [$user], new Ok($document));

        $instance = new User(
            $user,
            $this->stubTree(Settings::class, ['validateOnLogin' => ['value' => true]]),
            $this->stubTree(UserSettings::class, ['agreeDate' => ['value' => new DateTimeImmutable()]]),
            $this->stubTree(Provide::class, [
                'document' => $this->mockMethod(ProvideDocument::class, 'documentMatches', [$document, $user], true),
                'history' => $history,
            ]),
            $this->stub(Clock::class)
        );

        $this->assertFalse($instance->needsToAcceptNewDocument());
    }

    public function testDoesntMatch(): void
    {
        $document = $this->stub(Document::class);
        $user = $this->stub(ilObjUser::class);

        $instance = new User(
            $user,
            $this->stub(Settings::class),
            $this->stub(UserSettings::class),
            $this->stubTree(Provide::class, [
                'document' => $this->mockMethod(ProvideDocument::class, 'documentMatches', [$document, $user], true),
            ]),
            $this->stub(Clock::class)
        );

        $this->assertFalse($instance->doesntMatch($document));
    }

    public function testMatchingDocument(): void
    {
        $user = $this->stub(ilObjUser::class);
        $result = $this->stub(Result::class);

        $instance = new User(
            $user,
            $this->stub(Settings::class),
            $this->stub(UserSettings::class),
            $this->stubTree(Provide::class, [
                'document' => $this->mockMethod(ProvideDocument::class, 'chooseDocumentFor', [$user], $result),
            ]),
            $this->stub(Clock::class)
        );

        $this->assertSame($result, $instance->matchingDocument());
    }

    public function testAcceptedVersion(): void
    {
        $this->ensureDefined('ANONYMOUS_USER_ID', 13);
        $this->ensureDefined('SYSTEM_USER_ID', 9);

        $result = $this->stub(Result::class);

        $user = $this->stubTree(ilObjUser::class, ['getId' => 67]);

        $instance = new User(
            $user,
            $this->stub(Settings::class),
            $this->stubTree(UserSettings::class, ['agreeDate' => ['value' => new DateTimeImmutable()]]),
            $this->stubTree(Provide::class, ['history' => $this->mockMethod(ProvideHistory::class, 'acceptedVersion', [$user], $result)]),
            $this->stub(Clock::class)
        );

        $this->assertSame($result, $instance->acceptedVersion());
    }

    public function testAcceptVersionError(): void
    {
        $this->ensureDefined('ANONYMOUS_USER_ID', 13);
        $this->ensureDefined('SYSTEM_USER_ID', 9);

        $user = $this->stubTree(ilObjUser::class, ['getId' => 67]);

        $instance = new User(
            $user,
            $this->stub(Settings::class),
            $this->stubTree(UserSettings::class, ['agreeDate' => ['value' => null]]),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $result = $instance->acceptedVersion();
        $this->assertFalse($result->isOk());
        $this->assertSame('User never agreed.', $result->error());
    }

    public function testAcceptMatchingDocument(): void
    {
        $user = $this->stub(ilObjUser::class);
        $document = $this->stub(Document::class);
        $date = new DateTimeImmutable();

        $history = $this->mock(ProvideHistory::class);
        $history->expects($this->once())->method('acceptDocument')->with($user, $document);

        $setting = $this->mock(Setting::class);
        $setting->expects($this->once())->method('update')->with($date);

        $instance = new User(
            $user,
            $this->stub(Settings::class),
            $this->stubTree(UserSettings::class, ['agreeDate' => $setting]),
            $this->stubTree(Provide::class, [
                'document' => $this->mockMethod(ProvideDocument::class, 'chooseDocumentFor', [$user], new Ok($document)),
                'history' => $history,
            ]),
            $this->stubTree(Clock::class, ['now' => $date])
        );

        $instance->acceptMatchingDocument();
    }

    public function testIsLDAPUser(): void
    {
        $instance = new User(
            $this->stubTree(ilObjUser::class, ['getAuthMode' => 'default']),
            $this->stubTree(Settings::class, ['authMode' => ['value' => (string) ilAuthUtils::AUTH_LDAP]]),
            $this->stub(UserSettings::class),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $this->assertTrue($instance->isLDAPUser());
    }

    #[DataProvider('externalAuthModes')]
    public function testIsExternalUser(int $auth_mode, bool $is_external_account): void
    {
        $instance = new User(
            $this->stubTree(ilObjUser::class, ['getAuthMode' => 'default']),
            $this->stubTree(Settings::class, ['authMode' => ['value' => (string) $auth_mode]]),
            $this->stub(UserSettings::class),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $this->assertSame($is_external_account, $instance->isExternalAccount());
    }

    public function testFormat(): void
    {
        $instance = new User(
            $this->stubTree(ilObjUser::class, [
                'getFullname' => 'foo',
                'getLogin' => 'bar',
                'getExternalAccount' => 'baz',
            ]),
            $this->stub(Settings::class),
            $this->stub(UserSettings::class),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $this->assertSame('', $instance->format(''));
        $this->assertSame("hej foo bar\nbaz", $instance->format('hej %s %s[BR]%s'));
    }

    public function testRaw(): void
    {
        $user = $this->stub(ilObjUser::class);

        $instance = new User(
            $user,
            $this->stub(Settings::class),
            $this->stub(UserSettings::class),
            $this->stub(Provide::class),
            $this->stub(Clock::class)
        );

        $this->assertSame($user, $instance->raw());
    }

    public function testAcceptAnyDocument(): void
    {
        $user = $this->stub(ilObjUser::class);
        $history = $this->mock(ProvideHistory::class);
        $doc = $this->stub(Document::class);
        $history->expects($this->once())->method('acceptDocument')->with($user, $doc);

        $instance = new User(
            $user,
            $this->stub(Settings::class),
            $this->stub(UserSettings::class),
            $this->stubTree(Provide::class, [
                'document' => ['repository' => ['all' => [$doc]]],
                'history' => $history,
            ]),
            $this->stub(Clock::class)
        );

        $instance->acceptAnyDocument();
    }

    public static function externalAuthModes(): array
    {
        return [
            'lti' => [ilAuthUtils::AUTH_PROVIDER_LTI, true],
            'ecs' => [ilAuthUtils::AUTH_ECS, true],
            'ldap' => [ilAuthUtils::AUTH_LDAP, false],
        ];
    }

    private function ensureDefined(string $name, $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}
