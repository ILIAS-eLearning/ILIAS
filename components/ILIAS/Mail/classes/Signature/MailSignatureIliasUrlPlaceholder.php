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

namespace ILIAS\Mail\Signature;

use ilUtil;

class MailSignatureIliasUrlPlaceholder extends AbstractPlaceholderHandler
{
    public function getId(): string
    {
        return 'ILIAS_URL';
    }

    public function addPlaceholder(array $placeholder): array
    {
        $clientUrl = ilUtil::_getHttpPath();
        $clientdirs = glob(ILIAS_WEB_DIR . '/*', GLOB_ONLYDIR);
        if (is_array($clientdirs) && count($clientdirs) > 1) {
            $clientUrl .= '/login.php?client_id=' . CLIENT_ID;
        }
        $placeholder[$this->getId()] = $clientUrl;
        return $placeholder;
    }
}
