#!/usr/bin/perl -w

die "Usage: conv.pl <oldfile>" unless (defined $ARGV[0]);
$filename = $ARGV[0];

open(FIN, $filename);
@lines = ();
while(<FIN>)
{
	s/PSH_HFile/HFile/g;
	s#global \$BEAUT_PATH;#global \$BEAUT_PATH;\nif (!isset (\$BEAUT_PATH)) return;#g;
	if (s#'../PSH/HFile\.php'#"\$BEAUT_PATH/Beautifier/HFile\.php"#g)
	{
		push(@lines, "global \$BEAUT_PATH;\n");
	}
	else
	{
		s/PSH/Beautifier/g;
	}
	push(@lines, $_);
}
close(FIN);
open(FOUT, ">$filename");
foreach(@lines)
{
	chomp;
	print FOUT $_."\n";
}
close(FOUT);
