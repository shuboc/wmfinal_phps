<?php
$total_files = 8269;
$doc_index = 'doc_index';
$ifile = fopen( $doc_index, 'r' );
$unigram_path = 'doc_unigram/';
$bigram_path  = 'doc_bigram/';
$trigram_path = 'doc_trigram/';

if( $ifile == NULL ) die();

$df_unigram = array();
$df_bigram  = array();
$df_trigram = array();

while( !feof( $ifile) ){
	$doc_name = str_replace( "\n", "", fgets( $ifile ) );
	if( $doc_name == '' ) break;
	
	//echo $unigram_path.$doc_name;
	/* For Unigram */
	$doc_file = fopen( $unigram_path.$doc_name, 'r' );
	while( !feof( $doc_file ) ){
		$data = str_replace( "\n", "", fgets( $doc_file ) );
		if( $data == '' ) break;

		$term_array = explode( ' ', $data );
		$term = (int)$term_array[0];
		//echo $term."\n";
		if( !isset( $df_unigram[$term] ) ) $df_unigram[$term] = 1;
		else $df_unigram[$term] += 1;

	}
	/* For Bigram */
	$doc_file2 = fopen( $bigram_path.$doc_name, 'r' );
	while( !feof( $doc_file2 ) ){
		$data = str_replace( "\n", "", fgets( $doc_file2 ) );
		if( $data == '' ) break;
		//echo $data."\n";
		$term_array = explode( ' ', $data );
		$term = $term_array[0];
		//echo $term."\n";
		if( !isset( $df_bigram[$term] ) ) $df_bigram[$term] = 1;
		else $df_bigram[$term] += 1;
	}
	/* For Trigram */
	$doc_file3 = fopen( $trigram_path.$doc_name, 'r' );
	while( !feof( $doc_file3 ) ){
		$data = str_replace( "\n", "", fgets( $doc_file3 ) );
		if( $data == '' ) break;

		$term_array = explode( ' ', $data );
		$term = $term_array[0];
		if( !isset( $df_trigram[$term] ) ) $df_trigram[$term] = 1;
		else $df_trigram[$term] += 1;
	}

}
fclose( $ifile );
asort( $df_unigram );
asort( $df_bigram  );
asort( $df_trigram );
//print_r( $df );

$ofile = fopen( 'idf_unigram', 'w' );
foreach( $df_unigram as $term => $value ){
	fwrite( $ofile, $term." ".round( log($total_files/$value, 2), 4)."\n" );

}
fclose( $ofile );

$ofile = fopen( 'idf_bigram', 'w' );
foreach( $df_bigram as $term => $value ){
	fwrite( $ofile, $term." ".round( log($total_files/$value, 2), 4)."\n" );
}
fclose( $ofile );

$ofile = fopen( 'idf_trigram', 'w' );
foreach( $df_trigram as $term => $value ){
	fwrite( $ofile, $term." ".round( log($total_files/$value, 2), 4)."\n" );
}
fclose( $ofile );

?>
