<?php

declare(strict_types=1);

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

use Psr\Http\Message\RequestInterface;

/**
 * @author Stephan Winiker <stephan.winiker@hslu.ch>
 * $Id$
 */
class ilWebDAVMountInstructionsFactory
{
    private ilWebDAVMountInstructionsRepositoryImpl $repo;
    private RequestInterface $request;
    private ilObjUser $user;

    public function __construct(
        ilWebDAVMountInstructionsRepositoryImpl $a_repo,
        RequestInterface $a_request,
        ilObjUser $a_user
    ) {
        $this->repo = $a_repo;
        $this->request = $a_request;
        $this->user = $a_user;
    }

    public function getMountInstructionsObject(): ilWebDAVBaseMountInstructions
    {
        $uri_builder = new ilWebDAVUriBuilder($this->request);
        $uri = $this->request->getUri()->getPath();

        $splitted_uri = explode('/', $uri);

        // Remove path elements before and until webdav script
        while (array_shift($splitted_uri) != 'webdav.php' && count($splitted_uri) > 0);

        $path_value = $splitted_uri[1];

        if (strlen($path_value) == 2) {
            return new ilWebDAVObjectlessMountInstructions(
                $this->repo,
                $uri_builder,
                new ilSetting(),
                $path_value
            );
        }

        if (substr($path_value, 0, 4) == 'ref_') {
            return new ilWebDAVObjectMountInstructions(
                $this->repo,
                $uri_builder,
                new ilSetting(),
                $this->user->getLanguage(),
                (int) substr($path_value, 4)
            );
        }

        throw new InvalidArgumentException("Invalid path given");
    }
}
