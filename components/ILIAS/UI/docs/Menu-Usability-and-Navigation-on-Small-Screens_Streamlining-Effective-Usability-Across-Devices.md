# ILIAS Menu Usability and Navigation on Small Screens: Streamlining Effective Usability Across Devices

This analysis focuses on enhancing menu usability and navigation in ILIAS, with primary attention given to narrow screen devices such as smartphones and tablets. At the same time, it addresses design and interaction issues that affect users on wider screens whenever similar usability challenges arise, especially in layouts or menu structures that may influence both contexts.

## Main Menu Structure and Navigation in ILIAS

ILIAS features a hierarchical main menu that organizes essential platform functions such as the repository, personal workspace, communications, and administrative options. The menu is typically located on the left side of the screen on wider displays, grouping related items into expandable sections to provide structured access to available features.

Navigation within the ILIAS main menu involves selecting top-level categories that reveal nested submenus for more specific content and functions. On smaller screens, the menu shifts to the bottom of the viewport, showing three to four primary entries on smartphones and eight to ten entries on tablets, while additional entries are concealed behind a "more" button. Activating this button opens a menu displaying all further main menu entries.

The repository navigation uses an expandable tree structure. Expand and collapse icons indicate whether a node contains hidden sub-nodes. Users click these icons to show or hide sub-nodes. Further, clicking the text of a node opens that node's content in the viewport. The tree allows hierarchical organization and browsing of repository content.

Breadcrumb navigation supports user orientation by displaying the current location within the platform hierarchy. On wider screens, the full hierarchical breadcrumb path is visible and positioned beneath the header and above the object icon and title. On smaller screens, the breadcrumbs are nested within the header and accessed through a drop-down.

## Usage Issues and Challenges on small screens

### Main Menu Usability Challenges

Critical navigation and location elements are missing from the main menu, particularly on narrow screens. Users expect context-sensitive navigation elements such as close icons and clear indicators of their position. The design currently leads to reduced recognizability and usability.

The current main menu on small screens, such as smartphones, displays three to four primary entries. Further entries are grouped under a "more" button. When this button is activated, a menu opens with the additional items positioned at the top of the screen, which is far from the original trigger and point of interaction.

Additionally, there is limited visual separation between menu buttons, and users cannot rely on hover effects on mobile devices to confirm their selection. Long text labels, especially when combined with small font sizes, further reduce the clarity and readability of menu options.

