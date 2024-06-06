In this paper, we examine the different types of arrow icons used in ILIAS and explore whether we can reduce their quantity. Our aim is to improve the user experience by organizing the arrows based on their functions and simplifying their variety to create a more consistent appearance across the platform. Additionally, our goal is to make the styling of the system easier, more efficient, and thorough.

# Issues with the current arrow types and their usage

As you navigate ILIAS, you'll come across different arrow icons in various spots, such as the main bar, breadcrumb menu, and different buttons like the action button. As we delved deeper, we discovered even more instances where arrows are used, prompting us to question their necessity.

By pinpointing and organizing these specific uses of arrows and taking usability into account, we observed that many arrows serve similar functions but appear differently. This lack of consistency can be confusing for users. Additionally, we examined how these different arrow types are implemented.

# Group 1: Navigation arrows
## Test navigation

In the test area of ILIAS where you can navigate between the test content and questions you can find these navigation buttons with the following arrow type:

![test-nav](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/75186f3e-ede7-4816-98e5-f150cdbaf7bc)

**Some thoughts:** The arrow icon currently used is quite bulky, taking up a significant amount of space within the button area. This disrupts the balance between the label and the icon, creating a visual imbalance. Overall, it attracts undue attention to the button, potentially diverting the user's focus away from the test content.

**Integration:** This arrow is embedded via font means as a glyphicon.

## Survey navigation

In the survey area of ILIAS where you can navigate between questions you can find these navigation buttons with the following arrow type:

![survey](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/ff17ef89-841b-45de-928d-9ef874c326d1)

**Some thoughts:** The arrow icon appears rather static and outdated in terms of its look and feel, likely because it's embedded in the HTML as a value-text. While both icons aim to assist navigation between questions, they are displayed quite differently from the test navigation icon.

**Integration:** This arrow is embedded via HTML value.

## Lightbox navigation

When you open up a lightbox to navigate between pictures you can find these navigation arrows:

![lightbox](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/ba89b5b2-fb2e-4e3f-b575-fee2caba5681)

**Some thoughts:** This arrow type is simple and easily recognizable by the user when it comes to its simple purpose which the icon needs to fulfill. The icon is minimalist yet easy to understand, without using embellishments such as duplicating itself or giving it an extra line in the middle.

**Integration:** This arrow is embedded via font means as a glyphicon.

## Viewcontrol navigation (example: calendar)

To navigate between certain informations such as the dates in a calendar you can find the following arrow which is very similar to the arrow in the lightbox navigation:

![viewcontrol](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/d640a28e-3256-4c21-a829-bff586b915a9)

**Some thoughts:** Using a minimalistic and straightforward arrow icon for the view controls is a smart choice, ensuring it aligns seamlessly with other arrow types in the view controls area, like the dropdown icon.

**Integration:** This arrow is embedded via font means as a glyphicon.

## Results and recommendation for navigation arrows

This group can easily use a single arrow icon since all arrows are for navigation. Unlike the test and survey icons that take up a lot of space inside the button or compete with the button label, the lightbox and view controls use a simple arrow that works well everywhere. The recommendation for this group of arrows is to replace the test and survey arrows with the sleek arrow used in the lightbox and view controls sections.

# Group 2: sortation
## Descending and ascending sortation

In tables you have the opportunity to sort your results. The arrows direction — pointing up for ascending and down for descending — clearly indicates the sorting order. The following icon is being discussed:

![sortation1](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/2a7ac378-ece6-4215-8095-770a8583b478)

**Some thoughts:** Clearly indicating to the user the sorting direction is a crucial and intuitive feature and also an important information that should be retained. The arrow effectively represents its intended function.

**Integration:** This arrow is embedded via font means as a glyphicon.

## Sorting alphabetically and by location

In tables and menues you also have the opportunity to sort your results by alphabet or by location. The following icon is being discussed:

![sortation2](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/9fa5bd95-3d51-4bc1-8876-74e932546518)

**Some thoughts:** This type of arrow doesn't require a specific direction, so the icon should be more adaptable, as it currently is. The arrow effectively represents its intended function.

**Integration:** The arrow is embedded via font. Stylings used: content: "⇵";

## Results and recommendation for the sortation arrows

Both icons should maintain their functionalities, whether indicating sorting direction or serving a more general purpose. A noticeable difference between the two icons is their distinct styling. While the first icon appears bulky and prominent, the second is delicate and slim. The styling should be harmonized to maintain a consistent look and feel.

# Group 3: Trees and hierarchy arrows
## Breadcrumb

When the goal is to display a clickable path and indicate the user's navigation history, breadcrumbs are used. The following arrow icon displays the direction for it:

