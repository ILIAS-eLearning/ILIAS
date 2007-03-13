<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * Note: This code derives from other work by the original author that has been
 * published under Common Public License (CPL 1.0). Please send mail for more
 * information to <alfred.kohnert@bigfoot.com>.
 * 
 * You must not remove this notice, or any other, from this software.
 *  
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 * 
 * Content-Type: application/x-httpd-php; charset=ISO-8859-1
 *    
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2005-2007 Alfred Kohnert
 *  
 */ 
 
class ilSCORM13Template
{
	private $params = array();
	private $template = '';

	public function __construct($tpl = null) 
	{
		if (is_string($tpl)) $this->load($tpl); 
	}

	public function setParam($k, $v) 
	{
		$this->params['{' . $k . '}'] = $v;
	}

	public function toHTMLSelect($data, $value=null, $params = array(), $kName=null, $vName=null) 
	{
		if (!is_array($params)) 
		{
			$params = array('name'=>$params);
		}
		if (!$params['id']) 
		{
			$params['id'] = 'id_'.uniqid();
		}
		if (!$params['name']) 
		{
			$params['name'] = $params['id'];
		}
		$html = array('<select');
		foreach ($params as $k => &$v) 
		{
			$html[] = " $k=\"$v\"";
		}
		$html[] = '>';
		foreach ($data as $k => $v) 
		{
			if ($kName) $k = $v[$kName];
			if ($vName) $v = $v[$vName];
			$html[] = '<option value="' . $k . '"' . ($k==$value ? ' selected="selected"' : '') . '>' . $v . '</option>';
		} 
		$html[] = '</select>';
		return implode('', $html); 
	}

	public function setParams($pairs) 
	{
		if (!is_array($pairs)) return;
		foreach ($pairs as $k => $v) 
		{
			$this->setParam($k, $v);
		}
	}

	public function load($tpl) 
	{
		$this->template = file_get_contents($tpl);
	}

	public function save($save='php://output', $data=null) 
	{
		$out = strtr($this->template, is_array($data) ? $data	: $this->params);
		if (is_string($save)) // save into file or stream 
		{
			file_put_contents($save, $out);
		}
		else // return as string
		{
			return $out;
		} 
	}

}

?>
