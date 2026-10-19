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
use ILIAS\Language\LanguageFolder;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Input\Factory as InputFactory;

abstract class LanguageActivity extends ActivityImpl
{
    use GrindsFormInput;

    protected readonly RefineryFactory $refinery;
    protected Language $lng;

    /** @param RBAC|null $rbac null only for forSetup(); isAllowedToPerform() then throws. */
    public function __construct(
        RefineryFactory $refinery,
        Language $language,
        private readonly ?RBAC $rbac,
        private readonly LanguageFolder $language_folder,
    ) {
        $this->refinery = $refinery;
        $this->lng = $language;
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
            $this->language_folder->getRefId()
        );
    }

    public function maybePerformAs(InputFactory $input_factory, int $usr_id, array $raw_parameters): Result
    {
        try {
            // inside the try: getInputDescription() may throw too; value() throws grind's input error
            $parameters = $this->normalizeParameters(
                $this->grind($this->getInputDescription($input_factory->field()), $raw_parameters)->value()
            );
            if (!$this->isAllowedToPerform($usr_id, $parameters)) {
                return new Result\Error(
                    new \DomainException('You are not allowed to manage the languages of this installation.', 403)
                );
            }

            // array_merge(), not `+`: the trusted usr_id must win over a spoofed form key
            return new Result\Ok(
                $this->perform(array_merge($parameters, $this->additionalPerformParameters($usr_id)))
            );
        } catch (\Throwable $e) {
            return new Result\Error(Failure::of($e));
        }
    }

    /** @return array<string, mixed> */
    abstract protected function normalizeParameters(array $grind_result): array;

    /**
     * Hook for trusted, server-resolved extras (e.g. `usr_id`) for perform(); they win over
     * same-named keys of normalizeParameters(), see maybePerformAs().
     *
     * @return array<string, mixed>
     */
    protected function additionalPerformParameters(int $usr_id): array
    {
        return [];
    }
}
