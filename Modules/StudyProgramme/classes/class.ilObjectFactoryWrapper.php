<?php declare(strict_types=1);

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

require_once("./Services/Object/classes/class.ilObjectFactory.php");

/**
 * Class ilObjectFactoryWrapper.
 *
 * Wraps around static class ilObjectFactory to make the object factory
 * exchangeable in ilObjStudyProgramm for testing purpose.
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilObjectFactoryWrapper
{
    public static ?ilObjectFactoryWrapper $instance = null;
    
    public static function singleton() : ilObjectFactoryWrapper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getInstanceByRefId(int $ref_id, bool $stop_on_error = true) : ?ilObject
    {
        return ilObjectFactory::getInstanceByRefId($ref_id, $stop_on_error);
    }
}
