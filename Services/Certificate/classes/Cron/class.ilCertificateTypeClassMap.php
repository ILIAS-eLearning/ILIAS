<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTypeClassMap
{
	private $typeClassMap = array(
		'crs'  => array('placeholder' => 'ilCoursePlaceholderValues'),
		'tst'  => array('placeholder' => 'ilTestPlaceHolderValues'),
		'exc'  => array('placeholder' => 'ilExercisePlaceHolderValues'),
		'sahs' => array('placeholder' => 'ilScormPlaceholderValues'),
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
