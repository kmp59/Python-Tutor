<?php

$hostname = "sql1.njit.edu";
$username = "ap2257";
$project  = "ap2257";
$password = "Abhilay68";

$db = mysqli_connect($hostname,$username, $password ,$project);
if (mysqli_connect_errno())
  {	  
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

mysqli_select_db( $db, $project ); 

$struct = $_POST [ "struct" ] ;
$UCID = $_POST [ "UCID" ] ;
$newQ = $_POST [ "newQ" ] ;
$testcase = $_POST [ "testcase" ] ;
$funcName = $_POST [ "funcName" ] ;
$parameters = $_POST [ "parameters" ] ;
$examQ = $_POST [ "examQ" ] ; //comma delimited
$examName = $_POST [ "examName" ] ;
$examID = $_POST [ "examID" ] ;
$category = $_POST [ "category" ] ;
$difficulty = $_POST [ "difficulty" ] ;
$grade = $_POST [ "grade" ] ;
$answer = $_POST [ "answer" ] ;
$questionID = $_POST [ "questionID" ] ;
$parameterVal = $_POST [ "parameterVal" ] ;
$points = $_POST [ "points" ] ;
$constraints = $_POST [ "constraints" ] ;
$reasons = $_POST [ "reasons" ] ;
$pointsReceived = $_POST [ "pointsReceived" ] ;
$teacherComments = $_POST [ "teacherComments" ] ;
$cat = $_POST [ "cat" ] ;
$diff = $_POST [ "diff" ] ;
$key = $_POST [ "key" ] ;
$con = $_POST [ "con" ] ;


if ($struct == 'addQuestion'){
	$storeq = str_replace("'","''",$newQ);
	$s = "INSERT INTO questions (question,category,difficulty,constraints)
	VALUES ('$storeq','$category','$difficulty','$constraints')";
	mysqli_query($db,$s);
	$s ="select * from questions where question = '$storeq' ORDER BY questionID DESC";
	$t = mysqli_query($db,$s);
	$row=mysqli_fetch_row($t);
	$qid = $row[0];
	$storet = explode("~",$testcase);
	//$storef = explode("~",$funcName);
	$storepar = explode("~",$parameters);
	//$storep = explode("~",$parameterVal);
	$i = 0;
	foreach ($storet as &$whocares) {
		$storep = str_replace("'","''",$storepar[$i]);
		$storeg = str_replace("'","''",$storet[$i]);
		$s = "INSERT INTO testcases (questionID,testcase,functionName,parameters,parameterVal) values ('$qid', '$storeg', '$funcName', '$storep', '$parameterVal')";
		mysqli_query($db,$s);
		$i++;
	}
	//echo "inserted";
}
if ($struct == 'makeExam'){
	$s = "insert into exams (examName) VALUES ('$examName')"; 
	mysqli_query($db,$s);
	$s="SELECT examID from exams where examName='$examName'";
	$t=mysqli_query($db,$s);
	$row=mysqli_fetch_row($t);
	$theExam = $row[0];
	$qArray = explode(',', $examQ);
	$pArray = explode('~', $points);
	$i = 0;
	foreach($qArray as $entry){
		$s = "UPDATE questions
		SET examID = '$theExam'
		where questionID = '$entry'";
		mysqli_query( $db,  $s );
		$s = "UPDATE questions
		SET points = '$pArray[$i]'
		where questionID = '$entry'";
		mysqli_query( $db,  $s );
		$i++;
	}
}
if ($struct == 'takeExam'){
	$s="SELECT examID from exams where examName='$examName'";
	$t=mysqli_query($db,$s);
	$row=mysqli_fetch_row($t);
	$theExam = $row[0];
	//echo "the exam is $examName";
	$s="SELECT * from questions where examID='$theExam'";
	$t=mysqli_query($db,$s);
	while($row = mysqli_fetch_array($t)){
		$questionID = $row[0];
		$questionText = $row[1];
		$pointss = $row[5];
		$data[] = array(
			"questionID" => $questionID,
			"questionText" => $questionText,
			"points" => $pointss,
			"examName"=> $theExam
			);
	}	
	echo json_encode($data);
	//put all examQ in string comma delimited
	//return the exam in an array using JSON
	}
if ($struct == 'showAll'){
	$s="SELECT * from questions";
	$t=mysqli_query($db,$s);
	while($row = mysqli_fetch_array($t)){
		$questionID = $row[0];
		$questionText = $row[1];
    $questionCategory = $row[2];
		$questionDifficulty = $row[3];
		$data[] = array(
			"questionID" => $questionID,
			"questionText" => $questionText,
      "questionCategory" => $questionCategory,
      "questionDifficulty" => $questionDifficulty
			);
	}
	echo json_encode($data);
}

if ($struct == 'viewExams'){
	$s="SELECT * from exams";
	$t=mysqli_query($db,$s);
	while($row = mysqli_fetch_array($t)){
		$examName = $row[1];
		$data[] = array(
			"examName" => $examName
			);
	}
	echo json_encode($data);
}

if ($struct == 'viewStudents'){
	$s="SELECT * from people where role='student'";
	$t=mysqli_query($db,$s);
	while($row = mysqli_fetch_array($t)){
		$stud = $row[0];
		$fra = $row[3];
		$data[] = array(
			"UCID" => $stud,
			"grade" => $fra
			);
	}
	echo json_encode($data);	
}

if ($struct == 'storeAnswers'){
	//give me examName, examQ, UCID, answer
	$s="SELECT examID from exams where examName='$examName'";
	$t=mysqli_query($db,$s);
	$row=mysqli_fetch_row($t);
	$theExam = $row[0];
	$qArray = explode(',', $examQ);
	$aArray = explode('~', $answer);
	$count = count($qArray);
	//echo $count;
	for($i=0; $i < $count; $i++){
		$store = str_replace("'","''",$aArray[$i]);
		$s = "INSERT INTO answers (examID,questionID,UCID,answer)
		VALUES ('$theExam', '$qArray[$i]','$UCID','$store')";
		mysqli_query($db,$s);
	}
}
if ($struct == 'gradeExam'){
	$i = 0;
	//try to grade quesiton by question instead of this stupid way
	$s="SELECT * from testcases where questionID='$questionID'";
	$q="SELECT * from questions where questionID='$questionID'";
	$t=mysqli_query($db,$s);
	$r=mysqli_query($db,$q);
	$rowed=mysqli_fetch_row($r);
	$cons = $rowed[6];
	$poi = $rowed[5];
	$exmID = $rowed[4];
	while($row=mysqli_fetch_row($t)){
		$func = $row[2];
		$pval = $row[4];
		if($i == 0){
			$params .= $row[3];
			//$function .= $row[2];
			$result .= $row[1];
			//$parameterVal .= $row[4];
			//$points .= $poi
			//$send = [$params, $function, $questionID, $result, $parameterVal];
		}
		else{
			$params .= "~";
			//$function .= "~";
			$result .= "~";
			//$parameterVal .= "~";
			//$points .= "~";
			$params .= $row[3];
			//$function .= $row[2];
			$result .= $row[1];
			//$parameterVal .= $row[4];
			//$points .= $rowed[4];
		}
		$i++;
	}
	
	echo '{"parameter":"'.$params.'", "functions":"'. $func.'", "examID":"'. $exmID.'", "questionID":"'.$questionID.'", "results":"'.$result.'", "parameterVal":"'.$pval.'", "points":"'.$poi.'", "constraints":"'.$cons.'"}';
}
if ($struct == 'storeComment'){
	$storeq = str_replace("'","''",$reasons);
	$s = "INSERT INTO comments (questionID,UCID,pointsReceived,reasons,examID)
		VALUES ('$questionID', '$UCID','$pointsReceived','$storeq','$examID')";
		mysqli_query($db,$s);
	//store reasons
	//store pointsReceived
	//store UCID
	//store questionID
} 
if ($struct == 'storeGrade'){
	$s = "UPDATE people
	SET betaGrade = '$grade'
	where UCID = '$UCID'";
	mysqli_query( $db,  $s );
}
if ($struct == 'releaseGrade')
{

	$qids = explode("~",$examQ);
	$teachCom = explode("~",$teacherComments);
	$pointsRec = explode("~",$pointsReceived);
	$i = 0;
	$pointss = 0;
	$maxPoints = 0;
	foreach($qids as $question){
		$u="SELECT * from questions where questionID='$question'";
		$v=mysqli_query($db,$u);
		$row=mysqli_fetch_row($v);
		$maxPoints += $row[5];
		$pointss += $pointsRec[$i];
		
		$s = "UPDATE comments
		SET teacherComments = '$teachCom[$i]', pointsReceived = '$pointsRec[$i]'
		where UCID = '$UCID' and questionID = '$question'";
		mysqli_query( $db,  $s );
		$i++;
	}
	
	$gr = (($pointss/$maxPoints)*100);
	$grader = round($gr);
	
	$s = "UPDATE people
	SET betaGrade = '$grader'
	where UCID = '$UCID'";
	mysqli_query( $db,  $s );
	
	$s="SELECT betaGrade from people where UCID='$UCID'";
	$t=mysqli_query($db,$s);
	$row=mysqli_fetch_row($t);
	$theGrade = $row[0];
	$s = "UPDATE people
	SET releaseGrade = '$theGrade'
	where UCID = '$UCID'";
	mysqli_query( $db,  $s );
}
if ($struct == 'getMetrics')
{
	$s ="select examID from answers where UCID = '$UCID' ORDER BY examID DESC";
	$t = mysqli_query($db,$s);
	$row=mysqli_fetch_row($t);
	$theExam = $row[0];
	$s ="select examName from exams where examID = '$theExam'";
	$t = mysqli_query($db,$s);
	$row=mysqli_fetch_row($t);
	$theExamName = $row[0];
	
	$s = "select ans.answer, ans.questionID, qs.examID, qs.question, qs.points, com.pointsReceived, com.reasons , com.teacherComments
          from answers ans 
          inner join questions qs on ans.questionID = qs.questionID 
          inner join comments com on ans.questionID = com.questionID 
			where ans.examID = '$theExam' and qs.examID = '$theExam' and com.examID = '$theExam'
			group by questionID";
	$t = mysqli_query($db,$s);
	while($row = mysqli_fetch_array($t)){
		$answer = $row[0];
		$questionID = $row[1];
		$question = $row[3];
		$pointss = $row[4];
		$pointsRec = $row[5];
		$reasonss = $row[6];
		$teachCom = $row[7];
		
		$data[] = array(
			"answer" => $answer,
			"questionID" => $questionID,
			"question" => $question,
			"points" => $pointss,
			"pointsReceived" => $pointsRec,
			"teacherComments" => $teachCom,
			"reasons"=> $reasonss
			);
	}	
	echo json_encode($data);
	
	

}
if ($struct == 'viewGrade')
{
	$s="SELECT releaseGrade from people where UCID='$UCID'";
	$t=mysqli_query($db,$s);
	$row=mysqli_fetch_row($t);
	$theGrade = $row[0];
	
	if(is_null($row[0])){exit();}
	
	$s ="select examID from answers where UCID = '$UCID' ORDER BY examID DESC";
	$t = mysqli_query($db,$s);
	$row=mysqli_fetch_row($t);
	$theExam = $row[0];
	$s ="select examName from exams where examID = '$theExam'";
	$t = mysqli_query($db,$s);
	$row=mysqli_fetch_row($t);
	$theExamName = $row[0];
	
	
	
	$s = "select ans.answer, ans.questionID, qs.examID, qs.question, qs.points, com.pointsReceived, com.reasons , com.teacherComments
          from answers ans 
          inner join questions qs on ans.questionID = qs.questionID 
          inner join comments com on ans.questionID = com.questionID 
			where ans.examID = '$theExam' and qs.examID = '$theExam' and com.examID = '$theExam'
			group by questionID";
	$t = mysqli_query($db,$s);
	while($row = mysqli_fetch_array($t)){
		$answer = $row[0];
		$questionID = $row[1];
		$question = $row[3];
		$pointss = $row[4];
		$pointsRec = $row[5];
		$reasonss = $row[6];
		$teachCom = $row[7];
		
		$data[] = array(
			"answer" => $answer,
			"questionID" => $questionID,
			"question" => $question,
			"points" => $pointss,
			"pointsReceived" => $pointsRec,
			"teacherComments" => $teachCom,
			"reasons"=> $reasonss,
			"grade"=> $theGrade
			);
	}	
	echo json_encode($data);
	
}

if ($struct == 'filter'){	

	$keyR = "";
	$catR = "1=1";
	$diffR = "1=1";
	$conR = "1=1";
	
	if($key!=""){$keyR = $key;}
	if($cat!=""){$catR = "category = '$cat'";}
	if($diff!=""){$diffR = "difficulty = '$diff'";}
	if($con!=""){$conR = "constraints = '$con'";}
	
	$s = "SELECT * FROM questions WHERE question LIKE '%$keyR%' and $catR and $diffR and $conR";
	//echo "here";
	$t=mysqli_query($db,$s);
	$len = mysqli_num_rows($t);
	if ($len == 0){
		echo "no match";
		exit();
		}
	//echo "here2";
	while($row = mysqli_fetch_array($t)){
		$id = $row[0];
		$text = $row[1];
		$category = $row[2];
		$difficulty = $row[3];
		$constraints = $row[6];
		$data[] = array(
			"id" => $id,
			"text" => $text,
			"category" => $category,
			"difficulty" => $difficulty,
			"constraints" => $constraints
			);
	}	
	//echo "here3";
	echo json_encode($data);
}

mysqli_free_result($s);
mysqli_close($db);
exit();

?>
