<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Component\Panel;

use ILIAS\UI\Component\Component;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *      Standard Panels are used in the center content section to group content.
     *   composition: >
     *      Standard Panels consist of a title and a content section. The
     *      structure of this content might be varying from Standard
     *      Panel to Standard Panel. Standard Panels may contain View Controls and Sub Panels.
     *   rivals:
     *      Cards: >
     *        Often Cards are used in Decks to display multiple uniformly structured chunks of Data horizontally and vertically.
     *
     * rules:
     *   usage:
     *      1: In Forms Standard Panels MUST be used to group different sections into Form Parts.
     *      2: Standard Panels SHOULD be used in the center content as primary Container for grouping content of varying content.
     *
     *   composition:
     *      1: Standard Panels MAY contain a Section View Control to change the current presentation of the content.
     *      2: Standard Panels MAY contain a Pagination View Control to display data in chunks.
     *      3: Standard Panels MAY have a Sortation View Control to perform ordering actions to the presented data.
     * ---
     * @param string $title
     * @param Component[]|Component
     * @return \ILIAS\UI\Component\Panel\Standard
     */
    public function standard(string $title, $content): Standard;

    /**
     * ---
     * description:
     *   purpose: >
     *       Sub Panels are used to structure the content of Standard panels further into titled sections.
     *   composition: >
     *       Sub Panels consist of a title and a content section. They may contain either a Card or a Secondary Panel on
     *       their right side to display meta information about the content displayed.
     *   rivals:
     *      Standard Panel: >
     *        The Standard Panel might contain a Sub Panel.
     *      Card: >
     *        The Sub Panels may contain one Card.
     *      Secondary Panel: >
     *        The Sub Panels may contain one Secondary Panel.
     * rules:
     *   usage:
     *      1: Sub Panels MUST only be inside Standard Panels
     *   composition:
     *      1: Sub Panels MUST NOT contain Sub Panels or Standard Panels as content.
     * ---
     * @param string $title
     * @param Component[]|Component
     * @return \ILIAS\UI\Component\Panel\Sub
     */
    public function sub(string $title, $content): Sub;

    /**
     * ---
     * description:
     *   purpose: >
     *       Report Panels display user-generated data combining text in lists, tables and sometimes  charts.
     *       Report Panels always draw from two distinct sources: the structure / scaffolding of the Report Panels
     *       stems from user-generated content (i.e a question of a survey, a competence with levels) and is
     *       filled with user-generated content harvested by that very structure (i.e. participants’ answers to
     *       the question, self-evaluation of competence).
     *   composition: >
     *       They are composed of a Standard Panel which contains several Sub
     *       Panels. They might also contain
     *       a card to display information meta information in their first block.
     *   effect: >
     *       Report Panels are predominantly used for displaying data. They may however comprise links or buttons.
     *   rivals:
     *      Standard Panels: >
     *        The Report Panels contains sub panels used to structure information.
     *      Presentation Table: >
     *        Presentation Tables display only a subset of the data at first glance;
     *        their entries can then be expanded to show detailed information.
     * rules:
     *   usage:
     *      1: >
     *         Report Panels SHOULD be used when user generated content of two sources (i.e results, guidelines in a template)
     *         is to be displayed alongside each other.
     *   interaction:
     *      1: Links MAY open new views.
     *      2: Buttons MAY trigger actions or inline editing.
     * ---
     * @param string $title
     * @param \ILIAS\UI\Component\Panel\Sub[]|\ILIAS\UI\Component\Panel\Sub $sub_panels
     * @return \ILIAS\UI\Component\Panel\Report
     */
    public function report(string $title, $sub_panels): Report;

    /**
     * ---
     * description:
     *   purpose: >
     *       Listing Panels are used to list items following all one
     *       single template.
     *   composition: >
     *       Listing Panels are composed of several titled Item Groups.
     *       They further may contain a filter.
     *   effect: >
     *       The List Items of Listing Panels may contain a dropdown
     *       offering options to interact with the item. Further Listing Panels
     *       may be filtered and the number of sections or items to be displayed
     *       may be configurable.
     *   rivals:
     *      Report Panels: >
     *        Report Panels contain sections as Sub Panels each displaying
     *        different aspects of one item.
     *      Presentation Table: >
     *        Use Presentation Table if you have a data set at hand that you want to
     *        make explorable and/or present as a wholeness. Also use Presentation
     *        Table if your dataset does not contain Items that represent entities.
     *
     * rules:
     *   usage:
     *      1: >
     *         Listing Panels SHOULD be used, if a large number of items using
     *         the same template are to be displayed in an inviting way
     *         not using a Table.
     * ---
     * @return \ILIAS\UI\Component\Panel\Listing\Factory
     */
    public function listing(): Listing\Factory;

    /**
     * ---
     * description:
     *   purpose: >
     *       Secondary Panels are used to group content not located in the center section.
     *       Secondary Panels are used to display marginal content related to the current page context.
     *   composition: >
     *       Secondary Panels consist of a title and a content section. Secondary Panels may contain View Controls.
     *   rivals:
     *      Slate: >
     *        Secondary Panels are used to present secondary information or content that should appear in combination with the current
     *        center content.
     *        Other than the Slates in the Mainbar and the Metabar, the Secondary Panel always relates to some specific context
     *        indicated by the title of the current screen. Note that the slates in the tools of the Mainbar may have a very similar
     *        context-based characteristic. The difference between Slates, Tools and Secondary Panels currently is blurry and needs
     *        to be defined more rigorously in the future.
     *
     * rules:
     *   usage:
     *      1: Secondary Panels MUST NOT be inside the center content as primary Container for grouping content of varying content.
     *
     *   composition:
     *      1: Secondary Panels MAY contain a Section View Control to change the current presentation of the content.
     *      2: Secondary Panels MAY contain a Pagination View Control to display data in chunks.
     *      3: Secondary Panels MAY have a Button to perform actions listed in a Standard Dropdown.
     *      4: Secondary Panels MAY have a Sortation View Control to perform ordering actions to the presented data.
     *
     * ---
     * @return \ILIAS\UI\Component\Panel\Secondary\Factory
     */
    public function secondary(): Secondary\Factory;
}
