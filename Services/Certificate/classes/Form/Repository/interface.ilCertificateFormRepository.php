<?php


interface ilCertificateFormRepository
{
	/**
	 * @param ilCertificateGUI $certificateGUI
	 * @param ilCertificate $certificateObject
	 * @return ilPropertyFormGUI
	 */
	public function createForm(ilCertificateGUI $certificateGUI, ilCertificate $certificateObject);

	/**
	 * @param array $formFields
	 * @return mixed
	 */
	public function save(array $formFields);

	public function fetchFormFieldData($content);
}
