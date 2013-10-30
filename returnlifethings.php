<?php
class DetectLifeActivity {
	private $dob;
	private $name;
	private $litersDaily;
	private $computerDaily;
    	public function __construct() {
		//Main constructor (blank)
    	}
	public function setName($setName) {
		$this->name = $setName;
	}
	public function setDob($setDob) {
		$this->dob = $setDob;
	}
	public function setLitersDaily($liters) {
		$this->litersDaily = $liters;
	}
	public function getLitersDaily() {
		return $this->litersDaily;
	}
	public function getLitersInAllLife() {
		return $this->litersDaily * $this->returnAgeInDays();
	}
	public function setComputerHoursDaily($hours) {
		$this->computerDaily = $hours;
	}
	public function getComputerHoursDaily() {
		return $this->computerDaily;
	}
	public function getName() {
		return $this->name;
	}
	public function getDob() {
		return $this->dob;
	}
	public function returnAge() {
		$actualDate = strtotime(date("Y-m-d"));
		$dob = strtotime($this->getDob());
		$time_difference = $actualDate - $dob;
		$seconds_per_year = 60*60*24*365;
		$years = round($time_difference / $seconds_per_year);
		return $years;
	}
	public function returnAgeInDays() {
		return $this->returnAge()*365;
	}
	public function returnAgeInHours() {
		return $this->returnAgeInDays()*24;
	}
	public function returnDailySleepAverage() {
		return 8;
	}
	public function returnLifeSleepAverage() {
		$sleepAverageTime = $this->returnAgeInHours() / 8;
		return $sleepAverageTime;
	}
	public function returnLifeSleepAverageInYears() {
		$sleepAverageTimeInYears = $this->returnLifeSleepAverage() / 24 / 30 / 12;
		return $sleepAverageTimeInYears;
	}
	public function returnInAndOutBedAverage() {
		return round($this->returnAgeInDays()*1.5);
	}
	public function getDailyComputerAverage() {
		return $this->getComputerHoursDaily();
	}
	public function getLifeComputerAverage() {
		return $this->getComputerHoursDaily() * $this->returnAgeInDays();
	}
	public function getLifeComputerAverageInYears() {
		return $this->getLifeComputerAverage() / 24 / 30 / 12;
	}
	
}
$controlUser = new DetectLifeActivity();
$controlUser->setName("Ignacio");
$controlUser->setDob("1992-05-28");
$controlUser->setLitersDaily(2.2);
$controlUser->setComputerHoursDaily(4);
$usersAge = $controlUser->returnAge();
$usersName = $controlUser->getName();
$usersDays = $controlUser->returnAgeInDays();
$usersHours = $controlUser->returnAgeInHours();
$InAndOutOfBedAverage = $controlUser->returnInAndOutBedAverage();
$litersDaily = $controlUser->getLitersDaily();
$litersInAllLife = $controlUser->getLitersInAllLife();
$dailySleepAverage = $controlUser->returnDailySleepAverage();
$sleepAverage = $controlUser->returnLifeSleepAverage();
$sleepAverageInYears = $controlUser->returnLifeSleepAverageInYears();
$computerHoursDaily = $controlUser->getDailyComputerAverage();
$computerLifeAverage = $controlUser->getLifeComputerAverage();
$computerLifeAverageInYears = $controlUser->getLifeComputerAverageInYears();
echo "Your name is $usersName and you're $usersAge years old. You've been living for $usersDays days and $usersHours hours and in average you've been in and out of bed $InAndOutOfBedAverage times.<br>";
echo "Assuming you drink $litersDaily liters of liquid daily, you've drank over $litersInAllLife liters of liquid in your whole life.<br>";
echo "Also knowing that you sleep $dailySleepAverage hours daily, we can proceed to know that you've slept for $sleepAverage hours in your whole life which equals to $sleepAverageInYears years of sleep.<br>";
echo "You also spend $computerHoursDaily hours on the computer daily, which lets us know that you've spent over $computerLifeAverage hours in front of a computer by now, which equals to $computerLifeAverageInYears years in front of a computer, sitting on a chair.<br>";
?>
