<?php


class ilCertificateTypeClassMap
{
	private $typeClassMap = array(
		'crs'   => array('placeholder' => 'CoursePlaceholderValues'),
		'tst'   => array('placeholder' => 'TestPlaceHolderValues'),
		'exc'   => array('placeholder' =>'ilExercisePlaceHolderValues'),
		'scorm' => array('placeholder' =>'ilDefaultPlaceholderValues'),
		'skill' => array('placeholder' =>'ilDefaultPlaceholderValues')
	);

	/**
	 * @param string $type
	 * @return array
	 * @throws ilException
	 */
	public function getPlaceHolderClassNameByType($type)
	{
		if (false === $this->typeExistsInMap($type)) {
			throw new ilException('The given type ' . $type . 'is not mapped as a class on the class map');
		}
		return $this->typeClassMap[$type]['placeholder'];
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public function typeExistsInMap($type)
	{
		return array_key_exists($type, $this->typeClassMap);
	}
}
