<?php
class MakePasswords {
	public function __construct() {
	//Main constructor (blank)
	//Nothing to initialize
	}
	public function hashPassword($stringparse) {
		$myValue = NULL; //Initialize myValue.. Don't do this with pointers, initializing a pointer as null will crash any application if it's done in C++.
		for($c=0; $c<strlen($stringparse); $c++) {
			$returnVal = $this->returnNumberForLetter(strtolower($stringparse[$c]));
			if($returnVal != -1) { //If it's not invalid
				$myValue .= $returnVal; //Check char by char
			}
		}
		return md5($myValue*256); //Multiply it by 256, making it an enormous number..
	}
	public function returnNumberForLetter($specstring) {
		$letters = array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		//I do not think using an array is the correct way to do this but this is for testing purposes
		if(array_search($specstring, $letters)) { //If a match is found
			$returnIndex = array_search($specstring, $letters);
			return $returnIndex; //Let's hash an index instead of a letter
		} else {
			return -1; //Return null, we couldn't assimilate anything..
		}
	}
}
$createString = new MakePasswords();
$value = $createString->hashPassword("123456");
echo "Value: $value\n";
//End of file
?>
