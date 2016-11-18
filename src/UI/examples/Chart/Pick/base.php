<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

function base() {
	$bar1 = "<button type=\"button\" class=\"btn btn-info btn-sm\">Grade 1</button>";

	$bar2 = "<button type=\"button\" class=\"btn btn-info btn-sm active\">Grade 2</button>";

	$bar3 = "<button type=\"button\" class=\"btn btn-info btn-sm\">Grade 3</button>";

	return $bar1." ".$bar2." ".$bar3;
}
