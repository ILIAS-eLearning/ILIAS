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
include_once("./Services/Component/classes/class.ilService.php");

/**
 * @classDescription Service definition
 * 
 * @author Stefan Meyer <smeyer@leifos.com>
 * @version $Id$
 */
class ilPersonalDesktopService extends ilService
{
	
	/**
	 * Constructor 
	 */
	public function __construct()
	{
		
	}

    /**
     * @see ilComponent::getVersion()
     */
    public function getVersion()
    {
        //TODO Auto generated method stub
    }
    
    /**
     * @see ilComponent::isCore()
     */
    public function isCore()
    {
        //TODO Auto generated method stub
    }
	
}
