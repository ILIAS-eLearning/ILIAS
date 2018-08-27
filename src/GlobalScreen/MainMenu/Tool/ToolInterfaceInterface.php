<?php namespace ILIAS\GlobalScreen\MainMenu\Tool;

use ILIAS\GlobalScreen\MainMenu\AsyncContentEntry;
use ILIAS\GlobalScreen\MainMenu\EntryInterface;
use ILIAS\GlobalScreen\MainMenu\ParentEntryInterface;
use ILIAS\GlobalScreen\MainMenu\TopEntryInterface;

/**
 * Interface ToolInterfaceInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ToolInterfaceInterface extends EntryInterface, ParentEntryInterface, AsyncContentEntry, TopEntryInterface {

}
