<?php

if( $argc != 2 ) die( 'Usage: '.$argv[0]." output_file\n" );

$output_file = $argv[1];

//$query_file_uni = fopen( 'query_ngram/query'.$query_num.'_uni', 'r' );
//$query_file_bi  = fopen( 'query_ngram/query'.$query_num.'_bi',  'r' );
//$query_file_tri = fopen( 'query_ngram/query'.$query_num.'_tri', 'r' );

function myfile( $file_name ){
	//echo $file_name."\n"; // debug
	$array = array();
	$input = fopen( $file_name, 'r' );
	while( !feof( $input ) ){
		$data = str_replace( "\n", "", fgets($input) );
		if( $data == '' ) break;

		$term_array = explode( ' ', $data );
		$term  = $term_array[0];
		$value = $term_array[1];
		$array[$term] = $value;
	}
	fclose( $input );
	return $array;
}


$idf_uni = myfile( 'idf_unigram' );
$idf_bi  = myfile( 'idf_bigram' );
//$idf_tri = myfile( 'idf_trigram' );

$q_uni = myfile( 'query_uni' );
$q_bi  = myfile( 'query_bi'  );
//$q_tri = myfile( 'query_tri' );

$qv_uni = array(); // query vector
$qv_bi  = array();
//$qv_tri = array();

foreach( $q_uni as $term => $value ){
	if( isset($idf_uni[$term]) ) $qv_uni[$term] = $value*$idf_uni[$term];
	else $qv_uni[$term] = 0;
}
foreach( $q_bi as $term => $value ){
	if( isset($idf_bi[$term]) ) $qv_bi[$term] = $value*$idf_bi[$term];
	else $qv_bi[$term] = 0;
}
/*foreach( $q_tri as $term => $value ){
	if( isset($idf_tri[$term]) ) $qv_tri[$term] = $value*$idf_tri[$term];
	else $qv_tri[$term] = 0;
}*/

/* compute the length of all the query vectors */
$qv_uni_len = $qv_bi_len = $qv_tri_len = 0;

foreach( $qv_uni as $value ) $qv_uni_len += $value*$value;
$qv_uni_len = sqrt( $qv_uni_len );

foreach( $qv_bi as  $value ) $qv_bi_len  += $value*$value;
$qv_bi_len  = sqrt( $qv_bi_len );

/*foreach( $qv_tri as $value ) $qv_tri_len += $value*$value;
$qv_tri_len = sqrt( $qv_tri_len );*/

//print_r( $qv_bi );

$sim_doc_uni = array();
$sim_doc_bi  = array();
//$sim_doc_tri = array();

$doc_uni = array();
$doc_bi  = array();
//$doc_tri = array();

$doc_index = file( 'doc_index' );

foreach( $doc_index as $doc_name ){
	$doc_name = str_replace( "\n", "", $doc_name );
	if( $doc_name == '' ) break;

	$doc_uni[$doc_name] = myfile( "doc_weight_uni/$doc_name" );
	$doc_bi[$doc_name] = myfile( "doc_weight_bi/$doc_name" );
	//$doc_tri[$doc_name] = myfile( "doc_weight_tri/$doc_name" );
	
	$sim_doc_uni[$doc_name] = sim( $doc_uni[$doc_name], $qv_uni, $qv_uni_len );
	$sim_doc_bi[$doc_name]  = sim( $doc_bi[$doc_name],  $qv_bi,  $qv_bi_len  );
	//$sim_doc_tri[$doc_name] = sim( $doc_tri[$doc_name], $qv_tri, $qv_tri_len );

}

//print_r( $sim_doc_uni );
//print_r( $sim_doc_bi );
//print_r( $sim_doc_tri );

/* compute the final score */
$score = array();
$uni_weight = 0.3 ;
$bi_weight  = 0.7 ;
//$tri_weight = 0.3 ;
foreach( $sim_doc_uni as $doc_name => $value ){
	$score[$doc_name] = $value*$uni_weight + $sim_doc_bi[$doc_name]*$bi_weight;// + $sim_doc_tri[$doc_name]*$tri_weight;
}
arsort( $score );

$num_rel = 0;
$relevant  = array();
foreach( $score as $doc_name => $value ){
	if( $num_rel>=20 ) break;
	if( $value >= 2 || $num_rel<20 ){
		$relevant[$doc_name] = $value;
		$num_rel += 1;
	}
}
//print_r( $relevant ); // debug
$song_name = array();
$first_name = '';
foreach( $relevant as $doc_id => $value ){
	$data = getSongName( $doc_id );	
	if( array_search( $data, $song_name )!=false || $data==$first_name ) {
		continue;
	}
	if( $first_name == '' ) $first_name = $data;
	$song_name[] = $data;
	//echo getSongName( $doc_id )."\n";
}

$ofile = fopen( $output_file, 'w' ) or die("Cannot open $output_file!!");
foreach( $song_name as $value ){
	if( $value == "\n" || $value == '' ) continue;
	fwrite( $ofile, $value."\n" );
}

function getSongName( $file_name ){
	$loc = "resources/lyrics_hot/$file_name";
	$doc = new DOMDocument();
	$doc->load($loc);
	$docs = $doc->getElementsByTagName( "doc" );
	foreach( $docs as $doc ){
		$titles = $doc->getElementsByTagName( "title" );
		$title = $titles->item(0)->nodeValue;
		return $title;
	}
}
/* similarity of query and document */
function sim( $query, $doc, $q_len ){
	//if( $q_len == 0 ) echo "q_len==0\n"; // debug
	$doc_len = 0;
	foreach( $doc as $value )	$doc_len += $value*$value;
	$doc_len = sqrt( $doc_len );

	$res = 0; // the result of dot

	foreach( $query as $term => $value ){
		if( isset( $doc[$term] ) ) $res += $value*$doc[$term];
	}

	if( $doc_len==0 || $q_len==0 ) return 0;
	return round( $res/$q_len/$doc_len, 5 );
}





//print_r( $doc_uni );


?>
