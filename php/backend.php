<?php

/**
* functions:
*	line ** : Post Definitions
*	line ** : genQuestions
*	line ** : genAnswers
*	line ** : checkAnswers
*/


$jsonFile = file_get_contents($[$_SERVER_ROOT].'../appdata.json');
$json = json_decode($jsonFile, true);

/**
 * Check the type of POST request from frontend and respond accordingly.
 * Verifies there were no JSON Parse errors before proceeding.
 */
if (isset($_POST['Get Question'])) {
	if (json_last_error() == JSON_ERROR_NONE) {
		$questionType = rand(0, 1);
		return json_encode(genQuestion($json, $questionType));
	} else {
		// handle non-JSON error
	}
} elseif (isset($_POST['Check Answer'])) {
	return json_encode(checkAnswer($json, $_POST['Check Answer']));
} else{
	throw new Exception("Error Processing Request", 1);
	
}

/**
 * Generates a new question to send to the frontend.
 * @param  {array} $data         JSON data from appdata.json.
 * @param  {int} $questionType Integer value representing question type 0 = attribute of, 1 = definition of.
 * @return {array}               Returns the question and a set of answers for the frontend to display. Format array("Question" => "question string", "Answers" => array("answer strings"));
 */
function genQuestion($data, $questionType) {
	$domain =  $data['Domains'][rand(0, 7)];
	$size = count($data[$domain]['Attributes']) - 1;
	$attribute = $data[$domain]['Attributes'][rand(0, $size)];
	$question = null;

	if ($questionType == 0) { // Attribute of?
		$question = sprintf("_____ is an attribute of %s.", $domain);
	} elseif ($questionType == 1) {
		$question = sprintf("_____ is the definition of %s.", $attribute);
	} else {
		throw new Exception("Error Processing Request", 1);
		
	}

	$answers = genAnswers($data, $questionType, $question, $domain);
	return [
		"Question" => $question,
		"Answers" => $answers
	];
}

/**
 * Generates 1 correct and 3 incorrect answers to a given question. Shuffles answers before returning.
 * @param  {array} $data         JSON data from appdata.json.
 * @param  {int} $questionType Integer value representing question type 0 = attribute of, 1 = definition of.
 * @param  {string} $question     A question generated by genQuestion().
 * @param  {string} $domain       The domain used of the chosen attribute (for questionType 1) from genQuestion().
 * @return {array}               The shuffled array of generated answers.
 */
function genAnswers($data, $questionType, $question, $domain) {
	
	if ($questionType == 0) { // Attribute of?
		$size = count($data[$domain]['Attributes']) -1;
		$answers[] = $data[$domain]['Attributes'][rand(0, $size)]; // insert correct answer

		for ($i = 0; $i < 3; $i++) {
			$tmpDomain = null;

			do {
				$tmpSize = count($data['Domains']) - 1;
				$tmpDomain = $data['Domains'][rand(0, $tmpSize)];
			} while ($tmpDomain == $domain);

			do {
				$tmpSize = count($data['Domains']) - 1;
				$tmpAttribute = $data[$tmpDomain]['Attributes'][rand(0, $size)];
			} while (in_array($tmpAttribute, $answers));

			$answers[] = $tmpAttribute;
		}

		shuffle($answers);
		return $answers;
	} elseif ($questionType == 1) { // Definition of?
		$words = explode(' ', $question);
		$attribute = substr(array_pop($words), 0, -1);
		$answers[] = $data[$domain][$attribute]; // insert correct answer

		for ($i = 0; $i < 3; $i++) {
			$tmpSize = count($data['Domains']) - 1;
			$tmpDomain = $data['Domains'][rand(0, $tmpSize)];

			do {
				$tmpSize = count($data[$tmpDomain]['Attributes']) - 1;
				$tmpAttribute = $data[$tmpDomain]['Attributes'][rand(0, $tmpSize)];
				$tmpDefinition = $data[$tmpDomain][$tmpAttribute];
			} while (in_array($tmpAttribute, $answers));

			$answers[] = $tmpDefinition;
		}
		else { //invalid request
			throw new Exception("Error Processing Request", 1);
		}

		shuffle($answers);
		return $answers;
	}
}

/**
 * Checks a users answer  sent through from frontend.
 * @param  {array} $data     JSON data from appdate.json.
 * @param  {array} $userData The question and selected answer sent by frontend. Format: array("Question" => "question string", "Answers" => "users selected answer string").
 * @return {boolean}           Returns true for correct answer, false for incorrect answer.
 */
function checkAnswer($data, $userData) { // call on answer attempt
	$questionType = null;

	if (strstr($userData['Question'], "attribute"))
		$questionType = 0;
	elseif (strstr($userData['Question'], "definition"))
		$questionType = 1;

	$words = explode(' ', $userData['Question']);
	$actor = substr(array_pop($words), 0, -1);

	if ($questionType == 0) { // Attribute of?
		foreach ($data[$actor] as $key => $value) {
			if ($key == $userData['Answer'])
				return true;
		}

		return false;
	} elseif ($questionType == 1) { // Definition of?
		foreach ($data['Domains'] as $value) {
			foreach ($data[$value] as $v) {
				if ($v == $userData['Answer'])
					return true;
			}
		}

		return false;
	}
}
 ?>
