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

namespace ILIAS\Mail\Folder;

use Exception;
use ilSearchSettings;
use ilMailSearchResult;
use ilMailLuceneSearcher;
use ilMailLuceneQueryParser;
use ILIAS\Mail\Message\MailBoxQuery;
use ILIAS\Mail\Message\MailRecordData;
use ILIAS\Mail\Message\MailBoxOrderColumn;

/**
 * Search in mail folders
 * - utilises database and (if active) lucene based searchers
 * - caches results for counting of all or unread mails
 */
class MailFolderSearch
{
    private MailBoxQuery $mailbox_query;
    private ?ilMailLuceneSearcher $lucene_searcher = null;
    private ?ilMailSearchResult $lucene_result = null;
    private ?array $filtered_ids = null;
    private ?int $count = null;
    private ?int $unread = null;

    public function __construct(
        private readonly MailFolderData $folder,
        private readonly MailFilterData $filter,
        private readonly bool $lucene_enabled,
    ) {
        $this->mailbox_query = (new MailBoxQuery(
            $this->folder->getUserId()
        ))
            ->withFolderId($this->folder->getFolderId())
            ->withSender($this->filter->getSender())
            ->withRecipients($this->filter->getRecipients())
            ->withSubject($this->filter->getSubject())
            ->withBody($this->filter->getBody())
            ->withPeriodStart($this->filter->getPeriodStart())
            ->withPeriodEnd($this->filter->getPeriodEnd())
            ->withIsUnread($this->filter->isUnread())
            ->withIsSystem($this->filter->isSystem())
            ->withHasAttachment($this->filter->hasAttachment());

        if ($this->lucene_enabled && (
            !empty($this->filter->getSender()) ||
                !empty($this->filter->getRecipients()) ||
                !empty($this->filter->getSubject()) ||
                !empty($this->filter->getBody()) ||
                !empty($this->filter->getAttachment())
        )) {
            $query_parser = new ilMailLuceneQueryParser('');
            $query_parser->setFields([
                'title' => $this->filter->getSubject(),
                'content' => $this->filter->getBody(),
                'mattachment' => $this->filter->getAttachment(), // only possible with lucene
                'msender' => $this->filter->getSender(),
            ]);
            $query_parser->parse();

            // lucene search wil be done and cached by getFilteredIds
            $this->lucene_result = new ilMailSearchResult();
            $this->lucene_searcher = new ilMailLuceneSearcher($query_parser, $this->lucene_result);
        }
    }

    /**
     * Get a cached count of mails for the filter criteria
     */
    public function getCount(): int
    {
        if (!isset($this->count)) {
            $this->count = $this->mailbox_query->withFilteredIds($this->getFilteredIds())->count();
        }
        return $this->count;
    }

    /**
     * Get a cached count of unread mails for the filter criteria
     */
    public function getUnread(): int
    {
        if (!isset($this->unread)) {
            $this->unread = $this->mailbox_query->withFilteredIds($this->getFilteredIds())->countUnread();
        }
        return $this->unread;
    }

    /**
     * Get the ids of all filtered mails
     * @return int[]
     */
    public function getMaiIds(): array
    {
        return $this->mailbox_query
            ->withFilteredIds($this->getFilteredIds())
            ->queryMailIds();
    }

    /**
     * Get record objects of all filtered mails
     * @return MailRecordData[]
     */
    public function getRecords(): array
    {
        return $this->mailbox_query
            ->withFilteredIds($this->getFilteredIds())
            ->query(true);
    }

    /**
     * Get record objects of filtered and paged mails
     * @return MailRecordData[]
     */
    public function getPagedRecords(
        int $limit,
        int $offset,
        ?MailBoxOrderColumn $order_column,
        ?string $order_direction
    ): array {

        return $this->mailbox_query
            ->withFilteredIds($this->getFilteredIds())
            ->withLimit($limit)
            ->withOffset($offset)
            ->withOrderColumn($order_column)
            ->withOrderDirection($order_direction)
            ->query(true);
    }

    /**
     * Get the cached mail ids from a lucene search for selected filter criteria
     * These will be used as additional filter for the mailbox query
     */
    private function getFilteredIds(): ?array
    {
        if (!isset($this->filtered_ids)
            && isset($this->lucene_result)
            && isset($this->lucene_searcher)
        ) {
            $this->lucene_searcher->search($this->folder->getUserId(), $this->folder->getFolderId());
            $this->filtered_ids = $this->lucene_result->getIds();
        }
        return $this->filtered_ids;
    }

    /**
     * Inject already filtered mail ids, e.g. from a selection
     * @param int[] $ids
     * @return self
     */
    public function forMailIds(array $ids): self
    {
        $clone = clone $this;
        $clone->filtered_ids = $ids;
        return $clone;
    }
}
