<?php

/**
 * Class ilWebDAVMountInstructions
 *
 * This class creates the page and links for the WebDAV mount instruction page
 *
 * @author Raphael Heer <raphael.heer@hslu.ch>
 * $Id$
 */
class ilWebDAVMountInstructions extends ilWebDAVObjectlessMountInstructions
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
    protected $lng;
    
    protected $ref_id;
    protected $obj_id;
    protected $obj_title;

    protected $document_repository;
    protected $uri_provider;
    
    public function __construct($a_user_agent = '', $a_request_uri = '', $a_http_host = '', $a_script_name = '', $a_client_id = '', $uri_provider = NULL)
    {
        global $DIC;
        $this->settings = new ilSetting('file_access');
        $this->lng = $DIC->language();
        $request = $DIC->http()->request();

        $this->uri_provider = $uri_provider == NULL ? new ilWebDAVUriProvider($DIC->http()->request()) : $uri_provider;

        $this->document_repository = new ilWebDAVMountInstructionsRepositoryImpl($DIC->database());
        
        $this->user_agent = $a_user_agent == '' ? strtolower($_SERVER['HTTP_USER_AGENT']) : $a_user_agent;
        $this->request_uri = $a_request_uri == '' ? $_SERVER['REQUEST_URI'] : $a_request_uri;
        $this->http_host = $a_http_host == '' ? $_SERVER['HTTP_HOST'] : $a_http_host;
        $this->script_name = $a_http_host == '' ? $_SERVER['SCRIPT_NAME'] : $a_script_name;
        $this->client_id = $a_http_host == '' ? CLIENT_ID : $a_client_id;
        $this->path_to_template = 'Customizing/clients/'.$this->client_id.'/webdavtemplate.htm';
        
        $this->ref_id = 0;
        foreach(explode('/', $this->request_uri) as $uri_part)
        {
            if(strpos($uri_part, 'ref_') !== false && $this->ref_id == 0)
            {
                $this->ref_id = (int)explode('_', $uri_part)[1];
            }
        }
        if($this->ref_id == 0)
        {
            //throw new Exception('Bad Request: No ref id given!');
        }
        else
        {
            $this->obj_id = ilObject::_lookupObjectId($this->ref_id);
            $this->obj_title = ilObject::_lookupTitle($this->obj_id);
        }
        
        $this->base_uri = $this->http_host.$this->script_name.'/'.$this->client_id. '/ref_' . $this->ref_id . '/';
        
        $this->protocol_prefixes = array(
            'default' => 'https://',
            'konqueror' => 'webdavs://',
            'nautilus' => 'davs://'
        );
        
        //$this->setValuesFromUserAgent($this->user_agent);
    }

    // Everything below this line needs some refactoring
































    
    public function instructionsTplFileExists()
    {
        return is_file($this->path_to_template);
    }
    
    public function getInstructionsFromTplFile()
    {
        return fread(fopen($this->path_to_template, "rb"),filesize($this->path_to_template));
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
        
        if(strpos($this->user_agent,'MSIE')!==false){
            $a_instruction_tpl = preg_replace('/\[IF_IEXPLORE\](?:(.*))\[\/IF_IEXPLORE\]/s','\1', $a_instruction_tpl);
        }else{
            $a_instruction_tpl = preg_replace('/\[IF_NOTIEXPLORE\](?:(.*))\[\/IF_NOTIEXPLORE\]/s','\1', $a_instruction_tpl);
        }
        
        switch ($this->clientOS)
        {
            case 'windows' :
                $operatingSystem = 'WINDOWS';
                break;
            case 'unix' :
                switch ($this->clientOSFlavor)
                {
                    case 'osx' :
                        $operatingSystem = 'MAC';
                        break;
                    case 'linux' :
                        $operatingSystem = 'LINUX';
                        break;
                    default :
                        $operatingSystem = 'LINUX';
                        break;
                }
                break;
            default :
                $operatingSystem = 'UNKNOWN';
                break;
        }
        if ($operatingSystem != 'UNKNOWN')
        {
            $a_instruction_tpl = preg_replace('/\[IF_'.$operatingSystem.'\](?:(.*))\[\/IF_'.$operatingSystem.'\]/s','\1', $a_instruction_tpl);
            $a_instruction_tpl = preg_replace('/\[IF_([A-Z_]+)\](?:(.*))\[\/IF_\1\]/s','', $a_instruction_tpl);
        }
        else
        {
            $a_instruction_tpl = preg_replace('/\[IF_([A-Z_]+)\](?:(.*))\[\/IF_\1\]/s','\2', $a_instruction_tpl);
        }
        
        return $a_instruction_tpl;
    }
}