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

namespace ILIAS\Forum\Drafts;

use Generator;
use ilObjUser;
use ilLanguage;
use ilObjForum;
use ilObjForumGUI;
use ilCtrlInterface;
use ilForumPostDraft;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use DateTimeImmutable;
use ilCalendarSettings;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Table\DataRetrieval;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Table\Data as DataTable;

class ForumDraftsTable implements DataRetrieval
{
    /** @var list<array{draft_id: int, draft: string, edited_on: string}>|null */
    private ?array $records = null;

    public function __construct(
        private readonly ilObjForum $forum,
        private readonly UIFactory $ui_factory,
        private readonly ServerRequestInterface $httpRequest,
        private readonly ilLanguage $lng,
        private readonly string $parent_cmd,
        private readonly ilCtrlInterface $ctrl,
        private readonly DataFactory $data_factory,
        private readonly ilObjUser $user,
        private readonly bool $mayEdit,
        private readonly ilObjForumGUI $parent_object,
    ) {
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters,
    ): Generator {
        $records = $this->getRecords($range, $order);
        foreach ($records as $record) {
            yield $row_builder->buildDataRow((string) $record['draft_id'], $record);
        }
    }

    public function initRecords(): void
    {
        if ($this->records === null) {
            $this->records = [];
            $drafts = ilForumPostDraft::getThreadDraftData(
                $this->user->getId(),
                ilObjForum::lookupForumIdByObjId($this->forum->getId())
            );

            foreach ($drafts as $draft) {
                if (!isset($draft['draft_id'], $draft['subject'], $draft['post_update'])) {
                    continue;
                }

                $draft_id = $draft['draft_id'];
                $this->records[$draft_id] = ['draft_id' => $draft_id];
                if ($this->mayEdit) {
                    $this->ctrl->setParameter($this->parent_object, 'draft_id', $draft_id);
                    $url = $this->ctrl->getLinkTarget($this->parent_object, 'editThreadDraft');
                    $this->records[$draft_id]['draft'] = $this->ui_factory->link()->standard(
                        $draft['subject'],
                        $url
                    );
                    $this->ctrl->setParameter($this->parent_object, 'draft_id', null);
                } else {
                    $this->records[$draft_id]['draft'] = $draft['subject'];
                }
                $this->records[$draft_id]['edited_on'] = new DateTimeImmutable(
                    $draft['post_update']
                );
            }
        }
    }

    public function getComponent(): DataTable
    {
        $query_params_namespace = ['forum', 'drafts', 'delete'];
        $uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(ilObjForumGUI::class, 'confirmDeleteThreadDrafts')
        );
        $url_builder = new URLBuilder($uri);
        [$url_builder, $action_parameter_token, $row_id_token] = $url_builder->acquireParameters(
            $query_params_namespace,
            'table_action',
            'draft_ids'
        );

        return $this->ui_factory
            ->table()
            ->data(
                $this,
                $this->lng->txt('drafts'),
                $this->getColumns(),
            )
            ->withId(
                'frm_drafts_' . substr(
                    md5($this->parent_cmd),
                    0,
                    3
                ) . '_' . $this->forum->getId()
            )
            ->withRequest($this->httpRequest)
            ->withActions(
                [
                    'delete' => $this->ui_factory->table()->action()->multi(
                        $this->lng->txt('delete'),
                        $url_builder->withParameter($action_parameter_token, 'delete'),
                        $row_id_token
                    )
                ]
            );
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        $this->initRecords();

        return count((array) $this->records);
    }

    /**
     * @return list<array{draft_id: int, draft: string, edited_on: string}>
     */
    private function getRecords(Range $range, Order $order): array
    {
        $this->initRecords();

        return $this->limitRecords($this->records, $range);
    }

    /**
     * @param list<array{draft_id: int, draft: string, edited_on: string}> $records
     * @return list<array{draft_id: int, draft: string, edited_on: string}>
     */
    private function limitRecords(array $records, Range $range): array
    {
        return array_slice($records, $range->getStart(), $range->getLength());
    }

    /**
     * @return array{
     *     draft: \ILIAS\UI\Component\Table\Column\Link,
     *     edited_on: \ILIAS\UI\Component\Table\Column\Text
     * }
     */
    private function getColumns(): array
    {
        if ((int) $this->user->getTimeFormat() === ilCalendarSettings::TIME_FORMAT_12) {
            $format = $this->data_factory->dateFormat()->withTime12($this->user->getDateFormat());
        } else {
            $format = $this->data_factory->dateFormat()->withTime24($this->user->getDateFormat());
        }

        return [
            'draft' => $this->ui_factory->table()->column()->link($this->lng->txt('drafts'))->withIsSortable(
                false
            )->withIsSortable(false),
            'edited_on' => $this->ui_factory->table()->column()->date(
                $this->lng->txt('edited_on'),
                $format
            )->withIsSortable(false)
        ];
    }
}
