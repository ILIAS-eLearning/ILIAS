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
	 * Handle logout event
	 */
	public function handleLogout()
	{
		$auth_token = ilSession::get('oidc_auth_token');

		$this->getLogger()->info('Using token: ' . $auth_token);

		if(strlen($auth_token))
		{
			ilSession::set('oidc_auth_token','');
			$oidc = $this->initClient();
			$oidc->signOut(
				$auth_token,
				ILIAS_HTTP_PATH.'/logout.php'
			);
		}
		else
		{
			$this->getLogger()->info('No valid token found');
		}
	}

	/**
	 * Do authentication
	 * @param \ilAuthStatus $status Authentication status
	 * @return bool
	 */
	public function doAuthentication(\ilAuthStatus $status)
	{
		try {

			$oidc = $this->initClient();
			$oidc->setRedirectURL(ILIAS_HTTP_PATH.'/openidconnect.php');

			$this->getLogger()->debug(
				'Redirect url is: '.
				$oidc->getRedirectURL()
			);

			$oidc->setResponseTypes(
				[
					'id_token'
				]
			);
			$oidc->addScope(
				[
					'openid'
				]
			);
			$oidc->addAuthParam(
				[
					'response_mode' => 'form_post',
					'prompt' => 'consent'
				]
			);
			$oidc->setAllowImplicitFlow(true);

			$oidc->authenticate();
			// user is authenticated, otherwise redirected to authorization endpoint or exception
			$this->getLogger()->dump($_REQUEST);

			$claims = $oidc->getVerifiedClaims(null);

			$this->getLogger()->info('User is authenticated');
			$this->getLogger()->dump($claims);


			$token = $oidc->requestClientCredentialsToken();


			ilSession::set('oidc_auth_token', $token->access_token);

			$status->setAuthenticatedUserId(6);
			$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
			return true;
		}
		catch(Exception $e) {
			$this->getLogger()->warning($e->getMessage());
			$this->getLogger()->warning($e->getCode());
			$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
			$status->setTranslatedReason($e->getMessage());
			return false;
		}
	}

	/**
	 * @return OpenIDConnectClient
	 */
	private function initClient() : OpenIDConnectClient
	{
		$oidc = new OpenIDConnectClient(
			$this->settings->getProvider(),
			$this->settings->getClientId(),
			$this->settings->getSecret()
		);
		return $oidc;
	}
}