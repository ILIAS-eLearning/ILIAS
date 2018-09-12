<?php

/**
 * Collection of basic placeholder values that can be used
 */
class ilDefaultPlaceholderValues implements ilCertificatePlaceholderValues
{
	/**
	 * @var array
	 */
	private $placeholder;

	/**
	 * @param ilLanguage $language
	 */
	public function __construct()
	{
		$this->placeholder = array(
			'USER_LOGIN'         => '',
			'USER_FULLNAME'      => '',
			'USER_FIRSTNAME'     => '',
			'USER_LASTNAME'      => '',
			'USER_TITLE'         => '',
			'USER_SALUTATION'    => '',
			'USER_BIRTHDAY'      => '',
			'USER_INSTITUTION'   => '',
			'USER_DEPARTMENT'    => '',
			'USER_STREET'        => '',
			'USER_CITY'          => '',
			'USER_ZIPCODE'       => '',
			'USER_COUNTRY'       => '',
			'USER_MATRICULATION' => '',
			'DATE'               => '',
			'DATETIME'           => '',
			'DATE_COMPLETED'     => '',
			'DATETIME_COMPLETED' => '',
			'CLIENT_WEB_DIR'     => ''
		);
	}

	/**
	 * @param $userId
	 * @param $objId
	 * @return array - Array with a mapping of [placholder_key] => actual value
	 * @throws ilException
	 */
	public function getPlaceholderValues(int $userId, int $objId) : array
	{
		/** @var ilObjUser $user */
		$user = ilObjectFactory::getInstanceByObjId($userId);
		if (!$user instanceof ilObjUser) {
			throw new ilException('The entered id: ' . $userId . ' is not an user object');
		}

		$placeholder = $this->placeholder;

		$oldDatePresentationValue = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);

		$placeholder['USER_LOGIN']         = $user->getLogin();
		$placeholder['USER_FULLNAME']      = $user->getFullname();
		$placeholder['USER_FIRSTNAME']     = $user->getFirstname();
		$placeholder['USER_LASTNAME']      = $user->getLastname();
		$placeholder['USER_TITLE']         = $user->getTitle();
		$placeholder['USER_SALUTATION']    = $user->getGender();
		$placeholder['USER_BIRTHDAY']      = $user->getBirthday();
		$placeholder['USER_INSTITUTION']   = $user->getInstitution();
		$placeholder['USER_DEPARTMENT']    = $user->getDepartment();
		$placeholder['USER_STREET']        = $user->getStreet();
		$placeholder['USER_CITY']          = $user->getCity();
		$placeholder['USER_ZIPCODE']       = $user->getZipcode();
		$placeholder['USER_COUNTRY']       = $user->getCountry();
		$placeholder['USER_MATRICULATION'] = $user->getMatriculation();
		$placeholder['DATE']               = ilDatePresentation::formatDate(new ilDate(time(), IsL_CAL_UNIX));
		$placeholder['DATETIME']           = ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX));

		ilDatePresentation::setUseRelativeDates($oldDatePresentationValue);

		return $placeholder;
	}

	/**
	 * This method is different then the 'getPlaceholderValues' method, this
	 * method is used to create a placeholder value array containing dummy values
	 * that is used to create a preview certificate.
	 *
	 * Due the fact that this is a class to create default values
	 * the placeholder values will be identical to the description
	 *
	 * @return mixed
	 */
	public function getPlaceholderValuesForPreview()
	{
		return $this->placeholder;
	}
}
