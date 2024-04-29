<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

$cal_remote = new ilCalendarRemoteAccessHandler();
$cal_remote->parseRequest();
$cal_remote->handleRequest();
