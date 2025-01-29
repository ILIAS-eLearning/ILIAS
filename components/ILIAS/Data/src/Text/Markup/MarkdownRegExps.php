<?php

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

declare(strict_types=1);

namespace ILIAS\Data\Text\Markup;

/**
 * Regular expressions to detect constructs from CommonMarkdown.
 *
 * According to: https://commonmark.org/help/tutorial/
 */
enum MarkdownRegExps: string
{
    case HEADINGS = '^(\#){1,6}(\ )';
    case UNORDERED_LIST = '^(\[-*+] )';
    case ORDERED_LIST = '^([0-9]+)(\.\ )';
    case LINE_BREAK = "((  )|(\\\\))$";
    case PARAGRAPH = "$^$";
    case BLOCKQUOTE = '^(\>)+';
    case CODEBLOCK = "^```";
    case LINK = '\[(.)*\]\((.)+\)';           // [title](url)
    case LINK_REF_USAGE = '\[(.)*\]\([.]+\)'; // [title][id]
    case IMAGE = '\!\[(.)*\]\((.)+\)';        // ![](url)
    case IMAGE_REF_USAGE = '\!\[(.)+\]';            // ![][id]
    case REF = '\[(.)*\]\:(.)+';              // [id]:url
}
