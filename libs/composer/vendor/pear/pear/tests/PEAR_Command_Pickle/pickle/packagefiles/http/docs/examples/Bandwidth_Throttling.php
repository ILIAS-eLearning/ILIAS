<?php
// send 5000 bytes every 0.2 seconds, i.e. max ~25kByte/s
HttpResponse::setThrottleDelay(0.2);
HttpResponse::setBufferSize(5000);
HttpResponse::setCache(true);
HttpResponse::setContentType('application/x-zip');
HttpResponse::setFile('../archive.zip');
HttpResponse::send();
?>
