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

use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\Platform;

/**
 * A platform that launches ILIAS, stored in lti2_consumer. The library owns the columns of the LTI
 * protocol, this class adds what ILIAS needs on top of them.
 *
 * ILIAS has no data connector of the library, so the platform holds no more than the code building it
 * puts in: checking the OAuth1 signature of an outcome, for instance, only needs key, secret and record.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIPlatform extends Platform
{
    public function __construct(?DataConnector $data_connector = null)
    {
        parent::__construct($data_connector ?? DataConnector::getDataConnector());
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }
}
