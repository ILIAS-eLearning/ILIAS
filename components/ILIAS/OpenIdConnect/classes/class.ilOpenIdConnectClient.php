<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;

class ilOpenIdConnectClient extends OpenIDConnectClient
{
    /**
     * @throws OpenIDConnectClientException
     */
    public function signOutWithState(string $id_token, string $redirect, string $state): void
    {
        $sign_out_endpoint = $this->getProviderConfigValue('end_session_endpoint');

        $signout_params = [
            'id_token_hint' => $id_token,
            'post_logout_redirect_uri' => $redirect,
            'state' => $state,
        ];

        $sign_out_endpoint .= (!str_contains($sign_out_endpoint, '?') ? '?' : '&')
            . http_build_query($signout_params, '', '&', $this->encType);

        $this->redirect($sign_out_endpoint);
    }
}
