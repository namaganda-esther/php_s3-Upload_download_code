<?php

require("dataconnect.php");


$conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
// set the PDO error mode to exception
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 


error_reporting(E_ALL);
ini_set('display_errors', 1);


// importing the SDk class
require 'assets/aws_rsc/aws-autoloader.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;


    // creating a new client
			$s3 = new Aws\S3\S3Client([
				'version'     => '2006-03-01',
				'region'      => 'us-east-2',
				'credentials' => [
						'key'    => 'AKIAYJOTLJQX2MLJGRXE',
						'secret' => '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'
				]
			]);
			

// creating temporary file in rsc folder and uploading the file or object to s3
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
if(isset($_FILES["file"])){
    $error = array();
    $file_name = $_FILES["file"]["name"];
    $file_tmp = $_FILES["file"]["tmp_name"];

	$file_ext = explode('.', $file_name);
    $file_ext = strtolower(end($file_ext));
    $extensions = array("jpeg","jpg","png","pdf","gif","json","txt", "php");
        move_uploaded_file($file_tmp, "rsc/" . $file_name);
        
        $old_filename = "rsc/" . $file_name;
        $file_name = "FILE".uniqid().".".$file_ext.
        
        	
		// Upload a file. 
		$result = $s3->putObject(array( 
			'Bucket'       => "teamfile2",
			'Key'          => $file_name,
			'SourceFile'   => $old_filename,
			'ACL'          => 'public-read',
			'StorageClass' => 'REDUCED_REDUNDANCY',
			'Metadata'     => array(    

			)
		));

		try {


			// prepare sql and bind parameters
			$stmt = $conn->prepare("INSERT INTO team_files (team_id, team_files_create_date, team_files_user, team_files_original_name, team_files_key) 
			VALUES (:team_id, :team_files_create_date, :team_files_user, :team_files_original_name, :team_files_key )");
	
	
			$id_val = "CP".uniqid();
			$stmt->bindParam(':team_id', $id_val);
	
			$team_files_create_date = date("Y-m-d H:i:s");
			$stmt->bindParam(':team_files_create_date', $team_files_create_date);
	
			$user_id_val = $_SESSION['user_id'];
			$stmt->bindParam(':team_files_user', $user_id_val);
	
			$team_files_original_name = clean_string($file_name);
			$stmt->bindParam(':team_files_original_name', $team_files_original_name);
			
			$team_files_key = clean_string($file_tmp);
			$stmt->bindParam(':team_files_key', $team_files_key);
	
	
			$stmt->execute();
	
	
		}catch (Exception $e) {
	
	
			die('ERR REE82399as'.$e); 
	
		}
}


 echo"
    <div class=\"col-lg-6 col-sm-12\">
        <div class=\"card hover-shadow-lg\">
	        <div class=\"card-body text-start pl-5\">
		       <div class=\"avatar-parent-child\">
			     <form action=\"index.php\" method=\"post\" enctype=\"multipart/form-data\">
		           <p class=\"form-control-label\">Select image to upload:<p>
		           <input class=\"form-control\" enctype=\"multipart\" type=\"file\" name=\"file\" id = \"file\">
		           <button class=\"btn btn-sm btn-primary rounded-pill mt-4 form-control w-50\" onclick=\"upload_file('N');\">Upload</button>
				 </form>
			   </div>
		    </div>
        </div>
    </div>
  ";

    //  list objects
	$objects = $s3->getIterator('ListObjects', [
		'Bucket' => 'teamfile2',
	]);
    foreach($objects AS $object){
	// echo $object['Key'];
	//Getting the URL to an object
	echo "
  
	<div class=\"col-lg-4 col-sm-6\">
		<div class=\"card hover-shadow-lg\">
			<div class=\"card-body text-start thumbnail\">
				<div class=\"avatar-parent-child thumbnail\">
					<form action=\"campaign_files.php\" method=\"post\" enctype=\"multipart/form-data\">
						<p>".$object['Key']."</p>
						<img src=\"".$s3->getObjectUrl('teamfile2', $object['Key'])."\" class=\"\" style=\"width:100%; height: auto;\"><img>
						<a href=\"".$s3->getObjectUrl('teamfile2', $object['Key'])."\" downloadable=\"".$object['Key']."\">Download</a>
					</form>
				</div>
			</div>
		</div>
	</div>  
";
  }


?>