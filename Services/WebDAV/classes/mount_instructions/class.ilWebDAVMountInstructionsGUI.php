<?php

/**
 * Class ilWebDAVMountInstructionsGUI
 *
 * This class delivers or prints a representation of the mount instructions
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVMountInstructionsGUI {
    
    /**
     * 
     * @var $mount_instruction ilWebDAVMountInstructions
     */
    protected $protocol_prefixes;
    protected $base_url;
    protected $ref_id;
    protected $mount_instruction;
    
    public function __construct() 
    {
        $this->mount_instruction = new ilWebDAVMountInstructions();
    }

    public function getMountInstructionsGetRequestParameter()
    {
        return 'mount-instructions';
    }

    protected function getModalBaseURI()
    {
        global $DIC;
        $uri = $DIC->http()->request()->getUri();

        $base_uri = $uri->getScheme() . '://' . $uri->getHost() . '/trunk/webdav.php/' . CLIENT_ID;
        return $base_uri;
        // TODO: Replace mock with real URI
        //return "https://" . $_SERVER["HTTP_HOST"] . "/webdav.php/" . CLIENT_ID;
        return "http://localhost/trunk/webdav.php/" . CLIENT_ID;
    }

    protected function getModalURIByRef(int $ref_id)
    {
        return $this->getModalBaseURI() . "/$ref_id?".$this->getMountInstructionsGetRequestParameter();
    }

    protected function getModalURIByLanguage(string $lng)
    {
        if(strlen($lng) == 2)
        {
            return $this->getModalBaseURI() . "/$lng?".$this->getMountInstructionsGetRequestParameter();
        }
        else
        {
            throw new InvalidArgumentException("Language id should be exactly 2 characters");
        }
    }

    public function getAsyncMountInstructionModalByLanguage(string $lng) : \ILIAS\UI\Component\Modal\Modal
    {
        global $DIC;
        $modal = new ilWebDAVMountInstructionsModalGUI($this->mount_instruction, $DIC->ui()->factory(), $DIC->ui()->renderer(), $DIC->language());
        return $modal->getAsAsyncModal($this->getModalURIByLanguage($lng));
    }

    public function renderMontInstructionModal()
    {
        global $DIC;
        $uri = $DIC->http()->request()->getUri();
        $splitted_uri = explode('/', $uri->getPath());

        // Remove paht elements before webdav script
        while($value = array_shift($splitted_uri) != 'webdav.php');

        $client_id = array_shift($splitted_uri);
        $path_value = array_shift($splitted_uri);

        $modal = new ilWebDAVMountInstructionsModalGUI($this->mount_instruction, $DIC->ui()->factory(), $DIC->ui()->renderer(), $DIC->language());
        $modal->printMountInstructionModalAndExit();

        /**
        if(str_len($path_value) == 2)
        {
            $this->renderModalByLanguage($path_value);
        }
        else if (substr($path_value, 0, 3) == 'ref_')
        {
        }
         * */
    }
}