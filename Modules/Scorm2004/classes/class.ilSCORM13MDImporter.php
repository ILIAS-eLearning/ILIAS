<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/MetaData/classes/class.ilMDXMLCopier.php';

/**
 * SCORM 13 Metadata importer
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilSCORM13MDImporter extends ilMDXMLCopier
{
	protected $manifest_dom;
	protected $metadata_found = false;
	protected $path = array();
	protected $title = ""; // overall title extracted from manifest
	protected $description = ""; // overall description extracted from manifest

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_manifest_dom, $a_obj_id)
	{
		$this->manifest_dom = $a_manifest_dom;
		$path = new DOMXpath($a_manifest_dom);
		$path->registerNamespace("ims","http://www.imsproject.org/xsd/imscp_rootv1p1p2");
		$items = $path->query("//ims:manifest/ims:metadata");
		if($items->length == 1)
		{
			foreach($items as $i)
			{
				//echo htmlentities($a_manifest_dom->saveXML($i)); exit;
				parent::__construct($a_manifest_dom->saveXML($i), $a_obj_id,$a_obj_id,ilObject::_lookupType($a_obj_id));
				$this->metadata_found = true;
			}
		}
	}
	
	/**
	 * Set title
	 *
	 * @param string $a_val title	
	 */
	function setTitle($a_val)
	{
		$this->title = $a_val;
	}
	
	/**
	 * Get title
	 *
	 * @return string title
	 */
	function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Set description
	 *
	 * @param string $a_val description	
	 */
	function setDescription($a_val)
	{
		$this->description = $a_val;
	}
	
	/**
	 * Get description
	 *
	 * @return string description
	 */
	function getDescription()
	{
		return $this->description;
	}
	
	/**
	 * Import
	 *
	 * @param
	 * @return
	 */
	function import()
	{
		if ($this->metadata_found)
		{
			$this->startParsing();
			$this->getMDObject()->update();
		}
	}

	/**
	 * handler for begin of element
	 */
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguageItem.php';
//echo "<br>BEGIN:".$a_name;
		$this->path[count($this->path)] = $a_name;

		if(!$this->getMDParsingStatus())
		{
			return;
		}

		switch($a_name)
		{
			case 'metadata':
				$this->md_in_md = true;
				$this->in_meta_data = true;
				$this->__pushParent($this->md);
				break;

			case 'general':
				$this->md_gen = $this->md->addGeneral();
				$this->md_gen->save();
				$this->__pushParent($this->md_gen);
				break;

			case 'identifier':
				$par = $this->__getParent();
				$this->md_ide = $par->addIdentifier();
				$this->md_ide->save();
				$this->__pushParent($this->md_ide);
				break;

			case 'title':
				// nothing to do here
				break;

			case 'language':
				$par = $this->__getParent();
				$this->md_lan = $par->addLanguage();
				$this->md_lan->save();
				$this->__pushParent($this->md_lan);
				break;

			case 'description':
				$par = $this->__getParent();

				if(strtolower(get_class($par)) == 'ilmdrights' or
					strtolower(get_class($par)) == 'ilmdannotation' or
					strtolower(get_class($par)) == 'ilmdclassification')
				{
					// todo
//					$par->setDescriptionLanguage(new ilMDLanguageItem($a_attribs['Language']));
				}
				else if ($this->in("general"))
				{
					$this->md_des = $par->addDescription();
					$this->md_des->save();
					$this->__pushParent($this->md_des);
				}
				break;

			case 'keyword':
				$par = $this->__getParent();
				$this->md_key = $par->addKeyword();
				$this->md_key->save();
				$this->__pushParent($this->md_key);
				break;

			// todo
			/*case 'Coverage':
				$par =& $this->__getParent();
				$par->setCoverageLanguage(new ilMDLanguageItem($a_attribs['Language']));
				break;*/

			case 'lifeCycle':
				$par = $this->__getParent();
				$this->md_lif = $par->addLifecycle();
				//$this->md_lif->setStatus($a_attribs['Status']);
				$this->md_lif->save();
				$this->__pushParent($this->md_lif);
				break;

			case 'version':
				// nothing to do here
				break;

			// todo
			/*case 'Contribute':
				$par =& $this->__getParent();
				$this->md_con =& $par->addContribute();
				$this->md_con->setRole($a_attribs['Role']);
				$this->md_con->save();
				$this->__pushParent($this->md_con);
				break;

			case 'Entity':
				$par =& $this->__getParent();

				if(strtolower(get_class($par)) == 'ilmdcontribute')
				{
					$this->md_ent =& $par->addEntity();
					$this->md_ent->save();
					$this->__pushParent($this->md_ent);
					break;
				}
				else
				{
					// single element in 'Annotation'
					break;
				}
			case 'Date':
				break;

			case 'Meta-Metadata':
				$par =& $this->__getParent();
				$this->md_met =& $par->addMetaMetadata();
				$this->md_met->setMetaDataScheme($a_attribs['MetadataScheme']);
				$this->md_met->setLanguage(new ilMDLanguageItem($a_attribs['Language']));
				$this->md_met->save();
				$this->__pushParent($this->md_met);
				break;

			case 'Technical':
				$par =& $this->__getParent();
				$this->md_tec =& $par->addTechnical();
				$this->md_tec->save();
				$this->__pushParent($this->md_tec);
				break;

			case 'Format':
				$par =& $this->__getParent();
				$this->md_for =& $par->addFormat();
				$this->md_for->save();
				$this->__pushParent($this->md_for);
				break;

			case 'Size':
				break;

			case 'Location':
				$par =& $this->__getParent();
				$this->md_loc =& $par->addLocation();
				$this->md_loc->setLocationType($a_attribs['Type']);
				$this->md_loc->save();
				$this->__pushParent($this->md_loc);
				break;

			case 'Requirement':
				$par =& $this->__getParent();
				$this->md_req =& $par->addRequirement();
				$this->md_req->save();
				$this->__pushParent($this->md_req);
				break;

			case 'OrComposite':
				$par =& $this->__getParent();
				$this->md_orc =& $par->addOrComposite();
				$this->__pushParent($this->md_orc);
				break;

			case 'Type':
				break;

			case 'OperatingSystem':
				$par =& $this->__getParent();
				$par->setOperatingSystemName($a_attribs['Name']);
				$par->setOperatingSystemMinimumVersion($a_attribs['MinimumVersion']);
				$par->setOperatingSystemMaximumVersion($a_attribs['MaximumVersion']);
				break;

			case 'Browser':
				$par =& $this->__getParent();
				$par->setBrowserName($a_attribs['Name']);
				$par->setBrowserMinimumVersion($a_attribs['MinimumVersion']);
				$par->setBrowserMaximumVersion($a_attribs['MaximumVersion']);
				break;

			case 'InstallationRemarks':
				$par =& $this->__getParent();
				$par->setInstallationRemarksLanguage(new ilMDLanguageItem($a_attribs['Language']));
				break;

			case 'OtherPlatformRequirements':
				$par =& $this->__getParent();
				$par->setOtherPlatformRequirementsLanguage(new ilMDLanguageItem($a_attribs['Language']));
				break;

			case 'Duration':
				break;

			case 'Educational':
				$par =& $this->__getParent();
				$this->md_edu =& $par->addEducational();
				$this->md_edu->setInteractivityType($a_attribs['InteractivityType']);
				$this->md_edu->setLearningResourceType($a_attribs['LearningResourceType']);
				$this->md_edu->setInteractivityLevel($a_attribs['InteractivityLevel']);
				$this->md_edu->setSemanticDensity($a_attribs['SemanticDensity']);
				$this->md_edu->setIntendedEndUserRole($a_attribs['IntendedEndUserRole']);
				$this->md_edu->setContext($a_attribs['Context']);
				$this->md_edu->setDifficulty($a_attribs['Difficulty']);
				$this->md_edu->save();
				$this->__pushParent($this->md_edu);
				break;

			case 'TypicalAgeRange':
				$par =& $this->__getParent();
				$this->md_typ =& $par->addTypicalAgeRange();
				$this->md_typ->setTypicalAgeRangeLanguage(new ilMDLanguageItem($a_attribs['Language']));
				$this->md_typ->save();
				$this->__pushParent($this->md_typ);
				break;

			case 'TypicalLearningTime':
				break;

			case 'Rights':
				$par =& $this->__getParent();
				$this->md_rig =& $par->addRights();
				$this->md_rig->setCosts($a_attribs['Cost']);
				$this->md_rig->setCopyrightAndOtherRestrictions($a_attribs['CopyrightAndOtherRestrictions']);
				$this->md_rig->save();
				$this->__pushParent($this->md_rig);
				break;

			case 'Relation':
				$par =& $this->__getParent();
				$this->md_rel =& $par->addRelation();
				$this->md_rel->setKind($a_attribs['Kind']);
				$this->md_rel->save();
				$this->__pushParent($this->md_rel);
				break;

			case 'Resource':
				break;

			case 'Identifier_':
				$par =& $this->__getParent();
				$this->md_ide_ =& $par->addIdentifier_();
				$this->md_ide_->setCatalog($a_attribs['Catalog']);
				$this->md_ide_->setEntry($a_attribs['Entry']);
				$this->md_ide_->save();
				$this->__pushParent($this->md_ide_);
				break;

			case 'Annotation':
				$par =& $this->__getParent();
				$this->md_ann =& $par->addAnnotation();
				$this->md_ann->save();
				$this->__pushParent($this->md_ann);
				break;

			case 'Classification':
				$par =& $this->__getParent();
				$this->md_cla =& $par->addClassification();
				$this->md_cla->setPurpose($a_attribs['Purpose']);
				$this->md_cla->save();
				$this->__pushParent($this->md_cla);
				break;

			case 'TaxonPath':
				$par =& $this->__getParent();
				$this->md_taxp =& $par->addTaxonPath();
				$this->md_taxp->save();
				$this->__pushParent($this->md_taxp);
				break;

			case 'Source':
				$par =& $this->__getParent();
				$par->setSourceLanguage(new ilMDLanguageItem($a_attribs['Language']));
				break;

			case 'Taxon':
				$par =& $this->__getParent();
				$this->md_tax =& $par->addTaxon();
				$this->md_tax->setTaxonLanguage(new ilMDLanguageItem($a_attribs['Language']));
				$this->md_tax->setTaxonId($a_attribs['Id']);
				$this->md_tax->save();
				$this->__pushParent($this->md_tax);
				break;
			*/

			case 'string':
				$par = $this->__getParent();
				if ($this->in("general"))
				{
					if ($this->in("title"))
					{
						$par->setTitleLanguage(new ilMDLanguageItem($a_attribs['language']));
						$par->update();
					}
					else if ($this->in("description"))
					{
						$this->md_des->setDescriptionLanguage(new ilMDLanguageItem($a_attribs['language']));
						$this->md_des->update();
					}
					else if ($this->in("keyword"))
					{
						$this->md_key->setKeywordLanguage(new ilMDLanguageItem($a_attribs['language']));
						$this->md_key->update();
					}
				}
				if ($this->in("lifeCycle"))
				{
					if ($this->in("version"))
					{
						$par->setVersionLanguage(new ilMDLanguageItem($a_attribs['language']));
					}
				}

				break;

		}
	}

	/**
	 * handler for end of element
	 */
	function handlerEndTag($a_xml_parser,$a_name)
	{
//echo "<br>End TAG: ".$a_name;
		unset($this->path[count($this->path) - 1]);

		if(!$this->getMDParsingStatus())
		{
			return;
		}
		switch($a_name)
		{
			case 'metadata':
				$this->md_parent = array();
				$this->md_in_md = false;
				$this->in_meta_data = false;
				break;

			case 'general':
				$par = $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'identifier':
				$par = $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'title':
				// nothing to do here
				break;

			case 'language':
				$par = $this->__getParent();
				$par->setLanguage(new ilMDLanguageItem($this->__getCharacterData()));
				$par->update();
				$this->__popParent();
				break;

			case 'description':
				$par = $this->__getParent();
				if(strtolower(get_class($par)) == 'ilmddescription')
				{
					$this->__popParent();
					break;
				}
				else
				{
					// todo
//					$par->setDescription($this->__getCharacterData());
					break;
				}

			case 'keyword':
				$this->__popParent();
				break;

			// todo
			/*case 'Coverage':
				$par =& $this->__getParent();
				$par->setCoverage($this->__getCharacterData());
				break;*/

			case 'lifeCycle':
				$par = $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'version':
				// nothing to do here
				break;


			// todo
			/*case 'Contribute':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'Entity':
				$par =& $this->__getParent();

				if(strtolower(get_class($par)) == 'ilmdentity')
				{
					$par->setEntity($this->__getCharacterData());
					$par->update();
					$this->__popParent();
				}
				else
				{
					// Single element in 'Annotation'
					$par->setEntity($this->__getCharacterData());
				}
				break;

			case 'Date':
				$par =& $this->__getParent();
				$par->setDate($this->__getCharacterData());
				break;

			case 'Meta-Metadata':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'Technical':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'Format':
				$par =& $this->__getParent();
				$par->setFormat($this->__getCharacterData());
				$par->update();
				$this->__popParent();
				break;

			case 'Size':
				$par =& $this->__getParent();
				$par->setSize($this->__getCharacterData());
				break;

			case 'Location':
				$par =& $this->__getParent();
				$par->setLocation($this->__getCharacterData());
				$par->update();
				$this->__popParent();
				break;

			case 'Requirement':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'OrComposite':
				$this->__popParent();
				break;

			case 'Type':
				break;

			case 'OperatingSystem':
				break;

			case 'Browser':
				break;

			case 'InstallationRemarks':
				$par =& $this->__getParent();
				$par->setInstallationRemarks($this->__getCharacterData());
				break;

			case 'OtherPlatformRequirements':
				$par =& $this->__getParent();
				$par->setOtherPlatformRequirements($this->__getCharacterData());
				break;

			case 'Duration':
				$par =& $this->__getParent();
				$par->setDuration($this->__getCharacterData());
				break;

			case 'Educational':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'TypicalAgeRange':
				$par =& $this->__getParent();
				$par->setTypicalAgeRange($this->__getCharacterData());
				$par->update();
				$this->__popParent();
				break;

			case 'TypicalLearningTime':
				$par =& $this->__getParent();
				$par->setTypicalLearningTime($this->__getCharacterData());
				break;

			case 'Rights':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'Relation':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'Resource':
				break;

			case 'Identifier_':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'Annotation':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'Classification':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'TaxonPath':
				$par =& $this->__getParent();
				$par->update();
				$this->__popParent();
				break;

			case 'Taxon':
				$par =& $this->__getParent();
				$par->setTaxon($this->__getCharacterData());
				$par->update();
				$this->__popParent();
				break;

			case 'Source':
				$par =& $this->__getParent();
				$par->setSource($this->__getCharacterData());
				break;
			*/

			case 'string':
				$par = $this->__getParent();
				if ($this->in("general"))
				{
					if ($this->in("title"))
					{
						// set packaget title to first title
						if ($this->getTitle() == "")
						{
							$this->setTitle($this->__getCharacterData());
						}

						// parent is General here
						$par->setTitle($this->__getCharacterData());
						$par->update();
						//echo "<br>setTitle (".get_class($par)."): ".$this->__getCharacterData();
					}
					if ($this->in("description"))
					{
						// set packaget title to first title
						if ($this->getDescription() == "")
						{
							$this->setDescription($this->__getCharacterData());
						}

						$this->md_des->setDescription($this->__getCharacterData());
						$this->md_des->update();
					}
					if ($this->in("keyword"))
					{
						$par->setKeyword($this->__getCharacterData());
						$par->update();
					}
				}
				if ($this->in("lifeCycle"))
				{
					if ($this->in("version"))
					{
						$par->setVersion($this->__getCharacterData());
					}
				}
				break;

			case 'value':
				$par = $this->__getParent();
				if ($this->in("general"))
				{
					if ($this->in("structure"))
					{
						$map = array (
							"atomic" => "Atomic",
							"collection" => "Collection",
							"networked" => "Networked",
							"linear" => "Linear",
							"hierarchical" => "Hierarchical"
						);
						if (isset($map[$this->__getCharacterData()]))
						{
							// parent is General here
							$par->setStructure($map[$this->__getCharacterData()]);
							$par->update();
						}
					}
				}
				if ($this->in("lifeCycle"))
				{
					if ($this->in("status"))
					{
						$map = array (
							"draft" => "Draft",
							"revised" => "Revised",
							"unavailable" => "Unavailable",
							"final" => "Final"
						);
						if (isset($map[$this->__getCharacterData()]))
						{
							$this->md_lif->setStatus($map[$this->__getCharacterData()]);
							$this->md_lif->update();
						}
					}
				}
				break;

			case 'catalog':
				if ($this->in("identifier"))
				{
					$this->md_ide->setCatalog($this->__getCharacterData());
					$this->md_ide->update();
				}
				break;

			case 'entry':
				if ($this->in("identifier"))
				{
					$this->md_ide->setEntry($this->__getCharacterData());
					$this->md_ide->update();
				}
				break;

		}
		$this->md_chr_data = '';
	}

	/**
	 *
	 *
	 * @param
	 * @return
	 */
	function in($a_name)
	{
//		echo "<br>"; var_dump($this->path);
		if (in_array($a_name, $this->path))
		{
			return true;
		}
		return false;
	}

}

?>