#!/usr/bin/perl
print "Content-type: text/html\r\n\r\n";
print `cat ./header.part`;
$docroot = "/opt/www/peterlyons.com";
$galleryDir = "photos"; 
$defaultGallery = "sax_quartet_central_park_20030905";
if( $ENV{"QUERY_STRING"} =~ /gallery=([^&]+)/ ) {
    $gallery = $1;
}
if( $ENV{"QUERY_STRING"} =~ /picture=([^&]+)/ ) {
    $picture = $1;
}
@galleries = <$docroot/$galleryDir/*>;
$uri = $ENV{"REQUEST_URI"};
$length = index($uri, ".pl")+3;
$uri = substr($uri,0,$length);
if ( ! $gallery ) {
    $gallery = $defaultGallery;
}
foreach (<$docroot/$galleryDir/$gallery/*.*>) {
    $_ = substr( $_, rindex($_, "/")+1);
    if ( /^(.*).alt.txt$/ ) {
	$altTxts{$1} = $_;
	next;
    }
    if ( /^(.*)-TN.jpg$/ ) {
	$thumbnails{$1} = $_;
	next;
    }
    if ( /^(.*).jpg$/ ) {
	$pictures{$1} = $_;
	next;
    }
}
if (! -e "$docroot/$galleryDir/$gallery/$picture.jpg" ) {
    $picture = (keys %pictures)[0];
}
$pictAlt = `cat $docroot/$galleryDir/$gallery/$altTxts{$picture}`;
chomp $pictAlt;
print "\n<table><tr><td valign=\"top\">\n";
print "<img alt=\"$pictAlt\" title=\"$pictAlt\" src=\"/$galleryDir/$gallery/$picture.jpg\" />";
print "</td><td valign=\"top\">Galleries:<br /><br />";
foreach ( @galleries ) {
    $dir = substr( $_, rindex($_, "/")+1);
    print "<a href=\"$uri?gallery=$dir\">$dir</a><br /><br />";
}
print "</td></tr></table>";

print "<h1 class=\"caption\">$pictAlt</h1>\n";
@thumbs = keys %thumbnails;
@thumbs = sort @thumbs;
foreach (@thumbs) {
    $thumbAlt = `cat $docroot/$galleryDir/$gallery/$altTxts{$_}`;
    chomp $thumbAlt;
    print "<a href=\"$uri?gallery=$gallery&picture=$_\">";
    print "<img border=\"0\" src=\"/$galleryDir/$gallery/$_-TN.jpg\" alt=\"$thumbAlt\" title=\"$thumbAlt\"></a> \n";
}
print `cat ./footer.part`;


