<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/TermsOfService/interfaces/interface.ilTermsOfServiceAcceptanceDataGateway.php';

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilTermsOfServiceAcceptanceDatabaseGateway implements ilTermsOfServiceAcceptanceDataGateway
{
	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @param ilDB $db
	 */
	public function __construct(ilDB $db)
	{
		$this->db = $db;
	}

	/**
	 * @param ilTermsOfServiceAcceptanceEntity $entity
	 */
	public function trackAcceptance(ilTermsOfServiceAcceptanceEntity $entity)
	{
		$query = 'SELECT id FROM tos_versions WHERE hash = %s AND lng = %s';
		$res   = $this->db->queryF(
			$query,
			array('text', 'text'),
			array($entity->getHash(), $entity->getIso2LanguageCode())
		);

		if($this->db->numRows($res))
		{
			$row     = $this->db->fetchAssoc($res);
			$tosv_id = $row['id'];
		}
		else
		{
			$tosv_id = $this->db->nextId('tos_versions');
			$this->db->insert(
				'tos_versions',
				array(
					'id'       => array('integer', $tosv_id),
					'lng'      => array('text', $entity->getIso2LanguageCode()),
					'src'      => array('text', $entity->getSource()),
					'src_type' => array('integer', $entity->getSourceType()),
					'text'     => array('text', $entity->getText()),
					'hash'     => array('text', $entity->getHash()),
					'ts'       => array('integer', $entity->getTimestamp())
				)
			);
		}

		$this->db->insert(
			'tos_acceptance_track',
			array(
				'tosv_id' => array('integer', $tosv_id),
				'usr_id'  => array('integer', $entity->getUserId()),
				'ts'      => array('integer', $entity->getTimestamp())
			)
		);
	}

	/**
	 * @param ilTermsOfServiceAcceptanceEntity $entity
	 * @return ilTermsOfServiceAcceptanceEntity
	 */
	public function loadCurrentAcceptanceOfUser(ilTermsOfServiceAcceptanceEntity $entity)
	{
		$this->db->setLimit(1, 0);
		$res = $this->db->queryF('
			SELECT tos_versions.*, tos_acceptance_track.ts accepted_ts
			FROM tos_acceptance_track
			INNER JOIN tos_versions ON id = tosv_id
			WHERE usr_id = %s
			ORDER BY tos_acceptance_track.ts DESC
			',
			array('integer'),
			array($entity->getUserId())
		);
		$row = $this->db->fetchAssoc($res);

		$entity->setId($row['id']);
		$entity->setUserId($row['usr_id']);
		$entity->setIso2LanguageCode($row['lng']);
		$entity->setSource($row['src']);
		$entity->setSourceType($row['src_type']);
		$entity->setText($row['text']);
		$entity->setTimestamp($row['accepted_ts']);
		$entity->setHash($row['hash']);

		return $entity;
	}

	/**
	 * @param ilTermsOfServiceAcceptanceEntity $entity
	 * @return ilTermsOfServiceAcceptanceEntity
	 */
	public function loadById(ilTermsOfServiceAcceptanceEntity $entity)
	{
		$res = $this->db->queryF('
			SELECT *
			FROM tos_versions
			WHERE id = %s
			',
			array('integer'),
			array($entity->getId())
		);
		$row = $this->db->fetchAssoc($res);

		$entity->setId($row['id']);
		$entity->setIso2LanguageCode($row['lng']);
		$entity->setSource($row['src']);
		$entity->setSourceType($row['src_type']);
		$entity->setText($row['text']);
		$entity->setTimestamp($row['ts']);
		$entity->setHash($row['hash']);

		return $entity;
	}

	/**
	 * @param ilTermsOfServiceAcceptanceEntity $entity
	 */
	public function deleteAcceptanceHistoryByUser(ilTermsOfServiceAcceptanceEntity $entity)
	{
		$this->db->manipulate('DELETE FROM tos_acceptance_track WHERE usr_id = ' . $this->db->quote($entity->getUserId(), 'integer'));
	}
}
