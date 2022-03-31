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
 * Representation of an HTTP cookie
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilCookie
{
    private $name = '';// TODO PHP8-REVIEW Property type missing
    private $value = '';// TODO PHP8-REVIEW Property type missing
    private $expire = 0;// TODO PHP8-REVIEW Property type missing
    private $path = '';// TODO PHP8-REVIEW Property type missing
    private $domain = '';// TODO PHP8-REVIEW Property type missing
    private $secure = false;// TODO PHP8-REVIEW Property type missing
    private $http_only = false;// TODO PHP8-REVIEW Property type missing
    
    public function __construct($a_name)// TODO PHP8-REVIEW Type hints missing
    {
        $this->setName($a_name);
    }
    
    public function setName($a_name) : void// TODO PHP8-REVIEW Type hints missing
    {
        $this->name = $a_name;
    }
    
    /**
     * Get name
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * Currently no restriction on cookie length.
     * RFC 2965 suggests a minimum of 4096 bytes
     * @param string $a_value
     */
    public function setValue($a_value) : void// TODO PHP8-REVIEW Type hints missing
    {
        $this->value = $a_value;
    }
    
    public function getValue() : string
    {
        return $this->value;
    }
    
    public function setExpire($a_expire) : void// TODO PHP8-REVIEW Type hints missing
    {
        $this->expire = (int) $a_expire;
    }
    
    public function getExpire() : int
    {
        return $this->expire;
    }
    
    public function setPath($a_path) : void// TODO PHP8-REVIEW Type hints missing
    {
        $this->path = $a_path;
    }
    
    public function getPath() : string
    {
        return $this->path;
    }
    
    public function setDomain($a_domain) : void/// TODO PHP8-REVIEW Type hints missing
    {
        $this->domain = $a_domain;
    }
    
    public function getDomain() : string
    {
        return $this->domain;
    }
    
    public function setSecure($a_status) : void// TODO PHP8-REVIEW Type hints missing
    {
        $this->secure = (bool) $a_status;
    }
    
    public function isSecure() : bool
    {
        return $this->secure;
    }
    
    public function setHttpOnly($a_http_only) : void// TODO PHP8-REVIEW Type hints missing
    {
        $this->http_only = $a_http_only;
    }
    
    public function isHttpOnly() : bool
    {
        return $this->http_only;
    }
}
