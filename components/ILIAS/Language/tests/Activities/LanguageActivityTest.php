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

use ILIAS\Data\Description;
use ILIAS\Data\Text;
use ILIAS\Language\Activities\LanguageActivity;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ilLanguageBaseTestCase;

/**
 * LanguageActivity is abstract, exercised indirectly by every concrete Activity's own test suite;
 * this file targets LanguageActivity::maybePerformAs() directly via minimal throwing test doubles,
 * for behaviour no concrete Activity can trigger without an artificial collaborator failure.
 */
class LanguageActivityTest extends ilLanguageBaseTestCase
{
    use RealFieldsUiFactory;

    /**
     * A \Throwable raised while BUILDING the input description (not grinding, not perform()) must
     * still come back as a Result\Error, per Activity::maybePerformAs()'s contract - rbac must
     * never be reached either, since the failure happens before permission-checking.
     */
    public function testExceptionWhileBuildingTheInputDescriptionIsTurnedIntoAResultErrorNotPropagated(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $activity = new LanguageActivityThrowingInputDescriptionTestDouble(
            $this->createMock(RefineryFactory::class),
            $this->createMock(Language::class),
            $rbac
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, []);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(\RuntimeException::class, $error);
        $this->assertSame('simulated failure while building the input description', $error->getMessage());
    }

    /**
     * Same scenario, but the \Throwable is not an \Exception (a \TypeError) - `new
     * Result\Error($e)` only accepts string|\Exception, so it must arrive wrapped in a
     * \RuntimeException instead of raising an uncaught \InvalidArgumentException.
     */
    public function testATypeErrorWhileBuildingTheInputDescriptionIsWrappedInARuntimeExceptionResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $activity = new LanguageActivityThrowingInputDescriptionTestDouble(
            $this->createMock(RefineryFactory::class),
            $this->createMock(Language::class),
            $rbac,
            throw_type_error: true,
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, []);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(\RuntimeException::class, $error);
        $this->assertNotInstanceOf(\TypeError::class, $error);
        $this->assertSame('simulated TypeError while building the input description', $error->getMessage());
        $this->assertInstanceOf(\TypeError::class, $error->getPrevious());
    }

    /**
     * Regression test for maybePerformAs()'s array_merge() precedence: additionalPerformParameters()
     * must win over a same-named key from normalizeParameters(). Uses a dedicated LanguageActivity
     * double whose normalizeParameters() always returns a colliding 'usr_id', since
     * AddLanguageEntry's own getInputDescription() never collects a 'usr_id' field and so never
     * actually reaches this collision.
     */
    public function testAdditionalPerformParametersWinsOverANormalizeParametersKeyCollisionInMaybePerformAs(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $activity = new LanguageActivityMergePrecedenceTestDouble(
            $this->createMock(RefineryFactory::class),
            $this->createMock(Language::class),
            $rbac
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 42, []);

        $this->assertFalse($result->isError());
        $parameters = $result->value();
        // The trusted, server-resolved usr_id (42, from maybePerformAs()'s own
        // argument via additionalPerformParameters()) must win over the
        // spoofed value normalizeParameters() produced from (simulated) form
        // input.
        $this->assertSame(42, $parameters['usr_id']);
        // A non-colliding key from normalizeParameters() must still survive
        // the merge untouched.
        $this->assertSame('form', $parameters['source']);
    }
}

/**
 * Minimal concrete LanguageActivity whose getInputDescription() always
 * throws, used exclusively to exercise LanguageActivity::maybePerformAs()'s
 * generic try/catch around getInputDescription()/$input_factory->field() in
 * isolation - every other abstract method is unreachable from
 * maybePerformAs() once getInputDescription() throws, and simply throws
 * itself if a test ever calls it by mistake.
 */
final class LanguageActivityThrowingInputDescriptionTestDouble extends LanguageActivity
{
    public function __construct(
        RefineryFactory $refinery,
        Language $language,
        \ilRbacSystem|\Closure $rbac_system,
        private readonly bool $throw_type_error = false,
    ) {
        parent::__construct($refinery, $language, $rbac_system);
    }

    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->markdown('Test double for LanguageActivity::maybePerformAs().');
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
 * Minimal LanguageActivity used only by
 * testAdditionalPerformParametersWinsOverANormalizeParametersKeyCollisionInMaybePerformAs():
 * normalizeParameters() always returns a colliding 'usr_id', simulating spoofed form input.
 * getInputDescription() returns an empty group so grind() trivially succeeds; perform() returns
 * $parameters unchanged so the test can inspect what survived the merge.
 */
final class LanguageActivityMergePrecedenceTestDouble extends LanguageActivity
{
    public function getDescription(): Text\SimpleDocumentMarkdown
    {
        return $this->markdown('Test double for maybePerformAs() array_merge() precedence.');
    }

    public function getInputDescription(FieldFactory $f): FormInput
    {
        return $f->group([]);
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
        // Simulates a spoofed 'usr_id' smuggled in through form input - must
        // never win over additionalPerformParameters()'s trusted value below.
        return ['usr_id' => 'SPOOFED-VALUE', 'source' => 'form'];
    }

    /**
     * @return array{usr_id: int}
     */
    protected function additionalPerformParameters(int $usr_id): array
    {
        return ['usr_id' => $usr_id];
    }
}
