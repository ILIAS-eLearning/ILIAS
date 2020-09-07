<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
include_once './Services/WebServices/ECS/classes/class.ilECSConnectorException.php';

/**
 * Connector for course member ressource
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSCourseMemberConnector extends ilECSConnector
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
    public function getCourseMember($course_member_id, $a_details = false)
    {
        $this->path_postfix = '/campusconnect/course_members/' . (int) $course_member_id;
        
        if ($a_details and $course_member_id) {
            $this->path_postfix .= '/details';
        }

        try {
            $this->prepareConnection();
            $this->setHeader(array());
            if ($a_details) {
                $this->addHeader('Accept', 'application/json');
            } else {
                #$this->addHeader('Accept', 'text/uri-list');
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
                include_once './Services/WebServices/ECS/classes/class.ilECSEContentDetails.php';
                $details = new ilECSEContentDetails();
                $GLOBALS['DIC']['ilLog']->write(print_r($res, true));
                $details->loadFromJson($ecs_result->getResult());
                return $details;
            }
            
            return $ecs_result->getResult();
        } catch (ilCurlConnectionException $e) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $e->getMessage());
        }
    }
}