![ILIAS mobile view with main bar at bottom](https://files.ilias.de/images/navigation-and-menu-usability/bottom-main-menu.png)
![ILIAS mobile view with activated more button, showing the slate menu](https://files.ilias.de/images/navigation-and-menu-usability/bottom-main-menu-opened.png)
![ILIAS mobile view with activated communications button, showing communication sub-node menu](https://files.ilias.de/images/navigation-and-menu-usability/bottom-main-menu-communication.png)

*The main menu shows four primary entries plus a "more" button. There is no visual separation between the buttons. In English, the labels remain single spaced; in German, they are partly double spaced, which creates visual confusion.*
*Clicking the "more" button opens the main bar slate, where additional primary menu entries are displayed. The entries are positioned at the top, far from the interaction trigger, disrupting the user workflow. No intuitive way exists to close the menu. When navigating into a category, neither the main menu nor the slate indicate how to close the menu, which node the user is in, or how to navigate back.* 

*Retrieved November 06, 2025, from [docu.ilias.de](https://www.docu.ilias.de)*

### Breadcrumb Visibility and Usability Issues

On narrow screens, the placement of breadcrumbs differs from the version used on wider screens. Users described this as not intuitive. When object titles are medium or longer in length, the arrow icon may become hidden and the title truncated, making it more difficult to read and identify. The right arrow icon was not recognized as a navigation control for revealing the breadcrumb path in a drop-down. The lowest entry in drop-down menus was interpreted as representing the previous node, reflecting desktop conventions for the most recent location.

![ILIAS mobile view with breadcrumb in header](https://files.ilias.de/images/navigation-and-menu-usability/mobile-breadcrumb-closed.png)
![ILIAS mobile view with breadcrumb opened in header and seeing breadcrumb path in drop-down menu](https://files.ilias.de/images/navigation-and-menu-usability/mobile-breadcrumb-opened.png)

*The breadcrumb is placed in an unusual position within the header and due to the title length, the icon is not displayed, obscuring the drop-down function, which significantly complicates its usability; additionally, the title appears truncated, with both the beginning and the end not fully visible. The listing appears reversed because the bottom entry is associated with the previous node.* 

*Retrieved November 06, 2025, from [docu.ilias.de](https://www.docu.ilias.de)*

### Current Challenges in the Tree View Design

The tree view can appear cluttered when the hierarchy includes many nodes and sub-nodes, making it further difficult for users to scan and understand the overall structure. Further, clickable elements like text labels and icons are often small, reducing the ease of tapping on touch screens. When many sub-nodes are expanded, users can additionally lose track of their current position in the structure. The layout also lacks sufficient whitespace, which further reduces readability and visual comfort.

![ILIAS mobile view with snippet from the tree view](https://files.ilias.de/images/navigation-and-menu-usability/Tree-view.png)
![tap size of tree view entry](https://files.ilias.de/images/navigation-and-menu-usability/tap-size.png)

*The tree view becomes cluttered due to the deep hierarchy with many nodes and sub-nodes. There is limited whitespace, which reduces readability. The touch target height is 24px, making it difficult to select, especially for smaller fingers.* 

*Retrieved November 06, 2025, from [docu.ilias.de](https://www.docu.ilias.de)*

## Goals: Enhancing Usability and Streamlining Navigation

The goals of the analysis are to streamline navigation and interface design for small screen and wide screen devices in ILIAS to enhance usability. 

The analysis aims to:

- Make navigation elements, such as menus and breadcrumbs, consistently clear across device sizes.
- Reduce visual clutter and simplify access to core functions by reorganizing menus.
- Improve orientation by providing visible indicators for location and hierarchy.
- Ensure touch-friendly and accessible UI elements, particularly for mobile and tablet devices.
- Maintain readability by optimizing labels, icons, and whitespace.
- Support users in accomplishing tasks effectively on all device types and screen sizes.

With the goals outlined above in mind, we will now review best practices and industry standards for navigation, breadcrumb, and menu structures to inform improvements and ensure alignment with established usability principles.

## UI/UX Research and Industry Best Practices

### Industry Standards and Best Practices for Mobile Website Menu Placement and Usage 

#### Introduction

Industry standards for mobile website menu placement and usage emphasize balancing navigation accessibility with the need to preserve space for content on small screens. The choice of navigation pattern should be guided by the website or app type, the number of available options, and typical user behavior. The most common patterns include Navigation Bars and Tab Bars, Hamburger Menus and Navigation Hubs. Navigation Hubs are best suited for single-purpose implementations and can therefore be excluded in the ILIAS context.

### Tab Bars and Navigation Bars

#### Examples:

![Reddit app view, showing bottom tab bar, top navigation bar and a picture of a small kitten](https://files.ilias.de/images/navigation-and-menu-usability/reddit.png)

*The Reddit app features a header and a persistent bottom tab bar. The header includes a hamburger menu for accessing the main navigation, a user icon linking to personal settings, a search function, and controls for switching between different content views. Both the header and the bottom tab bar automatically hide when the user scrolls.*

*Retrieved November 11, 2025, from [Reddit App](https://www.reddit.com)*

![Pinterest app view, showing bottom tab bar, top navigation bar, and four different pictures](https://files.ilias.de/images/navigation-and-menu-usability/pinterest.png)

*The Pinterest app includes a persistent bottom tab bar and a personalized top navigation bar. As the user scrolls, the top navigation bar dynamically compresses, displaying only the titles and removing thumbnail images.*

*Retrieved November 11, 2025, from [Pinterest App](https://www.pinterest.com)*

![Spotify app view, showing bottom tab bar, top navigation bar and a selection of artists and albums](https://files.ilias.de/images/navigation-and-menu-usability/spotify.png)

*The Spotify app features a persistent bottom tab bar and a header. Within the header, users can access and customize their personal settings through the profile icon. It also provides options for selecting and managing different types of audio content.*

*Retrieved November 11, 2025, from [Spotify App](https://www.spotify.com)*

#### Best for: 

Shallow hierarchies with a small number of main navigation options. These are top-level navigation elements that provide immediate access to key sections of an app or website.

#### Placement and Behavior: 

Navigation bars commonly appear at the top of the screen and may disappear upon scrolling to maximize content visibility. Tab bars are typically persistent and especially popular in mobile apps. They are usually placed at the bottom of the screen for easy thumb reach.

#### Advantages:

- Provide quick, visible, persistent access to the primary areas or functions of the website or app.
- Clearly show the user's current location within the top-level sections via visual highlights such as color changes, bolder icons, or underlines.
- Support rapid switching between main sections with minimal taps.
- Enable intuitive and consistent navigation.

#### Disadvantages:

- Tab bars support typically four to five top-level items on smartphones, and up to eight to ten on tablets, due to limited space and necessary touch-target sizes.
- These bars occupy permanent screen space, reducing the visible content area, particularly when both tab bars and navigation bars or other persistent UI elements are used simultaneously.
- Tab bars highlight the active top-level section but do not show the user's location within deeper sub-levels; additional UI elements like breadcrumbs or secondary navigation are required for effective hierarchy clarity.

#### Hierarchy Fit: 

Ideal for simple, broad information architectures where users mostly navigate among high-level categories or main functions without extensive drilling. Examples include social media apps (e.g. Reddit, Pinterest), music player apps (e.g. Spotify), or simple ecommerce apps (e.g. Etsy) with few core sections.

### Hamburger Menus

#### Examples:

![Amazon website on a small screen view displaying a header with a hamburger menu for navigation, the brand icon, a username with access to profile and settings, a shopping cart icon, a search bar, and a horizontal category bar below.](https://files.ilias.de/images/navigation-and-menu-usability/amazon-top.png)

*The Amazon small-screen website layout features a header with navigation hidden behind a hamburger menu, the brand icon, user profile and settings accessible via the username, a shopping cart icon, a search bar, and a horizontal category bar. None of these menu elements are sticky and disappear when the user scrolls.*

*Retrieved November 11, 2025, from [amazon.de](https://www.amazon.de)*

![Codecademy website on a small screen showing a header with a brand icon, search and notification icons, and a hamburger menu for main navigation.](https://files.ilias.de/images/navigation-and-menu-usability/codeacademy-top.png)

*On narrow screens, the Codecademy website features a compact header including a brand icon linking to the homepage, a search icon that expands to a search interface, a notification icon providing access to messages, and a hamburger menu that consolidates the main navigation entries. A button below the header opens the personal home menu as an overlay. All menus are sticky.*

*Retrieved November 11, 2025, from [codeacademy.com](https://www.codeacademy.com)*

![Salesforce website on a small screen showing a sticky header with a hamburger menu, brand icon, personal icon, and a utility search bar.](https://files.ilias.de/images/navigation-and-menu-usability/salesforce-top.png)

*The Salesforce website on narrow screens features a sticky header that includes a hamburger menu with the main navigation options, a brand icon linking to the homepage and a personal icon for accessing user settings. Positioned below is a utility search bar that opens a conversational interface powered by generative AI.*

*Retrieved November 11, 2025, from [salesforce.com](https://www.salesforce.com)*

#### Best for: 

Complex, deep hierarchies with many menu or navigation options and sub-levels. Well suited to content-heavy, browse-focused apps or websites where users explore a wide range of categories and options.

#### Placement and Behavior: 

Hidden behind a three-line "hamburger" icon, typically placed in the top right corner or, if additional functions like user management exist, the top left corner. Menu or navigation options are revealed only after user interaction.

#### Advantages:

- Saves space by hiding large or complex navigation structures, keeping the interface clean and uncluttered.
- Accommodates numerous navigation items and multiple levels without overwhelming the main screen.
- Provides access to a comprehensive menu while preserving visible screen space for content.

#### Disadvantages:

- Reduces discoverability as options are hidden and require explicit action to open.
- Slows navigation due to extra taps needed to reveal and select options.
- Offers limited contextual awareness, showing only broad categories; users can lose track within deep submenus.
- May be overlooked by users unfamiliar with the icon or pattern.

#### Hierarchy Fit: 

Works best for applications or sites with complex, multi-level architectures where many menu or navigation items exist. It suits environments such as large ecommerce stores (e.g. Amazon), extensive educational platforms (e.g. Codeacademy, Udemy), or enterprise websites (e.g. Salesforce) with many features., where secondary and advanced options need to be accessible without cluttering the main workspace. In these scenarios, the hamburger menu helps maintain focus on core content while housing an extensive navigation system behind a simple, space-saving icon.

### Summary Table

| Navigation Pattern | Best for | Advantages | Disadvantages |  Hierarchy |
| :--- | :--- | :--- | :--- | :--- |
| Navigation / Tab Bar | Few (4-5) main top-level options | Persistent, visible, fast top-level access | Limited number of options, takes space | Shallow, broad hierarchies |
| Hamburger Menu | Many options, deep hierarchies | Saves space, manages complex navigation | Hidden, less discoverable, slower navigation | Deep, multi-level, complex hierarchies |

---

### Industry Standards and Best Practices for Drill-Down and Accordion Menus on Narrow Screens

#### Introduction:

Drill-down menus are navigation elements that allow users to access nested levels of content by revealing one level at a time. Accordion menus, on the other hand, present a set of sections that expand to reveal nested options when a section header is activated. Both patterns are designed to organize choices, help users discover available paths, and reduce clutter by hiding less-relevant options until the user interacts with the menu.

### Drill-Down Menus

#### Examples:

![School of Advanced Professional Studies mobile view showing a drill-down hamburger menu with current nodes and subnodes visually highlighted.](https://files.ilias.de/images/navigation-and-menu-usability/drill-down-example-saps.gif)

*On narrow screens, the School of Advanced Professional Studies website uses a drill-down navigation design. The hamburger menu is positioned in the top left corner of the header (UX research shows that users most often expect the main navigation in this location) and opens as a drop-down close to the menu trigger. The menu is additionally labeled "Menü", which improves visibility and accessibility. Each menu item contains one button that opens the content level of the selected node and another that reveals its subordinate nodes. The different functions are visually distinguished, which makes the functionality easier to understand.* 
*When users navigate to deeper levels, reopening the hamburger menu displays the active node along with its subnodes. As users move back up through the hierarchy, the nodes and paths leading to the currently open level are visually highlighted.*

*Retrieved November 11, 2025, from [uni-ulm.de](https://www.uni-ulm.de/einrichtungen/saps/)*

![University of Hamburg mobile view showing a drill-down hamburger menu displaying the active node and its subnodes.](https://files.ilias.de/images/navigation-and-menu-usability/drill-down-example-uni.gif)

*Like the SAPS website, the University of Hamburg website implements a drill-down navigation structure on narrow screens. The menu is positioned in the top right corner of the header (although UX research shows that users most often expect it on the left side) and opens close to the menu trigger as an off-canvas menu. Each menu item includes a button that opens the content level of the selected node and another that reveals its subordinate nodes. The different functions are visually distinguished, which makes the functionality easier to understand, although the contrast between these functions could be made more apparent.* 
*When users access deeper navigation levels and reopen the hamburger menu, it displays the active node along with its subnodes, maintaining contextual orientation within the hierarchy. However, when navigating back, the nodes leading to the current one are not highlighted.*

*Retrieved November 13, 2025, from [uni-hamburg.de](https://www.uni-hamburg.de)*

![University of Bern mobile view showing a drill-down hamburger menu displaying the active node and its subnodes.](https://files.ilias.de/images/navigation-and-menu-usability/drill-down-example-unibe.gif)

*Like the two previous examples, the University of Bern website implements a drill-down navigation structure on narrow screens. The hamburger menu is placed in a thumb-friendly position in the bottom left corner of a tab bar; however, it is not located in the expected position within the header, where users typically anticipate finding the main navigation. Each menu item includes a button that opens the content level of the selected node and another that reveals its subordinate nodes. The different functions are visually distinguished, which makes the functionality easier to understand, although the contrast between these functions could be made more apparent.* 
*When users access deeper navigation levels and reopen the hamburger menu, it displays the active node along with its subnodes, maintaining contextual orientation within the hierarchy. However, when navigating back, the nodes leading to the current one are not highlighted.*

*Retrieved November 13, 2025, from [unibe.ch](https://www.unibe.ch)*

#### Best for:

Drill-down menus should be used when the information architecture is hierarchical and users benefit from exploring content progressively rather than being presented with all options at once. They are also effective for dashboards, reports, and applications where managing context and reducing cognitive load per screen is important, especially on mobile or tablet devices.

#### Advantages:

- Simplifies navigation of complex, nested structures by presenting one hierarchy level at a time.
- Reduces information overload and cognitive load by focusing the user on relevant selections at each stage.
- Works well on small screens due to its compact, stepwise interface.
- Maintains context and orientation with clear stepwise progression and back-navigation controls.

#### Disadvantages:

- Slower navigation for power users or frequent tasks, as multiple levels must be accessed sequentially.
- Can require more user actions (clicks/taps) to reach deep content.
- May confuse users unfamiliar with the hierarchy or when structure is not clearly communicated.
- Difficult to gain an overview of all available options at once, possibly increasing the chance of missing options or paths.

#### Hierarchy Fit:

Drill-down menus are best suited for navigating deep or multi-level hierarchies, where content or options are organized in nested layers. This pattern excels when users need to explore one level of information at a time, such as moving from category to subcategory to detail. Drill-down is ideal for websites, menus or dashboards with complex structures - like analytics platforms, detailed directories, or enterprise resource planning tools - where clarity and control at each step matter most.

### Accordion Menus

#### Examples:

![Pittroff website mobile view showing a drop-down nested accordion hamburger menu with multiple entries.](https://files.ilias.de/images/navigation-and-menu-usability/accordion-example-pittroff.gif)

*Retrieved November 13, 2025, from [pittroff.de](https://www.pittroff.de)*

*On narrow screens, the Pittroff website uses a nested accordion navigation that expands as drop-down from the hamburger menu. The menu is positioned in the top right corner of the header (although UX research shows that users most often expect it on the left side) and opens close to the menu trigger. Each menu item with sub-nodes includes one button to open the selected content level and another to reveal its subordinate nodes. If a menu item has no sub-nodes, only the button to open its content level is displayed.* 
*When users navigate into deeper menu levels, reopening the hamburger menu shows the main navigation. On the top level, the current node location is not indicated. It becomes visually emphasized only when the parent level of that node is expanded.*

![MDN website mobile view showing an off-canvas nested accordion hamburger menu with multiple entries.](https://files.ilias.de/images/navigation-and-menu-usability/accordion-example-MDN.gif)

*Retrieved November 13, 2025, from [developer.mozilla.org](https://www.developer.mozilla.org/de/)*

*Like the Pittroff website, the MDN website uses a nested accordion navigation that expands from the hamburger menu on narrow screens. The menu is positioned in the top right corner of the header (although UX research shows that users most often expect it on the left side) and opens close to the menu trigger. Each menu item functions as an accordion header that reveals its subordinate nodes. When an accordion is opened, the previously opened one closes. This improves clarity on small screens but prevents users from scanning multiple options at once.*
*An additional navigation menu is integrated into the breadcrumb bar, designed specifically for navigating the learning content. This menu also follows a nested accordion structure that visually highlights the current node within the overview.*

![TH OWL website mobile view showing an overlay nested accordion hamburger menu with multiple entries.](https://files.ilias.de/images/navigation-and-menu-usability/accordion-example-thowl.gif)

*Like the two previous examples, the TH-OWL website uses a nested accordion navigation that expands from the hamburger menu on narrow screens. The hamburger menu is placed in a thumb-friendly position in the bottom right corner of a tab bar; however, it is not located in the expected position within the header, where users typically anticipate finding the main navigation. In addition, it does not open directly at the trigger point but instead appears in the upper half of the screen, which makes the thumb-friendly placement of the tab bar redundant. Each menu item containing sub-nodes includes one button to open the selected content level and another to reveal its subordinate nodes. If a menu item has no sub-nodes, only the button to access its content level is displayed. However, the distinction between these two functions is not immediately visible, as the visual differentiation appears only through the :focus and :hover states—both of which are not perceivable on mobile devices.*
*When reopening the hamburger menu, the top-level entry appears first. Scrolling down reveals the nodes leading to the current level emphasized, with the active node also visually highlighted. Open nodes are indicated by a darkened background, which helps with orientation but offers only limited accessibility from the third sublevel onward, as the contrast ratio is 3.41:1.*

*Retrieved November 13, 2025, from [th-owl.de](https://www.th-owl.de)*

#### Best for:

Accordion menus should be used when content needs to be explored across single or multiple levels, with nested options revealed progressively. They suit hierarchical content, side navigation, or sections where users may expand or collapse groups to compare or skim without leaving the current view. To keep readability on narrow screens, the number of simultaneously expanded sections should be limited to prevent clutter or unwieldy layouts. UX research indicates that nested accordions should be limited to a maximum of two to three levels.

#### Advantages:

- Enables progressive disclosure of content, allowing users to focus on one section at a time.
- Familiar in contexts like FAQs, settings groups, and multi-level navigation.
- Supports keyboard-friendly interactions and can be designed for good accessibility.
- Improves scannability by grouping related options and reducing immediate cognitive load when designed with clear labeling.

#### Disadvantages:

- Because previous nodes and their subnodes remain visible as navigation deepens, the overview becomes cluttered and hard to scan, making navigation unwieldy, especially when multiple node levels are expanded simultaneously.
- Requires clear labeling to prevent users from losing track of nested options.
- Performance and responsiveness can degrade with very deep or large hierarchies on some devices.
- Deep nesting in accordions hampers discoverability and increases interaction cost, making it impractical to access content across many levels and often leading to user disengagement.

#### Hierarchy Fit:

Accordion menus are well suited for shallow to moderately deep hierarchies or grouped content, where users benefit from expanding relevant sections to view nested options. They work best when content can be divided into collapsible groups, such as product categories with subcategories or settings that are naturally expandable. 

### Summary Table

| Pattern |	Hierarchy Fit |	Best For |	Advantages |	Disadvantages |
| :--- | :--- | :--- | :--- | :--- |
| Drill-Down Menu |	Deep, multi-level hierarchies |	Stepwise navigation through complex, nested info; dashboards; mobile screens |	Simplifies complex navigation; reduces overload; works on mobile; maintains context	| Slower for frequent access; more actions needed; limited overview; can confuse users |
| Accordion Menu | Shallow to moderately deep hierarchies | Expandable sections; side navigation; progressive disclosure without leaving the view | Flexible: shows/hides sections; good for FAQs and grouped settings; accessible when implemented well | Can become cluttered if many sections expanded; unwieldly in deep and large hierarchies; labeling and organization are crucial |

---

### Industry Standards and Best Practices for Breadcrumbs on Narrow Screens

#### Introduction:

Breadcrumbs are a secondary navigation aid commonly used on content-rich websites or apps with multiple hierarchical levels. They provide a clickable trail, usually displayed near the top of a page, that shows the user's path within the site structure. Each element in the breadcrumb trail represents a parent page or category, allowing users to understand their current location and quickly return to higher-level sections with a single tap or click.

#### Examples:

![Whirlpool website showing the breadcrumb bar functionality.](https://files.ilias.de/images/navigation-and-menu-usability/full-example-whirlpool.png)

![Whirlpool website mobile view showing the breadcrumb bar functionality.](https://files.ilias.de/images/navigation-and-menu-usability/breadcrumb-example-whirlpool.gif)

*On narrow screens, the breadcrumb of the Whirlpool website shows a blur effect on the left side that fades out part of the breadcrumb trail. The current node is visually distinguished from the others, while the preceding nodes are clearly identifiable as clickable elements through an underline. The focus is placed on the current node and the directly preceding ones.*

*Retrieved November 18, 2025, from [whirlpool.com](https://www.whirlpool.com)*

![SDU website showing the breadcrumb bar functionality.](https://files.ilias.de/images/navigation-and-menu-usability/full-example-sdu.png)

![SDU website mobile view showing the breadcrumb bar functionality.](https://files.ilias.de/images/navigation-and-menu-usability/breadcrumb-example-sdu.gif)

*On narrow screens, the SDU website's breadcrumb is truncated, displaying only the immediate parent node. This design omits the current page node from the breadcrumb trail, forcing users to rely on the main viewport page title for orientation. The navigation focuses exclusively on the single preceding step, visually represented by a left-pointing chevron.*

*Retrieved November 18, 2025, from [sdu.dk](https://www.sdu.dk/en)*

![eBay website showing the breadcrumb bar functionality.](https://files.ilias.de/images/navigation-and-menu-usability/full-example-ebay.png)

![eBay website mobile view showing the breadcrumb bar functionality.](https://files.ilias.de/images/navigation-and-menu-usability/breadcrumb-example-ebay.gif)

*The eBay breadcrumb employs a consistent truncation strategy across all screen sizes. It collapses intermediate path nodes into a dropdown menu.*
*The visible portion dynamically adjusts to available space, prioritizing the current node (which is visually distinct) and the immediate preceding nodes. In cases of severe space constraint or long titles, the display is reduced to a minimum of the Home link, the direct parent, and the current page node.*

*Retrieved November 18, 2025, from [ebay.de](https://www.ebay.de)*

#### Placement and Behaviour:

Breadcrumbs are typically placed at the top of the page, right below the main navigation or header. They are displayed as a horizontal sequence of text links separated by symbols (such as "/" or ">"), visually mapping out the user's path through the site hierarchy. Breadcrumbs function as secondary navigation - they never replace the main navigation but supplement it, especially on complex sites. Only the previous steps in the breadcrumb trail are clickable; the current page is shown as plain text for clarity and accessibility.

#### Advantages:

- Breadcrumbs give users a clear sense of their current location within the site or app's structure.
- They allow fast navigation to higher-level pages with a single click or tap, reducing the number of steps needed to move around.
- Breadcrumbs are helpful for new visitors or users entering from quick links or search engines, who might land deep within the site.
- They help reduce bounce rates by providing better orientation and encouraging further exploration.
- Breadcrumbs can support SEO by clarifying site structure for search engines.

#### Disadvantages:

- Breadcrumbs add little or no value on simple or flat sites with only one or two levels.
- Long breadcrumb trails may clutter the top of the screen, especially on mobile; truncation or collapsing may be needed.
- If breadcrumb trails do not accurately represent the site's hierarchy, they can confuse rather than help users.
- Mislabeling or inconsistent formatting can undermine usability.

#### Do's

- Use breadcrumbs as a secondary navigation aid, not a replacement for your main menu.
- Place them just below the primary navigation or at the top of the content area, and keep their appearance minimal.
- Start the breadcrumb trail with a link to the homepage.
- Use simple, clear labels to represent each page.
- Make each step clickable, except for the current page, which should be non-clickable and visually distinct.
- Always reflect the actual hierarchy to avoid misleading users.
- For mobile devices, shorten or collapse long breadcrumb trails, and ensure links are touch-friendly.
- Use semantic HTML and proper accessibility attributes for screen readers.

#### Don'ts

- Don't use breadcrumbs as your only or primary navigation system.
- Don't make the current page in the breadcrumb trail a link.
- Don't skip or hide hierarchy levels or link to non-existent categories.
- Don't include breadcrumbs on shallow, single-level sites.
- Don't crowd the interface with overly prominent or redundant breadcrumb trails.
- Don't ignore user testing - ensure your breadcrumbs actually help users navigate.


## Possible Solutions to Enhance Navigation and Menu Usability in ILIAS

### Main Menu Usability Challenges

The small screen main menu currently limits visible entries and groups others behind a "more" button, with the additional menu opening far from the trigger point. It further lacks sufficient visual separation between buttons and clear close or navigation aids, resulting in confusion and inefficient navigation.

Drawing from design principles emphasizing spatial consistency, touch-friendly sizing, and visible hierarchy cues, the following solutions are proposed:

- Place a hamburger menu icon in the header that opens a side drawer (Slate) on small screens; this drawer remains hidden by default to maximize screen space and is accessed by tapping the hamburger icon.
- Keep the menu and its entries anchored near the trigger button to maintain spatial context and minimize cognitive dissonance from distant menu placement.
- Design the side drawer to be vertically scrollable, allowing users to view all main menu items without truncation or overflow.
- Use clear visual separation and sufficient padding between menu items, ensuring touch targets are large enough to prevent selection errors.
- Include persistent, easily visible dismiss and back buttons within the menu panel to enable seamless dismissal and hierarchical navigation.
- Employ responsive typography and icon resizing to maintain readability and visual clarity across different devices.
- Ensure full support for keyboard navigation and screen readers by implementing semantic HTML and appropriate ARIA roles within the menu.
- Organize menu items using drill-down functionality to allow users to expand only the sections they need, minimizing cognitive load.
- Highlight the current menu location clearly with visual indicators to keep users oriented within the navigation hierarchy.

### Breadcrumb Visibility and Usability Issues

Breadcrumbs, essential for hierarchical orientation, are currently embedded in the header on small screens, where their usability is compromised by overlapping titles, icon truncation, and confusing drop-down behavior. This placement conflicts with the principle of consistent, unobtrusive, yet visible navigation paths.

Based on accessibility and usability standards, the following approach is recommended:

- Place breadcrumbs directly below the main header and above the page title or main content area on both wide and narrow screens, ensuring they don't overlap or compete visually with important elements.
- Use a simple, horizontal breadcrumb structure with clear separators such as ">" or "/" that visually express the hierarchy path.
- Ensure that seperators are large enough and have high contrast for visual recognition.
- Implement ellipsis truncation on longer breadcrumb trails on small screens, showing only the first, last, and possibly one intermediate item, with an accessible expansion option to reveal full paths.
- Replace drop-down-style breadcrumb expansion (which has accessibility issues) with expand-on-tap inline expansions or tooltips, improving discoverability and usability without hiding content.
- Ensure that only parent breadcrumb links are clickable, marking the current page as plain, non-interactive text with aria-current="page" for screen readers.
- Adapt breadcrumb length and layout responsively to maintain legibility across screen sizes.
- Use consistent breadcrumb ordering from the home page on the left to the current page on the right.
- Test breadcrumbs with real users on actual devices to identify truncation issues or usability confusion.
- Prioritize semantic HTML and ARIA landmarks to improve screen reader navigation and compliance with accessibility standards.

### Current Challenges in the Tree View Design

In the tree view, excessive node expansions and small touch targets diminish readability and user orientation in the deep repository hierarchy. This creates clutter and interaction difficulties, especially on mobile.

Applying core principles of progressive disclosure, responsive design, and accessibility, the following solutions are outlined:

- Avoid requiring users to keep deeply nested trees fully expanded by implementing a collapsible tree structure with drill-down functionality.
- Display breadcrumb paths or location indicators within or immediately above the tree view to continuously communicate the user's current position in the hierarchy.
- Increase spacing, padding and optimize whitespace in the tree layout to create larger, touch-friendly areas and prevent the interface from feeling cramped or visually cluttered, thereby enhancing readability and interaction comfort.
- Conduct usability testing of the tree view across a range of devices and user scenarios to ensure adequate touch target sizes, legibility, and manageable cognitive load.
- Implement progressive disclosure by loading and rendering child nodes only when expanded, improving performance and reducing on-screen clutter.
- Supplement the tree view with an incremental search or filter function that enables users to directly find nodes, minimizing the need for manual navigation through deep hierarchies.

Sources

1. [Acclaim Agency - The Future of Mobile Navigation: Hamburger Menus vs Tab Bars](https://acclaim.agency/blog/the-future-of-mobile-navigation-hamburger-menus-vs-tab-bars)  
2. [Aditus: Accessible Accordion](https://www.aditus.io/patterns/accordion/)  
3. [AppInstitute - Best Practices for Mobile App Navigation Menus](https://appinstitute.com/best-practices-for-mobile-app-navigation-menus/)  
4. [Baymard Institute - Drop-Down Usability - Drop-Down UX](https://baymard.com/blog/drop-down-usability)  
5. [Baymard Institute - Homepage & Navigation UX Best Practices 2025](https://baymard.com/blog/ecommerce-navigation-best-practice)  
6. [Best Practices for Designing Breadcrumbs for Mobile](https://www.telerik.com/blogs/best-practices-designing-breadcrumbs-mobile)  
7. [Brillmark - Mobile Navigation Menu A/B Testing Examples](https://www.brillmark.com/mobile-navigation-menu-a-b-testing-examples/)  
8. [Cieden - What should I know before creating a dropdown design?](https://cieden.com/book/atoms/dropdown/dropdown-design)  
9. [Codimite - Creating Intuitive Navigation: Best Practices for UX Design](https://codimite.ai/blog/creating-intuitive-navigation-best-practices-for-ux-design/)  
10. [CreativeCorner Studio - 13 Must-See Mega Menu Examples for 2025](https://www.creativecorner.studio/blog/mega-menu-examples)  
11. [Crocoblock: WordPress Accordion Menu UI/UX: Basics and Best Practices](https://crocoblock.com/blog/wordpress-accordion-menu-ui-ux/)  
12. [DesignCloud - 6 Tips for Better Mobile UI Design](https://designcloud.app/blog/big-ideas-for-small-screens-6-tips-for-better-mobile-ui-design-)  
13. [Design Shack - Mega Menus Revisited: UX Best Practices in 2025](https://designshack.net/articles/ux-design/mega-menus-ux/)  
14. [Divimode - 7 Stellar Mega Menu Design Examples for 2025](https://divimode.com/mega-menu-design-examples/)  
15. [Dorik - Hamburger Menu Alternatives](https://dorik.com/blog/hamburger-menu-alternatives)  
16. [DreamHost - Mega Menu Design: 9 Tips To Improve UX and SEO](https://www.dreamhost.com/blog/mega-menu-design/)  
17. [Eleken - 20 Accordion UI Examples and Best Practices for Better UX](https://www.eleken.co/blog-posts/accordion-ui)  
18. [Eleken - Dropdown Menu UI: Best Practices and Real-World Examples](https://www.eleken.co/blog-posts/dropdown-menu-ui)  
19. [Elementor - Hamburger Menus: Examples & Best Practices](https://elementor.com/blog/hamburger-menus-examples-best-practices/)  
20. [Excellent Web Check - Best practices for Mobile Breadcrumbs [+3 great examples]](https://excellentwebcheck.com/blogs/best-practices-for-mobile-friendly-breadcrumb-navigation)  
21. [FasterCapital - Prioritizing UI/UX for the Smallest Screens](https://www.fastercapital.com/content/Prioritizing-UI-UX-for-the-Smallest-Screens.html)  
22. [Flux Academy - 7 Website Navigation Best Practices With Examples](https://www.flux-academy.com/blog/7-website-navigation-best-practices-with-examples)  
23. [Happy Addons - 12+ Well-Designed Mega Menu Examples](https://happyaddons.com/mega-menu-examples/)  
24. [Horizon: Accordion](https://horizon.servicenow.com/workspace/components/now-accordion)  
25. [Infinum - Best UX Pattern for a Website Navigation With Dropdown Menus](https://infinum.com/blog/website-navigation-dropdown-menus/)  
26. [Interaction Design Foundation - Hamburger Menu UX](https://www.interaction-design.org/literature/article/hamburger-menu-ux)  
27. [Interaction Design Foundation (IxDF) - Mobile Breadcrumbs: 8 Best Practices in UX](https://www.interaction-design.org/literature/article/mobile-breadcrumbs)  
28. [Justinmind - Breadcrumb Website Design: Best Practices and Examples](https://www.justinmind.com/ui-design/breadcrumb-website-examples)  
29. [Justinmind - Dropdown Menu Design: Guidelines and Examples for Web and Mobile](https://www.justinmind.com/ui-design/drop-down-menu-ux-best-practices-examples)  
30. [Justinmind - Hamburger Menu UI Design](https://www.justinmind.com/ui-design/hamburger-menu)  
31. [Justinmind - Mobile Navigation Blog](https://www.justinmind.com/blog/mobile-navigation/)  
32. [Learning Atheros - How to Design a Better App Profile Menu](https://learning.atheros.ai/blog/how-to-a-design-better-app-profile-menu)  
33. [LevelAccess - Accessible Navigation Menus: Pitfalls and Best Practices](https://www.levelaccess.com/blog/accessible-navigation-menus-pitfalls-and-best-practices/)  
34. [LinkedIn - Top UX Research Best Practices for Navigation Experience](https://www.linkedin.com/advice/3/what-top-ux-research-best-practices-improving-navigation-7eqjf)  
35. [LogRocket Blog - Designing effective accordion UIs: Best practices for UX and implementation.](https://blog.logrocket.com/ux-design/accordion-ui-design/)  
36. [LogRocket Blog - Here’s How I’d Design a Mega Menu — with 3 Great Examples](https://blog.logrocket.com/ux-design/mega-menu-design-examples/)  
37. [Material Design - Menus (Drop-down menu guidelines)](https://m2.material.io/components/menus)  
38. [Nielsen Norman Group - Accordions: 5 Scenarios to Avoid Them](https://www.nngroup.com/videos/avoid-accordions/)  
39. [Nielsen Norman Group - Accordions on Desktop: When and How to Use.](https://www.nngroup.com/articles/accordions-on-desktop/)  
40. [Nielsen Norman Group - Breadcrumbs: 11 Design Guidelines for Desktop and Mobile](https://www.nngroup.com/articles/breadcrumbs/)  
41. [Nielsen Norman Group - Findability vs. Discoverability](https://www.nngroup.com/videos/findability-vs-discoverability/)  
42. [Nielsen Norman Group - Hamburger Menu Icon Update](https://www.nngroup.com/videos/hamburger-menu-icon-update/)  
43. [Nielsen Norman Group - Hamburger Menus and Hidden Navigation Hurt UX Metrics](https://www.nngroup.com/articles/hamburger-menus/)  
44. [Nielsen Norman Group - Menu-Design Checklist: 17 UX Guidelines](https://www.nngroup.com/articles/menu-design/)  
45. [Nielsen Norman Group - Navigation Menus - 5 Tips to Make Them Visible](https://www.nngroup.com/videos/navigation-menu-visibility/)  
46. [PageOneFormula - Mobile Navigation Patterns That Improve User Engagement](https://pageoneformula.com/mobile-navigation-patterns-that-improve-user-engagement/)  
47. [PixelFreeStudio Blog - Best Practices for Mobile-First Navigation Menus](https://blog.pixelfreestudio.com/best-practices-for-mobile-first-navigation-menus/#aioseo-the-importance-of-mobile-first)  
48. [Prototypr: Designing Accordions: Best practices.](https://blog.prototypr.io/designing-the-accordion-best-practices-3c1bd54bf26e)  
49. [Salsa Digital: Accordion UI design examples: inspiration, tips, and best practices](https://salsa.digital/insights/accordion-ui-design-examples-inspiration-tips-and-best-practices)  
50. [Semantic Scholar - Understanding Single Handed Mobile Device Usage](https://www.semanticscholar.org/paper/Understanding-Single-Handed-Mobile-Device-Karlson-Bederson/67702fe26fabeaf28df44837ea28d153fce3c1b3?p2df)  
51. [Smashing Magazine - Designing Navigation for Mobile: Design Patterns and Best Practices](https://www.smashingmagazine.com/2022/11/navigation-design-mobile-ux/)  
52. [The Good - Mobile Breadcrumbs Design Guidelines](https://thegood.com/insights/mobile-breadcrumbs/)  
53. [The Good - Mobile Design Menu Insights](https://thegood.com/insights/mobile-design-menu/)  
54. [The Plus Addons - 8 Best Mega Menu Examples in 2025 [with How to Guide]](https://theplusaddons.com/blog/best-mega-menu-examples/)  
55. [Tilipman Digital - 22 Mega Menu Examples for B2B & E-Commerce (2025)](https://www.tilipmandigital.com/resource-center/articles/mega-menu-examples)  
56. [User-Friendly Mega-Dropdowns: When Hover Menus Fail](https://www.smashingmagazine.com/2021/05/frustrating-design-patterns-mega-dropdown-hover-menus/)  
57. [Userpilot - How to Reduce Screen Complexity](https://userpilot.com/blog/how-to-reduce-screen-complexity/)  
58. [UXCel - Breadcrumbs Best Practices Course Lesson](https://app.uxcel.com/courses/ui-components-best-practices/breadcrumbs-best-practices-269)  
59. [UX Movement - Why Mobile Menus Belong at the Bottom of the Screen](https://uxmovement.com/mobile/why-mobile-menus-belong-at-the-bottom-of-the-screen/)  
60. [UX Patterns - Breadcrumb - UX Patterns](https://uxpatterns.dev/patterns/navigation/breadcrumb)  
61. [UXPin Studio - Mobile Navigation Examples](https://www.uxpin.com/studio/blog/mobile-navigation-examples/)  
62. [UX Planet - 4 Things to Keep In Mind While Designing for Small Screens](https://uxplanet.org/4-things-to-keep-in-mind-while-designing-for-small-screens-6524becfaff5)  
63. [UX Tools - UX Design for Navigation Menus](https://www.uxtools.co/blog/ux-design-for-navigation-menus)
64. [Web Content Accessibility Guidelines (WCAG) 2.2](https://www.w3.org/TR/WCAG22/)
65. [We Are Tenet - Website Navigation Best Practices 2025 (15 Do's & 9 Don'ts)](https://www.wearetenet.com/blog/website-navigation-best-practices)  
66. [Web Designer Depot - Breadcrumbs Are Dead in Web Design](https://webdesignerdepot.com/breadcrumbs-are-dead-in-web-design/)  
67. [Webstacks - 7 Mega Menu Examples with Exceptional UX Design](https://www.webstacks.com/blog/mega-menu-examples)  
68. [Webstacks - Mobile Navigation Menu Design](https://www.webstacks.com/blog/mobile-navigation-menu-design)
