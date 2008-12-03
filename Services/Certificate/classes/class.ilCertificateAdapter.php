<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Adapter class to provide certificate data for the certificate generator
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup Services
*/
abstract class ilCertificateAdapter
{
	abstract public function getCertificatePath();
	abstract public function getCertificateVariablesForPreview();
	abstract public function getCertificateVariablesForPresentation();
	abstract public function getCertificateVariablesForDescription();
	abstract public function getAdapterType();
	abstract public function getCertificateID();
	
	public function saveFormFields(&$form_fields)
	{
		
	}
	
	public function addAdditionalFormElements(&$form, $form_fields)
	{
		
	}

	public function addFormFieldsFromPOST(&$form_fields)
	{
		
	}
	
	public function addFormFieldsFromObject(&$form_fields)
	{
		
	}
}