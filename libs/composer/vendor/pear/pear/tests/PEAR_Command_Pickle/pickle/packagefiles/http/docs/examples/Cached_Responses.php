<?php
HttpResponse::setCacheControl('public');
HttpResponse::setCache(true);
HttpResponse::capture();

print "This will be cached until content changes!\n";
print "Note that this approach will only save the clients download time.\n";
?>
