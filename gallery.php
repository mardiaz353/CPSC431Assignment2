<?php
/*--------------------------------validation handling code--------------------------*/
// Checks if input is valid
if(isset($_POST["submit"])) { //if a variable is declared when submit is pressed
    // variables
    $file = $_FILES['fileToUpload'];
    $fileName = $_FILES['fileToUpload']['name'];
    $fileTmpName = $_FILES['fileToUpload']['tmp_name'];
    $fileError = $_FILES['fileToUpload']['error'];
    $fileType = $_FILES['fileToUpload']['type'];// Gets the ext of file
    $document_root = $_SERVER['DOCUMENT_ROOT'];
     // all input is trimed and uppercase
	$getPhotoName = strtoupper(trim($_POST['photoName'])); // input variables 
	$getDateTaken = $_POST['dateTaken']; // use _POST because its safer
    $getPhotoGrapher = strtoupper(trim($_POST['photographer']));
    $getLocation = strtoupper(trim($_POST['location']));

    // All Entries have to be filed
    if(!$getPhotoName || !$getDateTaken || !$getPhotoGrapher || !$getLocation){
        echo "<p> You have have to enter all the entries</p>";
        exit;
    }

    // if input is not uppercase then its a error
    if(!preg_match("/^[A-Z\d]+$/", $getPhotoName)) {
        echo "<p>Invalid Photo Name</p>";
        exit;
    }
    if(!preg_match("/^[A-Z\d]+$/", $getPhotoGrapher)) {
        echo "<p>Invalid PhotoGrapher</p>";
        exit;
    }
    if(!preg_match("/^[A-Z\d]+$/", $getLocation)) {
        echo "<p>Invalid Location</p>";
        exit;
    }
    //if there is a error then display error sign
    if($fileError > 0){ 
        echo 'Problem: ';
        switch ($fileError) {
            case 1:
                echo 'File exceed upload_max_file_size.';
                break;
            case 2:
                echo 'File exceed max_file_size';
                break;
            case 3: 
                echo 'File only partially uploaded.';
            break;
            case 4: 
                echo 'No file uploaded.';
            break;
            case 6:
                echo 'Cannot upload file: No temp directory specified.';
            break;
            case 7: 
                echo 'Upload failed: Cannot write to disk';
            break;
            case 8: 
                echo 'A PHP extension blocked the file upload';
            break;
            default:
                break;
        }
    } 
    
    $uploaded_file = 'uploads/'.$fileName;

    if(is_uploaded_file($fileTmpName)){
        if(!move_uploaded_file($fileTmpName,$uploaded_file)){
            echo 'Problem: Could not move file to destination directory';
            exit;
        }
    }

    // this checks if the file extension is correct
    if($fileType != 'image/jpeg' && $fileType != 'image/png'){
        echo 'Problem: file is not a PNG image or a JPEG: ';
        exit;
    } 
/* -------------------------------- End Validation --------------------------------- */
/* --------------------Establish a Database connection and Inserting Data -----------*/

    //Connect to the database and display error msg if can't connect
    $db = mysqli_connect("localhost", "root", "","Images") //dbHost,bdUsername,dbPassword,dbName
    or die('Cannot Connect To Database.' . mysqli_connect_error());

    // echo 'Connected successfully';

    //Insert query to DB
    $insertQuery = "INSERT INTO `Images` 
    (`fileName`, `name`, `date`, `photographer`, `location`, `image`) 
    VALUES ('$fileName','$getPhotoName','$getDateTaken','$getPhotoGrapher','$getLocation','$uploaded_file')";
    // if query works tell user
    echo (mysqli_query($db,$insertQuery)) ? "" : "</br>image not uploaded";

    // close the database;
    mysqli_close($db);
    /* -------------------- End Connectoin and Insert Query ----------------------*/
}
?>
<!------------------------ Added Html And Add Sort Button  ---------------->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="stylesheets/styles.css">
</head>
<body>
    <header>
        <h1>View All Photos</h1> 
    </header>
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

<?php
/* --------------------Adding A Sort Algorithm And Display Gallery -----------*/
$answer = 'name';
if(isset($_POST["ok"])) {
    $answer = $_POST["sort"];
}
// default sort
$sql = "SELECT * FROM Images ORDER BY name";
// Select sql to query
if($answer === 'name'){
    $sql = "SELECT * FROM Images ORDER BY name";
} else if($answer === 'date'){
    $sql = "SELECT * FROM Images ORDER BY date";
} else if($answer === 'photographer'){
    $sql = "SELECT * FROM Images ORDER BY photographer";
} else if($answer === 'location'){
    $sql = "SELECT * FROM Images ORDER BY location";
}

$db = mysqli_connect("localhost", "root", "","Images") //dbHost,bdUsername,dbPassword,dbName
or die('Cannot Connect To Database.' . mysqli_connect_error());

// echo "connected successfully";

$result = mysqli_query($db,$sql);

// Display the Images selected 
while($row = mysqli_fetch_row($result)) {
    echo '<div class="list-content">'; // fileName 
    echo'<img class="picture-content" src="'.$row[6].'"/ alt="Error on Displaying"></img>';
    echo'<div class="data-box">'. 'Name: '.$row[2].'</div>'; // name
    echo'<div class="data-box">'. 'Date: '.$row[3].'</div>'; // date
    echo'<div class="data-box">'. 'Photographer: '.$row[4].'</div>'; // photographer
    echo'<div class="data-box">'. 'Location: '.$row[5].'</div>'; // location
    echo'</div>';
}
// free memory and close the database;
mysqli_free_result($result);
mysqli_close($db);
/* -----------------------------Ending The Connection-----------------------*/
?>
</body>
</html>
<!-- End Html -->