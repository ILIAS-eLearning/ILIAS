<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Calendar/classes/class.ilCalendarRemoteAccessHandler.php';
$cal_remote = new ilCalendarRemoteAccessHandler();
$cal_remote->parseRequest();
$cal_remote->handleRequest();
