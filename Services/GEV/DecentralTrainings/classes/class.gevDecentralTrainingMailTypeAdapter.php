<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/MailTemplates/classes/class.ilMailTypeAdapter.php';

/**
 * GEV mail placeholders for courses
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 */
class gevDecentralTrainingMailTypeAdapter extends ilMailTypeAdapter {
	private $placeholders = null;
	
	public function getCategoryNameLocalized($category_name, $lng) {
		return 'DecentralTrainingCreation';
	}

	public function getTemplateTypeLocalized($category_name, $template_type, $lng) {
		return 'Generisch';
	}

	protected function getPlaceholders() {
		if ($this->placeholders == null) {
			$this->placeholders = array(
				  array("Titel"						, "Titel des angelegten dezentralen Trainings")
				, array("Startdatum"				, "Beginndatum des Trainings")
				, array("Startzeit"					, "Uhrzeit des Beginns des Trainings")
				, array("Enddatum"					, "Enddatum des Trainings")
				, array("Endzeit"					, "Uhrzeit des Ende des Trainings")
				, array("Buchungslink"				, "Link zum Buchungsformular des dezentralen Trainings")
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