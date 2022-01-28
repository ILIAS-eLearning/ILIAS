<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 *
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSCourseConnector extends ilECSConnector
{

    /**
     * Constructor
     * @param ilECSSetting $settings
     */
    public function __construct(ilECSSetting $settings = null)
    {
        parent::__construct($settings);
    }


    /**
     * Get single directory tree
     * @return array an array of ecs cms directory tree entries
     */
    public function getCourse($course_id, $a_details = false)
    {
        $this->path_postfix = '/campusconnect/courses/' . (int) $course_id;
        
        if ($a_details and $course_id) {
            $this->path_postfix .= '/details';
        }

        try {
            $this->prepareConnection();
            $this->setHeader(array());
            if ($a_details) {
                $this->addHeader('Accept', 'application/json');
            } else {
                $this->addHeader('Accept', 'text/uri-list');
            }
            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $res = $this->call();
            
            if (substr($res, 0, 4) == 'http') {
                $json = file_get_contents($res);
                $ecs_result = new ilECSResult($json);
            } else {
                $ecs_result = new ilECSResult($res);
            }
            
            // Return ECSEContentDetails for details switch
            if ($a_details) {
                $details = new ilECSEContentDetails();
                $details->loadFromJson($ecs_result->getResult());
                return $details;
            }
            // Return json result
            return $ecs_result->getResult();
        } catch (ilCurlConnectionException $e) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $e->getMessage());
        }
    }
}
