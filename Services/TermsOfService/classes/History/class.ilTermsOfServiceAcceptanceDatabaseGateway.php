<?php declare(strict_types=1);

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

/**
 * Class ilTermsOfServiceAcceptanceDatabaseGateway
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceDatabaseGateway implements ilTermsOfServiceAcceptanceDataGateway
{
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function trackAcceptance(ilTermsOfServiceAcceptanceEntity $entity) : void
    {
        $res = $this->db->queryF(
            'SELECT id FROM tos_versions WHERE hash = %s AND doc_id = %s',
            ['text', 'integer'],
            [$entity->getHash(), $entity->getDocumentId()]
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

    public function loadCurrentAcceptanceOfUser(
        ilTermsOfServiceAcceptanceEntity $entity
    ) : ilTermsOfServiceAcceptanceEntity {
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

    public function loadById(ilTermsOfServiceAcceptanceEntity $entity) : ilTermsOfServiceAcceptanceEntity
    {
        $res = $this->db->queryF(
            '
			SELECT *
			FROM tos_versions
			WHERE id = %s
			',
            ['integer'],
            [$entity->getId()]
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

    public function deleteAcceptanceHistoryByUser(ilTermsOfServiceAcceptanceEntity $entity) : void
    {
        $this->db->manipulate(
            'DELETE FROM tos_acceptance_track WHERE usr_id = ' . $this->db->quote($entity->getUserId(), 'integer')
        );
    }
}
