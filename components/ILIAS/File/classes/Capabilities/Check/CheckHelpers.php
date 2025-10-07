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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */

namespace ILIAS\File\Capabilities\Check;

use ILIAS\HTTP\Services;
use ILIAS\components\WOPI\Discovery\ActionRepository;
use ILIAS\Data\URI;
use ILIAS\StaticURL\Builder\URIBuilder;

class CheckHelpers
{
    public function __construct(
        public \ilAccessHandler $access,
        public \ilCtrlInterface $ctrl,
        public ActionRepository $action_repository,
        public Services $http,
        public URIBuilder $static_url,
        public \ilWorkspaceAccessHandler $workspace_access_handler
    ) {
    }

    public function fromTarget(string $target): URI
    {
        return (new URI(rtrim(ILIAS_HTTP_PATH, '/') . '/' . $target));
    }

}
