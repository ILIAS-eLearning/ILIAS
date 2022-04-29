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
    public ?string $feedbacktype;
    public ?string $linkrefid;
    public ?string $content;
    
    public function __construct()
    {
        $this->feedbacktype = null;
        $this->linkrefid = null;
        $this->content = null;
    }

    public function setFeedbacktype(string $a_feedbacktype) : void
    {
        $this->feedbacktype = $a_feedbacktype;
    }

    public function getFeedbacktype() : ?string
    {
        return $this->feedbacktype;
    }

    public function setLinkrefid(string $a_linkrefid) : void
    {
        $this->linkrefid = $a_linkrefid;
    }

    public function getLinkrefid() : ?string
    {
        return $this->linkrefid;
    }

    public function setContent(string $a_content) : void
    {
        $this->content = $a_content;
    }

    public function getContent() : ?string
    {
        return $this->content;
    }
}
