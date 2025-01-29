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

namespace ILIAS\Data\Text;

enum Structure: string
{
    // heading 1-6 are cases for <h1> to <h6>
    case HEADING_1 = "h1";
    case HEADING_2 = "h2";
    case HEADING_3 = "h3";
    case HEADING_4 = "h4";
    case HEADING_5 = "h5";
    case HEADING_6 = "h6";
    case BOLD = "b";
    case ITALIC = "i";
    case UNORDERED_LIST = "ul";
    case ORDERED_LIST = "ol";
    case LINK = "a";
    case PARAGRAPH = "p";
    case BLOCKQUOTE = "q";
    case CODE = "code";
}
