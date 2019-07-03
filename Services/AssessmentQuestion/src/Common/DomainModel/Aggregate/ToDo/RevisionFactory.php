<?php

namespace ILIAS\AssessmentQuestion\Common\Entity;

/**
 * Class RevisionFactory
 *
 * Generates Revision safe Revision id for IsRevisable object
 *
 * @package ILIAS\AssessmentQuestion\Common\Entity
 */
class RevisionFactory {
	const NAME_KEY = "revision_factory_revision_name_key";
	const NAME_SEPERATOR = "#:#";


	/**
	 * @param IsRevisable $entity
	 *
	 * Revisable object will be stamped with a valid RevisionId
	 */
	public static function SetRevisionId(IsRevisable $entity) {

		$key = self::GenerateRevisionKey($entity);

		$entity->setRevisionId(new RevisionId($key));
	}


	/**
	 * @param IsRevisable $entity
	 *
	 * check if the RevisionId of an object and his data match, if not the object
	 * is corrupt or has been tampered with
	 *
	 * @return bool
	 */
	public static function ValidateRevision(IsRevisable $entity) : bool {
		return $entity->getRevisionId()->GetKey() === self::GenerateRevisionKey($entity);
	}


	/**
	 * @param IsRevisable $entity
	 *
	 * Generates the key by hashing the revision data and adds the hash of the
	 * data containing the name with the name which should make it impossible
	 * to create objects that have the same key that do not contain the same data
	 *
	 * TODO md5 is no safe algorithm and needs to be replaced by something safe
	 * TODO or maybe this should be made configurable, which would meand that
	 * TODO the used algorithm needs also to be embedded in the key
	 *
	 * @return string
	 */
	private static function GenerateRevisionKey(IsRevisable $entity) : string {
		$data = $entity->getRevisionData();
		$data[self::NAME_KEY] = $entity->getRevisionName();

		return $entity->getRevisionName() . self::NAME_SEPERATOR . md5(serialize($data));
	}
}