<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

// A real Shib-Logout ("front" or "back" channel was never implemented resp. removed in ILIAS, because the front-channel
// also had problems concerning the security. If a logout mechanism is desired, please revisit the feature request
// https://docu.ilias.de/goto_docu_wiki_wpage_4657_1357.html and bring it as a suggestion to the community:
// https://docu.ilias.de/goto.php?target=wiki_5307&client_id=docu#ilPageTocA129
/** @noRector */
include 'logout.php';
