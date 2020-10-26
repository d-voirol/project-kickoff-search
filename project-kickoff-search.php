<?php
//06-02-2015 - Displays table of all existing project kickoffs with ProjectNumber and ProjectName - DGV
//06-03-2015 - Begin work on search capabilities. - DGV 
            //Search by Project Type(Drop Down), Building Type(Drop Down), LEED Only (Checkbox), Principal, Project Manager, Project Name, Project Number
            //Project Type and Building Type dropdowns created: ONLY PRINT out $_POST variables - NO search related query yet.
//06-04-2015 - kickoff_search form created, only echo values at this point.  - DGV
            //Begin work on function to query and display search results
//06-08-2015 - Search Form now linked to MySQL database. search results match submitted form -DGV
    
     
// $_POST variables
$submit_button = isset($_POST['submit_button'])?$_POST['submit_button']:"";
  //if form submitted: value is "Search"; otherwise value is ""
$reset_button = isset($_POST['reset_button'])?$_POST['reset_button']:"";
$project_type = isset($_POST['p_type'])?$_POST['p_type']:"";
  //Str value from dropdown, default "All", blank if form not submitted
  //change value "All" to "" to match database search
  if ($project_type == "All"){
    $project_type = "";
  }
$building_type = isset($_POST['b_type'])?$_POST['b_type']:"";
  //Str value from dropdown, default "All", blank if form not submitted
  //change value "All" to "" to match database search
  if ($building_type == "All"){
    $building_type = "";
  }
$LEED_only = isset($_POST['leed'])?$_POST['leed']:"";
  //value "on" if checked; otherwise value is ""
  //change values to Yes or No to match database search
  if ($LEED_only == "on") {
    $LEED_only = "Yes";
  } else {
      $LEED_only = "";  
    }
$project_name = isset($_POST['p_name'])?$_POST['p_name']:"";

$employee = isset($_POST['emp'])?$_POST['emp']:"";
if ($employee !== "") {
  $employee = strtolower(substr(trim(preg_replace("/[^A-Za-z0-9]/", '', $employee)), 0, 3));
}

//project number and sub number
$project_number = isset($_POST['p_num'])?$_POST['p_num']:"";
//set $display variable to show whatever is typed in previous form
$display = $project_number;
//set empty variable for sub number
$sub_project_number = "";
//check to see if period in string
$period_pos = strpos($project_number, ".");
//echo $period_pos;
//if period exists, split at period into two values
if ($period_pos != "") {
  //echo "<br>Not blank<br>";
  $p_num_parts = explode('.', $project_number);
  $sub_project_number = $p_num_parts[1];
  //switch to int and back to remove extra 0 at beginning
  $sub_project_number = (int)$sub_project_number;
  $sub_project_number = (string)$sub_project_number;
  //make variable $project_number part before period
  $project_number = $p_num_parts[0];
  //echo $sub_project_number;  
}




//include other files to create side bar, top bar
include("common/start-javascript-a.html");
include("common/start-javascript-b.html");
//include formatprojectnumber() function
include("common/formatprojectnumber.php");

//open "content" div
echo "<div id='content'>\n";
echo "<h2><span>MSKTD Project Kickoff Worksheets</span></h2>\n";

// database connection
// set database information
$dbhost = "*******";
$dbuser = "*******";
$dbpass = "*******";
$dbname = "*******";

// Connecting to MySQL database
$con=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (mysqli_connect_errno()){echo "Failed to connect to MySQL".mysqli_connect_error(); }

//MAIN CODE



//echo $submit_button;
//echo $reset_button;

//check variable $submit_button - if value is 'Search', form has been submitted - set $fillform = 1
if ($submit_button == "Search") {
  $fillform = 1;
} else {
    $fillform = "";  
  }

//echo variables for testing
/*
echo $fillform . "<br>";
echo $project_type . "<br>";
echo $building_type . "<br>";
echo $LEED_only . "<br>";
echo $employee . "<br>";
echo $project_name . "<br>";
echo $project_number . "<br>";
*/


//kickoff_search form begins  
echo "<h3>Search Existing Project Kickoffs</h3>\n";
echo "<table>\n";
echo "<form name='kickoff_search' action='" . $_SERVER['PHP_SELF'] . "' method='POST' onSubmit='this.form.submit();'>\n";

// Project Number input text box
echo "<tr><td>Project Number:</td><td><input name='p_num'";
if (($fillform == 1) && ($display !== "")) {      
  echo " value='$display'";
} else {
  echo " value=''";
}
echo " type=text size=50><br>";

// Project Name input text box
echo "<tr><td>Project Name:</td><td><input name='p_name'";
if (($fillform == 1) && ($project_name !== "")) {      
  echo " value='$project_name'";
} else {
  echo " value=''";
}
echo " type=text size=50><br>";

// Employee username
echo "<tr><td>Employee Username (Initials):</td><td><input name='emp'";
if (($fillform == 1) && ($employee !== "")) {      
  echo " value='$employee'";
} else {
  echo " value=''";
}
echo " type=text size=50><br>";

