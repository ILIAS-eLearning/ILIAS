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

/**
 * News feed script.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */

require_once '../vendor/composer/vendor/autoload.php';

ilContext::init(ilContext::CONTEXT_RSS_AUTH);

ilInitialisation::initILIAS();

global $lng, $ilSetting, $DIC;

$feed_set = new ilSetting('news');

$query = $DIC->http()->wrapper()->query();
$refinery = $DIC->refinery();

function sendUnauthorized()
{
    header('WWW-Authenticate: Basic realm="ILIAS Newsfeed"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
};

if (!isset($_SERVER['PHP_AUTH_PW']) || !isset($_SERVER['PHP_AUTH_USER'])) {
    sendUnauthorized();
}

$auth_password_hash = md5($_SERVER['PHP_AUTH_PW']);
$auth_username = $_SERVER['PHP_AUTH_USER'];

$check_private_feed_auth = function ($user_id, $feed_pass, $login_name) use ($auth_password_hash, $auth_username, $feed_set) {
    return $user_id > 0
        && $feed_pass !== ''
        && $feed_pass !== null
        && $auth_password_hash === $feed_pass
        && $auth_username === $login_name
        && $feed_set->get('enable_private_feed');
};

$request_user_id = $query->retrieve('user_id', $refinery->byTrying([
    $refinery->kindlyTo()->int(),
    $refinery->always(0)
]));

if ($request_user_id > 0) {
    $request_feed_pass = ilObjUser::_getFeedPass($request_user_id);
    $request_login_name = ilObjUser::_lookupLogin($request_user_id);

    if (
        $feed_pass !== ''
        && $feed_pass !== null
        && $auth_password_hash === $feed_pass
        && $auth_username === $login_name
        && $feed_set->get('enable_private_feed')
    ) {
        $request_hash = $query->retrieve('hash', $refinery->byTrying([
            $refinery->kindlyTo()->string(),
            $refinery->always('')
        ]));
        $writer = new ilUserFeedWriter($request_user_id, $request_hash, true);
        $writer->showFeed();
        exit;
    }
}

$request_ref_id = $query->retrieve('ref_id', $refinery->byTrying([
    $refinery->kindlyTo()->int(),
    $refinery->always(0)
]));

$server_user_id = ilObjUser::_lookupId($auth_username);
if ($server_user_id === null || $server_user_id === 0) {
    sendUnauthorized();
}

$server_feed_pass = ilObjUser::_getFeedPass($server_user_id);
if ($server_feed_pass === null || $auth_password_hash !== $server_feed_pass) {
    sendUnauthorized();
}

if ($request_ref_id > 0) {
    $writer = new ilObjectFeedWriter($request_ref_id, $server_user_id);
    $writer->showFeed();
    exit;
}

$blank_feed_writer = new ilFeedWriter();
$feed_item = new ilFeedItem();
$lng->loadLanguageModule('news');

$channel_title = $ilSetting->get('short_inst_name');
$blank_feed_writer->setChannelTitle($channel_title !== '' ? $channel_title : 'ILIAS');
$blank_feed_writer->setChannelAbout(ILIAS_HTTP_PATH);
$blank_feed_writer->setChannelLink(ILIAS_HTTP_PATH);

$enable_private_feed = $feed_set->get('enable_private_feed');
$feed_item->setTitle($lng->txt($enable_private_feed ? 'priv_feed_no_auth_title' : 'priv_feed_no_access_title'));
$feed_item->setDescription($lng->txt($enable_private_feed ? 'priv_feed_no_auth_body' : 'priv_feed_no_access_body'));
$feed_item->setLink(ILIAS_HTTP_PATH);
$blank_feed_writer->addItem($feed_item);
$blank_feed_writer->showFeed();
