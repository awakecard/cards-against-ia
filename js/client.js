var score = 0;
var tickerLength = 0;
var domain;

$(document).ready(function() {
  newQuestion();
});

$('.white').click(function(e) {
  var data = {
    question: $('.black p').text().replace(/ /g, '/'),
    answer: $(this).find('p').text(),
    domain: domain
  };
  var formattedQuestion = $('.black p').text();

  if(data.question.indexOf('definition') < 0) {
    data.answer = data.answer.replace(/ /g, '/');
  }

  var response = $.post('php/backend.php', {
    checkAnswer: data
  });

  response.done(function(mark) {
    if(mark == "true") {
      incrementScore(score);
      updateTicker(true, formattedQuestion);
      newQuestion();
    } else {
      updateTicker(false, formattedQuestion);
      newQuestion();
    }
  });
});

function newQuestion() {
  var response = $.post('php/backend.php', {
    getQuestion: true
  });

  response.done(function(data) {
    var cards = $.parseJSON(data);
    var answerCount = 0;
    var question = cards.question.replace(/\//g, ' ');
    domain = cards.domain;

    $('.black').html('<p>' + question + '</p>');
    $('.row').find('.white').each(function() {
      $(this).html('<p>' + cards.answers[answerCount].replace(/\//g, ' ') + '</p>');
      answerCount += 1;
    });
  });
}

function incrementScore() {
  score += 1;
  $('.score span').text(score);
}

function updateTicker(result, question) {
  var line = '<p>' + question;

  if(result) {
    line += ' <span class="glyphicon glyphicon-ok text-success"></span>';
  } else {
    line += ' <span class="glyphicon glyphicon-remove text-danger"></span>';
  }

  line += '</p>';
  $('.results').prepend(line);

  if(tickerLength == 9) {
    $('.results p').last().remove();
  } else {
    tickerLength += 1;
  }
}
