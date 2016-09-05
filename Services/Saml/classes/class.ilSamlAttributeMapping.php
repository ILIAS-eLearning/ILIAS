<?php
// saml-patch: begin
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Saml/classes/class.ilSamlAttributeMappingRule.php';

/**
 * Class ilSamlAttributeMapping
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlAttributeMapping implements ArrayAccess, Countable, Iterator
{
	/**
	 * @var self[]
	 */
	protected static $instances = array();

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var int
	 */
	protected $idp_id;

	/**
	 * @var ilSamlAttributeMappingRule[]
	 */
	protected $mapping = array();

	/**
	 * ilSamlAttributeMapping constructor.
	 * @param int $idp_id
	 */
	protected function __construct($idp_id)
	{
		/**
		 * $ilDB ilDB
		 */
		global $ilDB;

		$this->db = $ilDB;

		$this->setIdpId($idp_id);

		$this->read();
	}

	/**
	 * @param int $idp_id
	 * @return self
	 */
	public static function getInstanceByIdpId($idp_id)
	{
		if(!isset(self::$instances[$idp_id]) || !(self::$instances[$idp_id] instanceof self))
		{
			self::$instances[$idp_id] = new self($idp_id);
		}

		return self::$instances[$idp_id];
	}

	/**
	 * @return int
	 */
	public function getIdpId()
	{
		return $this->idp_id;
	}

	/**
	 * @param int $idp_id
	 */
	public function setIdpId($idp_id)
	{
		$this->idp_id = $idp_id;
	}

	/**
	 * @return ilSamlAttributeMappingRule
	 */
	public function getEmptyRule()
	{
		return new ilSamlAttributeMappingRule();
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		return isset($this->mapping[$offset]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		return $this->offsetExists($offset) ? $this->mapping[$offset] : null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		if(is_null($offset))
		{
			$this->mapping[] = $value;
		}
		else
		{
			$this->mapping[$offset] = $value;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		unset($this->mapping[$offset]);
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->mapping);
	}

	/**
	 * @return ilSamlAttributeMappingRule
	 */
	public function current()
	{
		return current($this->mapping);
	}

	/**
	 * {@inheritdoc}
	 */
	public function next()
	{
		next($this->mapping);
	}

	/**
	 * {@inheritdoc}
	 */
	public function key()
	{
		return key($this->mapping);
	}

	/**
	 * {@inheritdoc}
	 */
	public function valid()
	{
		return current($this->mapping);
	}

	public function rewind()
	{
		reset($this->mapping);
	}

	/**
	 *
	 */
	protected function read()
	{
		$this->mapping = array();

		$res = $this->db->query('SELECT * FROM saml_attribute_mapping WHERE idp_id = ' . $this->db->quote($this->getIdpId()));
		while($row = $this->db->fetchAssoc($res))
		{
			$rule = $this->getEmptyRule();
			$rule->setAttribute($row['attribute']);
			$rule->setIdpAttribute($row['idp_attribute']);
			$rule->updateAutomatically((bool)$row['update_automatically']);

			$this->mapping[$rule->getAttribute()] = $rule;
		}
	}

	/**
	 *
	 */
	public function save()
	{
		foreach($this->mapping as $rule)
		{
			$this->db->replace(
				'saml_attribute_mapping',
				array(
					'idp_id'    => array('integer', $this->getIdpId()),
					'attribute' => array('text', $rule->getAttribute())
				),
				array(
					'idp_attribute'        => array('text', $rule->getIdpAttribute()),
					'update_automatically' => array('integer', (int)$rule->isAutomaticallyUpdated())
				)
			);
		}
	}

	/**
	 *
	 */
	public function delete()
	{
		$this->mapping = array();
		$this->db->manipulate('DELETE FROM saml_attribute_mapping WHERE idp_id = ' . $this->db->quote($this->getIdpId()));
	}
}
// saml-patch: end