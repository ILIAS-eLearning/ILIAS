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
 * Connector for course member ressource
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCourseMemberConnector extends ilECSConnector
{
    /**
     * Get single directory tree
     * @return mixed an array of ecs cms directory tree entries
     */
    public function getCourseMember($course_member_id, bool $a_details = false)
    {
        $this->path_postfix = '/campusconnect/course_members/' . (int) $course_member_id;
        
        if ($a_details && $course_member_id) {
            $this->path_postfix .= '/details';
        }

        try {
            $this->prepareConnection();
            $this->setHeader(array());
            if ($a_details) {
                $this->addHeader('Accept', 'application/json');
            }
            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $res = $this->call();
            
            if (strpos($res, 'http') === 0) {
                $json = file_get_contents($res);
                $ecs_result = new ilECSResult($json);
            } else {
                $ecs_result = new ilECSResult($res);
            }
            
            // Return ECSEContentDetails for details switch
            if ($a_details) {
                $details = new ilECSEContentDetails();
                $this->logger->debug(print_r($res, true));
                $details->loadFromJson($ecs_result->getResult());
                return $details;
            }
            
            return $ecs_result->getResult();
        } catch (ilCurlConnectionException $e) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $e->getMessage());
        }
    }
}
