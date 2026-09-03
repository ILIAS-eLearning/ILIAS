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

namespace ILIAS\Database\PDO;

use ilDBInterface;
use ilDBPdoInterface;

/**
 * The part of the database contract other components consume.
 *
 * Counterpart to {@see Internal}, which additionally exposes what the PDO
 * manager and reverse classes need from the connection they work on. The two
 * names describe the scope a contract is meant for, not where the database
 * lives.
 *
 * Both the name and the place in the PDO namespace are provisional: this
 * interface still inherits the complete legacy surface of ilDBInterface and
 * ilDBPdoInterface, of which consuming components use a small fraction. It can
 * only be named and located for what it does once that surface is narrowed,
 * see ROADMAP.md, "Narrow the contract other components consume".
 */
interface External extends ilDBInterface, ilDBPdoInterface
{
}
