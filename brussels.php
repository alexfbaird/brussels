<html>
<body>

<form method="get">
You know what to do: <input type="text" name="question"><br>
<input type="hidden" name="status" value="true">
<input type="Submit">
</form>

<?php
//4-19-18 update: made the code more stable. apostrophes no longer break the code. added API key rotation. added error messages.
//3-19-19 update: cleaned the code. accounted for strange apostrophe character. better comments.

if($_GET["status"]){
		$questionInput = $_GET["question"];
	
		//Makes string searchable
		$questionInputOne = str_replace(' ', '+', $questionInput);
		$questionInputOne = str_replace("\"", '%22', $questionInputOne);
		$question = str_replace("'", '%27', $questionInputOne);
		$question = str_replace("’", '%27', $questionInputOne);
		$question = str_replace("‘", '%27', $questionInputOne);
		
		//An array of API keys in case the calls are used up for some reason.
		$apiKeys = array("AIzaSyCKiMd_LrboGdZ4XTM8H1KBF7lt6D9bqlY", "AIzaSyAHvOEInnoQlz2MoF0tX2DCwY-h0ptkDIE","AIzaSyBwG9ABQIYnVaQxmkCr05Y3Qf0XDghbyh4","AIzaSyAkXRT7QfWzPJ-IzOCFvmVCZyDd1dj6fM8","AIzaSyBVXaU5Ob9ZqaIHH5YyWz3SRNxfLksPDp4","AIzaSyCrOdKk39oM58C3mNoD6u24OIJ_E1ylrqs") ;
		$cxKeys= array("012247437574307805448:cfkezvkuhh8","012247437574307805448:9gyfnzgjfjw","012247437574307805448:r9syz8_0wu8","012247437574307805448:blww7dopxlg");

		$currentKey = 0;
		$currentCx = 0;
		//debug line- prints search engine-ized question
		//print_r($question);
		
		//Calls the Google API in order to get the Quizlet URL. Rotates API key if needed.
		$flagGo = 0;
		while($flagGo == 0){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/customsearch/v1?key=".$apiKeys[$currentKey]."&cx=".$cxKeys[$currentCx]."&whitespace=1&q=\"".$question."\"");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$googleData = curl_exec($ch);
			curl_close($ch);
			$googleDataJ = json_decode($googleData, true);
		
		
			//debug line- prints google JSON data
			//print_r($googleDataJ);
			
			//If there is an error, it is assigned to the noGoogle variable
			$noGoogle = $googleDataJ['error']['code'];
			
			//debug line- Displays error code
			//print_r($noGoogle);
			

			//Explains the error to the user
			if($noGoogle == "403"){
				
				if($currentKey <= 5){
					$currentKey = $currentKey + 1;
				}
				if($currentKey > 5){
					print("You used up all your API calls. That's actually kind of impressive.");
					exit();
				}
				
					
			}
			
			else if($noGoogle == "400"){
				if($currentCx <= 3){
					$currentCx = $currentCx + 1;
				}
				if($currentCx > 3){
					print("Google is angry because there is a problem with the code. Try again later.");
					exit();
				}
			}
			
			else if($noGoogle =! 1){
				print($noGoogle."Something went wrong. That's all we know.");
				exit();
				}
	
			
			
			
			else{
			$flagGo = 1;			
			}
			
			
		}
		
		//Isolates the URL from the JSON mess
		$urlBoi = $googleDataJ['items'][0]['link'];
		
		//another debug line- prints url
		//print_r($urlBoi);
		
		//Extracts Set ID from url
		$setIDArray = explode("/",$urlBoi);
		$setID = $setIDArray[3];
		
		//yet another debug line- prints the set id
		//print_r ($setID);
		
	
	
		//Calls the Quizlet API to pull up the set which corresponds to the ID
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.quizlet.com/2.0/sets/".$setID."?client_id=HMfHNsgGYT");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$setTerms = curl_exec($ch);
		curl_close($ch);
		
		$setTermsJ = json_decode($setTerms);
		
		//Debug line- prints set terms JSON data
		//print_r($setTermsJ);
		

		//If there is an error, assigns error code to variable noQuizlet
		$noQuizlet = $setTermsJ->http_code;
		
		//debug line- if google couldn't find a set, this should be 400.
		//print_r($noQuizlet);
		
		if($noQuizlet == "400"){
		print_r("Error: Unable to retrieve set (how do i know if I'M prengan?)");
		exit();
		}
		
		
		
		
		foreach($setTermsJ->terms as $item)
		{
		
			//Assigns current term and definition to a variable for comparison
			$temp = $item->definition;
	    	$tempT = $item->term;
	    		
	    	//Puts term, definition, and user input in lowercase and exchanges characters that don't like to play nice
	    	$lowerTemp = strtolower($temp);
	    	$lowerTemp = str_replace("\"", 'a', $lowerTemp);
			$lowerTemp = str_replace("'", 'b', $lowerTemp);
			$lowerTemp = str_replace("’", 'b', $lowerTemp);
			$lowerTemp = str_replace("‘", 'b', $lowerTemp);
	    		
	    	$lowerTempT = strtolower($tempT);
	    	$lowerTempT = str_replace("\"", 'a', $lowerTempT);
			$lowerTempT = str_replace("'", 'b', $lowerTempT);
			$lowerTempT = str_replace("’", 'b', $lowerTempT);
			$lowerTempT = str_replace("‘", 'b', $lowerTempT);
	    		
	    	$lowerQuestion = strtolower($questionInput);
	    	$lowerQuestion = str_replace("\"", 'a', $lowerQuestion);
			$lowerQuestion = str_replace("'", 'b', $lowerQuestion);
			$lowerQuestion = str_replace("’", 'b', $lowerQuestion);
			$lowerQuestion = str_replace("‘", 'b', $lowerQuestion);
	    	
			
			//Compares user input to term and definition, prints if there is a match.
			if (strpos($lowerTemp, $lowerQuestion) !== false || $lowerTemp == $lowerQuestion) {
	   		$finalResult = $item->term;
	        	print_r($finalResult ." ");
	        	exit();
	    	}
	    		
	    	else if (strpos($lowerTempT, $lowerQuestion) !== false || $lowerTemp == $lowerQuestion) {
	   			$finalResult = $item->definition;
	        	print_r($finalResult ." ");
	        	
	        	exit();
	        	
	    	}

		}
		
		
	
}
	


?>
	

</body>
</html>


