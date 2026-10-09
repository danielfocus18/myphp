<?php
// //Declaring Varialbes
// $myName = "Samuel";
// $myAge = 20;

// //Outputing some Strings with HTML Embedded and php Varibles 
// echo "<h2>My Name is $myName </h2>";
// echo "<h2>I am Currently $myAge years old</h2>";
// $myAge++;
// echo "<h2>Next Year I will be $myAge years old</h2>";

// $a = 5;
// $b = 4;
// $c = $a + $b;
// echo "<h1> $a + $b = $c </h1>";

// $c = $a - $b;
// echo "<h1> $a - $b = $c </h1>";

// $c = $a * $b;
// echo "<h1> $a x $b = $c </h1>";

// $c = $a / $b;
// echo "<h1> $a / $b = $c </h1>";

// $c = $a % $b;
// echo "<h1> $a Mod $b = $c </h1>";

// $c = $a ** $b;
// echo "<h1> $a ^ $b = $c </h1>";

// $today = date("Y-m-d");
// $curTime = date("H:i:s");
// echo "<h1 align='center'> Today's Date is: $today and the Current time is $curTime</h1>";
// $a = 7;
// $b=2;

// if($a>$b)
// {
//     echo "<h1>$a is Greater than $b </h1>";
// }
// elseif($a===$b)
// {
//    echo "<h1>$a is Equal to  $b </h1>"; 
// }
// else
// {
//     echo "<h1>$a is Less than $b </h1>";
// }

// $countTO = 120;
// $results = "";
// $factorCount = 0;
// for($x = 1; $x<=$countTO; $x++)
// {
//     //echo "<b> $x, </b>";
//     $rem = $countTO % $x;
//     if($rem===0)
//     {
//         $results .= "$x, ";
//         $factorCount++;
//     }
// }
// echo "<h1> $countTO Has $factorCount Factors, They are Listed Below <br/> $results</h1>";


$finalExamMark = array(58,72,89,24,8,91,35,73,84,32,17,49,13,66,71,45,58,67,76);
$finalExamMark[]=18;
$failedMarks = "";
$numFailstudent = 0;

for ($i = 0; $i <= 18; $i++ )
{
    $marks = $finalExamMark[$i];
    if ($marks < 50)
        {
            $failedMarks .= "$marks, ";
           $numFailstudent++;
        }
}
echo "<h2> $numFailstudent Failed the Exams <br> Theirs marks are as followed $failedMarks </h2>";

// $finalExamMarks = array(58,72,89,24,8,91,35,73,84,32,17,49,13,66,71,45,58,67,76);
// $finalExamMarks[]=38;s
// $numOfStudents = count($finalExamMarks);
// echo $numOfStudents;


$studRecords = array('id'=>'1110','fname'=>'John','sname'=>'Quansah','dob'=>'2018-10-01','lvl'=>300);
$studRecords['dept'] = "IT Studies";
$myName = $studRecords['fname']." E. ".$studRecords['sname'];
$nextLvl = $studRecords['lvl'] + 100;
$studMarks = ['ia'=>35,'ex'=>45];
echo "<h1>My Name is $myName <br/> I am Currently in Level  $studRecords[lvl]. <br/> I am in the $studRecords[dept] Department<br/> Next Year I will be in Level $nextLvl <br/> My IA Marks is $studMarks[ia]<br/> My exams Marks is: $studMarks[ex]</h1>";

// Question 4
// Study the defined array holding the marks of several students in an Exams for Web Development 
// $studMarks = (78,92,54,27,72,.,.,,37,71,89,60,39);
// NB:The ,.,,, means there are MORE student marks (ie the Array has more marks not displayed.
// a.Write a PHP snippet of code that calculates the average mark in the Exam
$studMarks = array(78,92,54,27,72,37,71,89,60,39);
$totalOfMarks = array_sum($studMarks);
$count = count($studMarks);
$averageMarks = $totalOfMarks/$count ;

echo "The Average Mark in the exams is $averageMarks";

// b. Write a PHP snippet of code that displays the list of Exams Marks more than the Average Marks Calculated in question 4a above.
$lastIndex = array_key_last($studMarks);
$ExMarksG="";
for ($c=0 ; $c <= $lastIndex; $c++){
    $marker = $studMarks[$c];
    if ($marker > $averageMarks){
        $ExMarksG .= "$marker, ";
    }
};
echo "<h2> the following are the marks more than the average : $ExMarksG </h2>";



// c. Write a PHP snippet code that finds the number of student who obtained an A grade if an A Grade is 80 1o 100.
$studNumber= 0;
for ($g=0 ; $g <= $lastIndex; $g++){
    $marker = $studMarks[$g];
    if ($marker >= 80){
       $studNumber++;
    }
}
echo "<h3>The number of students who obtained an A grade are $studNumber</h3>";