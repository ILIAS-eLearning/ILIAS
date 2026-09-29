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

namespace ILIAS\FileDelivery;

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\FileDelivery;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class EntryPoint implements \ILIAS\Component\EntryPoint
{
    public function __construct(
        private FileDeliveryServices $file_delivery,
        private GlobalHttpState $http_state
    ) {
    }

    public function getName(): string
    {
        return FileDelivery::class;
    }

    public function enter(): int
    {
        $requested_url = (string) $this->http_state->request()->getUri();
        $access_token = substr(
            $requested_url,
            strpos($requested_url, Services::DELIVERY_ENDPOINT) + strlen(Services::DELIVERY_ENDPOINT)
        );

        $this->file_delivery->delivery()->deliverFromToken($access_token);

        return 0;
    }
}
