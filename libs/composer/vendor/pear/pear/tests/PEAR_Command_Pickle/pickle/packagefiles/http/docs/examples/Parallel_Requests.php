<?php
try {
	$p = new HttpRequestPool;
	// if you want to set _any_ options of the HttpRequest object,
	// you need to do so *prior attaching* to the request pool!
	$p->attach(new HttpRequest('http://pear.php.net', HTTP_METH_HEAD));
	$p->attach(new HttpRequest('http://pecl.php.net', HTTP_METH_HEAD));
} catch (HttpException $e) {
	print $e;
	exit;
}

try {
	$p->send();
	// HttpRequestPool implements an iterator over attached HttpRequest objects
	foreach ($p as $r) {
		echo "Checking ", $r->getUrl(), " reported ", $r->getResponseCode(), "\n";
	}
} catch (HttpException $e) {
	print $e;
}
?>
