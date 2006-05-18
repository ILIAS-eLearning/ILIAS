<?php

class ilSoapStructureObject 
{
	var $obj_id;
	var $title;
	var $type;
	var $description;	
	
	var $structureObjects = array ();
	
	
	function ilSoapStructureObject ($objId, $type, $title, $description) {
		$this->setObjId ($objId);
		$this->setType ($type);
		$this->setTitle ($title);
		$this->setDescription ($description);		
	}
	
	/**
	*	add structure object to its parent
	* 
	*/
	function addStructureObject ($structureObject) 
	{
		$this->structureObjects [$structureObject->getObjId()] =  $structureObject;
	}
	
	/**
	 * returns sub structure elements
	 * 
	 */
	function getStructureObjects ()  {
		return $this->structureObjects;
	}

	
	/** 
	*	set current ObjId
	*	
	*/
	function setObjId ($value) {
		$this->obj_id= $value;
	}

	
	/**
	* return current object id
	*/	
	function getObjId() 
	{
		return $this->obj_id;
	}
	


	/** 
	*	set current title
	*	
	*/
	function setTitle ($value) {
		$this->title= $value;
	}
	
	
	/** 
	*	return current title
	*	
	*/
	function getTitle () {
		return $this->title;
	}
	
	/** 
	*	set current description
	*	
	*/
	function setDescription ($value) {
		$this->description = $value;
	}
	
	
	/** 
	*	return current description
	*	
	*/
	function getDescription () {
		return $this->description;
	}
	
	
	/** 
	*	set current type
	*	
	*/
	function setType ($value) {
		$this->type = $value;
	}
	
	
	/** 
	*	return current type
	*	
	*/
	function getType () {
		return $this->type;
	}
		
		
	/** 
	*	return current goto_link
	*	
	*/
	function getGotoLink () {
		die ("abstract");
	}
	
	/** 
	*	return current internal_link
	*	
	*/
	function getInternalLink () {
		die ("abstract"); 
	}		
	
	/**
	 * get xml tag attributes
	 */
	 
	function _getXMLAttributes () {
		return array(	'type' => $this->getType(),
					   	'obj_id' => $this->getObjId()
		);			
	}
	
	function _getTagName () {
		return "StructureObject";
	}
	
	/**
	 * export to xml writer
	 */
	 function exportXML ($xml_writer) {	 		 	
	 	$attrs = $this->_getXMLAttributes();			

		// open tag
 		$xml_writer->xmlStartTag($this->_getTagName(), $attrs);
		
		$xml_writer->xmlElement('Title',null,$this->getTitle());
		$xml_writer->xmlElement('Description',null,$this->getDescription());
		$xml_writer->xmlElement('InternalLink',null,$this->getInternalLink());
		$xml_writer->xmlElement('GotoLink',null,$this->getGotoLink());
			
		$xml_writer->xmlStartTag("StructureObjects");			
						
		// handle sub elements
		$structureObjects = $this->getStructureObjects();
		
		foreach ($structureObjects as $structureObject) 
		{
			$structureObject->exportXML ($xml_writer);
		}
					
		$xml_writer->xmlEndTag("StructureObjects");			
		
		$xml_writer->xmlEndTag($this->_getTagName());
			
	 }
		
	
}

?>