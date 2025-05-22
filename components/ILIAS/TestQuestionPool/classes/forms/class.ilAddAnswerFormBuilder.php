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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;

class ilAddAnswerFormBuilder
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly RefineryFactory $refinery,
        private readonly ilLanguage $language,
        private readonly ilCtrl $ctrl
    ) {
    }

    public function buildAddAnswerModal(string $title, array $data = []): RoundTripModal
    {
        return $this->ui_factory->modal()->roundtrip(
            $title,
            null,
            $this->buildInputs($data),
            $this->ctrl->getFormActionByClass([ilObjTestGUI::class, ilTestCorrectionsGUI::class], 'addAnswer')
        );
    }

    protected function buildInputs(array $data): array
    {
        $to_int_trafo = $this->refinery->kindlyTo()->int();
        $fix_points_trafo = $this->refinery->custom()->transformation(
            function ($v) {
                $v = str_replace(',', '.', $v);

                if (is_numeric($v)) {
                    return (float) $v;
                }

                return false;
            }
        );

        $inputs = [];
        $inputs['question_id'] = $this->ui_factory->input()->field()->hidden()
            ->withAdditionalTransformation($to_int_trafo)
            ->withValue((string) ($data['question_id'] ?? ''));
        $inputs['question_index'] = $this->ui_factory->input()->field()->hidden()
            ->withAdditionalTransformation($to_int_trafo)
            ->withValue((string) ($data['question_index'] ?? ''));
        $inputs['answer_value'] = $this->ui_factory->input()->field()->hidden($this->language->txt('answer'))
            ->withValue(($data['answer'] ?? ''));
        $inputs['answer'] = $this->ui_factory->input()->field()->text($this->language->txt('answer'))
            ->withValue(($data['answer'] ?? ''))
            ->withDisabled(true);
        $inputs['points'] = $this->ui_factory->input()->field()->text($this->language->txt('points'))
            ->withAdditionalTransformation($fix_points_trafo);
        return $inputs;
    }
}
