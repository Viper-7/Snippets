<?php
class trivia extends IRCServerChannel
{
	protected $current_question = '';
	protected $current_answer = '';

	protected $trivia_running = false;

	public $scores = array();
	public $question_data = array();
	public $max_questions = 2;
	public $answer_time = 60;
	public $time_between_questions = 5;

	protected $question_asked_ts = null;
	protected $last_answer_ts = null;

	protected $question_index = 0;
	protected $mode = 'question';

	protected function askQuestion()
	{
		if( $this->question_index < $this->max_questions && isset($this->question_data[$this->question_index]) )
		{
			$data = $this->question_data[$this->question_index];
			$this->current_question = $data['question'];
			$this->current_answer = $data['answer'];

			$this->send_msg("Question: {$this->current_question}");
			$this->question_asked_ts = time();

			$this->mode = 'answer';
		} else {
			$this->send_msg("Trivia Over!");
			$this->showScores();
			$this->trivia_running = false;
		}
	}

	protected function nextQuestion()
	{
		$this->last_answer_ts = time();
		$this->current_question = null;
		$this->current_answer = null;
		$this->question_index++;
		$this->send_msg("Next question in {$this->time_between_questions} seconds");
	}

	protected function correctAnswer($nick)
	{
		if(!isset($this->scores[$nick]))
			$this->scores[$nick] = 0;

		$this->scores[$nick] += 1;
		$this->send_msg("The correct answer was {$this->current_answer}. {$nick} gets 1 point!");

		$this->nextQuestion();
	}

	private function dummyData()
	{
		$this->question_data = array(
			array(
				'question' => 'Who pwns?',
				'answer' => 'zlanbot'
			),
			array(
				'question' => 'Who else pwns?',
				'answer' => 'zlanbot2'
			)
		)
	}

	public function poll($cycle)
	{
		if( empty($this->question_data) )
		{
			$this->dummyData();
			shuffle($this->question_data);
		}

		if( $this->trivia_running )
		{
			if( $this->mode == 'question' && $this->last_answer_ts + $this->time_between_questions < time() )
			{
				$this->askQuestion();
			}

			if( $this->mode == 'answer' && $this->question_asked_ts + $this->answer_time < time() )
			{
				$this->nextQuestion();
			}
		}

	}

	protected function showScores()
	{
		arsort($this->scores);
		$winners = array_slice($this->scores, 0, 3, false);

		list($first, $second, $third) = array_keys($winners);

		$this->send_msg("3rd Place on {$winners[$third]} points : {$third}");
		sleep(1);
		$this->send_msg("2nd Place on {$winners[$second]} points : {$second}");
		sleep(1);
		$this->send_msg("1st Place on {$winners[$first]} points : {$first}!");
	}

	public function event_msg($who, $message)
	{
		if( $this->mode == 'answer' )
		{
			if( preg_replace('/\W+/', '', strtolower(trim($message))) == $this->current_answer )
			{
				return $this->correctAnswer($who->nick);
			}
		}

		$message_parts = explode(' ', $message);
		switch($message_parts[0])
		{
			case '!time':
				$this->send_msg('The current date & time is ' . date('r'));
				break;
			case '!spin':
				$user = $this->users[array_rand($this->users)];
				$this->send_msg("{$user->nick} wins!");
				break;
			case '!trivia':	// start trivia
				$this->scores = array();
				if( !empty($message_parts[1]) && ctype_digit($message_parts[1]) )
				{
					$this->max_questions = $message_parts[1];
				}
			case '!rtrivia': // resume trivia
				$this->trivia_running = true;
				$this->send_msg('Trivia Started');
				break;
			case '!strivia': // stop/pause trivia
				$this->trivia_running = false;
				$this->showScores();
				$this->send_msg('Trivia Closed');
				break;
			case '!scores': // show current scores
				$this->showScores();
				break;
		}
	}
}