<?php
mb_internal_encoding('utf-8');

require_once '../Readability.php';

header('Content-Type: text/html; charset=utf-8');

function isUTF8($string) {
    return (utf8_encode(utf8_decode($string)) == $string);
}

// get latest Medialens alert 
// (change this URL to whatever you'd like to test)
$url = 'http://fashion.163.com/13/0416/09/8SIT765J00264J94.html';

$html = file_get_contents($url);

if ( !isUTF8($html) )
	$html = mb_convert_encoding($html, 'utf-8', 'gbk');


f (function_exists('tidy_parse_string')) {
	$tidy = tidy_parse_string($html, array(), 'UTF8');
	$tidy->cleanRepair();
	$html = $tidy->value;
}

$readability = new Readability($html, $url);
$readability->debug = false;
// convert links to footnotes?
$readability->convertLinksToFootnotes = true;
// process it
$result = $readability->init();
// does it look like we found what we wanted?
if ($result) {
	echo "== Title =====================================\n";
	echo $readability->getTitle()->textContent, "\n\n";
	echo "== Body ======================================\n";
	$content = $readability->getContent()->innerHTML;
	// if we've got Tidy, let's clean it up for output
	if (function_exists('tidy_parse_string')) {
		$tidy = tidy_parse_string($content, array('indent'=>true, 'show-body-only' => true), 'UTF8');
		$tidy->cleanRepair();
		$content = $tidy->value;
	}
	echo $content;
} else {
	echo 'Looks like we couldn\'t find the content. :(';
}
?>
