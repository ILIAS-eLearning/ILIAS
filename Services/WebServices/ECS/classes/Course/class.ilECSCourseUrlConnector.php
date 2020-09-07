<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
include_once './Services/WebServices/ECS/classes/class.ilECSConnectorException.php';

/**
 * Connector for writing ecs course urls
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSCourseUrlConnector extends ilECSConnector
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
     * Send url of newly created courses to ecs
     * @return type
     * @throws ilECSConnectorException
     */
    public function addUrl(ilECSCourseUrl $url, $a_target_mid)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write(__METHOD__ . ': Add new course url ...');

        $this->path_postfix = '/campusconnect/course_urls';
        
        try {
            $this->prepareConnection();

            $this->addHeader('Content-Type', 'application/json');
            $this->addHeader('Accept', 'application/json');
            $this->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, $a_target_mid);

            $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeader());
            $this->curl->setOpt(CURLOPT_POST, true);
            $this->curl->setOpt(CURLOPT_POSTFIELDS, json_encode($url));
            
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Sending url ' . print_r(json_encode($url), true));
            
            $ret = $this->call();

            $info = $this->curl->getInfo(CURLINFO_HTTP_CODE);
    
            $ilLog->write(__METHOD__ . ': Checking HTTP status...');
            if ($info != self::HTTP_CODE_CREATED) {
                $ilLog->write(__METHOD__ . ': Cannot create course url ressource, did not receive HTTP 201. ');
                $ilLog->write(__METHOD__ . ': POST was: ' . json_encode($url));
                $ilLog->write(__METHOD__ . ': HTTP code: ' . $info);
                throw new ilECSConnectorException('Received HTTP status code: ' . $info);
            }
            $ilLog->write(__METHOD__ . ': ... got HTTP 201 (created)');
        } catch (ilCurlConnectionException $exc) {
            throw new ilECSConnectorException('Error calling ECS service: ' . $exc->getMessage());
        }
    }
}
