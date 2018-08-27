<?php namespace ILIAS\UX\MainMenu\Tool;

use ILIAS\UX\MainMenu\AsyncContentEntry;
use ILIAS\UX\MainMenu\EntryInterface;
use ILIAS\UX\MainMenu\ParentEntryInterface;
use ILIAS\UX\MainMenu\TopEntryInterface;

/**
 * Interface ToolInterfaceInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ToolInterfaceInterface extends EntryInterface, ParentEntryInterface, AsyncContentEntry, TopEntryInterface {

}
