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

namespace ILIAS\Test\Scoring\Manual;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\DateFormat\DateFormat;
use ILIAS\Language\Language;
use Psr\Http\Message\ServerRequestInterface;

class ScoringByQuestionTable
{
    public const ACTION_SCORING = 'getAnswerDetail';

    public const COLUMN_NAME = 'name';
    public const COLUMN_ATTEMPT = 'attempt';
    public const COLUMN_POINTS_REACHED = 'points_reached';
    public const COLUMN_POINTS_AVAILABLE = 'points_available';
    public const COLUMN_FEEDBACK = 'feedback';
    public const COLUMN_FINALIZED = 'finalized';
    public const COLUMN_FINALIZED_BY = 'finalized_by';
    public const COLUMN_FINALIZED_ON = 'finalized_on';

    public const FILTER_FIELD_ONLY_ANSWERED = 'only_answered';

    public function __construct(
        private readonly Language $lng,
        private readonly URLBuilder $url_builder,
        private URLBuilderToken $action_parameter_token,
        private URLBuilderToken $row_id_token,
        private readonly UIFactory $ui_factory
    ) {
    }

    public function getTable(
        $title,
        DateFormat $date_format,
        ServerRequestInterface $request,
        \ilUIService $ui_service,
        string $target_url,
        ScoringByQuestionTableBinder $data_retrieval,
    ): array {
        $filter = $this->getFilter($ui_service, $target_url, $data_retrieval->getMaxAttempts());

        $f = $this->ui_factory->table();
        $table = $f->data(
            $title,
            [
                self::COLUMN_NAME => $f->column()->text($this->lng->txt('name'))->withIsSortable(true),
                self::COLUMN_ATTEMPT => $f->column()->number($this->lng->txt('tst_attempt')),
                self::COLUMN_POINTS_REACHED => $f->column()->number($this->lng->txt('tst_reached_points'))->withIsSortable(true),
                self::COLUMN_POINTS_AVAILABLE => $f->column()->number($this->lng->txt('tst_maximum_points'))->withIsSortable(true),
                self::COLUMN_FEEDBACK => $f->column()->text($this->lng->txt('tst_feedback')),
                self::COLUMN_FINALIZED => $f->column()->boolean(
                    $this->lng->txt('finalized_evaluation'),
                    $this->ui_factory->symbol()->icon()->custom(
                        'assets/images/standard/icon_checked.svg',
                        $this->lng->txt('yes'),
                        'small'
                    ),
                    $this->ui_factory->symbol()->icon()->custom(
                        'assets/images/standard/icon_unchecked.svg',
                        $this->lng->txt('no'),
                        'small'
                    )
                )->withIsSortable(true),
                self::COLUMN_FINALIZED_BY => $f->column()->text($this->lng->txt('finalized_by'))->withIsSortable(true),
                self::COLUMN_FINALIZED_ON => $f->column()->date($this->lng->txt('finalized_on'), $date_format)->withIsSortable(true)
            ],
            $data_retrieval->withFilterData($ui_service->filter()->getData($filter) ?? [])
        )->withActions(
            [
                self::ACTION_SCORING => $f->action()->single(
                    $this->lng->txt('grade'),
                    $this->url_builder->withParameter($this->action_parameter_token, self::ACTION_SCORING),
                    $this->row_id_token
                )->withAsync()
            ]
        )->withRequest($request);

        return [$filter, $table];
    }

    private function getFilter(
        \ilUIService $ui_service,
        string $target_url,
        int $max_attempts
    ): Filter {
        $field_factory = $this->ui_factory->input()->field();

        $filter_inputs = [
            self::COLUMN_ATTEMPT => $field_factory->select(
                $this->lng->txt('tst_attempt'),
                $this->buildTestAttemptsOptions($max_attempts)
            ),
            self::FILTER_FIELD_ONLY_ANSWERED => $field_factory->select(
                $this->lng->txt('tst_man_scoring_only_answered'),
                [
                    0 => $this->lng->txt('no'),
                    1 => $this->lng->txt('yes')
                ]
            )->withRequired(true),
            self::COLUMN_FINALIZED => $field_factory->select(
                $this->lng->txt('finalized_evaluation'),
                [
                    0 => $this->lng->txt('all_users'),
                    1 => $this->lng->txt('evaluated_users'),
                    2 => $this->lng->txt('not_evaluated_users')
                ]
            )->withRequired(true)
        ];

        $active = array_fill(0, count($filter_inputs), true);

        $filter = $ui_service->filter()->standard(
            'scoring_by_qst_filter_id',
            $target_url,
            $filter_inputs,
            $active,
            true,
            true
        );
        return $filter;
    }

    private function buildTestAttemptsOptions(int $max_attempts): array
    {
        $attempts = [];
        for ($i = 0;$i < $max_attempts;$i++) {
            $attempts[$i] = $i + 1;
        }
        return $attempts;
    }
}
