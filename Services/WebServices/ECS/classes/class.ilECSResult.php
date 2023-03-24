<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once('./Services/WebServices/ECS/classes/class.ilECSConnectorException.php');

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

    protected $log;
    
    protected $result_string = '';
    protected $http_code = '';
    protected $result;
    protected $result_type;

    protected $headers = array();

    /**
     * Constructor
     *
     * @access public
     * @param string result_string
     * @param int result type
     * @throws ilECSConnectorException
     *
     */
    public function __construct($a_res, $a_type = self::RESULT_TYPE_JSON)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $this->log = $ilLog;
        
        $this->result_string = $a_res;
        $this->result_type = $a_type;

        $this->init();
    }
    
    /**
     * set HTTP return code
     *
     * @access public
     * @param string http code
     *
     */
    public function setHTTPCode($a_code)
    {
        $this->http_code = $a_code;
    }
    
    /**
     * get HTTP code
     *
     * @access public
     */
    public function getHTTPCode()
    {
        return $this->http_code;
    }
    
    /**
     * get unformated result string
     *
     * @access public
     *
     */
    public function getPlainResultString()
    {
        return $this->result_string;
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

    /**
     * Set header
     * @param array $a_headers
     */
    public function setHeaders($a_headers)
    {
        $this->headers = $a_headers;
    }
    
    /**
     * get headers
     *
     * @access public
     */
    public function getHeaders()
    {
        return $this->headers ?: [];
    }
    
    /**
     * init result (json_decode)
     * @access private
     *
     */
    private function init()
    {
        switch ($this->result_type) {
            case self::RESULT_TYPE_JSON:
                if ($this->result_string) {
                    $this->result = json_decode($this->result_string);
                } else {
                    $this->result = array();
                }
                break;

            case self::RESULT_TYPE_URL_LIST:
                $this->result = $this->parseUriList($this->result_string);
                break;
        }
        return true;
    }

    /**
     *
     * @param <type> $a_content
     * @return ilECSUriList
     */
    private function parseUriList($a_content)
    {
        include_once 'Services/WebServices/ECS/classes/class.ilECSUriList.php';
        $list = new ilECSUriList();
        $lines = explode("\n", $this->getPlainResultString());
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
