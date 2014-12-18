<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTEPEntry
 * 
 * This interface is to be fulfilled by the tep-entry-objects which are passed
 * to the historizing service as element at index 'tep_entry' in the parameter-array.
 * See mail communication of may 5th 2014 for details. It is assumed - through the 
 * absence of a helper class in the requirements paper - that all data relevant for
 * historizing are available through this object.
 *
 * Note: Only documentation hints this interface, safe to not use it. :-)
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 */
interface ilITEPEntry 
{
	public function getUsrId();
	public function getCalEntryId();
	public function getCalDerivedEntryId();
	public function getContextId();
	public function getTitle();
	public function getSubtitle();
	public function getDescription();
	public function getLocation();
	public function getFullday();
	public function getBeginDate();
	public function getEndDate();
	public function getType();
} 