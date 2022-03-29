<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCloudPluginListGUI
 * Abstract class working as base for ilCloudPluginItemCreationListGUI and ilCloudPluginActionListGUI
 * @author  Timon Amstutz timon.amstutz@ilub.unibe.ch
 * @author  Martin Studer martin@fluxlabs.ch
 * @version $Id$
 * @ingroup ModulesCloud
 */
abstract class ilCloudPluginListGUI extends ilCloudPluginGUI
{
    protected ?ilcloudFileNode$node = null;

    abstract protected function addItemsBefore(): void;

    abstract protected function addItemsAfter(): void;
}
