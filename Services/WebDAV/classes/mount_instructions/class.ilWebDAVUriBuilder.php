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
 
use \Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class ilWebDAVUriBuilder
{
    /**
     * @var string[]
     */
    protected array $schemas = array(
            'default' => 'http',
            'konqueror' => 'webdav',
            'nautilus' => 'dav'
        );

    protected string $mount_instructions_query = 'mount-instructions';
    protected string $webdav_script_name = 'webdav.php';
    
    protected RequestInterface $request;
    protected UriInterface $uri;
    protected string $host;
    protected string $client_id;
    protected string $web_path_to_script;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;

        $this->uri = $request->getUri();
        $this->host = $this->uri->getHost();

        $this->client_id = CLIENT_ID;
        $this->web_path_to_script = $this->changePathToWebDavScript($this->uri->getPath());
    }
    
    protected function changePathToWebDavScript(string $a_original_path) : string
    {
        $exploded_path = explode('/', $a_original_path);
        
        if (in_array($this->webdav_script_name, $exploded_path)) {
            return implode('/', array_splice($exploded_path, 0, -2));
        }
                
        return implode('/', array_splice($exploded_path, 0, -1)) . '/' . $this->webdav_script_name;
    }
    
    protected function getWebDavPathToRef(int $a_ref_id) : string
    {
        return "$this->web_path_to_script/$this->client_id/ref_$a_ref_id";
    }
    
    protected function getWebDavPathToLanguageTemplate(string $language) : string
    {
        return "$this->web_path_to_script/$this->client_id/$language";
    }
    
    protected function getWebDavUriByPlaceholderName(string $placeholder_name, int $a_ref_id) : string
    {
        $scheme = $this->schemas[$placeholder_name];
        if ($this->uri->getScheme() == 'https') {
            $scheme .= 's';
        }
        return $scheme . '://' . $this->host . $this->getWebDavPathToRef($a_ref_id);
    }
    
    public function getWebDavDefaultUri(int $a_ref_id) : string
    {
        return $this->getWebDavUriByPlaceholderName('default', $a_ref_id);
    }
    
    public function getWebDavNautilusUri(int $a_ref_id) : string
    {
        return $this->getWebDavUriByPlaceholderName('nautilus', $a_ref_id);
    }
    
    public function getWebDavKonquerorUri(int $a_ref_id) : string
    {
        return $this->getWebDavUriByPlaceholderName('konqueror', $a_ref_id);
    }
    
    public function getUriToMountInstructionModalByRef(int $a_ref_id) : string
    {
        return $this->getWebDavPathToRef($a_ref_id) . '?' . $this->mount_instructions_query;
    }
    
    public function getUriToMountInstructionModalByLanguage(string $language) : string
    {
        return $this->getWebDavPathToLanguageTemplate($language) . '?' . $this->mount_instructions_query;
    }
}
