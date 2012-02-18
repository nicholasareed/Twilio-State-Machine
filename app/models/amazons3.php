<?
	class Amazons3 extends AppModel {

					// This is only for working with Amazon S3 (storing images)

		var $name = 'Amazons3';
	   
		var $useTable = false;



		// FUNCTIONS

		function upload($localFilepath = 'path/to/file.jpg',$s3Path = 'tmp/', $filename = 'filename.jpg',$bucket = 'idc_files'){

			// $filename is the name that we want to save it as
			// - this is going to be something unique every time

			// Import Vendor file
			App::import('Vendor', 'S3', array('file' => 'S3'.DS.'s3.php'));

			// Instantiate the S3 class
			$s3 = new S3(AWS_ACCESS_KEY, AWS_SECRET_KEY); // defined in bootstrap.php

			// Ensure $newPath is valid
			if(substr($s3Path,-1,1) != '/'){
				$s3Path .= '/';
			}

			// Intended directory
			// - buckets are like the C: drive
			$intendedPath = $s3Path.$filename;

			// Put our file (also with public read access)
			if ($s3->putObjectFile($localFilepath, $bucket, $intendedPath, S3::ACL_PUBLIC_READ)) {
					//echo "S3::putObjectFile(): File copied to {$bucket}/".baseName($uploadFile).PHP_EOL;
					return 1;
			} else {
					return 0;
			}

			exit;   

		}


	}
?>