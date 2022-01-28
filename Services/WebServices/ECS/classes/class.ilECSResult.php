<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesWebServicesECS
*/
class ilECSResult
{
    const RESULT_TYPE_JSON = 1;
    const RESULT_TYPE_URL_LIST = 2;
    
    private ilLogger $logger;
    
    private int $http_code = 0;
    private int $result_type = 0;
    private $result;
    
    private array $headers = array();
    
    /**
     * Constructor
     *
     * @access public
     * @param string result_string
     * @param int result type
     * @throws ilECSConnectorException
     *
     */
    public function __construct(string $a_res, int $a_type = self::RESULT_TYPE_JSON)
    {
        global $DIC;
        
        $this->logger = $DIC->logger()->wsrv();
        $this->result_type = $a_type;

        $this->init($a_res, $a_type);
    }
    
    /**
     * set HTTP return code
     *
     * @access public
     * @param string http code
     *
     */
    public function setHTTPCode(int $a_code) : void
    {
        $this->http_code = $a_code;
    }
    
    /**
     * get HTTP code
     *
     * @access public
     */
    public function getHTTPCode() : int
    {
        return $this->http_code;
    }

    /**
     * get result
     *
     * @access public
     * @return mixed JSON object, array of objects or false in case of errors.
     *
     */
    public function getResult()
    {
        return $this->result;
    }

    public function getResultType() : int
    {
        return $this->result_type;
    }

    /**
     * Set header
     * @param array $a_headers
     */
    public function setHeaders(array $a_headers)
    {
        $this->headers = $a_headers;
    }
    
    /**
     * get headers
     *
     * @access public
     */
    public function getHeaders() : array
    {
        return $this->headers ? $this->headers : array();
    }
    
    /**
     * init result (json_decode)
     */
    private function init(string $result_string, int $result_type)
    {
        switch ($result_type) {
            case self::RESULT_TYPE_JSON:
                if ($result_string) {
                    $this->result = json_decode($result_string);
                } else {
                    $this->result = array();
                }
                break;

            case self::RESULT_TYPE_URL_LIST:
                $this->result = $this->parseUriList($result_string);
                break;
        }
        return true;
    }

    /**
     *
     * @param string $a_content
     * @return ilECSUriList
     */
    private function parseUriList(string $a_content)
    {
        $list = new ilECSUriList();
        $lines = explode("\n", $a_content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!strlen($line)) {
                continue;
            }
            $uri_parts = explode("/", $line);
            $list->add($line, array_pop($uri_parts));
        }

        return $list;
    }
}
