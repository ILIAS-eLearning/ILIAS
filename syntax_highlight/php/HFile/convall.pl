#!/usr/bin/perl -w

foreach(<*.php>)
{
	print "Converting $_...";
	system("./conv.pl $_");
	print "Done.\n";
}
