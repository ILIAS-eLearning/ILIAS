<?php

/**
 * Class ilWebDAVMountInstructions
 *
 * This class creates the page and links for the WebDAV mount instruction page
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVObjectMountInstructions extends ilWebDAVBaseMountInstructions
{
    protected $user_agent;
    protected $request_uri;
    protected $http_host;
    protected $script_name;
    protected $client_id;
    protected $path_to_template;
    
    protected $clientOSFlavor;
    protected $clientOS;
    
    protected $settings;
    
    protected $ref_id;
    protected $obj_id;
    protected $obj_title;

    protected $document_repository;
    protected $uri_provider;
    
    public function __construct(
        ilWebDAVMountInstructionsRepository $a_repo,
        ilWebDAVUriBuilder $a_uri_builder,
        ilSetting $a_settings,
        String $language,
        int $a_ref_id
    ) {
        $this->ref_id = $a_ref_id;
        $this->language = $language;

        // TODO: Change this to be more unit testable!
        $this->obj_id = ilObject::_lookupObjectId($this->ref_id);
        $this->obj_title = ilObject::_lookupTitle($this->obj_id);

        parent::__construct($a_repo, $a_uri_builder, $a_settings, $ilLang);
    }

    protected function fillPlaceholdersForMountInstructions(array $mount_instructions) : array
    {
        foreach ($mount_instructions as $title => $mount_instruction) {
            $mount_instruction = str_replace("[WEBFOLDER_ID]", $this->ref_id, $mount_instruction);
            $mount_instruction = str_replace("[WEBFOLDER_TITLE]", $this->obj_title, $mount_instruction);
            $mount_instruction = str_replace("[WEBFOLDER_URI]", $this->uri_builder->getWebDavDefaultUri($this->ref_id), $mount_instruction);
            $mount_instruction = str_replace("[WEBFOLDER_URI_KONQUEROR]", $this->uri_builder->getWebDavKonquerorUri($this->ref_id), $mount_instruction);
            $mount_instruction = str_replace("[WEBFOLDER_URI_NAUTILUS]", $this->uri_builder->getWebDavNautilusUri($this->ref_id), $mount_instruction);
            $mount_instruction = str_replace("[ADMIN_MAIL]", $this->settings->get("admin_email"), $mount_instruction);

            $mount_instructions[$title] = $mount_instruction;
        }

        // TODO: Implement fillPlaceholdersForMountInstructions() method.
        return $mount_instructions;
    }

    // Everything below this line needs some refactoring
































    
    public function instructionsTplFileExists()
    {
        return is_file($this->path_to_template);
    }
    
    public function getInstructionsFromTplFile()
    {
        return fread(fopen($this->path_to_template, "rb"), filesize($this->path_to_template));
    }
    
    public function getCustomInstruction()
    {
        return $this->settings->get('custom_webfolder_instructions');
    }
    
    public function getDefaultInstruction()
    {
        return $this->lng->txt('webfolder_instructions_text');
    }
    
    public function getWebfolderTitle()
    {
        return $this->obj_title;
    }
    

    
    /**
     * Gets Webfolder mount instructions for the specified webfolder.
     *
     * The following placeholders are currently supported:
     *
     * [WEBFOLDER_TITLE] - the title of the webfolder
     * [WEBFOLDER_URI] - the URL for mounting the webfolder with standard
     *                   compliant WebDAV clients
     * [WEBFOLDER_URI_IE] - the URL for mounting the webfolder with Internet Explorer
     * [WEBFOLDER_URI_KONQUEROR] - the URL for mounting the webfolder with Konqueror
     * [WEBFOLDER_URI_NAUTILUS] - the URL for mounting the webfolder with Nautilus
     * [IF_WINDOWS]...[/IF_WINDOWS] - conditional contents, with instructions for Windows
     * [IF_MAC]...[/IF_MAC] - conditional contents, with instructions for Mac OS X
     * [IF_LINUX]...[/IF_LINUX] - conditional contents, with instructions for Linux
     * [ADMIN_MAIL] - the mailbox address of the system administrator
     *
     * @param unknown $a_instruction_tpl
     * @return mixed
     */
    public function setInstructionPlaceholders($a_instruction_tpl)
    {
        $a_instruction_tpl = str_replace("[WEBFOLDER_ID]", $this->ref_id, $a_instruction_tpl);
        $a_instruction_tpl = str_replace("[WEBFOLDER_TITLE]", $this->obj_title, $a_instruction_tpl);
        $a_instruction_tpl = str_replace("[WEBFOLDER_URI]", $this->getDefaultUri(), $a_instruction_tpl);
        $a_instruction_tpl = str_replace("[WEBFOLDER_URI_IE]", $this->getIEUri(), $a_instruction_tpl);
        $a_instruction_tpl = str_replace("[WEBFOLDER_URI_KONQUEROR]", $this->getKonquerorUri(), $a_instruction_tpl);
        $a_instruction_tpl = str_replace("[WEBFOLDER_URI_NAUTILUS]", $this->getNautilusUri(), $a_instruction_tpl);
        $a_instruction_tpl = str_replace("[ADMIN_MAIL]", $this->settings->get("admin_email"), $a_instruction_tpl);
        
        if (strpos($this->user_agent, 'MSIE') !== false) {
            $a_instruction_tpl = preg_replace('/\[IF_IEXPLORE\](?:(.*))\[\/IF_IEXPLORE\]/s', '\1', $a_instruction_tpl);
        } else {
            $a_instruction_tpl = preg_replace('/\[IF_NOTIEXPLORE\](?:(.*))\[\/IF_NOTIEXPLORE\]/s', '\1', $a_instruction_tpl);
        }
        
        switch ($this->clientOS) {
            case 'windows':
                $operatingSystem = 'WINDOWS';
                break;
            case 'unix':
                switch ($this->clientOSFlavor) {
                    case 'osx':
                        $operatingSystem = 'MAC';
                        break;
                    case 'linux':
                        $operatingSystem = 'LINUX';
                        break;
                    default:
                        $operatingSystem = 'LINUX';
                        break;
                }
                break;
            default:
                $operatingSystem = 'UNKNOWN';
                break;
        }
        if ($operatingSystem != 'UNKNOWN') {
            $a_instruction_tpl = preg_replace('/\[IF_' . $operatingSystem . '\](?:(.*))\[\/IF_' . $operatingSystem . '\]/s', '\1', $a_instruction_tpl);
            $a_instruction_tpl = preg_replace('/\[IF_([A-Z_]+)\](?:(.*))\[\/IF_\1\]/s', '', $a_instruction_tpl);
        } else {
            $a_instruction_tpl = preg_replace('/\[IF_([A-Z_]+)\](?:(.*))\[\/IF_\1\]/s', '\2', $a_instruction_tpl);
        }
        
        return $a_instruction_tpl;
    }
}
