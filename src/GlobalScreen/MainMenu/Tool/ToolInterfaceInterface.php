<?php namespace ILIAS\GlobalScreen\MainMenu\Tool;

use ILIAS\GlobalScreen\MainMenu\AsyncContentEntryInterface;
use ILIAS\GlobalScreen\MainMenu\EntryInterface;
use ILIAS\GlobalScreen\MainMenu\ParentEntryInterface;
use ILIAS\GlobalScreen\MainMenu\TopEntryInterface;

/**
 * Interface ToolInterfaceInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ToolInterfaceInterface extends EntryInterface, ParentEntryInterface, TopEntryInterface {

}
