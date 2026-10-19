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

namespace ILIAS\Language\Tests\Activities;

use ILIAS\AccessControl\PublicInterface\RBAC;
use ILIAS\Data\Description;
use ILIAS\Data\Text;
use ILIAS\Language\Activities\LanguageActivity;
use ILIAS\Language\Language;
use ILIAS\Language\LanguageFolder;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ilLanguageBaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Targets LanguageActivity::maybePerformAs() directly via throwing test doubles, for behaviour no concrete
 * Activity can trigger without an artificial collaborator failure.
 */
class LanguageActivityTest extends ilLanguageBaseTestCase
{
    use RealFieldsUiFactory;

    /**
     * A defect while building the input description comes back masked as 500 (original only as previous),
     * without reaching rbac.
     */
    public function testExceptionWhileBuildingTheInputDescriptionIsMaskedAsA500ResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $activity = new LanguageActivityThrowingInputDescriptionTestDouble(
            $this->createMock(Language::class),
            $this->rbacOf($rbac),
            $this->languageFolder()
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, []);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(\RuntimeException::class, $error);
        $this->assertSame(500, $error->getCode());
        $this->assertSame('The activity could not be performed.', $error->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $error->getPrevious());
        $this->assertSame('simulated failure while building the input description', $error->getPrevious()->getMessage());
    }

    /** A non-\Exception \Throwable (\TypeError) is masked as well. */
    public function testATypeErrorWhileBuildingTheInputDescriptionIsMaskedAsA500ResultError(): void
    {
        $activity = new LanguageActivityThrowingInputDescriptionTestDouble(
            $this->createMock(Language::class),
            $this->rbacOf($this->createMock(\ilRbacSystem::class)),
            $this->languageFolder(),
            throw_type_error: true,
        );

        $error = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [])->error();

        $this->assertInstanceOf(\RuntimeException::class, $error);
        $this->assertSame(500, $error->getCode());
        $this->assertStringNotContainsString('TypeError', $error->getMessage());
        $this->assertInstanceOf(\TypeError::class, $error->getPrevious());
    }

    /** grind() throws on a defect (FormInput without InputInternal); maybePerformAs() must mask it as 500. */
    public function testADefectThrownByGrindIsMaskedAsA500ResultError(): void
    {
        $activity = new LanguageActivityPerformEchoTestDouble(
            $this->createMock(Language::class),
            $this->rbacOf($this->createMock(\ilRbacSystem::class)),
            $this->languageFolder(),
            $this->createMock(FormInput::class),
        );

        $error = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, [])->error();

        $this->assertSame(500, $error->getCode());
        $this->assertInstanceOf(\LogicException::class, $error->getPrevious());
    }

    /** @return array<string, array{0: ?int}> */
    public static function unknownUserIdProvider(): array
    {
        return ['zero' => [0], 'negative' => [-1], 'anonymous' => [null]];
    }

    /** No RBAC lookup for a non-user: the anonymous user (null: ANONYMOUS_USER_ID) must not pass on its roles. */
    #[DataProvider('unknownUserIdProvider')]
    public function testIsAllowedToPerformRefusesNonUsersWithoutAskingRbac(?int $usr_id): void
    {
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }
        $usr_id ??= ANONYMOUS_USER_ID;

        $rbac_system = $this->createMock(\ilRbacSystem::class);
        $rbac_system->expects($this->never())->method('checkAccessOfUser');

        $activity = new LanguageActivityPerformEchoTestDouble(
            $this->createMock(Language::class),
            $this->rbacOf($rbac_system),
            $this->languageFolder()
        );

        $this->assertFalse($activity->isAllowedToPerform($usr_id, []));
    }

    public function testMaybePerformAsAnswers403ForANonUserAndNeverPerforms(): void
    {
        $rbac_system = $this->createMock(\ilRbacSystem::class);
        $rbac_system->expects($this->never())->method('checkAccessOfUser');

        $activity = new LanguageActivityPerformEchoTestDouble(
            $this->createMock(Language::class),
            $this->rbacOf($rbac_system),
            $this->languageFolder()
        );

        $error = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 0, [])->error();

        $this->assertSame(403, $error->getCode());
    }

    /**
     * perform() gets the usr_id of maybePerformAs(), not the spoofed one from normalizeParameters(). Needs a
     * dedicated double, as no real Activity collects a 'usr_id' field.
     */
    public function testMaybePerformAsPassesTheTrustedUsrIdToPerformOverASpoofedFormKey(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $activity = new LanguageActivityPerformEchoTestDouble(
            $this->createMock(Language::class),
            $this->rbacOf($rbac),
            $this->languageFolder()
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 42, []);

        $this->assertFalse($result->isError());
        $parameters = $result->value();
        // the trusted usr_id (42) wins over the spoofed one from normalizeParameters()
        $this->assertSame(42, $parameters['usr_id']);
        // a non-colliding key survives the merge
        $this->assertSame('form', $parameters['source']);
    }
}

final class LanguageActivityThrowingInputDescriptionTestDouble extends LanguageActivity
{
    public function __construct(
        Language $language,
        RBAC $rbac,
        LanguageFolder $language_folder,
        private readonly bool $throw_type_error = false,
    ) {
        parent::__construct($language, $rbac, $language_folder);
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument('Test double for LanguageActivity::maybePerformAs().');
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        if ($this->throw_type_error) {
            throw new \TypeError('simulated TypeError while building the input description');
        }

        throw new \RuntimeException('simulated failure while building the input description');
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        throw new \LogicException(__METHOD__ . ' is not used by this test double.');
    }

    public function perform(mixed $parameters): mixed
    {
        throw new \LogicException(__METHOD__ . ' is not used by this test double.');
    }

    protected function normalizeParameters(array $grind_result): array
    {
        throw new \LogicException(__METHOD__ . ' is not used by this test double.');
    }
}

/**
 * normalizeParameters() always returns a colliding 'usr_id' (spoofed form input); perform() returns $parameters
 * unchanged so the test can inspect the merge.
 */
final class LanguageActivityPerformEchoTestDouble extends LanguageActivity
{
    public function __construct(
        Language $language,
        RBAC $rbac,
        LanguageFolder $language_folder,
        private readonly ?FormInput $description = null,
    ) {
        parent::__construct($language, $rbac, $language_folder);
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->data->text()->markdown()->simpleDocument('Test double for the usr_id precedence in maybePerformAs().');
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $this->description ?? $f->group([]);
    }

    public function getOutputDescription(Description\Factory $f): Description\Description
    {
        throw new \LogicException(__METHOD__ . ' is not used by this test double.');
    }

    public function perform(mixed $parameters): mixed
    {
        return $parameters;
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeParameters(array $grind_result): array
    {
        // spoofed 'usr_id' that must lose against the one maybePerformAs() passes
        return ['usr_id' => 'SPOOFED-VALUE', 'source' => 'form'];
    }
}
