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
 */
class ilECSCourseLmsUrl
{
    public string $title = '';
    public string $url = '';
    
    /**
     * Set title
     */
    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }
    
    /**
     * Set url
     */
    public function setUrl(string $a_url) : void
    {
        $this->url = $a_url;
    }
}
