<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestTaxonomyFilterLabelTranslater
{
	/**
	 * @var ilDB
	 */
	private $db = null;

	private $taxonomyTreeIds = null;
	private $taxonomyNodeIds = null;

	private $taxonomyTreeLabels = null;
	private $taxonomyNodeLabels = null;

	/**
	 * @param ilDB $db
	 */
	public function __construct(ilDB $db)
	{
		$this->db = $db;

		$this->taxonomyTreeIds = array();
		$this->taxonomyNodeIds = array();

		$this->taxonomyTreeLabels = array();
		$this->taxonomyNodeLabels = array();
	}

	public function loadLabels(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$this->collectIds($sourcePoolDefinitionList);

		$this->loadTaxonomyTreeLabels();
		$this->loadTaxonomyNodeLabels();
	}

	private function collectIds(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		foreach($sourcePoolDefinitionList as $definition)
		{
			/** @var ilTestRandomQuestionSetSourcePoolDefinition $definition */

			$this->taxonomyTreeIds[] = $definition->getMappedFilterTaxId();
			$this->taxonomyNodeIds[] = $definition->getMappedFilterTaxNodeId();
		}
	}

	private function loadTaxonomyTreeLabels()
	{
		$IN_taxIds = $this->db->in('obj_id', $this->taxonomyTreeIds, false, 'integer');

		$query = "
			SELECT		obj_id tax_tree_id,
						title tax_tree_title

			FROM		object_data

			WHERE		$IN_taxIds
			AND			type = %s
		";

		$res = $this->db->queryF($query, array('text'), array('tax'));

		while( $row = $this->db->fetchAssoc($res) )
		{
			$this->taxonomyTreeLabels[ $row['tax_tree_id'] ] = $row['tax_tree_title'];
		}
	}

	private function loadTaxonomyNodeLabels()
	{
		$IN_nodeIds = $this->db->in('tax_node.obj_id', $this->taxonomyNodeIds, false, 'integer');

		$query = "
					SELECT		tax_node.obj_id tax_node_id,
								tax_node.title tax_node_title

					FROM		tax_node

					WHERE		$IN_nodeIds
				";

		$res = $this->db->query($query);

		while( $row = $this->db->fetchAssoc($res) )
		{
			$this->taxonomyNodeLabels[ $row['tax_node_id'] ] = $row['tax_node_title'];
		}
	}

	public function getTaxonomyTreeLabel($taxonomyTreeId)
	{
		return $this->taxonomyTreeLabels[$taxonomyTreeId];
	}

	public function getTaxonomyNodeLabel($taxonomyTreeId)
	{
		return $this->taxonomyNodeLabels[$taxonomyTreeId];
	}

	public function loadLabelsFromTaxonomyIds($taxonomyIds)
	{
		$this->taxonomyTreeIds = $taxonomyIds;

		$this->loadTaxonomyTreeLabels();
	}

}