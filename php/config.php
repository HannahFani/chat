<?php
	//连接DB chat数据库操作
	$dsn = "mysql:dbname=chat;host=localhost";
	$dbname = 'root';
	$dbpasswd = '19950809';

	try{
		$pdo = new PDO($dsn,$dbname,$dbpasswd);   //连接成功

	}catch(PDOException $e){
		echo "ERROR::".$e->getMessage();
	}

	try{   //将登陆数据记录到数据库 表useimg       id username entertime src
		$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);  //修改错误模式

		$username = $_POST['username1'];
		$entertime = $_POST['entertime1'];
		$src = $_POST['src'];
		
		$sql = "INSERT INTO useimg (username,entertime,src) values (:username,:entertime,:src)";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':username',$username);
		$stmt->bindParam(':entertime',$entertime);
		$stmt->bindParam(':src',$src);

		$stmt->execute();

		//echo $stmt->rowCount();  //查询到的条数
	}catch(PDOException $e){
		echo "ERROR::".$e->getMessage();
	}

?>