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
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ilLanguageBaseTestCase;

/**
 * Targets LanguageActivity::maybePerformAs() directly via throwing test doubles, for behaviour no concrete
 * Activity can trigger without an artificial collaborator failure.
 */
class LanguageActivityTest extends ilLanguageBaseTestCase
{
    use RealFieldsUiFactory;

    /**
     * A \Throwable while building the input description must come back as a Result\Error, without reaching rbac.
     */
    public function testExceptionWhileBuildingTheInputDescriptionIsTurnedIntoAResultErrorNotPropagated(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $activity = new LanguageActivityThrowingInputDescriptionTestDouble(
            $this->createMock(RefineryFactory::class),
            $this->createMock(Language::class),
            $this->rbacOf($rbac),
            $this->languageFolder()
        );

        $result = $activity->maybePerformAs($this->createRealFieldsUiFactory()->input(), 6, []);

        $this->assertTrue($result->isError());
        $error = $result->error();
        $this->assertInstanceOf(\RuntimeException::class, $error);
        $this->assertSame('simulated failure while building the input description', $error->getMessage());
    }

    /**
     * A non-\Exception \Throwable (\TypeError) must arrive wrapped in a \RuntimeException, since Result\Error
     * accepts only string|\Exception.
     */
    public function testATypeErrorWhileBuildingTheInputDescriptionIsWrappedInARuntimeExceptionResultError(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->expects($this->never())->method('checkAccessOfUser');

        $activity = new LanguageActivityThrowingInputDescriptionTestDouble(
            $this->createMock(RefineryFactory::class),
            $this->createMock(Language::class),
            $this->rbacOf($rbac),
            $this->languageFolder(),
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
     * additionalPerformParameters() must win over a same-named key from normalizeParameters() in maybePerformAs()'s
     * array_merge(). Needs a dedicated double, as AddLanguageEntry never collects a 'usr_id' field.
     */
    public function testAdditionalPerformParametersWinsOverANormalizeParametersKeyCollisionInMaybePerformAs(): void
    {
        $rbac = $this->createMock(\ilRbacSystem::class);
        $rbac->method('checkAccessOfUser')->willReturn(true);

        $activity = new LanguageActivityMergePrecedenceTestDouble(
            $this->createMock(RefineryFactory::class),
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

/**
 * LanguageActivity whose getInputDescription() always throws; every other abstract method throws too.
 */
final class LanguageActivityThrowingInputDescriptionTestDouble extends LanguageActivity
{
    public function __construct(
        RefineryFactory $refinery,
        Language $language,
        RBAC $rbac,
        LanguageFolder $language_folder,
        private readonly bool $throw_type_error = false,
    ) {
        parent::__construct($refinery, $language, $rbac, $language_folder);
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
 * normalizeParameters() always returns a colliding 'usr_id' (spoofed form input); perform() returns $parameters
 * unchanged so the test can inspect the merge.
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
        // spoofed 'usr_id' that must lose against additionalPerformParameters()
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
