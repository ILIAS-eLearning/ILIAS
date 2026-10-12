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

namespace ILIAS\Language\Activities;

use ILIAS\AccessControl\PublicInterface\RBAC;
use ILIAS\Component\Activities\ActivityImpl;
use ILIAS\Component\Activities\ActivityType;
use ILIAS\Data\Result;
use ILIAS\Data\Text;
use ILIAS\Data\Text\Shape\SimpleDocumentMarkdown as SimpleDocumentMarkdownShape;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Factory as InputFactory;

abstract class LanguageActivity extends ActivityImpl
{
    use GrindsFormInput;

    protected readonly RefineryFactory $refinery;
    protected Language $lng;
    private readonly \Closure $language_folder_ref_id;

    /**
     * @param RBAC|null $rbac null only for forSetup(); isAllowedToPerform() then throws.
     */
    public function __construct(
        RefineryFactory $refinery,
        Language $language,
        private readonly ?RBAC $rbac,
        int|\Closure $language_folder_ref_id = 0,
    ) {
        $this->refinery = $refinery;
        $this->lng = $language;
        $this->language_folder_ref_id = $language_folder_ref_id instanceof \Closure
            ? $language_folder_ref_id
            : static fn(): int => $language_folder_ref_id;
    }

    public function getType(): ActivityType
    {
        return ActivityType::Command;
    }

    protected function markdown(string $raw): Text\SimpleDocumentMarkdown
    {
        return new Text\SimpleDocumentMarkdown(
            new SimpleDocumentMarkdownShape(
                $this->refinery->string()->markdown()
            ),
            $raw
        );
    }

    public function isAllowedToPerform(int $usr_id, mixed $parameters): bool
    {
        if ($this->rbac === null) {
            throw new \LogicException(
                'RBAC is not available during Setup; '
                . static::class . '::isAllowedToPerform() cannot be used here.'
            );
        }

        return $this->rbac->system()->checkAccessOfUser(
            $usr_id,
            'write',
            ($this->language_folder_ref_id)()
        );
    }

    public function maybePerformAs(InputFactory $input_factory, int $usr_id, array $raw_parameters): Result
    {
        try {
            // getInputDescription()/$input_factory->field() might throw too (e.g. a concrete
            // Activity building its FormInput eagerly) - kept inside the try so it is wrapped in
            // a Result\Error like everything else, per Activity::maybePerformAs()'s contract,
            // instead of escaping uncaught.
            $grind_result = $this->grind($this->getInputDescription($input_factory->field()), $raw_parameters);
            if ($grind_result->isError()) {
                return new Result\Error($grind_result->error());
            }

            $parameters = $this->normalizeParameters($grind_result->value());
            if (!$this->isAllowedToPerform($usr_id, $parameters)) {
                return new Result\Error($this->lng->txt('msg_no_perm_write'));
            }

            // array_merge(), not `+`: `+` keeps the LEFT operand on a key collision, so a spoofed
            // 'usr_id' from form input would win over the trusted value from
            // additionalPerformParameters(). array_merge() keeps the RIGHT (last) operand instead
            // - safe here since normalizeParameters()'s contract below is `array<string, mixed>`,
            // never a numeric(-string) key, which is the one case array_merge() would not
            // overwrite by key.

            return new Result\Ok(
                $this->perform(array_merge($parameters, $this->additionalPerformParameters($usr_id)))
            );
        } catch (\Throwable $e) {
            return new Result\Error(
                $e instanceof \Exception ? $e : new \RuntimeException($e->getMessage(), 0, $e)
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    abstract protected function normalizeParameters(array $grind_result): array;

    /**
     * Template-method hook: lets a subclass thread trusted, server-resolved extras (e.g. the
     * caller's `usr_id`) into perform()'s $parameters without overriding maybePerformAs() itself.
     * Returned keys win over a same-named key from normalizeParameters() - see the array_merge()
     * call above.
     *
     * @return array<string, mixed>
     */
    protected function additionalPerformParameters(int $usr_id): array
    {
        return [];
    }
}
