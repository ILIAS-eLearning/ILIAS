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

use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Refinery\Factory as Refinery;

class ilObjTestSettingsGeneral extends TestSettings
{
    public function __construct(
        int $test_id,
        protected string $question_set_type = ilObjTest::QUESTION_SET_TYPE_FIXED,
        protected bool $anonymous_test = false
    ) {
        parent::__construct($test_id);
    }

    /**
     *
     * @return array<ILIAS\UI\Component\Input\Field\Input>
     */
    public function toForm(
        \ilLanguage $lng,
        FieldFactory $f,
        Refinery $refinery,
        array $environment = null
    ): FormInput | array {
        $inputs['question_set_type'] = $f->radio(
            $lng->txt('test_question_set_type')
        )->withOption(
            ilObjTest::QUESTION_SET_TYPE_FIXED,
            $lng->txt('test_question_set_type_fixed'),
            $lng->txt('test_question_set_type_fixed_info')
        )->withOption(
            ilObjTest::QUESTION_SET_TYPE_RANDOM,
            $lng->txt('test_question_set_type_random'),
            $lng->txt('test_question_set_type_random_info')
        )->withValue($this->getQuestionSetType());

        $trafo = $refinery->custom()->transformation(
            static function (string $v): bool {
                if ($v === '1') {
                    return true;
                }

                return false;
            }
        );

        $inputs['anonymity'] = $f->radio(
            $lng->txt('tst_anonymity')
        )->withOption(
            '0',
            $lng->txt('tst_anonymity_no_anonymization')
        )->withOption(
            '1',
            $lng->txt('tst_anonymity_anonymous_test')
        )->withValue($this->getAnonymity() ? '1' : '0')
        ->withAdditionalTransformation($trafo);

        if ($environment['participant_data_exists']) {
            $inputs['question_set_type'] = $inputs['question_set_type']->withDisabled(true);
            $inputs['anonymity'] = $inputs['anonymity']->withDisabled(true);
        }

        return $inputs;
    }

    public function toStorage(): array
    {
        return [
            'question_set_type' => ['text', $this->getQuestionSetType()],
            'anonymity' => ['integer', (int) $this->getAnonymity()]
        ];
    }

    public function getQuestionSetType(): string
    {
        return $this->question_set_type;
    }

    public function withQuestionSetType(string $question_set_type): self
    {
        $clone = clone $this;
        $clone->question_set_type = $question_set_type;
        return $clone;
    }

    public function getAnonymity(): bool
    {
        return $this->anonymous_test;
    }

    public function withAnonymity(bool $anonymous_test): self
    {
        $clone = clone $this;
        $clone->anonymous_test = $anonymous_test;
        return $clone;
    }
}
