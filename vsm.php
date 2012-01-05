<?php
set_time_limit(0);
$time_start = microtime(true);

if( $argc < 4 || $argc > 5 ) die( 'Usage: '.$argv[0]." query_file output_file doc_folder [reset]\n");
if( $argc == 5 && $argv[4] == 'reset' ) $reset = true;
else $reset = false;
$query_file  = $argv[1];
$output_file = $argv[2];
$doc_folder = $argv[3];
$doc_index = "doc_index";

if( $reset == true ){
	//system("rm vocaball");
	//system("rm doc_index");
	//system("rm query_uni");
	//system("rm query_bi");
	//system("rm query_tri");
}
if( !file_exists('vocab.all') )	system("./src/bin/create-vocab -o vocab.all -encoding utf8 $doc_folder/*"); 
if( !file_exists('doc_index') ) system("ls $doc_folder > $doc_index" );
if( !file_exists('doc_unigram') || !file_exists('doc_bigram') || !file_exists('doc_trigram') ){
	if( file_exists('doc_unigram') ) system('\rm -r doc_unigram');
	if( file_exists('doc_bigram') ) system('\rm -r doc_bigram');
	if( file_exists('doc_trigram') ) system('\rm -r doc_trigram');
	system('mkdir doc_unigram');
	system('mkdir doc_bigram');
	system('mkdir doc_trigram');
	$ifile = fopen( $doc_index, 'r' );
	if( $ifile == NULL ) die();
	while( !feof($ifile) ){
		$doc_name = str_replace( "\n", "", fgets( $ifile ) );
		if( $doc_name == '' ) break;	
		//echo $doc_name;echo "\n"; 
		$command  = "./src/bin/create-ngram -vocab vocab.all -o doc_unigram/$doc_name -n 1 $doc_folder/$doc_name";
		$command2 = "./src/bin/create-ngram -vocab vocab.all -o doc_bigram/$doc_name -n 2 $doc_folder/$doc_name";
		$command3 = "./src/bin/create-ngram -vocab vocab.all -o doc_trigram/$doc_name -n 3 $doc_folder/$doc_name";
		echo $command."\n";
		echo $command2."\n";
		echo $command3."\n";
		system( $command );
		system( $command2 );
		system( $command3 );
		//echo "\n";
	}
	fclose( $ifile );
}
if( !file_exists('idf_unigram') || !file_exists('idf_bigram') || !file_exists('idf_trigram') ){
	system( "php -f idf.php" );
}
if( !file_exists('doc_weight_uni') || !file_exists('doc_weight_bi') || !file_exists('doc_weight_tri') ){
	system( "mkdir doc_weight_uni" );
	system( "mkdir doc_weight_bi" );
	system( "mkdir doc_weight_tri" );
	
	$idf_uni = myfile( 'idf_unigram' ); 
	$idf_bi  = myfile( 'idf_bigram' );
	$idf_tri = myfile( 'idf_trigram' );
	
	$doc_uni = array();
	$doc_bi  = array();
	$doc_tri = array();

	$doc_index = file( 'doc_index' );

	foreach( $doc_index as $doc_name ){
		$doc_name = str_replace( "\n", "", $doc_name );
		if( $doc_name == '' ) break;
		
		$wfile_uni = fopen( "doc_weight_uni/$doc_name", 'w' );
		$wfile_bi  = fopen( "doc_weight_bi/$doc_name", 'w' );
		$wfile_tri = fopen( "doc_weight_tri/$doc_name", 'w' );
		
		$doc_uni[$doc_name] = myfile( "doc_unigram/$doc_name" );
		$doc_bi[$doc_name]  = myfile( "doc_bigram/$doc_name" );
		$doc_tri[$doc_name] = myfile( "doc_trigram/$doc_name" );

		foreach( $doc_uni[$doc_name] as $term => $value ){
			if( isset($idf_uni[$term]) ) $doc_uni[$doc_name][$term] = $value*$idf_uni[$term];
			else $doc_uni[$doc_name][$term] = 0;
			fwrite( $wfile_uni, "".$term." ".$doc_uni[$doc_name][$term]."\n" );
		}
		foreach( $doc_bi[$doc_name] as $term => $value ){
			if( isset($idf_bi[$term]) ) $doc_bi[$doc_name][$term] = $value*$idf_bi[$term];
			else $doc_bi[$doc_name][$term] = 0;
			fwrite( $wfile_bi, "".$term." ".$doc_bi[$doc_name][$term]."\n" );
		}
		foreach( $doc_tri[$doc_name] as $term => $value ){
			if( isset($idf_tri[$term]) ) $doc_tri[$doc_name][$term] = $value*$idf_tri[$term];
			else $doc_tri[$doc_name][$term] = 0;
			fwrite( $wfile_tri, "".$term." ".$doc_tri[$doc_name][$term]."\n" );
		}
		fclose( $wfile_uni );
		fclose( $wfile_bi );
		fclose( $wfile_tri );
	}
}
if( !file_exists('query_uni') || !file_exists('query_bi') || !file_exists('query_tri') || $reset==true ) {
	system( "rm query_uni query_bi query_tri" );
	system( "./src/bin/create-ngram -vocab vocab.all -o query_uni -n 1 -encoding utf8 $query_file" );
	system( "./src/bin/create-ngram -vocab vocab.all -o query_bi -n 2 -encoding utf8 $query_file" );
	system( "./src/bin/create-ngram -vocab vocab.all -o query_tri -n 3 -encoding utf8 $query_file" );
	system( "chmod 777 query_uni query_bi query_tri" );
}

system( "php -f vector.php $output_file " );

$time_end = microtime(true);
$runtime = $time_end - $time_start;
echo "runtime is $runtime seconds\n";

function myfile( $file_name ){
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

?>