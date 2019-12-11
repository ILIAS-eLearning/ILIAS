<?php


class ilWebDAVUriBuilder
{
    /** @var \Psr\Http\Message\RequestInterface */
    protected $request;

    /** @var array */
    protected $schemas = array(
            'default' => 'https',
            'konqueror' => 'webdavs',
            'nautilus' => 'davs'
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
        // Caution: Its stRRpos (with two 'r'). So the last '/' will be found instead of the first
        $last_slash_pos = strrpos($a_original_path, '/');

        // Cuts of last part of the path to replace it with later with "webdav.php"
        $path_without_script = substr($a_original_path, 0, $last_slash_pos + 1);

        return $path_without_script . $this->webdav_script_name;
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
        return $this->schemas[$placeholder_name] . '://' . $this->host . $this->getWebDavPathToRef($a_ref_id);
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
