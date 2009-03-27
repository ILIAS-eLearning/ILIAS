<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
* This class represents a text property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilTextInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $value;
	protected $maxlength = 200;
	protected $size = 40;
	protected $validationRegexp;

	// added for YUI autocomplete feature
	protected $yui_dataSource;
	protected $yui_dataSchema;
	protected $yui_formatCallback;
	protected $yui_delimiterarray = array();

	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setInputType("text");
		$this->validationRegexp = "";
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	* Get Value.
	*
	* @return	string	Value
	*/
	function getValue()
	{
		return $this->value;
	}

	/**
	* Set validation regexp.
	*
	* @param	string	$a_value	regexp
	*/
	function setValidationRegexp($a_value)
	{
		$this->validationRegexp = $a_value;
	}

	/**
	* Get validation regexp.
	*
	* @return	string	regexp
	*/
	function getValidationRegexp()
	{
		return $this->validationRegexp;
	}

	/**
	* Set Max Length.
	*
	* @param	int	$a_maxlength	Max Length
	*/
	function setMaxLength($a_maxlength)
	{
		$this->maxlength = $a_maxlength;
	}

	/**
	* Get Max Length.
	*
	* @return	int	Max Length
	*/
	function getMaxLength()
	{
		return $this->maxlength;
	}

	/**
	* Set Size.
	*
	* @param	int	$a_size	Size
	*/
	function setSize($a_size)
	{
		$this->size = $a_size;
	}

	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
	}

	/**
	* Get Size.
	*
	* @return	int	Size
	*/
	function getSize()
	{
		return $this->size;
	}
	
	/**
	 * set input type
	 *
	 * @access public
	 * @param string input type password | text
	 * 
	 */
	public function setInputType($a_type)
	{
	 	$this->input_type = $a_type;
	}
	
	/**
	 * get input type
	 *
	 * @access public
	 */
	public function getInputType()
	{
	 	return $this->input_type;
	}
	

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		else if (strlen($this->getValidationRegexp()))
		{
			if (!preg_match($this->getValidationRegexp(), $_POST[$this->getPostVar()]))
			{
				$this->setAlert($lng->txt("msg_wrong_format"));
				return FALSE;
			}
		}
		
		return $this->checkSubItemsInput();
	}

	/**
	 * get datasource link for YUI autocomplete
	 * @return	String	link to data generation script
	 */
	 function getDataSource()
	 {
	 	return $this->yui_dataSource;
	 }

	/**
	 * set datasource link for YUI autocomplete
	 * @param	String	link to data generation script
	 */
	function setDataSource($href)
	{
		$this->yui_dataSource = $href;
	}
	
	/**
	 * get datasource schema for YUI autocomplete
	 * @return	array	data schema as array
	 */
	 function getDataSourceSchema()
	 {
	 	return $this->yui_dataSchema;
	 }

	/**
	 * set datasource schema for YUI autocomplete
	 * @param array	Data Schema as array. The <b>first Element</b> contains
	 *      a path in dot notation to a result array in the expected json response
	 *	e.g. for the json response
	 * 		{response : { result : [firstObject, secondObject ...] }}
	 *	the dot notated path is 'response.result'
	 *	The <b>following Elements</b> contains names of attributes 
	 *	within the resultobjects (firstObject, secondObject... see above)
	 *	which should be passed to the autocomplete component. You can define
	 *	a javascript format callback function, to process the passed values
	 *	(see setDataSourceResultFormat for more information)
	 */
	function setDataSourceSchema($ds)
	{
		$this->yui_dataSchema = $ds;
	}

	/**
	 * get data result format callback for YUI autocomplete
	 */
	 function getDataSourceResultFormat()
	 {
	 	return $this->yui_formatCallback;
	 }

	/**
	 * set data result format callback for YUI autocomplete
	 * @param	String	Javascript callback function which takes three parameters.
	 *	$callback can be a the name of a function without parenthesis or an
	 *	function (a, b, c) {...} text block
	 */
	function setDataSourceResultFormat($callback)
	{
		$this->yui_formatCallback = $callback;
	}
	
	/**
	 * set data delimiter array
	 * @param	array	array of chars. Each char will be used as
	 *			delimiter to handle multiple inputs in
	 *			one field (e.g. multiple email recipients)
	 */
	public function setDataSourceDelimiter($ar)
	{
		if (!is_array($ar))
			$ar = array($ar);
		$this->yui_delimiterarray = $ar;
	}
	
	/**
	 * get data delimiter array
	 * @return	array	array of current delimiters
	 */
	public function getDataSourceDelimiter()
	{
		return $this->yui_delimiterarray;	
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		$tpl = new ilTemplate("tpl.prop_textinput.html", true, true, "Services/Form");
		if (strlen($this->getValue()))
		{
			$tpl->setCurrentBlock("prop_text_propval");
			$tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
			$tpl->parseCurrentBlock();
		}

		switch($this->getInputType())
		{
			case 'password':
				$tpl->setVariable('PROP_INPUT_TYPE','password');
				break;
			case 'hidden':
				$tpl->setVariable('PROP_INPUT_TYPE','hidden');
				break;
			case 'text':
			default:
				$tpl->setVariable('PROP_INPUT_TYPE','text');
		}
		$tpl->setVariable("POST_VAR", $this->getPostVar());
		$tpl->setVariable("ID", $this->getFieldId());
		$tpl->setVariable("SIZE", $this->getSize());
		$tpl->setVariable("MAXLENGTH", $this->getMaxLength());
		if ($this->getDisabled())
		{
			$tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
		}

		// use autocomplete feature?
		if ($this->getDataSource() && $this->getDataSourceSchema())
		{
			include_once "./Services/YUI/classes/class.ilYuiUtil.php";
			include_once "./Services/JSON/classes/class.ilJsonUtil.php";
			ilYuiUtil::initAutoComplete();
			$tpl->setVariable('ID_AUTOCOMPLETE', $this->getFieldId() . "_autocomplete");
			$tpl->setVariable('YUI_DATASOURCE', $this->getDataSource());
			$tpl->setVariable('YUI_DATASCHEMA', ilJsonUtil::encode($this->getDataSourceSchema()));
			
			if ($this->getDataSourceResultFormat())
			{
				$tpl->setVariable('YUI_FORMAT_CALLBACK', $this->getDataSourceResultFormat());
			}

			if ($this->getDataSourceDelimiter())
			{
				$tpl->setVariable('DELIMITER_ARRAY', ilJsonUtil::encode($this->getDataSourceDelimiter()));	
			}
		}

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $tpl->get());
		$a_tpl->parseCurrentBlock();
	}
}
