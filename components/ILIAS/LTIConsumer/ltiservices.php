<?php

declare(strict_types=1);

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

/** @noRector */

chdir("../../../");

require_once("Services/Init/classes/class.ilInitialisation.php");

ilContext::init(ilContext::CONTEXT_SCORM);
ilInitialisation::initILIAS();

global $DIC;

$path = $_SERVER['PATH_INFO'] ?? '';

if (empty($path)) {
    ilObjLTIConsumer::sendResponseError(500, json_encode(array('error' => "ERROR_NO_PATH_INFO")));
}

$serviceName = getService($path);

ilObjLTIConsumer::getLogger()->debug("lti service call $serviceName");
ilObjLTIConsumer::getLogger()->debug("lti service path $path");

$service = null;
switch ($serviceName) {
    case "gradeservice":
        $service = new ilLTIConsumerGradeService();
        $service->setResourcePath($path);
        break;
    default:
        ilObjLTIConsumer::sendResponseError(400, json_encode(array('error' => 'invalid_request')));
}

$response = new ilLTIConsumerServiceResponse();

$isGet = $response->getRequestMethod() === ilLTIConsumerResourceBase::HTTP_GET;
$isDelete = $response->getRequestMethod() === ilLTIConsumerResourceBase::HTTP_DELETE;

if ($isGet) {
    $response->setAccept($_SERVER['HTTP_ACCEPT'] ?? '');
} else {
    $response->setContentType(isset($_SERVER['CONTENT_TYPE']) ? explode(';', $_SERVER['CONTENT_TYPE'], 2)[0] : '');
}

$validRequest = false;

$accept = $response->getAccept();
$contenttype = $response->getContentType();
$resources = $service->getResources();
$res = null;

foreach ($resources as $resource) {
    if (($isGet && !empty($accept) && (!str_contains($accept, '*/*')) &&
            !in_array($accept, $resource->getFormats())) ||
        ((!$isGet && !$isDelete) && !in_array($contenttype, $resource->getFormats()))) {
        continue;
    }

    $template = $resource->getTemplate();
    $template = preg_replace('/\{[a-zA-Z_]+\}/', '[^/]+', $template);
    $template = preg_replace('/\(([0-9a-zA-Z_\-,\/]+)\)/', '(\\1|)', $template);
    $template = str_replace('/', '\/', $template);
    if (preg_match("/^$template$/", $path) === 1) {
        $validRequest = true;
        $res = $resource;
        break;
    }
}

if (!$validRequest || $res == null) {
    $response->setCode(400);
    $response->setReason("No handler found for $serviceName/$path $accept $contenttype");
} else {
    $body = file_get_contents('php://input');
    $response->setRequestData($body);
    if (in_array($response->getRequestMethod(), $res->getMethods())) {
        $res->execute($response);
    } else {
        $response->setCode(405);
    }
}
$response->send();

function getService(string &$path): string
{
    $route = explode("/", $path);
    array_shift($route); // first slash
    $ret = array_shift($route); // service name
    $path = "/" . implode("/", $route);
    return $ret;
}
