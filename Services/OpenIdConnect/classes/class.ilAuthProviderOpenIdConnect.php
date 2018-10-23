<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use Jumbojett\OpenIDConnectClient;

/**
 * Class ilAuthProviderOpenIdConnect
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 *
 */
class ilAuthProviderOpenIdConnect extends ilAuthProvider implements ilAuthProviderInterface
{
	/**
	 * @var ilOpenIdConnectSettings|null
	 */
	private $settings = null;


	/**
	 * ilAuthProviderOpenIdConnect constructor.
	 * @param ilAuthCredentials $credentials
	 */
	public function __construct(ilAuthCredentials $credentials)
	{
		parent::__construct($credentials);
		$this->settings = ilOpenIdConnectSettings::getInstance();
	}

	/**
	 * Do authentication
	 * @param \ilAuthStatus $status Authentication status
	 * @return bool
	 */
	public function doAuthentication(\ilAuthStatus $status)
	{
		try {

			$this->getLogger()->info('using: ' . $this->settings->getProvider());
			$this->getLogger()->info('using: ' . $this->settings->getClientId());
			$this->getLogger()->info('using: ' . $this->settings->getSecret());

			$oidc = new OpenIDConnectClient(
				$this->settings->getProvider(),
				$this->settings->getClientId(),
				$this->settings->getSecret()
			);
			$oidc->setResponseTypes(
				[
					'id_token'
				]
			);
			$oidc->addScope(['openid']);
			$oidc->setAllowImplicitFlow(true);
			$oidc->addAuthParam(
				[
					'response_mode' => 'form_post'
				]
			);
			$oidc->authenticate();
			$sub = $oidc->getVerifiedClaims('sub');
			$this->getLogger()->dump($sub, ilLogLevel::DEBUG);


		}
		catch(Exception $e) {
			$this->getLogger()->error($e->getMessage());
			$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
			$status->setTranslatedReason($e->getMessage());
			return false;
		}

	}
}