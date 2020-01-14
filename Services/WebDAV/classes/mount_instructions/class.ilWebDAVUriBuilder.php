<?php


class ilWebDAVUriBuilder
{
    /** @var \Psr\Http\Message\RequestInterface */
    protected $request;

    /** @var array */
    protected $schemas = array(
            'default' => 'http',
            'konqueror' => 'webdav',
            'nautilus' => 'dav'
        );

    protected $mount_instructions_query = 'mount-instructions';

    protected $webdav_script_name = 'webdav.php';

    public function __construct(\Psr\Http\Message\RequestInterface $a_request)
    {
        $this->request = $a_request;

        $this->uri = $a_request->getUri();
        $this->host = $this->uri->getHost();

        $this->client_id = CLIENT_ID;
        $this->web_path_to_script = $this->changePathToWebDavScript($this->uri->getPath());
    }

    /**
     *
     *
     * @param string $a_original_path
     * @return string
     */
    protected function changePathToWebDavScript(string $a_original_path)
    {
        $exploded_path = explode('/', $a_original_path);
        
        if (in_array($this->webdav_script_name, $exploded_path)) {
            return implode('/', array_splice($exploded_path, 0, -2));
        }
                
        return implode('/', array_splice($exploded_path, 0, -1)) . '/' . $this->webdav_script_name;
    }

    /**
     * @param int $a_ref_id
     * @return string
     */
    protected function getWebDavPathToRef(int $a_ref_id)
    {
        return "$this->web_path_to_script/$this->client_id/ref_$a_ref_id";
    }

    /**
     * @param string $language
     * @return string
     */
    protected function getWebDavPathToLanguageTemplate(string $language)
    {
        return "$this->web_path_to_script/$this->client_id/$language";
    }

    /**
     * @param string $placeholder_name
     * @param int $a_ref_id
     * @return string
     */
    protected function getWebDavUriByPlaceholderName(string $placeholder_name, int $a_ref_id)
    {
        $scheme = $this->schemas[$placeholder_name];
        if ($this->uri->getScheme() == 'https') {
            $scheme .= 's';
        }
        return $scheme . '://' . $this->host . $this->getWebDavPathToRef($a_ref_id);
    }

    /**
     * @param int $a_ref_id
     * @return string
     */
    public function getWebDavDefaultUri(int $a_ref_id)
    {
        return $this->getWebDavUriByPlaceholderName('default', $a_ref_id);
    }

    /**
     * @param int $a_ref_id
     * @return string
     */
    public function getWebDavNautilusUri(int $a_ref_id)
    {
        return $this->getWebDavUriByPlaceholderName('nautilus', $a_ref_id);
    }

    /**
     * @param int $a_ref_id
     * @return string
     */
    public function getWebDavKonquerorUri(int $a_ref_id)
    {
        return $this->getWebDavUriByPlaceholderName('konqueror', $a_ref_id);
    }

    /**
     * @param int $a_ref_id
     * @return string
     */
    public function getUriToMountInstructionModalByRef(int $a_ref_id)
    {
        return $this->getWebDavPathToRef($a_ref_id) . '?' . $this->mount_instructions_query;
    }

    /**
     * @param string $language
     * @return string
     */
    public function getUriToMountInstructionModalByLanguage(string $language)
    {
        return $this->getWebDavPathToLanguageTemplate($language) . '?' . $this->mount_instructions_query;
    }
}
