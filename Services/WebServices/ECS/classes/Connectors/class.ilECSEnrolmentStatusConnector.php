<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
include_once './Services/WebServices/ECS/classes/class.ilECSConnectorException.php';
include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatus.php';

/**
 * Connector for course member ressource
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSEnrolmentStatusConnector extends ilECSConnector
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
     * @return mixed object of EContentDetails or object of ECSEnrolmentStatus
     */
    public function getEnrolmentStatus($a_enrole_id = 0, $a_details = false)
    {
        if ($a_enrole_id) {
            $this->path_postfix = '/campusconnect/member_status/' . (int) $a_enrole_id;
        }
        if ($a_details and $a_enrole_id) {
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
            } else {
                include_once './Services/WebServices/ECS/classes/Connectors/class.ilECSEnrolmentStatus.php';
                $enrolment = new ilECSEnrolmentStatus();
                $enrolment->loadFromJson($ecs_result->getResult());
                return $enrolment;
            }
        } catch (ilCurlConnectionException $e) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $e->getMessage());
        }
    }
    
    
    /**
     * Add new enrolment status
     */
    public function addEnrolmentStatus(ilECSEnrolmentStatus $enrolment, $a_target_mid)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(__METHOD__ . ': Add new enrolment status');

        $this->path_postfix = '/campusconnect/member_status';
        
        try {
            $this->prepareConnection();

            $this->addHeader('Content-Type', 'application/json');
            $this->addHeader('Accept', 'application/json');
            $this->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, $a_target_mid);
            #$this->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, 1);

            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $this->curl->setOpt(CURLOPT_POST, true);
            $this->curl->setOpt(CURLOPT_POSTFIELDS, json_encode($enrolment));
            $ret = $this->call();

            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
    
            $ilLog->write(__METHOD__ . ': Checking HTTP status...');
            if ($info != self::HTTP_CODE_CREATED) {
                $ilLog->write(__METHOD__ . ': Cannot create auth resource, did not receive HTTP 201. ');
                $ilLog->write(__METHOD__ . ': POST was: ' . print_r($enrolment, true));
                $ilLog->write(__METHOD__ . ': HTTP code: ' . $info);
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $ilLog->write(__METHOD__ . ': ... got HTTP 201 (created)');

            $result = new ilECSResult($ret);
            $enrolment_res = $result->getResult();

            $ilLog->write(__METHOD__ . ': ... Received result: ' . print_r($enrolment_res, true));

            return $enrolment_res;
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }
}
