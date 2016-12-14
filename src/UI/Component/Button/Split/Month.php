<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button\Split;

use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes the month split Button. Note that actions are bound to the month by using the JavaScriptBindable
 * withOnLoadCodeFunction
 *
 * @example:
 * 		$month_picker->withOnLoadCode(function($id) {
 *			return "$( "#$id someMonthSelector" ).bind( "click", function() {
 *				someActionAfterClickedOnSpecificMonth;
 *			});";
 *		});
 */
interface Month extends Split, JavaScriptBindable {
}
