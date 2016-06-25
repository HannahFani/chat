<?php

	$username = $_POST['username'];
	$entertime = $_POST['entertime'];

	
	
	if ($_FILES["file"]["error"] > 0)
	{
		 	echo "Error: " . $_FILES["file"]["error"] . "<br />";
	}
	else
	{
			 // echo "Name: " . $_FILES["file"]["name"] . "<br />";
			 // echo "Type: " . $_FILES["file"]["type"] . "<br />";
			 // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
			 // echo "Stored in: " . $_FILES["file"]["tmp_name"];
		//获取文件后缀名称
		$suffix = strstr($_FILES["file"]["name"], '.');  //获取文件后缀名
		//$filename = $username.'&'.$entertime.$suffix;
		$filename = $entertime.$suffix;
	    move_uploaded_file($_FILES["file"]["tmp_name"], "..\\userimg\\" . $filename);
	    
	
		echo "..\\userimg\\" . $filename;
	}

	




?>