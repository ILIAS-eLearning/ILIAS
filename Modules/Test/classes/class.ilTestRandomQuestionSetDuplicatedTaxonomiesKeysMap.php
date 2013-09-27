<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestRandomQuestionSetDuplicatedTaxonomiesKeysMap
{
	/**
	 * @var array
	 */
	private $taxonomyKeyMap = array();

	/**
	 * @var array
	 */
	private $taxNodeKeyMap = array();

	/**
	 * @param integer $originalTaxonomyId
	 * @param integer $mappedTaxonomyId
	 * @param array $nodeKeyMapping
	 */
	public function addDuplicatedTaxonomy($originalTaxonomyId, $mappedTaxonomyId, $nodeKeyMappingArray)
	{
		$this->taxonomyKeyMap[$originalTaxonomyId] = $mappedTaxonomyId;

		foreach($nodeKeyMappingArray as $originalNodeId => $mappedNodeId)
		{
			$this->taxNodeKeyMap[$originalNodeId] = $mappedNodeId;
		}
	}

	/**
	 * @param integer $originalTaxonomyId
	 * @return integer
	 */
	public function getMappedTaxonomyId($originalTaxonomyId)
	{
		return $this->taxonomyKeyMap[$originalTaxonomyId];
	}

	/**
	 * @param integer $originalTaxNodeId
	 * @return integer
	 */
	public function getMappedTaxNodeId($originalTaxNodeId)
	{
		return $this->taxNodeKeyMap[$originalTaxNodeId];
	}
}