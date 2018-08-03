<?php


class ilCertificateDefaultPlaceholderValues implements ilCertificatePlaceholderValues
{
	/**
	 * @var array
	 */
	private $placeholder;

	/**
	 * @param ilLanguage $language
	 */
	public function __construct(ilLanguage $language)
	{
		$this->placeholder = array(
			"USER_LOGIN"         => $language->txt("certificate_ph_login"),
			"USER_FULLNAME"      => $language->txt("certificate_ph_fullname"),
			"USER_FIRSTNAME"     => $language->txt("certificate_ph_firstname"),
			"USER_LASTNAME"      => $language->txt("certificate_ph_lastname"),
			"USER_TITLE"         => $language->txt("certificate_ph_title"),
			"USER_SALUTATION"    => $language->txt("certificate_ph_salutation"),
			"USER_BIRTHDAY"      => $language->txt("certificate_ph_birthday"),
			"USER_INSTITUTION"   => $language->txt("certificate_ph_institution"),
			"USER_DEPARTMENT"    => $language->txt("certificate_ph_department"),
			"USER_STREET"        => $language->txt("certificate_ph_street"),
			"USER_CITY"          => $language->txt("certificate_ph_city"),
			"USER_ZIPCODE"       => $language->txt("certificate_ph_zipcode"),
			"USER_COUNTRY"       => $language->txt("certificate_ph_country"),
			"USER_MATRICULATION" => $language->txt("certificate_ph_matriculation")
		);
	}

	public function getPlaceholderValues($userId, $objId)
	{
		/** @var ilObjUser $user */
		$user = ilObjectFactory::getInstanceByObjId($userId);
		if (!$user instanceof ilObjUser) {
			throw new ilException('The entered id: ' . $userId . ' is not an user object');
		}

		$placeholder = $this->placeholder;

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

		return $placeholder;
	}

	public function getPlaceholderDescription()
	{
		return $this->placeholder;
	}
}
