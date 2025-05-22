<?php /**
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

exit; ?>

===================================
HTML Export
===================================

todo
- ilObjContentObject::exportHTML: create directories move to page components
- ilObjContentObject::exportHTML: export system style sheet -> system style class in


===================================
LMPresentation
===================================

Typical Control Flow

- executeCommand()
  - layout()
    - determineLayout()
      - getCurrentPageId()
    - ilPage
    - ilTOC
    - ilMainMenu
    - ...


===================================
Question processing
===================================




===================================
LP in Learning Modules
===================================

See ilLMTracker class.