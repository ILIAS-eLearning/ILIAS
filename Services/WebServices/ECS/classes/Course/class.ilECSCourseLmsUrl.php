<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Represents a ecs course lms url
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSCourseLmsUrl
{
    public $title = '';
    public $url = '';
    
    /**
     * Constructor
     */
    public function __construct()
    {
    }
    
    /**
     * Set title
     * @param type $a_title
     */
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    
    /**
     * Set url
     */
    public function setUrl($a_url)
    {
        $this->url = $a_url;
    }
}
