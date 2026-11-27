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

namespace ILIAS\UICore\Ctrl;

/**
 * Interface PathFactory describes the ilCtrl
 * Path factory.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface PathFactory
{
    /**
     * Returns the corresponding ilCtrlPath by the provided target type.
     *
     * @param Context $context
     * @param string[]|string        $target
     * @return Path
     */
    public function find(Context $context, $target): Path;

    /**
     * Returns an instance of an existing ilCtrlPath.
     *
     * @param string $cid_path
     * @return Path
     */
    public function existing(string $cid_path): Path;

    /**
     * Returns a pseudo instance of an ilCtrlPath.
     *
     * @return Path
     */
    public function null(): Path;
}
