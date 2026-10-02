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

/**
 * The line items tools create themselves in a context through the Assignment and Grade Services, besides
 * the one every LTI object is. Earlier releases kept them in the same table. A removed line item is only
 * disabled, so that the scores posted to it stay.
 *
 * A line item is an array with the keys id, context_id, client_id, label, score_maximum, resource_id,
 * resource_link_id, tag, start_date_time, end_date_time and grades_released (null when not given).
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
final readonly class ilLTIAdvantagePlatformLineItemRepository
{
    private const string TABLE_NAME = 'lti_consumer_lineitems';

    public function __construct(private ilDBInterface $db)
    {
    }

    /**
     * @return array[]
     */
    public function getAll(int $context_ref_id, string $client_id): array
    {
        $rows = $this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE context_id = %s AND client_id = %s AND enabled = %s ORDER BY id',
            ['integer', 'text', 'integer'],
            [$context_ref_id, $client_id, 1]
        );
        $line_items = [];
        while ($row = $this->db->fetchAssoc($rows)) {
            $line_items[] = $this->fromRow($row);
        }

        return $line_items;
    }

    /**
     * @return array|null
     */
    public function get(int $id, int $context_ref_id, string $client_id): ?array
    {
        $row = $this->db->fetchAssoc($this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = %s AND context_id = %s AND client_id = %s AND enabled = %s',
            ['integer', 'integer', 'text', 'integer'],
            [$id, $context_ref_id, $client_id, 1]
        ));

        return $row === null ? null : $this->fromRow($row);
    }

    /**
     * @param array $line_item without id
     * @return array the line item with its id
     */
    public function create(array $line_item): array
    {
        $line_item['id'] = $this->db->nextId(self::TABLE_NAME);
        $this->db->insert(self::TABLE_NAME, [
            'id' => ['integer', $line_item['id']],
            'context_id' => ['integer', $line_item['context_id']],
            'client_id' => ['text', $line_item['client_id']],
            'enabled' => ['integer', 1],
        ] + $this->getDbFields($line_item));

        return $line_item;
    }

    /**
     * @param array $line_item
     */
    public function update(array $line_item): void
    {
        $this->db->update(self::TABLE_NAME, $this->getDbFields($line_item), ['id' => ['integer', $line_item['id']]]);
    }

    public function disable(int $id): void
    {
        $this->db->update(self::TABLE_NAME, ['enabled' => ['integer', 0]], ['id' => ['integer', $id]]);
    }

    /**
     * @param array $line_item
     * @return array
     */
    private function getDbFields(array $line_item): array
    {
        return [
            'label' => ['text', $line_item['label']],
            'score_maximum' => ['float', $line_item['score_maximum']],
            'resource_id' => ['text', $line_item['resource_id']],
            'resource_link_id' => ['text', $line_item['resource_link_id']],
            'tag' => ['text', $line_item['tag']],
            'start_date_time' => ['text', $line_item['start_date_time']],
            'end_date_time' => ['text', $line_item['end_date_time']],
            'grades_released' => ['integer', $line_item['grades_released'] === null ? null : (int) $line_item['grades_released']],
        ];
    }

    /**
     * @param array $row
     * @return array
     */
    private function fromRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'context_id' => (int) $row['context_id'],
            'client_id' => (string) $row['client_id'],
            'label' => (string) $row['label'],
            'score_maximum' => (float) ($row['score_maximum'] ?? 0) > 0 ? (float) $row['score_maximum'] : 1.0,
            'resource_id' => (string) $row['resource_id'],
            'resource_link_id' => (string) $row['resource_link_id'],
            'tag' => (string) $row['tag'],
            'start_date_time' => $row['start_date_time'] ?? null,
            'end_date_time' => $row['end_date_time'] ?? null,
            'grades_released' => isset($row['grades_released']) ? (bool) $row['grades_released'] : null,
        ];
    }
}