![breadcrumb](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/3a9719ae-e952-4d3b-a9e1-842a13028ae5)

**Integration:** This arrow is embedded via font means as a glyphicon.
Stylings used: font-family: "il-icons"; content: "\e606";

## Tree view

Tree views also present a clickable path and illustrate hierarchy, but here the direction is downward rather than to the right.

![tree](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/1cdae5a7-a0f9-46a2-862b-d32e46900e8b)

**Integration:** This arrow is embedded via font means as a glyphicon 
Stylings used: font-family: "il-icons"; content: "\e604";

## Other cases

Here you can see that there are some other cases where a hierarchy or a clickable path with a direction is displayed:

![hierarchy2](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/1ade0ec2-6ee4-4866-8c2d-886f7feae034)

![hierarchy3](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/26721056-4ef6-4fe3-b124-d15c215df87c)

## Results and recommendation for hierarchy/tree arrows

Both breadcrumbs and the arrows in the hierarchy and tree view serve the same function: to indicate a clickable path, whether it's to the right or downwards. Therefore, it's recommended to use a consistent icon for this purpose. To stay with the minimalistic and simple style, using the breadcrumb or the tree view icon at this point would be most appropriate.

# Group 4: Dropdowns
## Filter dropdown

Wherever filtering options are available, you'll find the filter symbol accompanied by an arrow preceding the label.:

![filter](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/d6ad2da3-fe8b-43bd-8fa2-076f5b44641e)

**Integration:** This arrow is embedded via font means as a glyphicon 
Stylings used: font-family: "il-icons"; content: "\e604";

## Viewcontrols dropdown caret

Expandable information is displayed as a dropdown with a caret:

![caret dropdown](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/8cf35fcd-7515-4ea2-b9c6-93ca02c1463f)

**Integration:** This arrow is created with HTML and CSS stylings.

## Browser generated select dropdown

Also ILIAS uses browser generated dropdowns with arrows that are not easily adjustable:

![browser arrows](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/c2617015-e16b-4702-93ad-d30f650d4853)

For example in "Administration" > "Layout and Styles":

![browser arrows2](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/71ec017a-fc85-4cd9-9523-16abf6b7f20f)

**Integration:** This arrow is embedded automatically with the select-element in HTML.

## .ilc_Accordion

The .ilc_Accordion - element is used when building content for ILIAS:

![accordion](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/1fd0796c-a08c-4be4-81ec-a4170f1bed57)

**Integration:** This arrow is an SVG-file (image) which is used as an background-image (background-image: tree_col.svg).

## Results and recommendation for dropdown arrows

For dropdowns, an arrow icon isn't always essential. When it comes to accordions, there are various symbol designs to consider. A frequently used alternative is the plus icon instead of the arrow. Using a different icon instead of an arrow, as currently used in the accordion, has the advantage of reducing the risk of it being mistaken for a clickable link. Arrows pointing directly at labels can sometimes lead to confusion.

For the expanded view, the plus icon could be rotated to give it a tilted appearance resembling an "x" for "close":

![accordion-collapse](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/dcd9dee2-eb6c-43f0-beda-e5dfb1a02ffd)

Source: https://uxmovement.com/navigation/where-to-place-your-accordion-menu-icons/

Alternatively, if a plus icon is not desired, it is advisable to standardize the icons to one downward-pointing arrow version (such as the caret which is used for the dropdown in the calendar).

# Group 5: Action button
## Different variations

There are some different versions of the action button.

Often an action button consists of a label and the caret symbol:

![action](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/4af58d9f-9751-4e86-9fd0-23e3f29a6691)

In addition to that the action button is often seen without the label in two different versions.

**Version 1: filled with a white caret:**

![filled](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/27c6a088-518b-4bc8-8520-81b0edee76e8)

**Version 2 - reversed: white with a blue caret:**

![not filled](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/b9e57b7c-efac-4f1e-ab25-86d21b7ea1e1)

**Some thoughts:** Currently, the default button and the action button look quite similar. The default button should receive more focus and attention than the action button. This is because the action button typically includes menu settings, whereas the default button executes a specific action or task upon clicking, which should be more prominent than the action button that expands a menu. To reduce the focus on the action button, it's advisable to style it differently. For instance, using a less vibrant color scheme and aligning its design more closely with the view controls, where it primarily appears, would be beneficial.

Reducing the styling to only the reversed version of the button, arguing that it already has a slightly noticeable design, might not be advantageous for the button. This could potentially cause it to get lost next to the other viewcontrols.

# Link icon

A double arrow is used to indicate a link:

![link](https://github.com/alinaappel/ILIAS-Dokumentation/assets/167314349/f2281cae-1e7b-4d0d-88ee-567a272d7e49)

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
