<?php
$level = new Level;

class Level {
	public $level = 1;
	public $filenamePrefix = 'treasure-hunt-';
	
	protected $maxLevel = 1;
	protected $salt = 'treasure-hunt-';
	
	protected $answer = [
		'The Crown' => '输入店名以获得下一个提示',
		'Peer' => '输入第12种水果的英文名以获得下一个提示',
		'1013' => '输入四个号码的答案以获得下一个提示',
		'Penang First MMC' => '输入地点名字以获得下一个提示',
		null,
		null,
		'S24N13P' => '请输入宝石密码',
	];
	
	public function hasQuestionSolved() {
		return $this->maxLevel > $this->level;
	}
	
	public function hasQuestion() {
		$question = $this->getCurrentQuestion();
		return isset($question);
	}
	
	public function getCurrentQuestion() {
		$questions = array_values($this->answer);
		return isset($questions[$this->level - 1]) ? $questions[$this->level - 1] : null;
	}
	
	public function answer($answer) {
		$answers = array_keys($this->answer);
		return $this->normalizeAnswer($answers[$this->level - 1]) == $this->normalizeAnswer($answer);
	}
	
	public function getCurrentImage() {
		return $this->getImage($this->level);
	}
	
	public function setLevelByCode($code, $goBackLevel = null) {
		$this->maxLevel = $this->getLevelByCode($code);
		$this->level = isset($goBackLevel) ? $goBackLevel : $this->maxLevel;
	}
	
	protected function getLevelByCode($code) {
		$level = $this->decode($code);
		
		if (!is_numeric($level)) {
			throw new \Exception('Invalid Code: '.$level);
		}
		
		return $level;
	}
	
	public function getPreviousLevelUrl() {
		$level = $this->level - 1;
		return '?level='.$level.'&code='.$this->getCode($this->maxLevel);
	}
	
	public function getNextLevelUrl() {
		if ($this->maxLevel > $this->level) {
			return '?level='.($this->level + 1).'&code='.$this->getCode($this->maxLevel);
		} else {
			$level = $this->level + 1;
			return '?code='.$this->getCode($level);
		}
	}
	
	protected function normalizeAnswer($answer) {
		$answer = trim(strtolower($answer));
		return $answer;
	}
	
	protected function encode($level) {
		$salt = $this->salt.'_';
		return base64_encode($salt.$level);
	}
	
	protected function decode($code) {
		$salt = $this->salt.'_';
		
		$decoded = base64_decode($code);
		return substr($decoded, strlen($salt));
	}
	
	protected function getCode($level) {
		return $this->encode($level);
	}
	
	protected function getImage($level) {
		$levelName = str_pad($level, 2, '0', STR_PAD_LEFT);
		$filename = $this->filenamePrefix.$levelName.'.jpg';

		return $filename;
	}
}

$code = isset($_GET['code']) ? $_GET['code'] : null;
$goLevel = isset($_GET['level']) ? $_GET['level'] : null;

if (isset($code)) {
	$level->setLevelByCode($code, $goLevel);
}

$answer = isset($_POST['answer']) ? $_POST['answer'] : null;

if (isset($answer)) {
	if ($level->answer($answer)) {
		$url = $level->getNextLevelUrl();
		header('Location: '.$url);
	} else {
		echo '<script>alert("回答错误")</script>';
	}
}
?>
<!doctype html>
<html lang="zh">
<head>
  <title>寻宝游戏</title>
    <meta charset="UTF-8">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { background-color: #EEEEEE; }
.image { width: 100%; }
.container { max-width: 596px; margin: auto; }
</style>
</head>
<body>

<div class="container">
<img class="image" src="<?= $level->getCurrentImage() ?>" />
<?php if ($level->level > 1): ?>
	<a href="<?= $level->getPreviousLevelUrl() ?>">Prev</a>
<?php endif ?>
<?php if ($level->hasQuestion() && !$level->hasQuestionSolved()): ?>
<form method="post">
	<p><?= $level->getCurrentQuestion() ?></p>
	<input name="answer" />
	<input type="submit" />
</form>
<?php endif ?>
<?php if ($level->hasQuestionSolved()): ?>
	<a href="<?= $level->getNextLevelUrl() ?>">Next</a>
<?php endif ?>
<div style="display: none; ">
yyy:<?= $level->getNextLevelUrl() ?>
</div>
</div>
</body>
</html>