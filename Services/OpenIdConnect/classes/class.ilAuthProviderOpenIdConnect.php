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

			$oidc = new OpenIDConnectClient(
				$this->settings->getProvider(),
				$this->settings->getClientId(),
				$this->settings->getSecret()
			);
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
					'response_mode' => 'form_post'
				]
			);
			$oidc->setAllowImplicitFlow(true);

			$oidc->authenticate();
			// user is authenticated, otherwise redirected to authorization endpoint or exception
			$this->getLogger()->dump($_REQUEST);

			$claims = $oidc->getVerifiedClaims(null);

			$this->getLogger()->info('User is authenticated');
			$this->getLogger()->dump($claims);

			$status->setAuthenticatedUserId(6);
			$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);

			$token = $oidc->requestClientCredentialsToken();
			$this->getLogger()->dump($token);

			//$oidc->signOut($token->access_token, ILIAS_HTTP_PATH.'/logout.php');


			return true;
		}
		catch(Exception $e) {
			$this->getLogger()->error($e->getMessage());
			$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
			$status->setTranslatedReason($e->getMessage());
			return false;
		}

	}
}