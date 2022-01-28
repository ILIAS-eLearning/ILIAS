<?php declare(strict_types = 1);

use Psr\Http\Message\UriInterface;
use Sabre\DAV\Browser\Plugin;

/**
 * The only purpose for this class is to redirect a browsers WebDAV-Request to the mount-instructions page
 */
class ilWebDAVSabreBrowserPlugin extends Plugin
{
    protected ilCtrl $ilCtrl;
    private string $mount_instruction_path;
    
    public function __construct(ilCtrl $ilCtrl, UriInterface $uri)
    {
        $this->mount_instruction_path = $uri->getScheme() . '://';
        $this->mount_instruction_path .= $uri->getHost();
        $this->mount_instruction_path .= $uri->getPath();
        $this->mount_instruction_path .= "?mount-instructions";
        $this->ctrl = $ilCtrl;
        parent::__construct(false);
    }

    /**
     * @inheritdoc
     */
    public function generateDirectoryIndex($path)
    {
        $this->ctrl->redirectToURL($this->mount_instruction_path);
    }
}
