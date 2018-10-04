<?php
// Currently unused
if ($skipmsg) {
    $a = new $ec($code, $mode, $options, $userinfo);
} else {
    $a = new $ec($message, $code, $mode, $options, $userinfo);
}
?>