<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
if (class_exists('ActiveRecord') != true) {
	require_once('./Services/ActiveRecord/class.ActiveRecord.php');
}

/**
 * Class ilBibliographicSetting
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version 1.0.0
 */
class ilBibliographicSetting extends ActiveRecord {

	const TABLE_NAME = 'il_bibl_settings';


	/**
	 * @return string
	 * @deprecated
	 */
	static function returnDbTableName() {
		return self::TABLE_NAME;
	}


	/**
	 * @return string
	 */
	public function getConnectorContainerName() {
		return self::TABLE_NAME;
	}


	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 * @con_is_notnull true
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 */
	protected $id;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     50
	 * @con_is_notnull true
	 */
	protected $name;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     128
	 * @con_is_notnull true
	 */
	protected $url;
	/**
	 * @var
	 *
	 * @con_has_field true
	 * @con_fieldtype text
	 * @con_length    128
	 */
	protected $img;
	/**
	 * @var
	 *
	 * @con_has_field true
	 * @con_fieldtype integer
	 * @con_length    1
	 */
	protected $show_in_list;


	/**
	 * @return ilBibliographicSetting[]
	 */
	public static function getAll() {
		return self::get();
	}


	/**
	 * @param ilBibliographicEntry $entry
	 * @param string               $type (bib|ris)
	 *
	 * @return string
	 */
	public function generateLibraryLink(ilBibliographicEntry $entry, $type) {
		$attributes = $entry->getAttributes();
		switch ($type) {
			case 'bib':
				$prefix = "bib_default_";
				if (!empty($attributes[$prefix . "isbn"])) {
					$attr = Array( "isbn" );
				} elseif (!empty($attributes[$prefix . "issn"])) {
					$attr = Array( "issn" );
				} else {
					$attr = Array( "title", "author", "year", "number", "volume" );
				}
				break;
			case 'ris':
				$prefix = "ris_" . strtolower($entry->getType()) . "_";
				if (!empty($attributes[$prefix . "sn"])) {
					$attr = Array( "sn" );
				} else {
					$attr = Array( "ti", "t1", "au", "py", "is", "vl" );
				}
				break;
		}

		$url_params = "?";
		if (sizeof($attr) == 1) {
			$url_params .= $this->formatAttribute($attr[0], $type, $attributes, $prefix) . "=" . urlencode($attributes[$prefix . $attr[0]]);
		} else {
			foreach ($attr as $a) {
				if (array_key_exists($prefix . $a, $attributes)) {
					if (strlen($url_params) > 1) {
						$url_params .= "&";
					}
					$url_params .= $this->formatAttribute($a, $type, $attributes, $prefix) . "=" . urlencode($attributes[$prefix . $a]);
				}
			}
		}

		// return full link
		$full_link = $this->getUrl() . $url_params;

		return $full_link;
	}


	/**
	 * @param ilObjBibliographic   $bibl_obj
	 * @param ilBibliographicEntry $entry
	 *
	 * @return string
	 */
	public function getButton(ilObjBibliographic $bibl_obj, ilBibliographicEntry $entry) {
		if ($this->getImg()) {
            require_once('./Services/UIComponent/Button/classes/class.ilImageLinkButton.php');
            $button = ilImageLinkButton::getInstance();
            $button->setUrl($this->generateLibraryLink($entry, $bibl_obj->getFiletype()));
            $button->setImage($this->getImg(), false);
            $button->setTarget('_blank');
            return $button->render();
		} else {
            require_once('./Services/UIComponent/Button/classes/class.ilLinkButton.php');
            $button = ilLinkButton::getInstance();
			$button->setUrl($this->generateLibraryLink($entry, $bibl_obj->getFiletype()));
			$button->setTarget('_blank');
			$button->setCaption('bibl_link_online');

			return $button->render();
		}
	}


	/**
	 * @param String $a
	 * @param String $type
	 * @param Array  $attributes
	 * @param String $prefix
	 *
	 * @return String
	 */
	private function formatAttribute($a, $type, $attributes, $prefix) {
		if ($type = 'ris') {
			switch ($a) {
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
					if (strlen($attributes[$prefix . "sn"]) <= 9) {
						$a = "issn";
					} else {
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
		} elseif ($type = 'bib') {
			switch ($a) {
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
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param mixed $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return mixed
	 */
	public function getImg() {
		return $this->img;
	}


	/**
	 * @param mixed $img
	 */
	public function setImg($img) {
		$this->img = $img;
	}


	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}


	/**
	 * @param mixed $name
	 */
	public function setName($name) {
		$this->name = $name;
	}


	/**
	 * @return mixed
	 */
	public function getShowInList() {
		return $this->show_in_list;
	}


	/**
	 * @param mixed $show_in_list
	 */
	public function setShowInList($show_in_list) {
		$this->show_in_list = $show_in_list;
	}


	/**
	 * @return mixed
	 */
	public function getUrl() {
		return $this->url;
	}


	/**
	 * @param mixed $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}
}

?>
