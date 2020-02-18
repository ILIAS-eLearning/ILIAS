<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input;

/**
 * This is how a factory for inputs looks like.
 */
interface Factory
{

    /**
     * ---
     * description:
     *   purpose: >
     *      Inputs fields are different from other UI components. They bundle two
     *      things:
     *      First, they are used for displaying, as similar to other components.
     *      Second, they are used to define the server side processing of data that
     *      is received from the client.
     *      Thus, an input field defines which visual input elements a user will see,
     *      which constraints are put on the data entered in these fields and which
     *      data developers on the server side retrieve from these inputs.
     *      Fields need to be enclosed by a container which defines the means of
     *      submitting the data collected by the fields and the way those inputs
     *      are arranged to be displayed for some client.
     *   composition: >
     *      Fields are either individuals or groups of inputs. Both, individual fields
     *      and groups, share the same basic input interface. Input-Fields may have a
     *      label and byline.
     *
     * rules:
     *   style:
     *     1: >
     *         Disabled input elements MUST be indicated by setting the “disabled”
     *         attribute.
     *     2: >
     *         If focused, the input elements MUST change their input-border-color
     *         to the input-focus-border-color.
     *   composition:
     *     1: >
     *        A byline (explanatory text) MAY be added to input fields.
     *   wording:
     *     1: >
     *         If a label is set, it MUST be composed of one single term or a very
     *         short phrase. The identifier is an eye catcher for users skimming over a
     *         potentially large set of fields.
     *     2: >
     *         If a label is set, it MUST avoid lingo. Intelligibility by occasional
     *         users is prioritized over technical accuracy. The accurate technical
     *         expression is to be mentioned in the byline.
     *     3: >
     *         If a label is set, it MUST make a positive statement. If the purpose of
     *         the setting is inherently negative, use Verbs as “Limit..”, “Lock..”.
     *     4: >
     *         If bylines are provided they MUST be informative, not merely repeating
     *         the identifier’s or input element’s content. If no informative description
     *         can be devised, no description is needed.
     *     5: >
     *         A byline MUST clearly state what effect the fields produces and explain,
     *         why this might be important and what it can be used for.
     *     6: >
     *         Bulk bylines underneath a stack of option explaining all of the options in
     *         one paragraph MUST NOT be used. Use individual bylines instead.
     *     7: >
     *         A byline SHOULD NOT address the user directly. Addressing users
     *         directly is reserved for cases of high risk of severe mis-configuration.
     *     8: >
     *         A byline MUST be grammatically complete sentence with a period (.) at the end.
     *     9: >
     *         Bylines SHOULD be short with no more than 25 words.
     *     10: >
     *         Bylines SHOULD NOT use any formatting in descriptions (bold, italic or similar).
     *     11: >
     *        If bylines refer to other tabs or options or tables by name, that reference
     *        should be made in quotation marks:  ‘Info’-tab, button
     *        ‘Show Test Results’,  ‘Table of Detailed Test Results’. Use proper
     *        quotation marks, not apostrophes. Use single quotation marks
     *        for english language and double quotation marks for german language.
     *     12: >
     *        By-lines MUST NOT feature parentheses since they greatly diminish readability.
     *     13: >
     *        By-lines SHOULD NOT start with terms such as: If this option is set … If
     *        this setting is active … Choose this setting if … This setting … Rather
     *        state what happens directly: Participants get / make  / can … Point in time
     *        after which…. ILIAS will monitor… Sub-items xy are automatically whatever
     *        ... Xy will be displayed at place.
     *
     *   accessibility:
     *     1: >
     *         All fields visible in a view MUST be accessible by keyboard by using the
     *         ‘Tab’-Key.
     *
     * ---
     *
     * @return    \ILIAS\UI\Component\Input\Field\Factory
     */
    public function field();


    /**
     * ---
     * description:
     *   purpose: >
     *      An input container defines which means are used to submit the data to the
     *      system and how input-fields are being displayed in the UI. Furthermore
     *      containers will process received data according to the transformations
     *      and constraints of its fields.
     *   composition: >
     *      A Container holds one ore more fields.
     *   rivals:
     *      Group Field Input: >
     *        Groups are used within containers to functionally bundle input-fields.
     *      Section Field Input: >
     *        Sections are used within containers to visually tie fields together.
     *
     * ---
     *
     * @return    \ILIAS\UI\Component\Input\Container\Factory
     */
    public function container();
}
