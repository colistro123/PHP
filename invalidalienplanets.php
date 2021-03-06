<?php
//Github doesn't want to indent it.
class MakeStrings {
    public function __construct() {
        //Main constructor (blank)
    }
    public function returnCharIfMatchIsFound($specstring) {
        if(strcmp("_", $specstring) == 0) { //If a match is found
            $valToReturn = "VALID";
        }
        if(!empty($valToReturn)) {
            return $valToReturn;
        } else {
            return "INVALID";
        }
    }
    public function verifyPlanetUser($stringparse) {
        $myValue = NULL; //Initialize myValue.. so it doesn't throw warnings
		for($c=0; $c<strlen($stringparse); $c++) {
			$myValue .= $this->returnCharIfMatchIsFound($stringparse[$c]); //Check char by char
            break;
        }
		return $myValue;
	}
}
$createString = new MakeStrings();
$value = $createString->verifyPlanetUser("_alienplanet01");
echo "$value\n";
$value = $createString->verifyPlanetUser("invalidalienplanet02");
echo "$value\n";
$value = $createString->verifyPlanetUser("invalidalienplanet03");
echo "$value\n";
?>
