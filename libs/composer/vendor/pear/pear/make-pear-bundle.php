#!/usr/bin/php
<?php
/* piece together a windows pear distro */

if (!$_SERVER['argv'][1]) {
	echo "Usage: {$_SERVER['argv'][0]} dist_dir\n";
	exit(1);
}

$dist_dir = $_SERVER['argv'][1];

/* very light-weight function to extract a single named file from
 * a gzipped tarball.  This makes assumptions about the files
 * based on the PEAR info set in $packages. */
function extract_file_from_tarball($pkg, $filename, $dest_dir) /* {{{ */
{
	global $packages;

	$name = $pkg . '-' . $packages[$pkg];
	$tarball = $dest_dir . "/" . $name . '.tgz';
	$filename = $name . '/' . $filename;
	$destfilename = $dest_dir . "/" . basename($filename);

	$fp = gzopen($tarball, 'rb');

	$done = false;
	do {
		/* read the header */
		$hdr_data = gzread($fp, 512);
	   	if (strlen($hdr_data) == 0)
			break;
		$checksum = 0;
		for ($i = 0; $i < 148; $i++)
			$checksum += ord($hdr_data{$i});
		for ($i = 148; $i < 156; $i++)
			$checksum += 32;
		for ($i = 156; $i < 512; $i++)
			$checksum += ord($hdr_data{$i});

		$hdr = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor", $hdr_data);

		$hdr['checksum'] = octdec(trim($hdr['checksum']));

		if ($hdr['checksum'] != $checksum) {
			echo "Checksum for $tarball $hdr[filename] is invalid\n";
			print_r($hdr);
			return;
		}

		$hdr['size'] = octdec(trim($hdr['size']));
		echo "File: $hdr[filename] $hdr[size]\n";

		if ($filename == $hdr['filename']) {
			echo "Found the file we want\n";
			$dest = fopen($destfilename, 'wb');
			$x = stream_copy_to_stream($fp, $dest, $hdr['size']);
			fclose($dest);
			echo "Wrote $x bytes into $destfilename\n";
			break;
		}

		/* skip body of the file */
		$size = 512 * ceil((int)$hdr['size'] / 512);
		echo "Skipping $size bytes\n";
		gzseek($fp, gztell($fp) + $size);

	} while (!$done);

} /* }}} */

echo "Creating PEAR in $dist_dir\n";

/* Let's do a PEAR-less pear setup */
if (!file_exists($dist_dir)) {
    mkdir($dist_dir);
}
if (!file_exists($dist_dir)) {
    die("could not make $dist_dir");
}
mkdir("$dist_dir/PEAR");
mkdir("$dist_dir/PEAR/go-pear-bundle");

/* grab the bootstrap script */
echo "Downloading go-pear\n";
copy("http://pear.php.net/go-pear", "$dist_dir/PEAR/go-pear.php");
echo "Downloading go-pear.bat\n";
copy("http://cvs.php.net/viewvc.cgi/pear-core/scripts/go-pear.bat?revision=1.1", "$dist_dir/go-pear.bat");

/* This is a list of packages and versions
 * that will be used to create the PEAR folder
 * in the windows snapshot.
 */
$packages  = array(
// required packages for the installer
"PEAR"                  =>    "1.5.1",
"Console_Getopt"        =>    "1.2.1",
"Archive_Tar"           =>    "1.3.2",
"Structures_Graph"      =>    "1.0.2",

// required packages for the web frontend
"PEAR_Frontend_Web"     =>    "0.5.1",
"HTML_Template_IT"      =>    "1.1.4",
"Net_UserAgent_Detect"  =>    "2.0.1",
);


/* download the packages into the destination */
echo "Fetching packages\n";

foreach ($packages as $name => $version) {
	$filename = "$name-$version.tgz";
	$destfilename = "$dist_dir/PEAR/go-pear-bundle/$filename";
	if (file_exists($destfilename))
		continue;
	$url = "http://pear.php.net/get/$filename";
	echo "Downloading $name from $url\n";
	flush();
	copy($url, $destfilename);
}

echo "Download complete.  Extracting bootstrap files\n";

/* Now, we want PEAR.php, Getopt.php (Console_Getopt) and Tar.php (Archive_Tar)
 * broken out of the tarballs */
extract_file_from_tarball('PEAR', 'PEAR.php', "$dist_dir/PEAR/go-pear-bundle");
extract_file_from_tarball('Archive_Tar', 'Archive/Tar.php', "$dist_dir/PEAR/go-pear-bundle");
extract_file_from_tarball('Console_Getopt', 'Console/Getopt.php', "$dist_dir/PEAR/go-pear-bundle");
extract_file_from_tarball('Structures_Graph', 'Structures/Graph.php', "$dist_dir/PEAR/go-pear-bundle");
extract_file_from_tarball('Structures_Graph', 'Structures/Graph/Node.php', "$dist_dir/PEAR/go-pear-bundle");
extract_file_from_tarball('Structures_Graph', 'Structures/Graph/Manipulator/AcyclicTest.php', "$dist_dir/PEAR/go-pear-bundle");
extract_file_from_tarball('Structures_Graph', 'Structures/Graph/Manipulator/TopologicalSorter.php', "$dist_dir/PEAR/go-pear-bundle");
?>