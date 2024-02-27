<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCoursePdfFilename implements ilCertificateFilename
{
    /** @var ilSetting*/
    private $scormSetting;
    /** @var ilCertificateFilename */
    private $origin;
    /** @var ilLanguage */
    private $lng;
    private ilSetting $setting;

    /**
     * @param ilCertificateFilename $origin
     * @param ilLanguage $lng
     * @param ilSetting $setting
     */
    public function __construct(ilCertificateFilename $origin, ilLanguage $lng, ilSetting $setting)
    {
        $this->setting = $setting;
        $this->origin = $origin;
        $this->lng = $lng;
    }

    /**
     * @inheritDoc
     */
    public function createFileName(ilUserCertificatePresentation $presentation) : string
    {
        $fileName = $this->origin->createFileName($presentation);

        $short_title = $this->setting->get('certificate_short_name_' . $presentation->getObjId());
        $certificate_title = $presentation->getObjectTitle();
        if(isset($short_title) && strlen(trim($short_title))> 0){
            $certificate_title = $short_title;
        }
        if (null === $presentation->getUserCertificate()){
            $fileNameParts = implode('_', array_filter([
                strftime('%y%m%d', time()),
                $this->lng->txt('certificate_var_user_firstname'),
                $this->lng->txt('certificate_var_user_lastname')
            ]));
        }else{

            $user = new ilObjUser($presentation->getUserCertificate()->getUserId());
            $fileNameParts = implode('_', array_filter([
                str_replace("-", "", $this->getCompletionDate($presentation)),
                $user->getLastname(),
                $user->getFirstname()
            ]));
        }

        $fileName = implode('_', array_filter([
            $fileNameParts,
            $certificate_title,
            $fileName
        ]));

        return $fileName;
    }

    function getCompletionDate(ilUserCertificatePresentation $presentation)
    {
        $statusHelper = new ilCertificateLPStatusHelper();
        $participantsHelper = new ilCertificateParticipantsHelper();
        $userId = $presentation->getUserCertificate()->getUserId();
        $objId =  $presentation->getObjId();
        $completion_date = $participantsHelper->getDateTimeOfPassed($objId, $userId);
        if ($completion_date == false || $completion_date =='' || $completion_date == null){
            $completion_date = $statusHelper->lookupStatusChanged($objId, $userId);
        }

        return (new ilDateTime($completion_date, IL_CAL_DATETIME))->get(IL_CAL_DATE);
    }
}
