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
	/**
	* Returns the certificate path (with a trailing path separator)
	*
	* @return string The certificate path
	*/
	abstract public function getCertificatePath();
	
	/**
	* Returns an array containing all variables and values which can be exchanged in the certificate.
	* The values will be taken for the certificate preview.
	*
	* @return array The certificate variables
	*/
	abstract public function getCertificateVariablesForPreview();

	/**
	* Returns an array containing all variables and values which can be exchanged in the certificate
	* The values should be calculated from real data. The $params parameter array should contain all
	* necessary information to calculate the values.
	*
	* @param array $params An array of parameters to calculate the certificate parameter values
	* @return array The certificate variables
	*/
	abstract public function getCertificateVariablesForPresentation($params = array());

	/**
	* Returns a description of the available certificate parameters. The description will be shown at
	* the bottom of the certificate editor text area.
	*
	* @return string The certificate parameters description
	*/
	abstract public function getCertificateVariablesDescription();

	/**
	* Returns the adapter type
	* This value will be used to generate file names for the certificates
	*
	* @return string A string value to represent the adapter type
	*/
	abstract public function getAdapterType();

	/**
	* Returns a certificate ID
	* This value will be used to generate unique file names for the certificates
	*
	* @return mixed A unique ID which represents a certificate
	*/
	abstract public function getCertificateID();
	
	/**
	* Allows to add additional form fields to the certificate editor form
	* This method will be called when the certificate editor form will built
	* using the ilPropertyFormGUI class. Additional fields will be added at the
	* bottom of the form.
	*
	* @param object $form An ilPropertyFormGUI instance
	* @param array $form_fields An array containing the form values. The array keys are the names of the form fields
	*/
	public function addAdditionalFormElements(&$form, $form_fields)
	{
		
	}

	/**
	* Allows to add additional form values to the array of form values evaluating a
	* HTTP POST action.
	* This method will be called when the certificate editor form will be saved using
	* the form save button.
	*
	* @param array $form_fields A reference to the array of form values
	*/
	public function addFormFieldsFromPOST(&$form_fields)
	{
		
	}
	
	/**
	* Allows to add additional form values to the array of form values evaluating the
	* associated adapter class if one exists 
	* This method will be called when the certificate editor form will be shown and the
	* content of the form has to be retrieved from wherever the form values are saved.
	*
	* @param array $form_fields A reference to the array of form values
	*/
	public function addFormFieldsFromObject(&$form_fields)
	{
		
	}

	/**
	* Allows to save additional adapter form fields
	* This method will be called when the certificate editor form is complete and the
	* form values will be saved.
	*
	* @param array $form_fields A reference to the array of form values
	*/
	public function saveFormFields(&$form_fields)
	{
		
	}
}