// project type - Drop Down options. name = p_type
if (($fillform == 1) && ($project_type !== "")) {
  echo "<tr><td>Project Type:</td><td><select name='p_type'><option>$project_type</option>\n";
  echo "<option>All</option>\n";
} else {
  echo "<tr><td>Project Type:</td><td><select name='p_type'><option>All</option>\n";
}                       
echo "<option>Airports</option>\n";
echo "<option>Apartments</option>\n";
echo "<option>Churches</option>\n";
echo "<option>Condominiums</option>\n";
echo "<option>Convention Centers</option>\n";
echo "<option>Hospitals</option>\n";
echo "<option>Hotels</option>\n";
echo "<option>Houses</option>\n";
echo "<option>Jails</option>\n";
echo "<option>Libraries</option>\n";
echo "<option>Manufacturing</option>\n";
echo "<option>Mass Transit</option>\n";
echo "<option>Office Buildings</option>\n";
echo "<option>Parking Structures</option>\n";
echo "<option>Pools & Playgrounds</option>\n";
echo "<option>Recreation & Sports</option>\n";
echo "<option>Schools & Colleges</option>\n";
echo "<option>Shopping Centers & Retail</option>\n";
echo "<option>Site Development</option>\n";
echo "<option>Warehouses</option>\n";
echo "</select></td></tr>\n";


//Building Type dropdown
//$building_type stores str() and is linked to TABLE project_kickoff by builbing type number (project_building_type.building_type == project_kickoff.building_type)
//get building_type_name(s) from table project_building_type
$sql = "SELECT pbt.building_type, pbt.building_type_name FROM project_building_type AS pbt ;";
$result = $con->query($sql);
//if from previously submitted AND there is a value for $building_type, display previously selected option
if (($fillform == 1) && ($building_type !== "")) {
    echo "<tr><td>Building Type:</td><td><select name='b_type'><option>$building_type</option>\n";
    echo "<option>All</option>\n";
  } else {
    echo "<tr><td>Building Type:</td><td><select name='b_type'><option>All</option>\n";
  }
while ($row = $result->fetch_assoc()) {
  if ($row['building_type_name'] != $building_type) {
    echo "<option>". $row['building_type_name']."</option>\n";
  }                       
}
echo "</select></td></tr>\n";

//LEED Only checkbox
echo "<tr><td>LEED Only:</td><td>";
echo "<input name='leed' type='checkbox'";
if (($fillform == 1) && ($LEED_only == "Yes")) {      
  echo " checked";
}
echo "></td></tr>\n";

//Search & Reset buttons - END FORM - END TABLE
echo "<tr><td><input name='reset_button' type='Submit' value='Reset'></td><td><input name='submit_button' type='Submit' value='Search'></td></tr>\n";
echo "</form>\n";
echo "</table>\n";







//Display search results. NEW TABLE
echo "<br><h3>Search Results</h3>";

$sql = "SELECT DISTINCT pk.project_uid, p.uid, p.project_type, pbt.building_type_name, pk.leed_cert, GROUP_CONCAT(pt.username ORDER BY pt.username) AS employees, p.project_name, p.company_type, p.project_number, p.sub_project_number ";
$sql .= "FROM project_kickoff AS pk, project AS p, project_building_type AS pbt, project_team AS pt ";
$sql .= "WHERE pt.project_uid = pk.project_uid ";
$sql .= "AND pk.building_type = pbt.building_type ";
$sql .= "AND pk.project_uid = p.uid ";
$sql .= "AND p.project_type LIKE \"%$project_type%\" ";
$sql .= "AND pbt.building_type_name LIKE \"%$building_type%\" ";
$sql .= "AND pk.leed_cert LIKE \"%$LEED_only%\" ";
$sql .= "AND pt.username LIKE \"%$employee%\" ";
$sql .= "AND p.project_name LIKE \"%$project_name%\" ";
$sql .= "AND p.project_number LIKE \"%$project_number%\" ";
if ($sub_project_number) {
  $sql .= "AND p.sub_project_number LIKE \"%$sub_project_number%\" ";
}  
$sql .= "GROUP BY pk.project_uid ";
$sql .= "ORDER BY pk.project_uid ";
$sql .= ";";
//echo $sql;
$result = $con->query($sql);

if ($result->num_rows > 0) 
	{
	//echo "Success!";
  
  echo "<table>";
  echo "<tr><td><b>Project Number</b></td><td><b>Project Name</b></td><td><b>Employee(s)</b></td><td><b>Project Type</b></td><td><b>Building Type</b></td><td><b>LEED Certified</b></td></tr>";
  $printed_rows = 0;
  while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    
    //3 number values must be formatted
    echo "<td>";
    $num_formatted = formatprojectnumber($row['company_type'], $row['project_number'], $row['sub_project_number']);
    echo "<a href='projectkickoff.php?project=".$row['project_uid']."'>" .$num_formatted. "</a>";
    echo "</td>";
    
    echo "<td>".$row['project_name']."</td>";
    echo "<td>".$row['employees']."</td>";
    echo "<td>".$row['project_type']."</td>";
    echo "<td>".$row['building_type_name']."</td>";
    echo "<td>".$row['leed_cert']."</td>";  
    echo "</tr>";
    $printed_rows += 1;
    }
    
  echo "</table>";
  echo "Projects Found: ".$printed_rows;
  
  }
else 
	{
	echo "0 results";
	}

//close database connection
mysqli_close($con);

//close "content" div 
echo "</div>";
include("common/end.html");
?>