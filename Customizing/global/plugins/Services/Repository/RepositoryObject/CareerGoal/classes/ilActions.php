<?php
namespace CaT\Plugins\CareerGoal;

class ilActions {
	const F_TITLE = "title";
	const F_DESCRIPTION = "description";
	const F_LOWMARK = "lowmark";
	const F_SHOULD_SPECIFICATION = "should_specification";
	const F_DEFAULT_TEXT_FAILED = "default_text_failed";
	const F_DEFAULT_TEXT_PARTIAL = "default_text_partial";
	const F_DEFAULT_TEXT_SUCCESS = "default_text_success";

	public function __construct(\CaT\Plugins\CareerGoal\ObjCareerGoal $object, \CaT\Plugins\CareerGoal\Settings\DB $db) {
		$this->object = $object;
		$this->db = $db;
	}

	/**
	 * Update the object with the values from the array.
	 *
	 * @param	array	filled with fields according to F_*-constants
	 * @return  null
	 */
	public function update(array &$values) {
		assert('array_key_exists(self::F_TITLE, $values)');
		assert('is_string($values[self::F_TITLE])');
		$this->object->setTitle($values[self::F_TITLE]);
		if (array_key_exists(self::F_DESCRIPTION, $values)) {
			assert('is_string($values[self::F_DESCRIPTION])');
			$this->object->setDescription($values[self::F_DESCRIPTION]);
		}
		else {
			$this->object->setDescription("");
		}

		$values[self::F_LOWMARK] = $this->tofloat($values[self::F_LOWMARK]);
		$values[self::F_SHOULD_SPECIFICATION] = $this->tofloat($values[self::F_SHOULD_SPECIFICATION]);

		$this->object->updateSettings(function($s) use (&$values) {
			return $s
				->withLowmark($values[self::F_LOWMARK])
				->withShouldSpecifiaction($values[self::F_SHOULD_SPECIFICATION])
				->withDefaultTextFailed($values[self::F_DEFAULT_TEXT_FAILED])
				->withDefaultTextPartial($values[self::F_DEFAULT_TEXT_PARTIAL])
				->withDefaultTextSuccess($values[self::F_DEFAULT_TEXT_SUCCESS])
				;
		});
		$this->object->update();
	}

	/**
	 * Read the object to an array.
	 *
	 * @return array
	 */
	public function read() {
		$values = array();
		$values[self::F_TITLE] = $this->object->getTitle();
		$values[self::F_DESCRIPTION] = $this->object->getDescription();

		$settings = $this->object->getSettings();
		$values[self::F_LOWMARK] = $settings->getLowmark();
		$values[self::F_SHOULD_SPECIFICATION] = $settings->getShouldSpecification();
		$values[self::F_DEFAULT_TEXT_FAILED] = $settings->getDefaultTextFailed();
		$values[self::F_DEFAULT_TEXT_PARTIAL] = $settings->getDefaultTextPartial();
		$values[self::F_DEFAULT_TEXT_SUCCESS] = $settings->getDefaultTextSuccess();

		return $values;
	}

	/**
	 * replace last comma with dot
	 *
	 * @param 	string 	$num
	 *
	 * @return 	float
	 */
	protected function tofloat($num) {
		$dotPos = strrpos($num, '.');
		$commaPos = strrpos($num, ',');
		$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos : 
					((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

		if (!$sep) {
			return floatval(preg_replace("/[^0-9]/", "", $num));
		} 

		return floatval(
			preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
			preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
		);
	}
}