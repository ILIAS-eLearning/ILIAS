<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* valus to import crs from WBD to GOA
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevImportCourseData extends ImportCourseData{
    public function __construct($values) {
        $this->wbd_booking_id       = $values[self::WBD_BOOKING_ID];
        $this->title                = $values[self::TITLE];
        $this->credit_points        = $values[self::CREDIT_POINTS];
        $this->begin_date           = $values[self::BEGIN_DATE];
        $this->end_date             = $values[self::END_DATE];
        $this->course_type          = $values[self::COURSE_TYPE];
        $this->study_content        = $values[self::STUDY_CONTENT];
    }
}