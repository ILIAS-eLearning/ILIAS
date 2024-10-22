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

namespace Data\src\TextHandling;

enum Structure
{
    // heading 1-6 are cases for <h1> to <h6>
    case HEADING_1;
    case HEADING_2;
    case HEADING_3;
    case HEADING_4;
    case HEADING_5;
    case HEADING_6;
    case BOLD;
    case ITALIC;
    case UNORDERED_LIST;
    case ORDERED_LIST;
    case LINK;
    case PARAGRAPH;
    case BLOCKQUOTE;
    case CODE;
}
