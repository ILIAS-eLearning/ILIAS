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

namespace ILIAS\WebDAV\Auth;

use Sabre\DAV\Auth\Plugin;
use Sabre\DAV\Auth\Backend\BasicCallBack;
use ILIAS\FileDelivery\Token\Signer\Key\Secret\SecretKeyRotation;
use ILIAS\Filesystem\Filesystem;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class BasicAuthentication extends Plugin
{
    public function __construct(
        SecretKeyRotation $secret_key_rotation,
        \ilLogger $logger,
        \ilObjUser $user,
        \ilAuthSession $auth_session,
        Filesystem $filesystem
    ) {
        $webdav_auth = new ILIASAuthenticationCallback(
            $user,
            $auth_session,
            $logger,
            $filesystem,
            $secret_key_rotation
        );
        $auth_callback_class = new BasicCallBack(
            fn(string $username, string $password): bool => $webdav_auth->authenticate($username, $password)
        );

        parent::__construct(
            $auth_callback_class
        );
    }

}
