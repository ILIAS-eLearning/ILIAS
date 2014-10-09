<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilBibliographicSetting
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author Martin Studer <ms@studer-raimann.ch>
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
	 * @var boolean
	 */
	protected $show_in_list = false;


	/**
	 * @param $id
	 */
	public function __construct($id = 0) {
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
		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = '
			. $ilDB->quote($this->getId(), 'integer'));
		$rec = $ilDB->fetchObject($set);
		$this->setBaseUrl($rec->url);
		$this->setImageUrl($rec->img);
		$this->setName($rec->name);
		$this->setShowInList($rec->show_in_list);
	}


	/**
	 * @return ilBibliographicSetting[]
	 */
	public static function getAll() {
		global $ilDB;
		$return = array();
		$set = $ilDB->query('SELECT * FROM ' . self::TABLE_NAME);
		while ($rec = $ilDB->fetchObject($set)) {
			$return[] = new self($rec->id);
		}

		return $return;
	}


	public function update() {
		global $ilDB;

		$ilDB->update(self::TABLE_NAME, array("name" => array(
			"text", $this->getName()),
			"url" => array( "text", $this->getBaseUrl()),
			"img" => array( "text", $this->getImageUrl()),
			"show_in_list" => array("integer", $this->getShowInList())),
			array("id" => array("integer", $this->getId())));
	}


	public function create() {
		global $ilDB;
		// get lowest available id
		$res = $ilDB->query("SELECT * FROM " . self::TABLE_NAME . " ORDER BY id ASC");
		$id = 1;
		while ($row = $ilDB->fetchAssoc($res)) {
			if ($row['id'] == $id) {
				$id ++;
			} else {
				break;
			}
		}
		// insert new entry
		$ilDB->insert(self::TABLE_NAME, array(
			"id" => array( "integer", $id ),
			"name" => array( "text", $this->getName() ),
			"url" => array( "text", $this->getBaseUrl() ),
			"img" => array( "text", $this->getImageUrl() ),
			"show_in_list" => array( "integer", $this->getShowInList() )
		));
	}


	/**
	 * @param ilBibliographicEntry $entry
	 * @param string $type (bib|ris)
	 *
	 * @return string
	 */
	public function generateLibraryLink($entry, $type) {

		$attributes = $entry->getAttributes();
		switch ($type) {
			case 'bib':
				$prefix = "bib_default_";
				if(!empty($attributes[$prefix."isbn"])){
                    $attr = Array("isbn");
                }elseif(!empty($attributes[$prefix."issn"])){
                    $attr = Array("issn");
                }else{
                    $attr = Array("title", "author", "year", "number", "volume");
                }
                break;
			case 'ris':
				$prefix = "ris_" . strtolower($entry->getType()) . "_";
				if(!empty($attributes[$prefix."sn"])){
                    $attr = Array("sn");
                }else{
                    $attr = Array("ti", "t1", "au", "py", "is", "vl");
                }
				break;
		}

        $url_params = "?";
		if(sizeof($attr) == 1){
            $url_params .= $this->formatAttribute($attr[0], $type, $attributes, $prefix) . "=" . urlencode($attributes[$prefix . $attr[0]]);
        }else{
            foreach($attr as $a){
                if(array_key_exists($prefix.$a, $attributes)){
                    if(strlen($url_params) > 1){
                        $url_params .= "&";
                    }
                    $url_params .= $this->formatAttribute($a, $type, $attributes, $prefix) . "=" . urlencode($attributes[$prefix . $a]);
                }
            }
        }

		// return full link
		$full_link = $this->getBaseUrl() . $url_params;

		return $full_link;
	}

    /**
     * @param String $a
     * @param String $type
     * @param Array $attributes
     * @param String $prefix
     * @return String
     */
    private function formatAttribute($a, $type, $attributes, $prefix){
        if($type = 'ris'){
            switch($a){
                case 'ti':
                    $a = "title";
                    break;
                case 't1':
                    $a = "title";
                    break;
                case 'au':
                    $a = "author";
                    break;
                case 'sn':
                    if (strlen($attributes[$prefix."sn"]) <= 9) {
                        $a = "issn";
                    }else{
                        $a = "isbn";
                    }
                    break;
                case 'py':
                    $a = "date";
                    break;
                case 'is':
                    $a = "issue";
                    break;
                case 'vl':
                    $a = "volume";
                    break;
            }
        }elseif($type = 'bib'){
            switch($a){
                case "number":
                    $a = "issue";
                    break;
                case "year":
                    $a = "date";
                    break;
            }
        }
        return $a;
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


	/**
	 * @param boolean $show_in_list
	 */
	public function setShowInList($show_in_list) {
		$this->show_in_list = $show_in_list;
	}


	/**
	 * @return boolean
	 */
	public function getShowInList() {
		return $this->show_in_list;
	}
}

?>
