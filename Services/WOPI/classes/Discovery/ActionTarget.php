<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Services\WOPI\Discovery;

/**
 * Officialy supported action targets, see https://learn.microsoft.com/en-us/openspecs/office_protocols/ms-wopi/429749b7-5ec3-4553-a589-0ec5240121ad
 * @author Fabian Schmid <fabian@sr.solutions>
 */
enum ActionTarget: string
{
    case EDIT = 'edit';
    case VIEW = 'view';
    case MOBILE_VIEW = 'mobileView';
    case EMBED_VIEW = 'embedview';
    case EMBED_EDIT = 'embededit';
    case PRESENT = 'present';
    case PRESENT_SERVICE = 'presentservice';
    case ATTEND = 'attend';
    case ATTEND_SERVICE = 'attendservice';
    case EDIT_NEW = 'editnew';
    case IMAGE_PREVIEW = 'imagepreview';
    case INTERACTIVE_PREVIEW = 'interactivepreview';
    case FORM_SUBMIT = 'formsubmit';
    case FORM_EDIT = 'formedit';
    case REST = 'rest';
    case PRELOAD_VIEW = 'preloadview';
    case PRELOAD_EDIT = 'preloadedit';
    case RTC = 'rtc';
    case GET_INFO = 'getinfo';
    case CONVERT = 'convert';
    case SYNDICATE = 'syndicate';
    case LEGACY_WEBSERVICE = 'legacywebservice';
    case COLLAB = 'collab';
    case FORM_PREVIEW = 'formpreview';
    case DOCUMENT_CHAT = 'documentchat';
}
