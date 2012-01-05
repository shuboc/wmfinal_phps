<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Songs recommendated</title>
</head>
<body>
	<div align='center'>
<?php
if( isset($_GET['found']) && $_GET['found']=='false' ) $found = false;
$query = $_GET['watermark'];

echo "<p>Your status is: $query</p>";
if( $found == false ){
	$ofile = fopen( 'query.txt', 'w' );
	fwrite( $ofile, $query );
	fclose( $ofile );
}

//system( "rm -f output.txt" );
system( "php -f vsm.php query.txt output.txt resources/lyrics_hot reset" );
echo "<p>The followings are songs recommended for you!</p>";

//echo $query; 

//system( "php -f myquery.php $query" );

$song_set = array();

$name_file = fopen( 'output.txt', 'r' );
while( !feof($name_file) ){
	$doc_name = str_replace( "\n", "", fgets( $name_file ) );
	if( $doc_name == '' ) break;	
	
	$song_set[] = $doc_name;
}

//system( "rm -f output.txt" );
//$song_set = array( '分開旅行', '分手快樂', '男人不該讓女人流淚' );

$i = 0;
foreach( $song_set as $song ){
	if( $i%2==0) echo "<p>".embed_url( $song );
	else echo embed_url( $song )."</p>";
}

/* Return the first URL of the search results from youtube */
function embed_url( $song_name ){
	$api_search = 'http://gdata.youtube.com/feeds/api/videos?v=2&q='.$song_name.'&max-results=1';
	//echo $api_search; endline();
	$doc = new DOMDocument();
	$doc->load($api_search);
	$entrys = $doc->getElementsByTagName( "entry" );
	//$entry = $entrys[0];
	foreach( $entrys as $entry ){
		$ids = $entry->getElementsByTagName( "id" );
		$id = $ids->item(0)->nodeValue;
		$data_array = explode( ':', $id );
		$videoid = $data_array[3];
		$url = '<iframe width="560" height="315" src="http://www.youtube.com/embed/'.$videoid.'" frameborder="0" allowfullscreen></iframe>';
		return $url;
		//echo $videoid; endline();
	}
}

function endline(){
	echo "<br/>\n";
}
?>
	</div>
</body>
<html>


