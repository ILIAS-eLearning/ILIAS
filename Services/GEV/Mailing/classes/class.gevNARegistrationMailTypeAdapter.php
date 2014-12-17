<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailTypeAdapter.php';

/**
 * GEV mail placeholders for courses
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 */
class gevNARegistrationMailTypeAdapter extends ilMailTypeAdapter {
	private $placeholders = null;
	
	public function getCategoryNameLocalized($category_name, $lng) {
		return 'NARegistration';
	}

	public function getTemplateTypeLocalized($category_name, $template_type, $lng) {
		return 'Generisch';
	}

	protected function getPlaceholders() {
		if ($this->placeholders == null) {
			$this->placeholders = array(
				  array( "NA-Vorname"				, "Vorname des NA")
				, array( "NA-Nachname"				, "Nachname des NA")
				, array( "Bestätigungslink"			, "Link zur Bestätigung des NAs")
				, array( "Ablehnungslink"			, "Link zur Ablehnung des NAs")
				);
		}
	
		return $this->placeholders;
	}

	public function getPlaceholdersLocalized($category_name = '', $template_type = '', $lng = '') {
		$ret = array();

		foreach($this->getPlaceholders() as $item)
		{
			$ret[] = array(
				'placeholder_code'          => strtoupper($item[0]),
				'placeholder_name'          => $item[0],
				'placeholder_description'   => $item[1]
			);
		};

		return $ret;
	}

	public function getPlaceHolderPreviews($category_name = '', $template_type = '', $lng = '') {
		$ret = array();

		foreach($this->getPlaceholders() as $item)
		{
			$ret[] = array(
				'placeholder_code'			=> strtoupper($item[0]),
				'placeholder_content'       => $item[0]
			);
		}

		return $ret;
	}

	public function hasAttachmentsPreview() {
		return false;
	}

	public function getAttachmentsPreview() {

	}
}

?>