$('#quiz').hide();
questionHolder = $('#question');
answerHolder = $('#answer');
questions = [];
doneQuestions = 0;
var editor = CodeMirror.fromTextArea(document.getElementById("answer"), {
	mode: "text/html",
	indentUnit: 4
});
$("#quiz").submit(function(e) {
	e.preventDefault();
	questions[doneQuestions - 1].answer = editor.getValue('');
	getQuestion();
});
$.getJSON("interface.php", function(data) {
	questions = data;
	getQuestion();
});

function getQuestion() {
	if (doneQuestions >= questions.length) {
		submitQuestions();
	} else {
		$('#quiz').show();
		var item = questions[doneQuestions].question;
		questionHolder.html(item);
		editor.setValue('');
		doneQuestions++;
	}
}

function submitQuestions() {
	questionHolder.html("Done! Getting your results");
	$('#quiz').hide();
	var results = JSON.stringify(questions);
	$.post("interface.php", {
		answers: results
	}, function(answers) {
		questionHolder.hide();
		$('.results').html('');
		var correct = 0;
		for (var i = 0; i < answers.length; i++) {
			var question = answers[i]['question'];
			var answer = answers[i]['original'];
			var desired = answers[i]['desired'];
			$('.results').append("<h3>Question: " + question + "</h3>");
			if (answers[i]['correct']) {
				correct++;
				$('.results').append("<p>Nice work! Correct answer!</p>");
			} else {
				$('.results').append("<p>Your answer doesn't match the structure we were looking for! :(</p>");
			}
			$('.results').append("<div class='code'><div><p class='title'>YOUR ANSWER</p><pre class='prettyprint lang-html'>" + answer + "</pre></div><div><p class='title'>ANSWER</p><pre class='prettyprint lang-html'>" + desired + "</pre></div></div>");
		}
		var percent = Math.ceil(correct * 100 / answers.length);
		$('h2').html('Quiz Results - ' + percent + '%, <a href="quiz">Try again?</a>');
		PR.prettyPrint();
	});
}