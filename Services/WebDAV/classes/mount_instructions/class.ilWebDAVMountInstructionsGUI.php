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
     * @var $mount_instruction ilWebDAVObjectMountInstructions
     */
    protected $protocol_prefixes;
    protected $base_url;
    protected $ref_id;
    protected $mount_instruction;
    
    public function __construct() 
    {
        global $DIC;

        $this->uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
        $this->user_language = $DIC->user()->getLanguage();
        $this->mount_instruction = $this->buildMountInstructionsFromUri(
            $DIC->http()->request()->getUri()->getPath(),
            new ilWebDAVMountInstructionsRepositoryImpl($DIC->database())
        );

    }

    public function buildMountInstructionsFromUri($a_uri, ilWebDAVMountInstructionsRepository $repo)
    {
        $splitted_uri = explode('/', $a_uri);

        // Remove path elements before and until webdav script
        while($value = array_shift($splitted_uri) != 'webdav.php');

        $client_id = array_shift($splitted_uri);
        $path_value = array_shift($splitted_uri);

        if(strlen($path_value) == 2)
        {
            return new ilWebDAVObjectlessMountInstructions($repo,
                $this->uri_builder,
                new ilSetting('file_access'),
                $path_value);
        }
        else if (substr($path_value, 0, 4) == 'ref_')
        {
            return new ilWebDAVObjectMountInstructions($repo,
                $this->uri_builder,
                new ilSetting('file_access'),
                $this->user_language,
                (int)substr($path_value, 4));
        }
        else{
            throw new InvalidArgumentException("Invalid path given");
        }
    }

    public function renderMountInstructionModal()
    {
        global $DIC;

        $modal = new ilWebDAVMountInstructionsModalGUI($this->mount_instruction, $DIC->ui()->factory(), $DIC->ui()->renderer(), $DIC->language());
        $modal->printMountInstructionModalAndExit();
    }
}