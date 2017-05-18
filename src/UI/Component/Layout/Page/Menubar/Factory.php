<?php
namespace ILIAS\UI\Component\Layout\Page\Menubar;
/**
 * This is the factory for page-layout components
 */
interface Factory {

    /**
     * ---
     * description:
     *   purpose: >
     *     Entries, or better, the entry-area of the menubar, hold all top-level
     *     entires of the menubar.
     *
     *   composition: >
     *     Entries is a collection of buttons.
     *     While a button _could_ trigger navigation to a repository object,
     *     menubar-items will usually open a slate when clicked.
     *
     *
     * rules:
     *   usage:
     *
     *   interaction:
     *     1: Buttons SHOULD trigger a slate.
     *
     *   composition:
     *     1: There MUST be exactly one Entries-element in the menubar.
     *     1: Entries MUST NOT contain items other than buttons.
     *     1: Entries MUST contain at least one button.
     *     3: Entries SHOULD NOT contain more than five buttons.
     *
     * ----
     *
     * @return  \ILIAS\UI\Component\Layout\Page\Menubar\Entries
     */
    public function entries();


    /**
     * ---
     * description:
     *   purpose: >
     *     The slate is a page-area within the menubar; it acts as an enhanced
     *     fly-out menu for menubar-entries.
     *
     *   composition: >
     *     The slate can hold a large variety of components. These can be
     *     (further) navigational entries, forms, text and images or combinations of those.
     *
     *
     *   effect: >
     *     When triggered, the slate opens on the right hand of the menubar,
     *     between menubar and content, thus "pushing" the content to the right.
     *     The slate will allways have a "close"-button at its bottom.
     *     The contents of a slate can vary heavily:
     *     A search form, the repository tree, contextual help, further navigation via buttons, etc.
     *
     *
     *
     *   rivals: >
     *
     * rules:
     *   usage:
     *     1: There MUST be but one slate visible on the page.
     *     2: >
     *        The contents of a slate CAN navigate immanently within the slate
     *        w/o changing context
     *
     *   accessibility:
     *     1: The slate SHOULD be triggered by a menubar-entry
     *
     * ----
     *
     * @return  \ILIAS\UI\Component\Layout\Page\Menubar\Slate
     */
    public function slate();
}
