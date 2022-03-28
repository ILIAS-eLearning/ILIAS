<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

/**
* QTI displayfeedback class
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @package assessment
*/
class ilQTIDisplayfeedback
{
    /** @var string|null */
    public $feedbacktype;

    /** @var string|null */
    public $linkrefid;

    /** @var string|null */
    public $content;
    
    public function __construct()
    {
    }

    /**
     * @param string $a_feedbacktype
     */
    public function setFeedbacktype($a_feedbacktype) : void
    {
        $this->feedbacktype = $a_feedbacktype;
    }

    /**
     * @return string|null
     */
    public function getFeedbacktype()
    {
        return $this->feedbacktype;
    }

    /**
     * @param string $a_linkrefid
     */
    public function setLinkrefid($a_linkrefid) : void
    {
        $this->linkrefid = $a_linkrefid;
    }

    /**
     * @return string|null
     */
    public function getLinkrefid()
    {
        return $this->linkrefid;
    }

    /**
     * @param string $a_content
     */
    public function setContent($a_content) : void
    {
        $this->content = $a_content;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }
}
