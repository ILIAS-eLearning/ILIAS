<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Service class for tests.
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @ingroup ModulesTest
 */
interface ilTestSequenceSummaryProvider
{
    public function getActiveId();

    public function getSequenceSummary($obligationsFilterEnabled = false);
}
