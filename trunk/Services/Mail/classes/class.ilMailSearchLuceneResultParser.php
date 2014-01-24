<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesMail
 */
class ilMailSearchLuceneResultParser
{
	/**
	 * @var ilMailSearchResult
	 */
	protected $result;

	/**
	 * @var string
	 */
	protected $xml;

	/**
	 * @param ilMailSearchResult $result
	 * @param string             $xml
	 */
	public function __construct(ilMailSearchResult $result, $xml)
	{
		$this->result = $result;
		$this->xml    = $xml;
	}

	/**
	 * @return string
	 */
	public function getXml()
	{
		return $this->xml;
	}

	/**
	 * @return ilMailSearchResult
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 *
	 */
	public function parse()
	{
		if(!strlen($this->getXml()))
		{
			return;
		}

		$hits = new SimpleXMLElement($this->getXml());
		foreach($hits->children() as $user)
		{
			foreach($user->children() as $item)
			{
				/**
				 * @var $item SimpleXMLElement
				 */
				$fields = array();
				foreach($item->children() as $field)
				{
					/**
					 * @var $field SimpleXMLElement
					 */
					$name     = (string)$field['name'];
					$content  = (string)$field;
					$fields[] = array(
						$name, $content
					);
				}
				$this->getResult()->addItem((int)$item['id'], $fields);
			}
		}
	}
}
