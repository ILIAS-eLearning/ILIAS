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

/**
 * Presentation of ecs uril (http://...campusconnect/courselinks)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesWebServicesECS
 */
class ilECSUriList
{
    public $uris = array();

    /**
     * Constructor
     */
    public function __construct()
    {
    }


    /**
     * Add uri
     * @param  string $a_uri
     * @param int $a_link_id
     */
    public function add($a_uri, $a_link_id)
    {
        #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': '.$a_uri.', '.$a_link_id);
        $this->uris[$a_link_id] = $a_uri;
    }

    /**
     * Get link ids
     * @return <type>
     */
    public function getLinkIds()
    {
        return (array) array_keys($this->uris);
    }

    /**
     * Get uris
     * @return array
     */
    public function getUris()
    {
        return (array) $this->uris;
    }
}
