In this paper, we examine the different types of arrow icons used in ILIAS and explore whether we can reduce their quantity. Our aim is to improve the user experience by organizing the arrows based on their functions and simplifying their variety to create a more consistent appearance across the platform. Additionally, our goal is to make the styling of the system easier, more efficient, and thorough.

# Issues with the current arrow types and their usage

As you navigate ILIAS, you'll come across different arrow icons in various spots, such as the main bar, breadcrumb menu, and different buttons like the action button. As we delved deeper, we discovered even more instances where arrows are used, prompting us to question their necessity.

By pinpointing and organizing these specific uses of arrows and taking usability into account, we observed that many arrows serve similar functions but appear differently. This lack of consistency can be confusing for users. Additionally, we examined how these different arrow types are implemented.

# Group 1: Navigation arrows
## Test navigation

In the test area of ILIAS where you can navigate between the test content and questions you can find these navigation buttons with the following arrow type:

![test-nav](https://files.ilias.de/images/arrows/test-navigation.png)

**Some thoughts:** The arrow icon currently used is quite bulky, taking up a significant amount of space within the button area. This disrupts the balance between the label and the icon, creating a visual imbalance. Overall, it attracts undue attention to the button, potentially diverting the user's focus away from the test content.

**Integration:** This arrow is embedded via font means as a glyphicon.

## Survey navigation

In the survey area of ILIAS where you can navigate between questions you can find these navigation buttons with the following arrow type:

![survey](https://files.ilias.de/images/arrows/survey-navigation.png)

**Some thoughts:** The arrow icon appears rather static and outdated in terms of its look and feel, likely because it's embedded in the HTML as a value-text. While both icons aim to assist navigation between questions, they are displayed quite differently from the test navigation icon.

**Integration:** This arrow is embedded via HTML value.

## Lightbox navigation

When you open up a lightbox to navigate between pictures you can find these navigation arrows:

![lightbox](https://files.ilias.de/images/arrows/lightbox-navigation.png)

**Some thoughts:** This arrow type is simple and easily recognizable by the user when it comes to its simple purpose which the icon needs to fulfill. The icon is minimalist yet easy to understand, without using embellishments such as duplicating itself or giving it an extra line in the middle.

**Integration:** This arrow is embedded via font means as a glyphicon.

## Viewcontrol navigation (example: calendar)

To navigate between certain informations such as the dates in a calendar you can find the following arrow which is very similar to the arrow in the lightbox navigation:

![viewcontrol](https://files.ilias.de/images/arrows/viewcontrol-navigation-calendar.png)

**Some thoughts:** Using a minimalistic and straightforward arrow icon for the view controls is a smart choice, ensuring it aligns seamlessly with other arrow types in the view controls area, like the dropdown icon.

**Integration:** This arrow is embedded via font means as a glyphicon.

## Results and recommendation for navigation arrows

This group can easily use a single arrow icon since all arrows are for navigation. Unlike the test and survey icons that take up a lot of space inside the button or compete with the button label, the lightbox and view controls use a simple arrow that works well everywhere. The recommendation for this group of arrows is to replace the test and survey arrows with the sleek arrow used in the lightbox and view controls sections.

# Group 2: sortation
## Descending and ascending sortation

In tables you have the opportunity to sort your results. The arrows direction — pointing up for ascending and down for descending — clearly indicates the sorting order. The following icon is being discussed:

![sortation1](https://files.ilias.de/images/arrows/sortation-descending-ascending.png)

**Some thoughts:** Clearly indicating to the user the sorting direction is a crucial and intuitive feature and also an important information that should be retained. The arrow effectively represents its intended function.

**Integration:** This arrow is embedded via font means as a glyphicon.

## Sorting alphabetically and by location

In tables and menues you also have the opportunity to sort your results by alphabet or by location. The following icon is being discussed:

![sortation2](https://files.ilias.de/images/arrows/sortation-alphabetically.png)

**Some thoughts:** This type of arrow doesn't require a specific direction, so the icon should be more adaptable, as it currently is. The arrow effectively represents its intended function.

**Integration:** The arrow is embedded via font. Stylings used: content: "⇵";

## Results and recommendation for the sortation arrows

Both icons should maintain their functionalities, whether indicating sorting direction or serving a more general purpose. A noticeable difference between the two icons is their distinct styling. While the first icon appears bulky and prominent, the second is delicate and slim. The styling should be harmonized to maintain a consistent look and feel.

# Group 3: Trees and hierarchy arrows
## Breadcrumb

When the goal is to display a clickable path and indicate the user's navigation history, breadcrumbs are used. The following arrow icon displays the direction for it:

![breadcrumb](https://files.ilias.de/images/arrows/breadcrumb.png)

**Integration:** This arrow is embedded via font means as a glyphicon.
Stylings used: font-family: "il-icons"; content: "\e606";

## Tree view

Tree views also present a clickable path and illustrate hierarchy, but here the direction is downward rather than to the right.

![tree](https://files.ilias.de/images/arrows/tree-view.png)

**Integration:** This arrow is embedded via font means as a glyphicon 
Stylings used: font-family: "il-icons"; content: "\e604";

## Other cases

Here you can see that there are some other cases where a hierarchy or a clickable path with a direction is displayed:

![hierarchy2](https://files.ilias.de/images/arrows/double-arrow.png)

![hierarchy3](https://files.ilias.de/images/arrows/double-arrow2.png)

## Results and recommendation for hierarchy/tree arrows

Both breadcrumbs and the arrows in the hierarchy and tree view serve the same function: to indicate a clickable path, whether it's to the right or downwards. Therefore, it's recommended to use a consistent icon for this purpose. To stay with the minimalistic and simple style, using the breadcrumb or the tree view icon at this point would be most appropriate.

# Group 4: Dropdowns
## Filter dropdown

Wherever filtering options are available, you'll find the filter symbol accompanied by an arrow preceding the label.:

![filter](https://files.ilias.de/images/arrows/filter-dropdown.png)

**Integration:** This arrow is embedded via font means as a glyphicon 
Stylings used: font-family: "il-icons"; content: "\e604";

## Viewcontrols dropdown caret

Expandable information is displayed as a dropdown with a caret:

![caret dropdown](https://files.ilias.de/images/arrows/viewcontrol-dropdown-caret.png)

**Integration:** This arrow is created with HTML and CSS stylings.

## Browser generated select dropdown

Also ILIAS uses browser generated dropdowns with arrows that are not easily adjustable:

![browser arrows](https://files.ilias.de/images/arrows/select-dropdown.png)

For example in "Administration" > "Layout and Styles":

![browser arrows2](https://files.ilias.de/images/arrows/select-dropdown-example.png)

**Integration:** This arrow is embedded automatically with the select-element in HTML.

## .ilc_Accordion

The .ilc_Accordion - element is used when building content for ILIAS:

![accordion](https://files.ilias.de/images/arrows/accordion.png)

**Integration:** This arrow is an SVG-file (image) which is used as an background-image (background-image: tree_col.svg).

## Results and recommendation for dropdown arrows

For dropdowns, an arrow icon isn't always essential. When it comes to accordions, there are various symbol designs to consider. A frequently used alternative is the plus icon instead of the arrow. Using a different icon instead of an arrow, as currently used in the accordion, has the advantage of reducing the risk of it being mistaken for a clickable link. Arrows pointing directly at labels can sometimes lead to confusion.

Implementing a plus icon for the .ilc-Accordion would be an intriguing option here. Not only would it introduce variety into the theme, moving away from using arrows exclusively, but it would also provide a meaningful representation of the functionality of the .ilc-Accordion.

For the expanded view, the plus icon could be rotated to give it a tilted appearance resembling an "x" for "close":

![accordion-collapse](https://files.ilias.de/images/arrows/recommendation.png)

Source: https://uxmovement.com/navigation/where-to-place-your-accordion-menu-icons/

For traditional dropdown menus, which can be visually separated from the .ilc-Accordion, the Viewcontrols dropdown caret can be recommended. This visually separates the dropdowns effectively from, for example, the hierarchy areas/trees.

# Group 5: Action button
## Different variations

There are some different versions of the action button.

Often an action button consists of a label and the caret symbol:

![action](https://files.ilias.de/images/arrows/action-button.png)

In addition to that the action button is often seen without the label in two different versions.

**Version 1: filled with a white caret:**

![filled](https://files.ilias.de/images/arrows/calendar-action-button.png)

**Version 2 - reversed: white with a blue caret:**

![not filled](https://files.ilias.de/images/arrows/course-action-button.png)

**Some thoughts:** Currently, the default button and the action button look quite similar. The default button should receive more focus and attention than the action button. This is because the action button typically includes menu settings, whereas the default button executes a specific action or task upon clicking, which should be more prominent than the action button that expands a menu. To reduce the focus on the action button, it's advisable to style it differently. For instance, using a less vibrant color scheme and aligning its design more closely with the view controls, where it primarily appears, would be beneficial.

Reducing the styling to only the reversed version of the button, arguing that it already has a slightly noticeable design, might not be advantageous for the button. This could potentially cause it to get lost next to the other viewcontrols.

# Link icon

A double arrow is used to indicate a link:

![link](https://files.ilias.de/images/arrows/link-icon.png)

**Some thoughts:** For listing links, an arrow isn't strictly necessary. Lists can be represented using other symbols, such as a bullet point. So, this is another valid scenario where we can avoid using another version of the arrow icon.

# Conclusion

We examined the various arrow icons used in ILIAS and provided recommendations for optimizing their usage to create a more consistent user interface and simplify system design.

**Navigation Arrows:** It is recommended to standardize all navigation elements with a sleek, minimalist arrow icon. The current bulky design of the test and survey navigation should be replaced with the simple and clear icon used in the lightbox and view control navigation.

**Sortation Arrows:** Sorting arrows should maintain their functionality, but their styles should be harmonized. The bulky sorting arrow should be replaced with a slimmer design that aligns with the overall visual language of the system.

**Hierarchy/Tree Arrows:** Both the breadcrumb and tree view arrows should be unified. A consistent, minimalist symbol should be used to enhance usability and visual consistency.

**Dropdown Arrows:** For dropdowns, an arrow icon is not always necessary. Instead, a plus symbol can be used, which can be rotated to an "x" for "close" when needed. This reduces confusion and improves the user experience. If a plus icon is not desired, it is advisable to standardize the icons to one downward-pointing arrow version (such as the caret which is used for the dropdown in the calendar). 

**Action Buttons:** Action buttons should be less prominent than default buttons. A reduced color scheme and a simpler design that matches the view controls are recommended. The currently used different versions should be reduced to a single, less conspicuous version.

**Link Icon:** The double arrow icon for links can be replaced with other symbols, such as bullet points, to reduce the variety of arrow icons and avoid confusion.

We are focusing on establishing a uniform visual language within ILIAS. By implementing the above recommendations, we aim to create a cohesive overall appearance for the icons. The used icon combinations will provide a simple and minimalist look yet achieve clear recognition regarding their functionality. This uniformity will enhance the user experience by making the interface more intuitive, user-friendly and visually consistent while creating an easier-to-maintain user interface.

## Next Steps

The next steps involve initiating the unification process through collaboration between a designer and a software developer. This process will include systematically addressing each section and group of icons identified in this study. For each group, we will clarify the tasks involved and replace the icons that need to be exchanged, one by one.
