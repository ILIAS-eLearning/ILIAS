<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Collection of basic placeholder values that can be used
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilDefaultPlaceholderValues implements ilCertificatePlaceholderValues
{
	/**
	 * @var array
	 */
	private $placeholder;

	/**
	 * @var ilCertificateObjectHelper
	 */
	private $objectHelper;

	/**
	 * @var ilCertificateDateHelper
	 */
	private $dateHelper;

	/**
	 * @var integer
	 */
	private $dateFormat;

	/**
	 * @var ilLanguage|null
	 */
	private $language;

	/**
	 * @var ilCertificateUtilHelper|null
	 */
	private $utilHelper;

	/**
	 * @var ilUserDefinedFieldsPlaceholderValues|null
	 */
	private $userDefinedFieldsPlaceholderValues;

	/**
	 * @var int
	 */
	private $birthdayDateFormat;

	/**
	 * @param ilCertificateObjectHelper $objectHelper
	 * @param ilCertificateDateHelper $dateHelper
	 * @param int $dateFormat
	 * @param ilLanguage|null $language
	 * @param ilCertificateUtilHelper|null $utilHelper
	 * @param ilUserDefinedFieldsPlaceholderValues|null $userDefinedFieldsPlaceholderValues
	 */
	public function __construct(
		ilCertificateObjectHelper $objectHelper = null,
		ilCertificateDateHelper $dateHelper = null,
		int $dateFormat = null,
		ilLanguage $language = null,
		ilCertificateUtilHelper $utilHelper = null,
		ilUserDefinedFieldsPlaceholderValues $userDefinedFieldsPlaceholderValues = null,
		$birthdayDateFormat = IL_CAL_DATE
	) {
		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $dateHelper) {
			$dateHelper = new ilCertificateDateHelper();
		}
		$this->dateHelper = $dateHelper;

		if (null === $dateFormat) {
			$dateFormat = IL_CAL_UNIX;
		}
		$this->dateFormat = $dateFormat;

		if (null === $language) {
			global $DIC;
			$language = $DIC->language();
		}
		$this->language = $language;

		if (null === $utilHelper) {
			$utilHelper = new ilCertificateUtilHelper();
		}
		$this->utilHelper = $utilHelper;

		if (null === $userDefinedFieldsPlaceholderValues) {
			$userDefinedFieldsPlaceholderValues = new ilUserDefinedFieldsPlaceholderValues();
		}
		$this->userDefinedFieldsPlaceholderValues = $userDefinedFieldsPlaceholderValues;

		$this->birthdayDateFormat = $birthdayDateFormat;

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
		$user = $this->objectHelper->getInstanceByObjId($userId);
		if (!$user instanceof ilObjUser) {
			throw new ilException('The entered id: ' . $userId . ' is not an user object');
		}

		$placeholder = $this->placeholder;

		$placeholder['USER_LOGIN']         = $this->utilHelper->prepareFormOutput((trim($user->getLogin())));
		$placeholder['USER_FULLNAME']      = $this->utilHelper->prepareFormOutput((trim($user->getFullname())));
		$placeholder['USER_FIRSTNAME']     = $this->utilHelper->prepareFormOutput((trim($user->getFirstname())));
		$placeholder['USER_LASTNAME']      = $this->utilHelper->prepareFormOutput((trim($user->getLastname())));
		$placeholder['USER_TITLE']         = $this->utilHelper->prepareFormOutput((trim($user->getUTitle())));
		$placeholder['USER_SALUTATION']    = $this->utilHelper->prepareFormOutput($this->language->txt("salutation_" . trim($user->getGender())));

		$birthday = '';
		$dateObject = $user->getBirthday();
		if (null !== $dateObject) {
			$birthday   = $this->dateHelper->formatDate($dateObject, $this->birthdayDateFormat);
		}

		$placeholder['USER_BIRTHDAY']      = $this->utilHelper->prepareFormOutput((trim($birthday)));
		$placeholder['USER_INSTITUTION']   = $this->utilHelper->prepareFormOutput((trim($user->getInstitution())));
		$placeholder['USER_DEPARTMENT']    = $this->utilHelper->prepareFormOutput((trim($user->getDepartment())));
		$placeholder['USER_STREET']        = $this->utilHelper->prepareFormOutput((trim($user->getStreet())));
		$placeholder['USER_CITY']          = $this->utilHelper->prepareFormOutput((trim($user->getCity())));
		$placeholder['USER_ZIPCODE']       = $this->utilHelper->prepareFormOutput((trim($user->getZipcode())));
		$placeholder['USER_COUNTRY']       = $this->utilHelper->prepareFormOutput((trim($user->getCountry())));
		$placeholder['USER_MATRICULATION'] = $this->utilHelper->prepareFormOutput((trim($user->getMatriculation())));
		$placeholder['DATE']               = $this->utilHelper->prepareFormOutput((trim($this->dateHelper->formatDate(time(), $this->dateFormat))));
		$placeholder['DATETIME']           = $this->utilHelper->prepareFormOutput((trim($this->dateHelper->formatDatetime(time(), $this->dateFormat))));

		$placeholder = array_merge($placeholder, $this->userDefinedFieldsPlaceholderValues->getPlaceholderValues($userId, $objId));

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
	 * @param int $userId
	 * @param int $objId
	 * @return mixed
	 * @throws ilDateTimeException
	 * @throws ilException
	 * @throws ilInvalidCertificateException
	 */
	public function getPlaceholderValuesForPreview(int $userId, int $objId) : array
	{
		$previewPlacholderValues = array(
			"USER_LOGIN" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_login")),
			"USER_FULLNAME" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_fullname")),
			"USER_FIRSTNAME" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_firstname")),
			"USER_LASTNAME" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_lastname")),
			"USER_TITLE" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_title")),
			"USER_SALUTATION" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_salutation")),
			"USER_BIRTHDAY" => $this->utilHelper->prepareFormOutput((trim($this->dateHelper->formatDate(time(), $this->dateFormat)))),
			"USER_INSTITUTION" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_institution")),
			"USER_DEPARTMENT" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_department")),
			"USER_STREET" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_street")),
			"USER_CITY" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_city")),
			"USER_ZIPCODE" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_zipcode")),
			"USER_COUNTRY" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_country")),
			"USER_MATRICULATION" => $this->utilHelper->prepareFormOutput($this->language->txt("certificate_var_user_matriculation")),
			'DATE' => $this->utilHelper->prepareFormOutput((trim($this->dateHelper->formatDate(time(), $this->dateFormat)))),
			'DATETIME' => $this->utilHelper->prepareFormOutput((trim($this->dateHelper->formatDatetime(time(), $this->dateFormat)))),
			'DATE_COMPLETED' => $this->utilHelper->prepareFormOutput((trim($this->dateHelper->formatDate(time(), $this->dateFormat)))),
			'DATETIME_COMPLETED' => $this->utilHelper->prepareFormOutput((trim($this->dateHelper->formatDatetime(time(), $this->dateFormat))))
		);

		return array_merge($previewPlacholderValues, $this->userDefinedFieldsPlaceholderValues->getPlaceholderValuesForPreview($userId, $objId));
	}
}
