<?php

	header('Content-Type:text/html; charset=utf-8');
//	session_start(); 
	$query = $_REQUEST["q"];
	
	$limit = 10;

	$hint = "";
   
	if($query !== "" && !trim($query) == ''){
	

		$pieces = explode(" ", $query);
		$query = array_pop($pieces);
		require_once('Apache/Solr/Service1.php');
// Service1.php is a replica of Service.php with requesthandler as suggest.
		$solr = new Apache_Solr_Service('localhost',8983,'/solr/myexample/');
		$q = htmlspecialchars($query.$temp, ENT_QUOTES, 'utf-8');
		echo $q;
		if(get_magic_quotes_gpc() == 1){ $q = stripslashes($q);}


		try{
			$results = $solr->search($q, 0, $limit); 

		}
		catch(Exception $e){
			 die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
		}


	}
	else{
		
		echo json_encode(""); 
	}

	if($results){
//		print_r($results->suggest);
		$doc = $results->suggest; 
		foreach($doc as $field=>$value) $solution = $value; 
//		print_r($solution); 
		foreach($solution as $field=>$value) $try = $value; 
		$output[] = array();
		for($i = 0; $i < $try->numFound ; $i++){
//			if($strlen($output[$i]) < 15)
				$output[$i] = ($try->suggestions[$i]->term);
		}
//		$_SESSION['output'] = $output; 
//		header('Location: '.$_SERVER['HTTP_REFERER']); 
//		session_destroy(); 
		echo json_encode($output);
	} else echo "Timepass";

?>
