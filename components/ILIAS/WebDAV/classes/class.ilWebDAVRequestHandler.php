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

use Sabre\DAV\Server;

/**
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVRequestHandler
{
    public function __construct(private ilWebDAVDIC $webdav_dic)
    {
    }

    public function handleRequest(array $post_array): void
    {
        $post_object = $_POST;
        $_POST = $post_array;
        $server = new Server($this->getRootDir());
        $_POST = $post_object;
        $this->setPlugins($server);
        $server->start();
    }

    protected function setPlugins(Server $server): void
    {
        $auth_plugin = $this->webdav_dic->authplugin();
        $server->addPlugin($auth_plugin);

        $lock_plugin = $this->webdav_dic->locksplugin();
        $server->addPlugin($lock_plugin);

        //        $browser_plugin = $this->webdav_dic->browserplugin();
        //        $server->addPlugin($browser_plugin);
    }

    protected function getRootDir(): ilDAVMountPoint
    {
        return $this->webdav_dic->dav_factory()->getMountPoint();
    }
}
