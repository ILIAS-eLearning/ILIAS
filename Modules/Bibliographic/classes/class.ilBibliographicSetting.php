<?php

/**
 * Class ilBibliographicSetting
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilBibliographicSetting {

	const TABLE_NAME = 'il_bibl_settings';
	/**
	 * @var int
	 */
	protected $id = 0;
	/**
	 * @var string
	 */
	protected $name = '';
	/**
	 * @var string
	 */
	protected $base_url = '';
	/**
	 * @var string
	 */
	protected $image_url = '';


	/**
	 * @param $id
	 */
	public function __construct($id) {
		$this->id = $id;
		if ($this->id > 0) {
			$this->read();
		}
	}


	public function read() {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */

		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = ' . $ilDB->quote($this->getId(), 'integer'));
		$rec = $ilDB->fetchObject($set);

		$this->setBaseUrl($rec->base_url);
		$this->setImageUrl($rec->image_url);
		$this->setName($rec->name);
	}


	/**
	 * @return ilBibliographicSetting[]
	 */
	public static function getAll() {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$return = array();
		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME);
		while ($rec = $ilDB->fetchObject($set)) {
			$return[] = new self($rec->id);
		}

		return $return;
	}


	/**
	 * @param string $base_url
	 */
	public function setBaseUrl($base_url) {
		$this->base_url = $base_url;
	}


	/**
	 * @return string
	 */
	public function getBaseUrl() {
		return $this->base_url;
	}


	/**
	 * @param string $image_url
	 */
	public function setImageUrl($image_url) {
		$this->image_url = $image_url;
	}


	/**
	 * @return string
	 */
	public function getImageUrl() {
		return $this->image_url;
	}


	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
}

?>
