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
class ilECSEnrolmentStatusConnector extends ilECSConnector
{
    /**
     * Get single directory tree
     * @return ilECSEContentDetails|ilECSEnrolmentStatus object of EContentDetails or object of ECSEnrolmentStatus
     */
    public function getEnrolmentStatus($a_enrole_id = 0, $a_details = false)
    {
        if ($a_enrole_id) {
            $this->path_postfix = '/campusconnect/member_status/' . (int) $a_enrole_id;
        }
        if ($a_details && $a_enrole_id) {
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
            $enrolment = new ilECSEnrolmentStatus();
            $enrolment->loadFromJson($ecs_result->getResult());
            return $enrolment;
        } catch (ilCurlConnectionException $e) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $e->getMessage());
        }
    }
    
    
    /**
     * Add new enrolment status
     */
    public function addEnrolmentStatus(ilECSEnrolmentStatus $enrolment, $a_target_mid)
    {
        $this->logger->info('Add new enrolment status');

        $this->path_postfix = '/campusconnect/member_status';
        
        try {
            $this->prepareConnection();

            $this->addHeader('Content-Type', 'application/json');
            $this->addHeader('Accept', 'application/json');
            $this->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, $a_target_mid);
            #$this->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, 1);

            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $this->curl->setOpt(CURLOPT_POST, true);
            $this->curl->setOpt(CURLOPT_POSTFIELDS, json_encode($enrolment, JSON_THROW_ON_ERROR));
            $ret = $this->call();

            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
    
            $this->logger->debug(': Checking HTTP status...');
            if ($info !== self::HTTP_CODE_CREATED) {
                $this->logger->debug(': Cannot create auth resource, did not receive HTTP 201. ');
                $this->logger->debug(': POST was: ' . print_r($enrolment, true));
                $this->logger->debug(': HTTP code: ' . $info);
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $this->logger->debug(': ... got HTTP 201 (created)');

            $result = new ilECSResult($ret);
            $enrolment_res = $result->getResult();

            $this->logger->debug(': ... Received result: ' . print_r($enrolment_res, true));

            return $enrolment_res;
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }
}
