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
 * Represents a ecs course lms url
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSCourseLmsUrl
{
    public string $title = '';
    public string $url = '';
    
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
