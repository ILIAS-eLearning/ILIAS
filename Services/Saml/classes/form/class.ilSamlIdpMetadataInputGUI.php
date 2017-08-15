<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlIdpMetadataInputGUI
 */
class ilSamlIdpMetadataInputGUI extends \ilTextAreaInputGUI
{
	/**
	 * @inheritdoc
	 */
	public function checkInput()
	{
		$valid = parent::checkInput();
		if(!$valid)
		{
			return false;
		}

		$valid = true;

		// @todo: Refactor
		$httpParam = $_POST[$this->getPostVar()];
		\libxml_use_internal_errors(true);

		try
		{
			$xml = new \SimpleXMLElement($httpParam);

			$xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
			$xml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

			$idps     = $xml->xpath('//md:EntityDescriptor[//md:IDPSSODescriptor]');
			$entityid = null;
			if($idps && isset($idps[0]))
			{
				$entityid = (string)$idps[0]->attributes('', true)->entityID[0];
			}

			$errors = [];
			foreach(\libxml_get_errors() as $error)
			{
				$errors[] = $error->line . ': ' . $error->message;
			}

			if(!$entityid || count($errors) > 0)
			{
				if(count($errors) > 0)
				{
					$this->setAlert(implode('<br />', $errors));
				}
				else
				{
					$this->setAlert($GLOBALS['DIC']->language()->txt('auth_saml_add_idp_md_error'));
				}
				$valid = false;
			}
		}
		catch(\Exception $e)
		{
			$this->setAlert($GLOBALS['DIC']->language()->txt('auth_saml_add_idp_md_error'));
			$valid = false;
		}

		\libxml_clear_errors();

		return $valid;
	}
}