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

final class ilLTIConsumerLineItemRepository
{
    public function __construct(private readonly ilDBInterface $db)
    {
    }

    /** @return list<ilLTIConsumerLineItem> */
    public function getForContextAndClient(int $contextId, string $clientId): array
    {
        $result = $this->db->query(
            'SELECT * FROM lti_consumer_lineitems'
            . ' WHERE context_id = ' . $this->db->quote($contextId, 'integer')
            . ' AND client_id = ' . $this->db->quote($clientId, 'text')
            . ' AND enabled = ' . $this->db->quote(1, 'integer')
        );

        $lineItems = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $lineItems[] = $this->fromRow($row);
        }
        return $lineItems;
    }

    public function get(int $id, int $contextId, string $clientId): ?ilLTIConsumerLineItem
    {
        $result = $this->db->query(
            'SELECT * FROM lti_consumer_lineitems'
            . ' WHERE id = ' . $this->db->quote($id, 'integer')
            . ' AND context_id = ' . $this->db->quote($contextId, 'integer')
            . ' AND client_id = ' . $this->db->quote($clientId, 'text')
            . ' AND enabled = ' . $this->db->quote(1, 'integer')
        );
        $row = $this->db->fetchAssoc($result);

        return $row ? $this->fromRow($row) : null;
    }

    public function create(
        int $contextId,
        string $clientId,
        ?string $label,
        float $scoreMaximum,
        string $resourceId,
        string $resourceLinkId,
        string $tag
    ): ilLTIConsumerLineItem {
        $id = $this->db->nextId('lti_consumer_lineitems');
        $lineItem = new ilLTIConsumerLineItem(
            $id,
            $contextId,
            $clientId,
            $label ?? 'Line Item ' . $id,
            $scoreMaximum,
            $resourceId,
            $resourceLinkId,
            $tag
        );
        $this->db->insert('lti_consumer_lineitems', [
            'id' => ['integer', $lineItem->id],
            'context_id' => ['integer', $lineItem->contextId],
            'obj_id' => ['integer', null],
            'client_id' => ['text', $lineItem->clientId],
            'label' => ['text', $lineItem->label],
            'score_maximum' => ['float', $lineItem->scoreMaximum],
            'resource_id' => ['text', $lineItem->resourceId],
            'resource_link_id' => ['text', $lineItem->resourceLinkId],
            'tag' => ['text', $lineItem->tag],
            'enabled' => ['integer', 1]
        ]);

        return $lineItem;
    }

    public function update(ilLTIConsumerLineItem $lineItem): void
    {
        $this->db->update('lti_consumer_lineitems', [
            'label' => ['text', $lineItem->label],
            'score_maximum' => ['float', $lineItem->scoreMaximum],
            'resource_id' => ['text', $lineItem->resourceId],
            'resource_link_id' => ['text', $lineItem->resourceLinkId],
            'tag' => ['text', $lineItem->tag]
        ], [
            'id' => ['integer', $lineItem->id]
        ]);
    }

    public function disable(ilLTIConsumerLineItem $lineItem): void
    {
        $this->db->update('lti_consumer_lineitems', [
            'enabled' => ['integer', 0]
        ], [
            'id' => ['integer', $lineItem->id]
        ]);
    }

    /** @param array<string, mixed> $row */
    private function fromRow(array $row): ilLTIConsumerLineItem
    {
        return new ilLTIConsumerLineItem(
            (int) $row['id'],
            (int) $row['context_id'],
            (string) ($row['client_id'] ?? ''),
            (string) ($row['label'] ?? ''),
            (float) ($row['score_maximum'] ?? 1),
            (string) ($row['resource_id'] ?? ''),
            (string) ($row['resource_link_id'] ?? ''),
            (string) ($row['tag'] ?? '')
        );
    }
}
