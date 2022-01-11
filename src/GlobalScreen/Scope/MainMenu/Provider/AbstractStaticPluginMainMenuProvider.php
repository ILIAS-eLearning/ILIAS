<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Provider;

use ILIAS\GlobalScreen\Provider\PluginProvider;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class AbstractStaticPluginMainMenuProvider
 * @deprecated use AbstractStaticMainMenuPluginProvider instead. This class will be removed in ILIAS 7
 * @see        AbstractStaticMainMenuPluginProvider
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractStaticPluginMainMenuProvider extends AbstractStaticMainMenuPluginProvider implements PluginProvider, StaticMainMenuProvider
{
}
