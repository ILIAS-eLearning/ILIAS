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

use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationAnalyzer;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssue;
use ILIAS\LearningSequence\Content\Condition\StaticInputConfigurationIssueDetail;
use PHPUnit\Framework\TestCase;

class StaticInputConfigurationAnalyzerTest extends TestCase
{
    public function testDetectsContradictoryStaticInputConstraints(): void
    {
        $analyzer = new StaticInputConfigurationAnalyzer();

        $issues = $analyzer->getIssues([
            200 => [
                $this->mockCondition(
                    obj_ref_id: 200,
                    constraints: [['kind' => 'all_completed', 'ref_ids' => [151, 152]]]
                ),
                $this->mockCondition(
                    obj_ref_id: 200,
                    constraints: [['kind' => 'none_completed', 'ref_ids' => [151]]]
                ),
            ],
        ]);

        $this->assertSame([200], $analyzer->getAffectedRefIds($issues));
    }

    public function testAcceptsCompatibleStaticInputConstraints(): void
    {
        $analyzer = new StaticInputConfigurationAnalyzer();

        $issues = $analyzer->getIssues([
            200 => [
                $this->mockCondition(
                    obj_ref_id: 200,
                    constraints: [
                        ['kind' => 'all_completed', 'ref_ids' => [151]],
                        ['kind' => 'any_completed', 'ref_ids' => [151, 152]],
                    ]
                ),
                $this->mockCondition(
                    obj_ref_id: 200,
                    constraints: [['kind' => 'none_completed', 'ref_ids' => [152]]]
                ),
            ],
        ]);

        $this->assertSame([], $analyzer->getAffectedRefIds($issues));
    }

    public function testAggregatesAffectedRefIdsFromConditionIssues(): void
    {
        $analyzer = new StaticInputConfigurationAnalyzer();

        $issues = $analyzer->getIssues([
            200 => [
                $this->mockCondition(
                    obj_ref_id: 200,
                    issues: [new StaticInputConfigurationIssue('missing_points_output', [150, 151])]
                ),
                $this->mockCondition(
                    obj_ref_id: 200,
                    issues: [new StaticInputConfigurationIssue('owning_conflict', [200])]
                ),
            ],
        ]);

        $this->assertSame([150, 151, 200], $analyzer->getAffectedRefIds($issues));
    }

    public function testMergesIssueDetailsByAffectedRefId(): void
    {
        $analyzer = new StaticInputConfigurationAnalyzer();

        $details_by_ref_id = $analyzer->getIssueDetailsByRefId([
            new StaticInputConfigurationIssue(
                'points_input_source_without_points_output',
                [150],
                details: [
                    new StaticInputConfigurationIssueDetail(
                        150,
                        'lso_points_input_missing_output_on_object',
                        properties_by_language_var: [
                            'lso_static_input_configuration_referenced_by_objects' => [201]
                        ]
                    )
                ]
            ),
            new StaticInputConfigurationIssue(
                'points_input_source_without_points_output',
                [150],
                details: [
                    new StaticInputConfigurationIssueDetail(
                        150,
                        'lso_points_input_missing_output_on_object',
                        properties_by_language_var: [
                            'lso_static_input_configuration_referenced_by_objects' => [202]
                        ]
                    )
                ]
            ),
        ]);

        $this->assertSame([150], array_keys($details_by_ref_id));
        $this->assertCount(1, $details_by_ref_id[150]);
        $this->assertSame(
            [201, 202],
            $details_by_ref_id[150][0]->properties_by_language_var['lso_static_input_configuration_referenced_by_objects']
        );
    }

    /**
     * @param array<int, array{kind: string, ref_ids: int[]}> $constraints
     * @param StaticInputConfigurationIssue[] $issues
     */
    private function mockCondition(int $obj_ref_id, array $constraints = [], array $issues = []): AbstractCondition
    {
        return new class ($obj_ref_id, $constraints, $issues) extends AbstractCondition {
            /**
             * @param array<int, array{kind: string, ref_ids: int[]}> $constraints
             * @param StaticInputConfigurationIssue[] $issues
             */
            public function __construct(
                private int $configured_obj_ref_id,
                private array $constraints,
                private array $issues
            ) {
            }

            public function check(): bool
            {
                return true;
            }

            public function getObjRefId(): ?int
            {
                return $this->configured_obj_ref_id;
            }

            public function getStaticInputConditionConstraints(): array
            {
                return $this->constraints;
            }

            public function getStaticInputConfigurationIssues(array $context = []): array
            {
                return $this->issues;
            }
        };
    }
}
