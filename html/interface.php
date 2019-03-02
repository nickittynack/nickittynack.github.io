<?php

require("backend/htmLawed.php");

function getConn() {
	return new PDO('sqlite:backend/quiz.db');
}
function getQuestions() {
	$conn = getConn();
	// $result = $conn->query('SELECT * FROM questions WHERE ID = 13');
	$result = $conn->query('SELECT * FROM questions ORDER BY RANDOM() LIMIT 3');
	$questions = array();
	foreach($result as $row) {
		$question = array();
		$text = $row['text'];
		$x = rand (1, 4);
		$y = rand (1, 4);
		$z = rand (1, 4);
		$text = str_replace('(x)', $x, $text);
		$text = str_replace('(y)', $y, $text);
		$text = str_replace('(z)', $z, $text);
		$question['id'] = $row['id'];
		$question['question'] = $text;
		$question['x'] = $x;
		$question['y'] = $y;
		$question['z'] = $z;
		array_push($questions, $question);
	}
	header('Content-Type: application/json');
	echo json_encode($questions);
}
function checkQuestions() {
	// Get Regex
	$conn = getConn();
	$result = $conn->query('SELECT * FROM questions');
	$regex = array();
	$cases = array();
	foreach ($result as $question) {
		$regex[$question['id']] = '!' .$question['form'] . '!i';
	}
	$result = $conn->query('SELECT * FROM cases');
	foreach ($result as $case) {
		$cases[$case['id']] = $case['form'];
	}
	// print_r($regex);
	// print_r($cases);
	$answers = json_decode($_POST["answers"]);
	// $answers = json_decode('[{"id":"11","question":"Create a paragraph","x":3,"y":3,"z":3,"answer":"<p>rawR</p>"}]');
	foreach ($answers as $answer) {
		$html = $answer->answer;
		$html = preg_replace('/\s+/', '', $html);
		$pattern = $regex[$answer->id];
		if (strpos($pattern, '(x)') !== false) {
			$repeatPattern = str_repeat($cases['x'], $answer->x);
			$pattern = str_replace('(x)', $repeatPattern, $pattern);
		} else if (strpos($pattern, '(y)') !== false) {
			$repeatPattern = str_repeat($cases['y'], $answer->y);
			$innerRepeatPattern = str_repeat($cases['z'], $answer->z);
			$pattern = str_replace('(y)', $repeatPattern, $pattern);
			$pattern = str_replace('(z)', $innerRepeatPattern, $pattern);
		}
		// Make our desired string
		$desired = substr ($pattern , 2, strlen($pattern) - 5);
		$desired = str_replace('.*', 'stuff', $desired);
		// echo "$pattern";
		$answer->original = htmlspecialchars($answer->answer);
		$answer->answer = formatCode($html);
		$answer->desired = formatCode($desired);
		// Match Time
		$basicString = "[^<,>]*";
		//[^<,>]
		$pattern = str_replace('.*', $basicString, $pattern);
		// $pattern = str_replace('.*', '[a-zA-Z ]*', $pattern);
		$match = preg_match($pattern , $html);
		if ($match === 1) {
			$answer->correct = True;
		} else {
			$answer->correct = False;
		}
	}
	header('Content-Type: application/json');
	echo json_encode($answers);
	// print_r($answers);
}
function formatCode($code) {
	return htmlspecialchars(
		htmLawed($code, array('tidy'=>4, "make_tag_strict"=>0))
    );
} 
// checkQuestions();
if (isset($_POST["answers"])) {
	checkQuestions();
} else {
	getQuestions();
}
?>