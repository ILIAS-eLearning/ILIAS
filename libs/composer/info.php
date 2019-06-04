<?php
/* Copyright (c) 2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This script can be used to quickly compare the state of the vendor directory
 * with some reference. It simply outputs a hash of the composer lock and a
 * combined hash of all files in vendor.
 */

define("HASH_ALGO", "sha1");
define("COMPOSER_LOCK_PATH", __DIR__."/composer.lock");
define("VENDOR_PATH", __DIR__."/vendor");

header("Content-Type: text/plain");

echo "composer.lock: ".composer_lock_hash()."\n";
echo "vendor:        ".vendor_hash()."\n";

function composer_lock_hash() {
	if (!file_exists(COMPOSER_LOCK_PATH)) {
		return "composer.lock does not exist";
	}
	return hash_file(HASH_ALGO, COMPOSER_LOCK_PATH);
}

function vendor_hash() {
	if(!file_exists(VENDOR_PATH)) {
		return "vendor directory does not exist";
	}
	return hash_directory(HASH_ALGO, VENDOR_PATH);
}

function hash_directory($algo, $path) {
	return hash(
		HASH_ALGO,
		implode(
			"",
			array_map(
				function($o) use ($algo, $path) {
					if ($o === "." || $o === "..") {
						return "";
					}
					$o = "$path/$o";
					if (is_link($o)) {
						return "";
					}
					if (is_dir($o)) {
						return hash_directory($algo, $o);
					}
					return hash_file($algo, $o);
				},
				scandir($path)
			)
		)
	);
}
