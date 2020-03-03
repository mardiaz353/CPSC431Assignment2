<?php
// since the schools server does not support array_column I wrote it here
if (! function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if ( !array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            }
            else {
                if ( !array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if ( ! is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}
?>
<?php
// Checks if input is valid
if(isset($_POST["submit"])) { //if a variable is declared when submit is pressed
    // variables
    $file = $_FILES['fileToUpload'];
    $fileName = $_FILES['fileToUpload']['name'];
    $fileTmpName = $_FILES['fileToUpload']['tmp_name'];
    $fileError = $_FILES['fileToUpload']['error'];
    $fileType = $_FILES['fileToUpload']['type'];// Gets the ext of file
    $document_root = $_SERVER['DOCUMENT_ROOT'];

    if($fileError > 0){ //if there is a error then display error sign
        echo 'Problem: '.$fileError;
        exit;
    } 
    
    // this checks if the file extension is correct
    if($fileType != 'image/jpeg' && $fileType != 'image/png'){
        echo 'Problem: file is not a PNG image or a JPEG: ';
        exit;
    } 
     $uploaded_file = 'uploads/'.$fileName;

     if(is_uploaded_file($fileTmpName)){
         if(!move_uploaded_file($fileTmpName,$uploaded_file)){
             echo 'Problem: Could not move file to destination directory';
             exit;
         }
    }
    else {
        echo 'Problem: Possible file upload attack. Filename: '. $fileName;
        exit;
    }
    ?>

    <?php
	//Save meta data and name of image file to a text document
    //$fp = fopen("gallery.txt", 'ab');
	$dbhost = "ecs.fullerton.edu/~ecs.fullerton.edu/~cs431s28/";
	$dbusername = "cs431s28";
	$dbpassword = "Moh3poox";
	//$dbname = "cs431s28";
	$conn = mysql_connect($dbhost, $dbusername, $dbpassword);
	
	if(!$conn) {
		die('Could not connect: '.mysql_error());
	}
	
	echo 'Connected successfully';
	
    
    if(!$fp){ // if fopen fails exit
        echo '<p><strong> Your order could not be processed at this time. 
        .Please try again later.</strong></p></body></html>';
        exit;
    }
    
    // all input is trimed and uppercase
	$getPhotoName = strtoupper(trim($_POST['photoName'])); // input variables 
	$getDateTaken = trim($_POST['dateTaken']); // use _POST because its safer
    $getPhotoGrapher = strtoupper(trim($_POST['photographer']));
    $getLocation = strtoupper(trim($_POST['location']));

    $outputString = $fileName."\t".$getPhotoName."\t".// string to append
    $getDateTaken."\t".$getPhotoGrapher."\t".$getLocation."\n";
	
	//Instead of putting the information in a text file
	//I need to store it in the database
	
	//From the cs431s28 db select the Images table and put these variables inside
	$query = "FROM cs431s28 SELECT Images INSERT INTO (name, date, photographer, location) VALUES($getPhotoName, $getDateTaken, $getPhotoGrapher, $getLocation)";
    if(mysql_query($conn, $query)) {
		echo "New record entered successfully";
	} else {
		echo "Could not enter record, error line 104";
	}
	
	$img_query = "FROM cs431s28 SELECT Images INSERT INTO (fileName, image) VALUES ($fileName, $uploaded_file)";
	
	//fclose($fp);
	mysql_close($conn);
    ?>
	
<?php
    // Read file and add data to array and show pictures.
    $fp = fopen("gallery.txt", 'rb');

    if(!$fp){
        echo 'error reading file!';
        exit;
    }

    $bigarray = [];

    while(!feof($fp)){
        $lines = fgets($fp); // gets the whole line
        if($lines === false) break; // deletes empty line at the end
        $line = explode("\t",$lines); // explodes the lines into separate varaibles
        $tmparray = [$line[0],$line[1],$line[2],$line[3],$line[4]]; // pushing to an array
        array_push($bigarray,$tmparray);
    }

    fclose($fp); // close file
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gallery</title>
    <link rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="stylesheets/styles.css">
</head>
<body>
    <header>
        <h1>View All Photos</h1>   
    </header>
	<!-- Create a form to perform the same thing as index.php and leave it blank-->
<form action = "gallery.php" method="post" enctype="multipart/form-data">
    <table> 
        <tr> 
            <td> 
            <div class="form-group">
                <h2>Sort By:
                <select id="sortby" class="form-control" name="sort">
                    <option value="name">Name</option>
                    <option value="date">Date</option>
                    <option value="photographer">Photographer</option>
                    <option value="location">Location</option>
                </select>
                <button type="submit" name="ok">Ok</button>
                </h2>
            </div>
            </td>
	        <form action = "gallery.php" method = "post" enctype = "multipart/form-data">
            <td> 
            <!--<input type="button" value="Add another Picture" onClick="javascript:history.go(-1)" />-->
		    <!-- Go back to the uploads page if the user presses the add another picture button-->
		    <button type="submit" formaction="$document_root/../index.html"> Add Another Picture</button>
	        </td>
	        </form>
        </tr>
    </table>
</form>
    <div>
        <?php
        $answer='name';
//If the user has pressed the ok button for sort....
if (isset($_POST["ok"])) {
//...have gallery.txt be read into $bigarray since the form has refreshed...
	 $fp = fopen("gallery.txt", 'rb');

    if(!$fp){
        echo 'error reading file';
    }
	
    $answer = $_POST["sort"];
    $bigarray = [];

    while(!feof($fp)){
        $lines = fgets($fp); // gets the whole line
        if($lines === false) break; // deletes empty line at the end
        $line = explode("\t",$lines); // explodes the lines into separate varaibles
        $tmparray = [$line[0],$line[1],$line[2],$line[3],$line[4]]; // pushing to an array
        array_push($bigarray,$tmparray);
    }
    fclose($fp); // close file
}

// ...And sort the array according to which "sort" method the user selected in the dropdown
if($answer === 'name'){
    array_multisort( array_column( $bigarray, 1),SORT_ASC,  $bigarray);
} else if($answer === 'date'){
    array_multisort( array_column( $bigarray, 2),SORT_ASC, SORT_NUMERIC, $bigarray);
} else if($answer === 'photographer'){
    array_multisort( array_column( $bigarray, 3),SORT_ASC, $bigarray);
} else if($answer === 'location'){
    array_multisort( array_column( $bigarray, 4),SORT_ASC, $bigarray);
}

//Display the gallery by using a for loop and echo data-boxes to the screen
$len = count($bigarray); // gets bigarray length
for($row = 0; $row < $len; $row++){
    echo '<div class="list-content">'; // fileName 
    echo'<img class="picture-content" src="uploads/'.$bigarray[$row][0].'"/ alt="Error on Displaying"></img>';
    echo'<div class="data-box">'.$bigarray[$row][1].'</div>'; // name
    echo'<div class="data-box">'.$bigarray[$row][2].'</div>'; // date
    echo'<div class="data-box">'.$bigarray[$row][3].'</div>'; // photographer
    echo'<div class="data-box">'.$bigarray[$row][4].'</div>'; // location
    echo'</div>';
}
?>
</div>
</main>
</body>
</html>
