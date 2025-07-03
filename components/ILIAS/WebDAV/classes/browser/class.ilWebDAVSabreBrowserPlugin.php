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

use Psr\Http\Message\UriInterface;
use Sabre\DAV\Browser\Plugin;

/**
 * The only purpose for this class is to redirect a browsers WebDAV-Request to the mount-instructions page
 */
class ilWebDAVSabreBrowserPlugin extends Plugin
{
    private string $mount_instruction_path;

    public function __construct(protected ilCtrlInterface $ctrl, UriInterface $uri)
    {
        $this->mount_instruction_path = $uri->getScheme() . '://';
        $this->mount_instruction_path .= $uri->getHost();
        $this->mount_instruction_path .= $uri->getPath();
        $this->mount_instruction_path .= "?mount-instructions";
        parent::__construct(false);
    }

    /**
     * @inheritdoc
     */
    #[\Override]
    public function generateDirectoryIndex($path)
    {
        //        return parent::generateDirectoryIndex($path);
        $this->ctrl->redirectToURL($this->mount_instruction_path);
        return '';
    }
}
