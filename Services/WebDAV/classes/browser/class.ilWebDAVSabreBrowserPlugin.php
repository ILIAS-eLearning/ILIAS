<?php

/**
 * Class ilWebDAVSabreBrowserPlugin
 *
 * The only purpose for this class is to redirect a browsers WebDAV-Request to the mount-instructions page
 */
class ilWebDAVSabreBrowserPlugin extends Sabre\DAV\Browser\Plugin
{
    /** @var ilCtrl */
    protected $ilCtrl;

    /**
     * Override the original contructor. ilCtrl is needed to redirect to the mount-instructions page.
     *
     * @param ilCtrl $ilCtrl
     */
    public function __construct(ilCtrl $ilCtrl)
    {
        $this->ctrl = $ilCtrl;
        parent::__construct(false);
    }

    /**
     * Override the original generateDirectoryIndex method. Instead of creating the HTML-Code for a WebDAV site, redirect
     * to the mount-instrunctions page. The ILIAS WebDAV Service is made to communicate with file managers. Browsers
     * shall use the regular way to interact with ILIAS
     *
     * @param string $path
     */
    public function generateDirectoryIndex($path)
    {
        $this->ctrl->redirectToURL("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]?mount-instructions");
    }
}
