<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceDatabaseGateway
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceDatabaseGateway implements \ilTermsOfServiceAcceptanceDataGateway
{
    /** @var \ilDBInterface */
    protected $db;

    /**
     * ilTermsOfServiceAcceptanceDatabaseGateway constructor.
     * @param \ilDBInterface $db
     */
    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function trackAcceptance(\ilTermsOfServiceAcceptanceEntity $entity)
    {
        $res = $this->db->queryF(
            'SELECT id FROM tos_versions WHERE hash = %s AND doc_id = %s',
            array('text', 'integer'),
            array($entity->getHash(), $entity->getDocumentId())
        );

        if ($this->db->numRows($res)) {
            $row = $this->db->fetchAssoc($res);
            $versionId = $row['id'];
        } else {
            $versionId = $this->db->nextId('tos_versions');
            $this->db->insert(
                'tos_versions',
                [
                    'id' => ['integer', $versionId],
                    'text' => ['clob', $entity->getText()],
                    'hash' => ['text', $entity->getHash()],
                    'doc_id' => ['integer', $entity->getDocumentId()],
                    'title' => ['text', $entity->getTitle()],
                    'ts' => ['integer', $entity->getTimestamp()]
                ]
            );
        }

        $this->db->insert(
            'tos_acceptance_track',
            [
                'tosv_id' => ['integer', $versionId],
                'usr_id' => ['integer', $entity->getUserId()],
                'criteria' => ['clob', $entity->getSerializedCriteria()],
                'ts' => ['integer', $entity->getTimestamp()]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function loadCurrentAcceptanceOfUser(\ilTermsOfServiceAcceptanceEntity $entity) : \ilTermsOfServiceAcceptanceEntity
    {
        $this->db->setLimit(1, 0);

        $res = $this->db->queryF(
            '
			SELECT tos_versions.*,
				tos_acceptance_track.ts accepted_ts,
				tos_acceptance_track.criteria,
				tos_acceptance_track.usr_id
			FROM tos_acceptance_track
			INNER JOIN tos_versions ON id = tosv_id
			WHERE usr_id = %s
			ORDER BY tos_acceptance_track.ts DESC
			',
            ['integer'],
            [$entity->getUserId()]
        );
        $row = $this->db->fetchAssoc($res);

        $entity = $entity
            ->withId((int) $row['id'])
            ->withUserId((int) $row['usr_id'])
            ->withText((string) $row['text'])
            ->withTimestamp((int) $row['accepted_ts'])
            ->withHash((string) $row['hash'])
            ->withDocumentId((int) $row['doc_id'])
            ->withTitle((string) $row['title'])
            ->withSerializedCriteria((string) $row['criteria']);

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function loadById(\ilTermsOfServiceAcceptanceEntity $entity) : \ilTermsOfServiceAcceptanceEntity
    {
        $res = $this->db->queryF(
            '
			SELECT *
			FROM tos_versions
			WHERE id = %s
			',
            array('integer'),
            array($entity->getId())
        );
        $row = $this->db->fetchAssoc($res);

        $entity = $entity
            ->withId($row['id'])
            ->withText($row['text'])
            ->withHash($row['hash'])
            ->withDocumentId($row['doc_id'])
            ->withTitle($row['title']);

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function deleteAcceptanceHistoryByUser(\ilTermsOfServiceAcceptanceEntity $entity)
    {
        $this->db->manipulate(
            'DELETE FROM tos_acceptance_track WHERE usr_id = ' . $this->db->quote($entity->getUserId(), 'integer')
        );
    }
}
