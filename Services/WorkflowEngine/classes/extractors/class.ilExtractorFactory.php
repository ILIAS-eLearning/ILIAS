<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

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
     *
     * @return ilExtractor|stdClass
     */
    public static function getExtractorByEventDescriptor($component)
    {
        require_once './Services/WorkflowEngine/classes/extractors/class.ilExtractedParams.php';
        $params_object = new ilExtractedParams();

        // Code for transition phase only!
        $extractor_class_name = 'il' . str_replace('/', '', $component) . 'Extractor';
        $final_path = './' . $component . '/classes/';
        $final_fullpath = $final_path . 'class.' . $extractor_class_name . '.php';

        $transition_fullpath = './Services/WorkflowEngine/classes/extractors/class.' . $extractor_class_name . '.php';

        if (file_exists($final_fullpath)) {
            require_once $final_fullpath;
        } elseif (file_exists($transition_fullpath)) {
            require_once $transition_fullpath;
        }

        if (class_exists($extractor_class_name, false)) {
            $extractor = new $extractor_class_name($params_object);
            return $extractor;
        } else {
            return new stdClass();
        }
    }
}
