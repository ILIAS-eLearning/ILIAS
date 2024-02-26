# Container - Technical Documentation

This file documents the main internal technical concepts. There is currently no external API for other components.

## Data

- **Block**: An abstract block (no items) in the container: TypeBlock, SessionBlock, OtherBlock, ItemGroupBlock
- **BlockSequencePart**: Abstract, partial sequence of blocks: TypeBlocks (all "by type" blocks), ItemGroupBlocks, ObjectivesBlocks
- **BlockSequence**: An abstract (no items) sequence of all blocks in a container view.
- **ItemBlock**: A concrete block filled with items (ref IDs). Holds information on items (ref id), block ID, embedded in page status, position, block limit exhausted status.
- **ItemBlockSequence**: The concrete sequence of blocks in a container view, filled with items.

## Manager

### ByTypeViewManager implements ViewManager (and similar)

This manager needs to be implemented by each container view that provides a separate grouping of items.

- api
  - getBlockSequence() : BlockSequence

### ItemBlockSequenceGenerator

- uses ModeManager, ItemSetManager, ilContainer
- determines ItemBlocks sequence incl. page embedded blocks and items per block
  - checks visible permission for items
  - in the case of sessions this still holds hidden "previous" and "next" sessions
- determines block sorting, item sorting is mostly done in ItemSetManager, except for item group subitems (is done also here)
- api
  - getSequence() : ItemBlockSequence

### ModeManager

- handles admin/content/ordering mode

### ItemManager

- uses ModeManager, ItemSessionRepository
- handles expand state
- handles details level

### ItemSetManager

- gets items from tree
- applies user filter
- gets complete descriptions
- applies classification filter
- groups items (by grouped repo type)
- applies sorting for all items (note that this does not work for item group subitems, these are sorted in ItemBlockSequenceGenerator)
- api
  - getRefIdsOfType()
  - getAllRefIds()
  - getRawDataByRefId()
  
### ItemPresentationManager

- uses ItemBlockSequenceGenerator, ItemSetManager
- api
  - canManageItems()
  - canOrderItems()
  - isClassificationFilterActive()
  - filteredSubtree() (should a filtered subtree be displayed due to classification filter?)
  - hasItems()
  - getItemBlockSequence()
  - getRawDataByRefId()
  - getRefIdsOfType()

## Presentation Control Flow

### (1) ilCategoryGUI extends ilContainerGUI (and similar classes)

- initialises ModeManager
- renderObject
  - getContentGUI() gets ilContainerContentGUI instance
  - -> ilContainerContentGUI::setOutput
  - outputs tabs, administration panel, "Add" dropdown, filter, "Edit Page" button, permalink

### (2) ilContainerByTypeContentGUI extends ilContainerContentGUI (and similar classes)

- uses ItemManager, ItemPresentationManager
- setOutput -> getRightColumnHTML, getCenterColumnHTML -> getMainContent -> renderItemList
- renderItemList
  - -> initRenderer() gets ilContainerRenderer instance 
  - -> ilContainerRenderer::renderItemBlockSequence(ItemPresentationManager::getItemBlockSequence());

### (3) ilContainerRenderer

- uses ItemPresentationManager, ItemRenderer, ObjectiveRenderer
- renderItemBlockSequence
  - initialises block template
  - initialises object preloader
  - iterates over ItemBlockSequence::getBlocks()
    - determine block ID and position
    - adds block with ID
    - -> ItemRenderer::renderItem()
    - adds item HTML to block
    - -> renderHelperCustomBlock, renderHelperTypeBlock (render block into block template)
  - -> renderDetails() (needed ???)
- provides closures (for the ContentGUI classes) to manipulate the output
  - used be ContainerSessionsContentGUI to show links to previous/next and hide previous/next session items
  - setBlockPrefixClosure
  - setBlockPostfixClosure
  - setItemHiddenClosure
