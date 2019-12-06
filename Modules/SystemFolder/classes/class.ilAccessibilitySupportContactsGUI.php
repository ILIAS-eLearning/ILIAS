<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilitySupportContactsGUI
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilAccessibilitySupportContactsGUI
{
	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var \ILIAS\DI\HTTPServices
	 */
	protected $http;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$ctrl = $DIC->ctrl();
		$tpl = $DIC["tpl"];
		$lng = $DIC->language();
		$http = $DIC->http();

		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->http = $http;
	}


	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$cmd = $this->ctrl->getCmd("sendIssueMail");
		if (in_array($cmd, array("sendIssueMail")))
		{
			$this->$cmd();
		}
	}


	function sendIssueMail() : void
	{
		$back_url = $this->http->request()->getServerParams()['HTTP_REFERER'];
		$this->ctrl->redirectToURL(
			ilMailFormCall::getRedirectTarget(
				$back_url,
				'',
				[],
				[
					'type' => 'new',
					'rcp_to' => $this->getContactLogins(),
					'sig' => $this->getAccessibilityIssueMailMessage($back_url)
				]
			)
		);
	}

	/**
	 * @return string
	 */
	private function getAccessibilityIssueMailMessage(string $back_url) : string
	{
		$sig = chr(13).chr(10).chr(13).chr(10).chr(13).chr(10);
		$sig .= $this->lng->txt('report_accessibility_link');
		$sig .= chr(13).chr(10);
		$sig .= $back_url;
		$sig = rawurlencode(base64_encode($sig));

		return $sig;
	}

	/**
	 * Get accessibility support contacts as comma separated string
	 *
	 * @return string
	 */
	private function getContactLogins() : string
	{
		$logins = [];

		foreach (ilAccessibilitySupportContacts::getValidSupportContactIds() as $contact_id)
		{
			$logins[] = ilObjUser::_lookupLogin($contact_id);
		}

		return implode(',', $logins);
	}

	/**
	 * Get footer link
	 *
	 * @return string footer link
	 */
	static function getFooterLink()
	{
		global $DIC;

		$ctrl = $DIC->ctrl();
		$user = $DIC->user();
		$http = $DIC->http();


		$users = ilAccessibilitySupportContacts::getValidSupportContactIds();
		if (count($users) > 0)
		{
			if(!$user->getId() || $user->getId() == ANONYMOUS_USER_ID)
			{
				$mails = ilUtil::prepareFormOutput(ilAccessibilitySupportContacts::getMailsToAddress());
				$request_scheme =
					isset($http->request()->getServerParams()['HTTPS'])
					&& $http->request()->getServerParams()['HTTPS'] !== 'off'
						? 'https' : 'http';
				$url = $request_scheme . '://'
					. $http->request()->getServerParams()['HTTP_HOST']
					. $http->request()->getServerParams()['REQUEST_URI'];
				return "mailto:".$mails."?body=%0D%0A%0D%0AGemeldeter%20Link:%0D%0A".rawurlencode($url);
			}
			else
			{
				return $ctrl->getLinkTargetByClass("ilaccessibilitysupportcontactsgui", "");
			}
		}
		return "";
	}

	/**
	 * Get footer text
	 *
	 * @return string footer text
	 */
	static function getFooterText()
	{
		global $DIC;

		$lng = $DIC->language();
		return $lng->txt("report_accessibility_issue");
	}
}

?>