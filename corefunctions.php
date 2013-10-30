//This forms part of a website I developed for myself, named (whileago.com)
<?php
function flagMessage($messageid) {
	$userip = makesafe($_SERVER['REMOTE_ADDR']);
	$longip = ip2long($userip);
	$queryflags = "SELECT post_id, flagger, type, ip FROM flaggedcontent where post_id = '". $messageid ."' && ip = '". $longip ."'";
	$resultflags = gs_query($queryflags);
	$num_flags = mysqli_num_rows($resultflags);
  	if($num_flags > 0) {
		echo "You can't flag this comment again.";
  		die();
  	} else {
  		doFlag($messageid, $longip);
	}
}
function doFlag($messageid, $longip) { //messageid, amount of times to flag
	$MAX_TIMES_FLAG = 4;
	$num_flags = getTimesFlagged($messageid);
	if($num_flags >= $MAX_TIMES_FLAG) {
		setCommentFlagged($messageid, 1);
	}
	toFlaggedContentTable($messageid, -1, 1, $longip);
}
function setCommentFlagged($commentid, $flagged) {
	$updatequery = "UPDATE `news_posts` SET flagged = '". $flagged ."' where id = '". $commentid ."'";
	gs_query($updatequery);
}
function toFlaggedContentTable($messageid, $userid, $type, $longip) {
	$sqlpostflag = sprintf("INSERT INTO flaggedcontent (post_id, flagger, type, ip, flagdate) VALUES ('%d', '%d', '%d', '%d', NOW())",
					 $messageid,
					 $userid,
					 $type,
					 $longip);
					 
	//end of sprintf experimental
	if(!gs_query($sqlpostflag)) {
		echo "The Flag Could Not Be Sent Because Of An Unexpected Error.";
	} else {
		echo "Your flag has been sent.";
	}
}
function getTimesFlagged($messageid) {
	$queryflags = "SELECT post_id, flagger, type, ip FROM flaggedcontent where post_id = '". $messageid ."'";
	$resultflags = gs_query($queryflags);
	$num_flags = mysqli_num_rows($resultflags);
	return $num_flags;
}
function getTimesLiked($messageid) {	//Used for messages only
	$querylikes = "SELECT id, likeid, author, liker, likestatus, ip FROM likes where likeid = '". $messageid ."'";
	$resultlikes = gs_query($querylikes);
	$num_likes = mysqli_num_rows($resultlikes);
	return $num_likes;
}
function likeMessage($messageid) {
	$userip = makesafe($_SERVER['REMOTE_ADDR']);
	$longip = ip2long($userip);
	$querylikes = "SELECT id, likeid, author, liker, likestatus, ip FROM likes where likeid = '". $messageid ."' && ip = '". $longip ."'";
	$resultlikes = gs_query($querylikes);
	$num_likes = mysqli_num_rows($resultlikes);
  	if($num_likes > 0) {
		echo "You can't like this comment again.";
  		die();
  	} else {
  		toLikesContentTable($messageid, -1, 1, $longip);
	}
}
?>
