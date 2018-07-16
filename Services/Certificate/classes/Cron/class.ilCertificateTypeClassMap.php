<?php


class ilCertificateTypeClassMap
{
	private $typeClassMap = array(
		'crs' => 'ilCourseCertificateAdapter',
		'tst' => 'ilTestCertificateAdapter',
		'exc' => 'ilExerciseCertificateAdapter',
		'scorm' => 'ilScormCertificateAdapter'
	);

	/**
	 * @param string $type
	 * @return array
	 * @throws ilException
	 */
	public function getClassNameByType($type)
	{
		if (false === $this->typeExists($type)) {
			throw new ilException('The given type ' . $type . 'is not mapped as a class on the class map');
		}
		return $this->typeClassMap[$type];
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public function typeExists($type)
	{
		return array_key_exists($type, $this->typeClassMap);
	}
}
