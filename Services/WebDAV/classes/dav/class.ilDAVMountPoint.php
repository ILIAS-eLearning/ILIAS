<?php declare(strict_types = 1);

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
 
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\ICollection;

/**
 * This class represents the absolut Root-Node on a WebDAV request. If for example following URL is called:
 * https://ilias.de/webdav.php/client/ref_1234/folder
 * this class represents the very first '/' slash after "webdav.php".
 *
 * This kind of procedure is needed for the way how sabreDAV works
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 */
class ilDAVMountPoint implements ICollection
{
    use ilWebDAVReadOnlyNodeWriteFunctionsTrait;
    
    protected string $client_id;
    protected int $user_id;
    protected ilWebDAVObjFactory $web_dav_object_factory;
    protected ilWebDAVRepositoryHelper $repo_helper;

    public function __construct(
        string $client_id,
        ilWebDAVObjFactory $web_dav_object_factory,
        ilWebDAVRepositoryHelper $repo_helper,
        ilObjUser $user
    ) {
        $this->client_id = $client_id;
        $this->web_dav_object_factory = $web_dav_object_factory;
        $this->repo_helper = $repo_helper;
        $this->user_id = $user->getId();
    }

    public function getName() : string
    {
        return 'MountPoint';
    }

    /**
     * @return \Sabre\DAV\INode[]
     */
    public function getChildren() : array
    {
        if ($this->user_id === ANONYMOUS_USER_ID) {
            throw new Forbidden('Only for logged in users');
        }
        return array($this->web_dav_object_factory->getClientNode($this->client_id));
    }

    public function getChild($name) : ilDAVClientNode
    {
        return $this->web_dav_object_factory->getClientNode($name);
    }

    public function childExists($name) : bool
    {
        if ($name === $this->client_id) {
            return true;
        }
        return false;
    }

    public function getLastModified() : int
    {
        return strtotime('2000-01-01');
    }
}
