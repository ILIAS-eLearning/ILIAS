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

/**
 * Class ilExtractorFactory
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilExtractorFactory
{
    /**
     * @param string $component
     * @return ilExtractor|stdClass
     */
    public static function getExtractorByEventDescriptor(string $component)
    {
        $params_object = new ilExtractedParams();

        // Code for transition phase only!
        $extractor_class_name = 'il' . str_replace('/', '', $component) . 'Extractor';
        $final_path = './' . $component . '/classes/';
        $final_fullpath = $final_path . 'class.' . $extractor_class_name . '.php';

        $transition_fullpath = './Services/WorkflowEngine/classes/extractors/class.' . $extractor_class_name . '.php';

        if (is_file($final_fullpath)) {
            require_once $final_fullpath;
        } elseif (is_file($transition_fullpath)) {
            require_once $transition_fullpath;
        }

        if (class_exists($extractor_class_name, false)) {
            $extractor = new $extractor_class_name($params_object);
            return $extractor;
        }

        return new stdClass();
    }
}
