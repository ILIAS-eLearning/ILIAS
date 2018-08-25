<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Taxonomy/classes/class.ilTaxonomyTree.php';

/**
 * Class ilTestTaxonomyTree
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test
 */
class ilTestTaxonomyTree extends ilTaxonomyTree
{
	private $allNodes = array();
	private $maxOrderValueLength = 1;
	private $pathNodesByNodeCache = array();
	
	public function __construct($taxonomyId)
	{
		parent::__construct($taxonomyId);
		$this->readRootId();
	}
	
	public function initOrderedTreeIndex()
	{
		$this->allNodes = $this->getSubTree($this->getNodeData($this->getRootId()));
		$this->maxOrderValueLength = $this->getMaxOrderValueLength($this->allNodes);
	}
	
	public function getNodeOrderingPathString($nodeId)
	{
		$pathNodes = $this->getPathNodes($nodeId);
		
		$pathString = '';
		
		foreach($pathNodes as $n)
		{
			if( strlen($pathString) )
			{
				$pathString .= '-';
			}
			
			$pathString .= sprintf("%0{$this->maxOrderValueLength}d", (int)$n['order_nr']);
		}
		
		return $pathString;
	}
	
	protected function getPathNodes($nodeId)
	{
		if( !isset($this->pathNodesByNodeCache[$nodeId]) )
		{
			$this->pathNodesByNodeCache[$nodeId] = $this->getPathFull($nodeId);
		}
		
		return $this->pathNodesByNodeCache[$nodeId];
	}
	
	protected function getMaxOrderValueLength($nodes)
	{
		$length = 0;
		
		foreach($nodes as $n)
		{
			$l = strlen($n['order_nr']);
			
			if( $l > $length )
			{
				$length = $l;
			}
		}
		
		return $length;
	}
}