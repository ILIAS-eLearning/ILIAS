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
/*
	PHP port of several ADL-sources
	@author Hendrik Holtmann <holtmann@mac.com>
	
	This .php file is GPL licensed (see above) but based on
 	Sourcecode by ADL Co-Lab, which is licensed as:
	
	Advanced Distributed Learning Co-Laboratory (ADL Co-Lab) Hub grants you 
	("Licensee") a non-exclusive, royalty free, license to use, modify and 
	redistribute this software in source and binary code form, provided that 
	i) this copyright notice and license appear on all copies of the software; 
	and ii) Licensee does not utilize the software in a manner which is 
	disparaging to ADL Co-Lab Hub.

	This software is provided "AS IS," without a warranty of any kind.  ALL 
	EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING 
	ANY IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE 
	OR NON-INFRINGEMENT, ARE HEREBY EXCLUDED.  ADL Co-Lab Hub AND ITS LICENSORS 
	SHALL NOT BE LIABLE FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF 
	USING, MODIFYING OR DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES.  IN NO 
	EVENT WILL ADL Co-Lab Hub OR ITS LICENSORS BE LIABLE FOR ANY LOST REVENUE, 
	PROFIT OR DATA, OR FOR DIRECT, INDIRECT, SPECIAL, CONSEQUENTIAL, 
	INCIDENTAL OR PUNITIVE DAMAGES, HOWEVER CAUSED AND REGARDLESS OF THE 
	THEORY OF LIABILITY, ARISING OUT OF THE USE OF OR INABILITY TO USE 
	SOFTWARE, EVEN IF ADL Co-Lab Hub HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH 
	DAMAGES.
*/

	require_once("SeqActivity.php");
	
	require_once("SeqRule.php");
	require_once("SeqRuleset.php");
	
	require_once("SeqCondition.php");
	require_once("SeqConditionSet.php");
	
	require_once("SeqObjective.php");
	require_once("SeqObjectiveMap.php");
	
	require_once("SeqRollupRule.php");
	require_once("SeqRollupRuleset.php");
	
	require_once("ADLAuxiliaryResource.php");

	class SeqTreeBuilder{

	  public function buildNodeSeqTree($file){
		
	  	$doc = new DomDocument();
	  	$doc->load($file);
	  	$organizations = $doc->getElementsByTagName("organizations");
	  	
	  	//lookup default organization id
	  	$default=preg_replace('/(%20)+/', ' ', trim($organizations->item(0)->getAttribute("default")));
	  	
	  	//get all organization nodes
	  	$organization = $doc->getElementsByTagName("organization");
	  	
	  	//lookup the default organization
	  	foreach ($organization as $element) {
	  		if (preg_replace('/(%20)+/', ' ', trim($element->getAttribute("identifier")))==$default) {
	  			$default_organization = $element;
	  		}
	  	}
	  	
	  	//read seqCollection
	  	$seqCollection = $doc->getElementsByTagName("sequencingCollection")->item(0);
	  	
	  	$root = $this->buildNode($default_organization,$seqCollection,$doc);
	  	
		//return no data please check
		$objectivesGlobalToSystem = $default_organization->getAttributeNS("http://www.adlnet.org/xsd/adlseq_v1p3","objectivesGlobalToSystem");
	
		$org = preg_replace('/(%20)+/', ' ', trim($default_organization->getAttribute("identifier")));
		
		//default true
		$globaltosystem = 1;
		
		if ($objectivesGlobalToSystem=="false") {
			$globaltosystem = 0;
		}
		
	    //return no data please check
		$dataGlobalToSystem = $default_organization->getAttributeNS("http://www.adlnet.org/xsd/adlcp_v1p3","sharedDataGlobalToSystem");
	
		//default true
		$dataglobaltosystem = 1;
		
		if ($dataGlobalToSystem=="false") {
			$dataglobaltosystem = 0;
		}
				
		//assign SeqActivity to top node
		$c_root['_SeqActivity']=$root;
				
		$ret['global'] = $globaltosystem;
		$ret['dataglobal'] = $dataglobaltosystem;
		$ret['tree'] = $c_root;
		
		return $ret;
	  	
	  }
	  
	  
	  
	  private function buildNode($node,$seq,$doc) {

	  	//create a new activity object
	  	$act = new SeqActivity();
	  		  	
	  	//set various attributes, if existent
	  	$act->setID(preg_replace('/(%20)+/', ' ', trim($node->getAttribute("identifier"))));
	  	
	  	$tempVal = preg_replace('/(%20)+/', ' ', trim($node->getAttribute("identifierref")));
	  	if ($tempVal){
	  		$act->setResourceID($tempVal);
	  	}
	  	
	  	$tempVal = $node->getAttribute("isvisible");
	  
	  	if ($tempVal){
	  			$act->setIsVisible(self::convert_to_bool($tempVal));
	  	}
	  	 
	    
	  	
	    	//Proceed nested items
	  	$children = $node->childNodes;
	  	
	  	 for ($i = 0; $i < $children->length; $i++ ) {
	  		
	  		$curNode=$children->item($i);
	  		//elements only
	  		
	  		if ($curNode->nodeType == XML_ELEMENT_NODE) {
	  			//only items are nested
	  			if ($curNode->localName == "item") {
					//init;
					$c_nestedAct=null;
	  				$nestedAct = new SeqActivity();
	  				$nestedAct = $this->buildNode($curNode,$seq,$doc);
	  				if ($nestedAct != null ) {
						$act->AddChild($nestedAct);
	                }
	  			}
	  			else if ($curNode->localName == "title") {
	  				 $act->setTitle($this->lookupElement($curNode,null));
	  			}
	  			else if ($curNode->localName == "completionThreshold"){
		  			$tempVal = $curNode->getAttribute("minProgressMeasure");
				  	 
				  	 if ($tempVal){
				  	 	$act->setCompletionThreshold($tempVal);
				  	 }
				  	 else if ($curNode->nodeValue != null && $curNode->nodeValue != '') {
				  	 	$act->setCompletionThreshold($curNode->nodeValue);
				  	 }
		
				  	 $tempVal = $curNode->getAttribute("progressWeight");
				  	 
				  	 if ($tempVal){
				  	 	$act->setProgressWeight($tempVal);
				  	 }
				  	 $tempVal = $curNode->getAttribute("completedByMeasure");
			  	 
				  	 if ($tempVal){
				  	 	$act->setCompletedByMeasure(self::convert_to_bool($tempVal));
				  	 }
	  			}
	  			else if ($curNode->localName == "sequencing") {
	  				$seqInfo = $curNode;
	  				//get IDRef
	  				$tempVal = preg_replace('/(%20)+/', ' ', trim($curNode->getAttribute("IDRef")));
	  				//only execute for referenced sequencing parts
	  				if ($tempVal) {
	  					//init seqGlobal
	  					$seqGlobal = null;
	  					
	  					//get all sequencing nodes in collections
	  					$sequencing = $seq->getElementsByTagName("sequencing");
	  
	  					//lookup the matching sequencing element
	  					foreach ($sequencing as $element) {
	  						if (preg_replace('/(%20)+/', ' ', trim($element->getAttribute("ID")))==$tempVal) {
	  							$seqGlobal = $element;
	  						}
	  					}
	  					
	  					//clone the global node
	  					$seqInfo = $seqGlobal->cloneNode(TRUE);
	  					
	  					//back to the local node
	  					$seqChildren = $curNode->childNodes;
	                   	for ($j = 0; $j < $seqChildren->length; $j++ ) {
	  						//process local nodes
	  						$curChild = $seqChildren->item($j);
	                      	if ( $curChild->nodeType == XML_ELEMENT_NODE ) {
	  							//echo "\nFound Sequencing Element Node".$curChild->localName;
	  							//add local to global sequencing info
	  							$seqInfo->appendChild($curChild);
	                            
	                          }
	  				 	}
	                 }	
	  				//extract the sequencing info, if we have one
					//avoid working with 
	  				$act=$this->extractSeqInfo($seqInfo, $act);
	  				
	  			}
	  		}
	  		
	  	
	  	 
	  		$item=$children->item($i)->nodeValue;
	  	}
		//add class
		//$c_act['_SeqActivity']=$act;
	  	return $act;
	  
	  }
	  
	  
	  private function extractSeqInfo($iNode, $ioAct) {
		//set sequencing information
		$children = $iNode->childNodes;
		for ( $i = 0; $i < $children->length; $i++ ) {
			$curNode = $children->item($i);
          	if ( $curNode->nodeType == XML_ELEMENT_NODE ) {
				if ($curNode->localName == "controlMode") {
					//look for choice
					$tempVal=$curNode->getAttribute("choice");
					if ($tempVal) {
						$ioAct->setControlModeChoice(self::convert_to_bool($tempVal));
					}
					//look for choiceExit
					$tempVal=$curNode->getAttribute("choiceExit");
					if ($tempVal) {
						$ioAct->setControlModeChoiceExit(self::convert_to_bool($tempVal));
					}
					
					//look for flow
					$tempVal=$curNode->getAttribute("flow");
					if ($tempVal) {
						$ioAct->setControlModeFlow(self::convert_to_bool($tempVal));
					}
					
					// Look for 'forwardOnly'
	               	$tempVal=$curNode->getAttribute("forwardOnly");
					if ($tempVal) {
						$ioAct->setControlForwardOnly(self::convert_to_bool($tempVal));
					}
					
					// Look for 'useCurrentAttemptObjectiveInfo'
		            $tempVal=$curNode->getAttribute("useCurrentAttemptObjectiveInfo");
					if ($tempVal) {
						$ioAct->setUseCurObjective(self::convert_to_bool($tempVal));
					}
					
					// Look for 'useCurrentAttemptProgressInfo'
			        $tempVal=$curNode->getAttribute("useCurrentAttemptProgressInfo");
					if ($tempVal) {
						$ioAct->setUseCurProgress(self::convert_to_bool($tempVal));
					}
					
				}
				
				else if ($curNode->localName == "sequencingRules") {
					$ioAct = $this->getSequencingRules($curNode,$ioAct);
					
				}
				
				else if ($curNode->localName == "limitConditions") {
					// Look for 'useCurrentAttemptObjectiveInfo'
			 		$tempVal=$curNode->getAttribute("attemptLimit");
					if ($tempVal) {
						$ioAct->setAttemptLimit($tempVal);
					}
				
					// Look for 'attemptAbsoluteDurationLimit'
	               	$tempVal=$curNode->getAttribute("attemptAbsoluteDurationLimit");
					if ($tempVal) {
				 		$ioAct->setAttemptAbDur($tempVal);
					}
			
			       // Look for 'attemptExperiencedDurationLimit'
			       	$tempVal=$curNode->getAttribute("attemptExperiencedDurationLimit");
					if ($tempVal) {
		        		$ioAct->setAttemptExDur($tempVal);
					}
	
	               // Look for 'activityAbsoluteDurationLimit'
			       	$tempVal=$curNode->getAttribute("activityAbsoluteDurationLimit");
					if ($tempVal) {
						$ioAct->setActivityAbDur($tempVal);
					}
				   
					// Look for 'activityExperiencedDurationLimit'
					$tempVal=$curNode->getAttribute("activityExperiencedDurationLimit");
					if ($tempVal) {
				       	$ioAct->setActivityExDur($tempVal);
					}
			
	               // Look for 'beginTimeLimit'
					$tempVal=$curNode->getAttribute("beginTimeLimit");
					if ($tempVal) {
						$ioAct->setBeginTimeLimit($tempVal);
					}
					
					// Look for 'endTimeLimit'
	               	$tempVal=$curNode->getAttribute("endTimeLimit");
					if ($tempVal) {
						$ioAct->setEndTimeLimit($tempVal);
					}
				}
				else if ($curNode->localName == "auxiliaryResources") {
					$ioAct = self::getAuxResources($curNode, $ioAct);
				}
				
				else if ($curNode->localName == "rollupRules") {
					$ioAct = self::getRollupRules($curNode, $ioAct);
				}
				
				else if ($curNode->localName == "objectives" && $curNode->namespaceURI == "http://www.imsglobal.org/xsd/imsss") {
					$ioAct = self::getObjectives($curNode,$ioAct);
				}
				
          		else if ($curNode->localName == "objectives" && $curNode->namespaceURI == "http://www.adlnet.org/xsd/adlseq_v1p3") {
					$ioAct = self::getADLSEQObjectives($curNode,$ioAct);
				}
				else if ($curNode->localName == "randomizationControls") {
					
					// Look for 'randomizationTiming'
	               	$tempVal=$curNode->getAttribute("randomizationTiming");
					if ($tempVal) {
						$ioAct->setRandomTiming($tempVal);
					}
					
					// Look for 'selectCount'
					$tempVal=$curNode->getAttribute("selectCount");
					if ($tempVal) {
						$ioAct->setSelectCount($tempVal);
					}
					
					// Look for 'reorderChildren'
	               	$tempVal=$curNode->getAttribute("reorderChildren");
					if ($tempVal) {
						$ioAct->setReorderChildren(self::convert_to_bool($tempVal));
					}
					
					// Look for 'selectionTiming'
	               	$tempVal=$curNode->getAttribute("selectionTiming");
					if ($tempVal) {
		               	$ioAct->setSelectionTiming($tempVal);
					}
				}
				else if ($curNode->localName == "deliveryControls") {
					
					// Look for 'tracked'
					$tempVal=$curNode->getAttribute("tracked");
					if ($tempVal) {
						$ioAct->setIsTracked(self::convert_to_bool($tempVal));
					}
	              	
	               // Look for 'completionSetByContent'
					$tempVal=$curNode->getAttribute("completionSetByContent");
					if ($tempVal) {
						$ioAct->setSetCompletion(self::convert_to_bool($tempVal));
					}
	              
	 				// Look for 'objectiveSetByContent'
					$tempVal=$curNode->getAttribute("objectiveSetByContent");
					if ($tempVal) {
						$ioAct->setSetObjective(self::convert_to_bool($tempVal));
					}
				}	
	            else if ($curNode->localName == "constrainedChoiceConsiderations") {
					
					// Look for 'preventActivation'
	               	$tempVal=$curNode->getAttribute("preventActivation");
					if ($tempVal) {
						$ioAct->setPreventActivation(self::convert_to_bool($tempVal));
					}
					
					// Look for 'constrainChoice'
	               	$tempVal=$curNode->getAttribute("constrainChoice");
					if ($tempVal) {
						$ioAct->setConstrainChoice(self::convert_to_bool($tempVal));
					}
				}   	
				else if ($curNode->localName == "rollupConsiderations") {
				
					// Look for 'requiredForSatisfied'
					$tempVal=$curNode->getAttribute("requiredForSatisfied");
					if ($tempVal) {
						$ioAct->setRequiredForSatisfied($tempVal);
					}
					
					// Look for 'requiredForNotSatisfied'
					$tempVal=$curNode->getAttribute("requiredForNotSatisfied");
					if ($tempVal) {
						$ioAct->setRequiredForNotSatisfied($tempVal);
					}
					
					// Look for 'requiredForCompleted'
	               	$tempVal=$curNode->getAttribute("requiredForCompleted");
					if ($tempVal) {
						$ioAct->setRequiredForCompleted($tempVal);
					}
					
					// Look for 'requiredForIncomplete'
	               	$tempVal=$curNode->getAttribute("requiredForIncomplete");
					if ($tempVal) {
		               	$ioAct->setRequiredForIncomplete($tempVal);
					}
				
				   // Look for 'measureSatisfactionIfActive'
				   	$tempVal=$curNode->getAttribute("measureSatisfactionIfActive");
					if ($tempVal) {
					   	$ioAct->setSatisfactionIfActive(self::convert_to_bool($tempVal));
					}
				}  	
				
				
	  			
         	}  //end note-type check
		
		} //end for-loop
		 
		return $ioAct;
	  }
	
	
	public static function getObjectives ($iNode,$ioAct) {
		global $ilLog;
		
		
		$ok = true;
	    $tempVal = null;
	    $objectives = array();
		$children = $iNode->childNodes;
		for ($i = 0; $i < $children->length; $i++ ) {
			$curNode=$children->item($i);
	  		if ($curNode->nodeType == XML_ELEMENT_NODE) {
				if ($curNode->localName == "primaryObjective" || $curNode->localName == "objective" ) {
		
			   		$obj = new SeqObjective();
					if ($curNode->localName == "primaryObjective") { 
               			$obj->mContributesToRollup = true;
					}
					
               		// Look for 'objectiveID'
					$tempVal = preg_replace('/(%20)+/', ' ', trim($curNode->getAttribute("objectiveID")));
					if($tempVal) {
						$obj->mObjID = $tempVal;
                	}

					// Look for 'satisfiedByMeasure'
               		$tempVal = $curNode->getAttribute("satisfiedByMeasure");
					if($tempVal) {
						$obj->mSatisfiedByMeasure = self::convert_to_bool($tempVal);
                	}
					// Look for 'minNormalizedMeasure'
					$tempVal=self::lookupElement($curNode,"minNormalizedMeasure");
               		if($tempVal) {
						$obj->mMinMeasure = $tempVal;
                	}
				
					//get ObjectiveMaps
					$maps = self::getObjectiveMaps($curNode);
					if ( $maps != null ){
						$obj->mMaps = $maps;
	            	}
					//$obj->mContributesToRollup = true;
					//add class
					$c_obj['_SeqObjective'] = $obj;
					array_push($objectives,$c_obj);
               }
			}
		}
		$ioAct->setObjectives($objectives);
     	return $ioAct;
	}
	
	public static function getADLSEQObjectives ($iNode,$ioAct) {
		global $ilLog;
		$objectives = $ioAct->mObjectives;
		$children = $iNode->childNodes;
		for ($i = 0; $i < $children->length; $i++ ) {
			$curNode=$children->item($i);
			if ($curNode->nodeType == XML_ELEMENT_NODE) {
				if ($curNode->localName == "objective" ) {
					// get the objectiveID
					$adlseqobjid = preg_replace('/(%20)+/', ' ', trim($curNode->getAttribute("objectiveID")));
					
					// find the imsss objective with the same objectiveID
					$curseqobj = null;
					for ( $j = 0; $j < count($objectives); $j++ ){
						$seqobj = $objectives[$j]['_SeqObjective'];
						if ( $seqobj->mObjID == $adlseqobjid ){
							$curseqobj = $seqobj;
							$curseqobjindex = $j;
							break;
						}			
					}
					
					// if there's a current seq then let's add the maps
					if ( $curseqobj != null ){
						//  for each adlseq map info populate that mMaps with map info in the adlseq objective
						$curseqobj = self::getADLSeqMaps($curNode, $curseqobj);
						$seqobj = $curseqobj;
						$objectives[$curseqobjindex]['_SeqObjective'] = $seqobj;
					}
					
				}
			}
		}
		// before i leave what do i have to duplicate in SeqActivity or some other class?
		// prolly just 
		$ioAct->setObjectives($objectives); 
		return $ioAct;
	}
	
	public static function getADLSeqMaps($iNode, $curseqobj){
		if (count($curseqobj->mMaps)==null) $curseqobj->mMaps = array();
		$maps = $curseqobj->mMaps;

		$children = $iNode->childNodes;
		for ($i = 0; $i < $children->length; $i++ ) {
			$curNode=$children->item($i);
	  		if ($curNode->nodeType == XML_ELEMENT_NODE) {
				if ($curNode->localName == "mapInfo") {
					$map = new SeqObjectiveMap();
					$curadltargetobjid = preg_replace('/(%20)+/', ' ', trim($curNode->getAttribute("targetObjectiveID")));
					// if the adl map target id matches an imsssssss one, then add to the imsssss one
					$matchingmapindex = -1;
					for ( $j = 0; $j < count($maps); $j++ ){
						if ($maps[$j]['_SeqObjectiveMap']->mGlobalObjID == $curadltargetobjid){
							$map = $maps[$j]['_SeqObjectiveMap'];
							$matchingmapindex = $j;
						}
					}
// tom: if default access is dependent on map existence then this will need to know if an imsss:mapInfo existed					
					$map = self::fillinADLSeqMaps($curNode, $map);
					
					if ( $matchingmapindex > -1 ){
						$c_map['_SeqObjectiveMap']=$map;
						$maps[$matchingmapindex] = $c_map;
					}
					else{
	             		$c_map['_SeqObjectiveMap']=$map;
	             		array_push($maps, $c_map);
					}
				}
	  		}
		}
		$curseqobj->mMaps = $maps;
		return $curseqobj;
	}
	
	public static function fillinADLSeqMaps($iNode, $map){
				
		if($map->mGlobalObjID == null) $map->mGlobalObjID = preg_replace('/(%20)+/', ' ', trim($iNode->getAttribute("targetObjectiveID")));

		$tempVal = $iNode->getAttribute("readRawScore");
		if($tempVal) {
			$map->mReadRawScore = self::convert_to_bool($tempVal);
        }

		$tempVal = $iNode->getAttribute("readMinScore");
		if($tempVal) {
			$map->mReadMinScore = self::convert_to_bool($tempVal);
        }
        
		$tempVal = $iNode->getAttribute("readMaxScore");
		if($tempVal) {
			$map->mReadMaxScore = self::convert_to_bool($tempVal);
        }
        
		$tempVal = $iNode->getAttribute("readCompletionStatus");
		if($tempVal) {
			$map->mReadCompletionStatus = self::convert_to_bool($tempVal);
        }
        
		$tempVal = $iNode->getAttribute("readProgressMeasure");
		if($tempVal) {
			$map->mReadProgressMeasure = self::convert_to_bool($tempVal);
        }
        
		$tempVal = $iNode->getAttribute("writeRawScore");
		if($tempVal) {
			$map->mWriteRawScore = self::convert_to_bool($tempVal);
        }

		$tempVal = $iNode->getAttribute("writeMinScore");
		if($tempVal) {
			$map->mWriteMinScore = self::convert_to_bool($tempVal);
        }
        
		$tempVal = $iNode->getAttribute("writeMaxScore");
		if($tempVal) {
			$map->mWriteMaxScore = self::convert_to_bool($tempVal);
        }
        
		$tempVal = $iNode->getAttribute("writeCompletionStatus");
		if($tempVal) {
			$map->mWriteCompletionStatus = self::convert_to_bool($tempVal);
        }
        
		$tempVal = $iNode->getAttribute("writeProgressMeasure");
		if($tempVal) {
			$map->mWriteProgressMeasure = self::convert_to_bool($tempVal);
        }
        
        return $map;
	}
	
	public static function getObjectiveMaps($iNode) {
		$tempVal = null;
	    $maps = array();
		$children = $iNode->childNodes;
		for ($i = 0; $i < $children->length; $i++ ) {
			$curNode=$children->item($i);
	  		if ($curNode->nodeType == XML_ELEMENT_NODE) {
				if ($curNode->localName == "mapInfo") {
					 $map = new SeqObjectiveMap();
					
					// Look for 'targetObjectiveID'
	               	 $tempVal = preg_replace('/(%20)+/', ' ', trim($curNode->getAttribute("targetObjectiveID")));
					 if($tempVal) {
						$map->mGlobalObjID = $tempVal;
		             }
		
		           // Look for 'readSatisfiedStatus'
	    			$tempVal = $curNode->getAttribute("readSatisfiedStatus");
					if($tempVal) {
						$map->mReadStatus = self::convert_to_bool($tempVal);
		            }
		
					// Look for 'readNormalizedMeasure'
					$tempVal = $curNode->getAttribute("readNormalizedMeasure");
					if($tempVal) {
						$map->mReadMeasure = self::convert_to_bool($tempVal);
			        }
			
			       // Look for 'writeSatisfiedStatus'
	        		$tempVal = $curNode->getAttribute("writeSatisfiedStatus");
					if($tempVal) {
						$map->mWriteStatus = self::convert_to_bool($tempVal);
			        }
			
			       // Look for 'writeNormalizedMeasure'
	        		$tempVal = $curNode->getAttribute("writeNormalizedMeasure");
					if($tempVal) {
						$map->mWriteMeasure = self::convert_to_bool($tempVal);
			        }
	                //add class
					$c_map['_SeqObjectiveMap']=$map;
					array_push($maps,$c_map);
				}
			}
		}	
		if (count($maps)==null) {
			$maps = null;
		}
		return $maps;
	}
	
	public static function getRollupRules($iNode, $ioAct) {
		$ok = true;
		$tempVal = null;
		$rollupRules = array();
		
		// Look for 'rollupObjectiveSatisfied'
      	$tempVal = $iNode->getAttribute("rollupObjectiveSatisfied");
		if($tempVal) {
			$ioAct->setIsObjRolledUp(self::convert_to_bool($tempVal));
		}
		
      // Look for 'objectiveMeasureWeight'
      	$tempVal = $iNode->getAttribute("objectiveMeasureWeight");
      	if($tempVal) {
			$ioAct->setObjMeasureWeight($tempVal);
	
		}
		// Look for 'rollupProgressCompletion'
      	$tempVal = $iNode->getAttribute("rollupProgressCompletion");
		if($tempVal) {
			$ioAct->setIsProgressRolledUp(self::convert_to_bool($tempVal));
			
		}
		$children = $iNode->childNodes;
		for ($i = 0; $i < $children->length; $i++ ) {
			$curNode=$children->item($i);
	  		if ($curNode->nodeType == XML_ELEMENT_NODE) {
				if ($curNode->localName == "rollupRule") {
					$rule = new SeqRollupRule();
			
					// Look for 'childActivitySet'
	               	$tempVal=$curNode->getAttribute("childActivitySet");
			      	if($tempVal) {
						$rule->mChildActivitySet = $tempVal;
			      	}
					// Look for 'minimumCount'
	               	$tempVal=$curNode->getAttribute("minimumCount");
					if($tempVal) {
							$rule->mMinCount = $tempVal;
				    }
				
				   // Look for 'minimumPercent'
	               	$tempVal=$curNode->getAttribute("minimumPercent");
	            	if($tempVal) {
							$rule->mMinPercent = $tempVal;
				    }
					$rule->mConditions['_SeqConditionSet'] =  new SeqConditionSet(true);
		            $conditions = array();
					$ruleInfo = $curNode->childNodes;
					for ($j = 0; $j < $ruleInfo->length; $j++ ) {
						$curRule=$ruleInfo->item($j);
						//check for element
						if ($curRule->nodeType == XML_ELEMENT_NODE) {
						  if ($curRule->localName == "rollupConditions") {
							$tempVal = $curRule->getAttribute("conditionCombination");
			               	if($tempVal) {
								$rule->mConditions['_SeqConditionSet']->mCombination = $tempVal;
						   } else {
								$rule->mConditions['_SeqConditionSet']->mCombination = COMBINATION_ANY;		
						   }
						$conds = $curRule->childNodes;
						for ($k = 0; $k < $conds->length; $k++ ) {
							$con=$conds->item($k);
							if ($con->nodeType == XML_ELEMENT_NODE) {
								if ($con->localName == "rollupCondition") {
									 $cond =  new SeqCondition();
									 // Look for 'condition'
	                                 $tempVal = $con->getAttribute("condition");
									 if($tempVal) {
										$cond->mCondition=$tempVal;
									 }
									 // Look for 'operator'
	                                  $tempVal = $con->getAttribute("operator");
									  if($tempVal) {
										if($tempVal=='not') {$cond->mNot = true;} else {$cond->mNot = false;}
									  }	
									//add class
									$c_cond['_SeqCondition'] = $cond;
									array_push($conditions,$c_cond);
								}								
							
						  }	
						}
					}	
					else if ($curRule->localName == "rollupAction") {
							$tempVal = $curRule->getAttribute("action");
							if ($tempVal) {
								$rule->setRollupAction($tempVal);
	                 }
						
				  }
				
				}
			   }
				// Add the conditions to the condition set for the rule
		         $rule->mConditions['_SeqConditionSet']->mConditions = $conditions;

	           // Add the rule to the ruleset
				//add class 
				$c_rule['_SeqRollupRule']=$rule;
				array_push($rollupRules,$c_rule);
				}
			}
		}
		 
		if ( $rollupRules != null ) {
	         $rules = new SeqRollupRuleset($rollupRules);
	         // Set the Activity's rollup rules
			 //add class
			 $c_rules['_SeqRollupRuleset']=$rules;
	         $ioAct->setRollupRules($c_rules);
	     }

		return $ioAct;
	}
   
   
	public static function getSequencingRules($iNode,$ioAct) {
		//local variables
		$ok = true;
	    $tempVal = null;

	    $preRules = array();
	    $exitRules = array();
	    $postRules = array();

		//get children
		$children = $iNode->childNodes;
		
		//find sequencing rules
		 for ($i = 0; $i < $children->length; $i++ ) {
	  		$curNode=$children->item($i);
		  	if ($curNode->nodeType == XML_ELEMENT_NODE) {
				if ($curNode->localName == "preConditionRule" || $curNode->localName == "exitConditionRule" || $curNode->localName == "postConditionRule" ) {
					$rule = new SeqRule();
	                $ruleInfo = $curNode->childNodes;
					for ($j = 0; $j < $ruleInfo->length; $j++ ){
						$curRule=$ruleInfo->item($j);
						//echo "$curRule->localName\n";
				  		if ($curRule->nodeType == XML_ELEMENT_NODE) { 
							if ($curRule->localName == "ruleConditions") {
									$rule->mConditions = self::extractSeqRuleConditions($curRule);
							}
							else if($curRule->localName == "ruleAction"){
								$tempVal=$curRule->getAttribute("action");
				               	if($tempVal) {
									$rule->mAction = $tempVal;
	                            }
							}
							
					  	}
					}//end for inner
	               	if ( $rule->mConditions != null && $rule->mAction != null ) {
						//changed from ADL Code..
						if ($curNode->localName == "preConditionRule") {
							//echo "ADD PRE";
							//add class
							$c_rule['_SeqRule'] = $rule;
	                		array_push($preRules,$c_rule);
						}
						if ($curNode->localName == "exitConditionRule") {
							//echo "ADD EXIT";
							//add class
							$c_rule['_SeqRule'] = $rule;
							array_push($exitRules,$c_rule);
						}
						if ($curNode->localName == "postConditionRule") {
							//echo "ADD POST";
							//add class
							$c_rule['_SeqRule'] = $rule;
			                array_push($postRules,$c_rule);
						}
					}
				} //end if preCondition
			
			}  //end if ELEMENT
		}
		
		if ( count($preRules) > 0 ) {
	         $rules = new SeqRuleset($preRules);
			 //add class
			 $c_rules['_SeqRuleset']=$rules;
	         $ioAct->setPreSeqRules($c_rules);
	
	    }
	
		if ( count($exitRules) > 0 ){
	         $rules = new SeqRuleset($exitRules);
			 //add class
		 	 $c_rules['_SeqRuleset']=$rules;
	         $ioAct->setExitSeqRules($c_rules);
	    }
		if ( count($postRules) > 0 ){
	        $rules = new SeqRuleset($postRules);
			//add class
		 	$c_rules['_SeqRuleset']=$rules;
         	$ioAct->setPostSeqRules($c_rules);
		}
		//echo json_encode($ioAct);
	  	
		return $ioAct;
		
	}
	
	public static function extractSeqRuleConditions($iNode) {

		$tempVal = null;
	    $condSet = new SeqConditionSet(false);

	    $conditions = array();
		$tempVal=$iNode->getAttribute("conditionCombination");
		if ($tempVal) {
		  	$condSet->mCombination=$tempVal;
		} else {
			$condSet->mCombination=COMBINATION_ALL;
		}
		$condInfo = $iNode->childNodes;
		for ($i = 0; $i < $condInfo->length; $i++ ) {
			$curCond=$condInfo->item($i);
	  		if ($curCond->nodeType == XML_ELEMENT_NODE) {
				if ($curCond->localName == "ruleCondition") {
					$cond = new SeqCondition();
					
					//look for condition
	               	$tempVal=$curCond->getAttribute("condition");
					if ($tempVal) {
						$cond->mCondition = $tempVal;
                    }

	               // Look for 'referencedObjective'
					$tempVal=preg_replace('/(%20)+/', ' ', trim($curCond->getAttribute("referencedObjective")));
	               	if ($tempVal) {
						$cond->mObjID = $tempVal;
						
	                }
	
	               // Look for 'measureThreshold'
					$tempVal=$curCond->getAttribute("measureThreshold");
					if ($tempVal) {
						$cond->mThreshold = $tempVal;
		            }
		
		           // Look for 'operator'
	    			$tempVal = $curCond->getAttribute("operator");
					if ($tempVal) {
						if ($tempVal == 'not') {
							$cond->mNot = true;
						} else {
							$cond->mNot = false;
						}
					}
					
					//add class
					$c_cond['_SeqCondition']=$cond;
					array_push($conditions,$c_cond);
					
				}
		  	}	
		}
		
		if (count($conditions)>0) {
			$condSet->mConditions = $conditions;
		} else {
			$condSet->mConditions = null;
		}
		//add class
		$c_condSet['_SeqConditionSet']=$condSet;
		return $c_condSet;
	}
   
	public static function getAuxResources($iNode, $ioAct) {
		$ok = true;
	    $tempVal = null;
        $auxRes = array();
		//get children
		$children = $iNode->childNodes;

		//find  ressources
		for ($i = 0; $i < $children->length; $i++ ) {
			$curNode=$children->item($i);
			if ($curNode->nodeType == XML_ELEMENT_NODE) {
				if ($curNode->localName == "auxiliaryResource") {
					//found it
					$res = new ADLAuxiliaryResource();
					
					// Get the resource's purpose
	               	$tempVal=$curNode->getAttribute("purpose");
					if ($tempVal) {
						$res->mType = $tempVal;
			        }
			       // Get the resource's ID
					$tempVal=preg_replace('/(%20)+/', ' ', trim($curNode->getAttribute("auxiliaryResourceID")));
					if ($tempVal) {
						$res->mResourceID = $tempVal;
				    }
					array_push($auxRes,$res);
				}
			}
		}	
		//add class
		$c_auxRes['_ADLAuxiliaryResource']=$auxRes;
		$ioAct->setAuxResources($c_auxRes);
      	return $ioAct;
	}
	
	  //helper functions
	
	  private static function convert_to_bool($string) {
		if (strtoupper($string)=="FALSE") {
			return false;
		} else {
			return true;
		}
	  }
	
	
	  private function lookupElement($iNode, $iElement){
	  	$value = null;
		$curNode = null;
		$children = null;
		
	  	if ( $iNode != null && $iElement != null ){
	  		$children = $iNode->childNodes;
	  		for ($i = 0; $i < $children->length; $i++ ) {
				$curNode = $children->item($i);
  		        if ( ($curNode->nodeType == XML_ELEMENT_NODE)) {
					if ($curNode->localName == $iElement) {
						break;
					}
				}
	    
  			}
		  	if ($curNode != null ) {
				$comp = $curNode->localName;
				if ($comp != null) {
					if ($comp != $iElement) {
						$curNode = null;
					} 
				} else {
					$curNode = null;
	            
				}
			}
         
	  	}
	  	else {
	  		//$iElement is null
	  		$curNode = $iNode;
	      }
	  
	  	if ( $curNode != null )
	      {
	  		$children = $curNode->childNodes;
	       	if ( $children != null ) {
	  			for ($i = 0; $i < $children->length; $i++ ) {
	  				$curNode = $children->item($i);
	  				 // make sure we have a 'text' element
	  	               if ( ($curNode->nodeType == XML_TEXT_NODE) ||($curNode->nodeType == XML_CDATA_SECTION_NODE) )
	  	               {
	  	                  $value = $value.$curNode->nodeValue;
	  	               }
	               }
	  		}
	      }
	  	return $value;
	  }
	  
	  
}	  //end class
	  
?>