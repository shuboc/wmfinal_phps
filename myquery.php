<?php

	error_reporting (E_ERROR);
	set_time_limit(0);
	
	$lyricsPath = "resources/lyrics_hot/";
	$xmlUrl = "resources/playlists2.xml";
	$query_file = "query.txt";

	if( isset($_POST['watermark']) ){
		$query = $_POST['watermark'];
	}
	
	#echo $query . '<br>';	

	//Parsing playlists2.xml		
	$xml = new DOMDocument();
	$xml->load($xmlUrl);

	/*******************************************************************************
	*	matching the query and the themes using regular expression (unicode mode)  *
	*******************************************************************************/
	
	$refSongs = array();
	$lists = $xml->getElementsByTagName('list');
	foreach ($lists as $list){
		$theme = $list->getElementsByTagName('theme')->item(0);

		//Check if the theme matches the query
		$isThemeMatching = False;
		if( preg_match("/$query/u", "/{$theme->nodeValue}/u") ) $isThemeMatching = True;		
		#echo $theme->nodeValue . '<br>';

		$songs = $list->getElementsByTagName('song');
		foreach ($songs as $song){

			//Check if the title of the song matches the query
			$isSongTitleMatching = False;
			if( preg_match("/$query/u", "/{$song->nodeValue}/u") ) $isSongTitleMatching = True;

			if( $isThemeMatching or $isSongTitleMatching ) {
				#echo "<li>{$song->nodeValue}</li><br>";
				#echo $theme->nodeValue . " ";
				#echo $song->nodeValue . '<br>';
				$refSongs[$song->nodeValue] = 0; #dummy value 0
			}			
		}		
	}

	#print_r($refSongs);	
	

	
	$files = scandir($lyricsPath);

	//Use combined lyrics of reference songs to form a new query
	$combinedLyrics = '';
	
	for( $i=0; $i<count($files); $i++){
		$file = $files[$i];
		if( $file != '.' and $file != '..' ){
			$filePath = $lyricsPath . $file;
			#echo $filePath . '<br>';

			try{
				$doc = new DOMDocument();
				$doc->load($filePath);
						
				$title = $doc->getElementsByTagName("title")->item(0)->nodeValue;				
			
				if(isset($refSongs[$title])){
					$author = $doc->getElementsByTagName('author')->item(0)->nodeValue;
					$lyrics = $doc->getElementsByTagName('lyrics')->item(0)->nodeValue;				
					$lid    = $doc->getElementsByTagName('lid')->item(0)->nodeValue;
					#echo $author . '<br>';
					#echo $title  . '<br>';
					#echo $lyrics . '<br>';

					//Use combined lyrics of reference songs to form a new query
					$combinedLyrics .= $lyrics;
				}
			} catch (Exception $e){
				//Do nothing...
			}
		}
	}

	if ( $combinedLyrics == '' ) {
		$found = "false";
		$combinedLyrics = $query;
	}
	else $found = "true";
	
	$file = fopen($query_file, "w") or die("Unable to open $query_file");
	fwrite($file, $combinedLyrics);
	fclose($file);
	
	header( "Location: recommend.php?watermark=$query&found=$found" ) ;	

?>
