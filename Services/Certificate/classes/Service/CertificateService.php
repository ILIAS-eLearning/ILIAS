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

namespace ILIAS\Certificate\Service;

use ILIAS\DI\Container;
use ILIAS\Certificate\API\UserCertificateAPI;

class CertificateService
{
    public const USER_API = 'certificate.user.api';

    public function __construct(private Container $dic)
    {
        if (!isset($this->dic[self::USER_API])) {
            $this->dic[self::USER_API] = static function (Container $c): UserCertificateAPI {
                return new UserCertificateAPI();
            };
        }
    }

    public function userCertificates(): UserCertificateAPI
    {
        return $this->dic[self::USER_API];
    }
}
