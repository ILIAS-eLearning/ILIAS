<?php

include_once "./webservice/soap/classes/class.ilSoapStructureReader.php";
include_once "./webservice/soap/classes/class.ilSoapStructureObjectFactory.php";

class ilSoapLMStructureReader extends ilSoapStructureReader 
{
	
	function ilSoapLMStructureReader ($object) 
	{		
		parent::ilSoapStructureReader($object);	
	}
	
	function _parseStructure () {		
		// get all child nodes in LM
		$ctree =& $this->object->getLMTree();
		
		$nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
		
		$currentParentStructureObject = $this->structureObject;
		$currentParent = 1;
		
		$parents = array ();
		$parents [$currentParent]= $currentParentStructureObject;

		$lastStructureObject = null;
		$lastNode = null;		
		$i =0;	
		foreach($nodes as $node)
		{
			
			// only pages and chapters
			if($node["type"] == "st" || $node["type"] == "pg")
			{
//				print_r($node);
//				echo $node["parent"]."<br>";
//				echo $node["obj_id"]."<br>";
//				echo $node["title"]."<br>";
//				print_r($parents);
//				echo "<br>";
				
				// parent has changed, to build a tree
				if ($currentParent != $node["parent"]) 
				{
					// did we passed this parent before?
				
					if (array_key_exists($node["parent"], $parents))
					{
//						echo "current_parent:".$currentParent."\n";	
//						echo "parent:".$node["parent"]."\n";			
//						// yes, we did, so use the known parent object
//						print_r($parents);
						$currentParentStructureObject = $parents[$node["parent"]];
						
//						print_r($currentParentStructureObject);
//							
//						die();					
					} 
					else  
					{
						// no, we did not, so use the last inserted structure as new parent
						if ($lastNode["type"] != "pg") 
						{											
							$parents[$lastNode["child"]] = $lastStructureObject;
							$currentParentStructureObject = $lastStructureObject;
						} 
						
					}
					 $i++;
					$currentParent = $lastNode["child"];
				}
				
				$lastNode = $node;
						
				$lastStructureObject = ilSoapStructureObjectFactory::getInstance ($node["obj_id"],$node["type"], $node["title"]);
									
				$currentParentStructureObject->addStructureObject( $lastStructureObject);			
							
			}
		}

//		print_r($this->structureObject);
//		
//		die();		
	}
	
		

		
}

?>