<?php
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

		
		$sql = "SELECT username,src FROM useimg";
		$stmt = $pdo->prepare($sql);
	
		$stmt->execute();

		$result = $stmt-> fetchAll(PDO::FETCH_ASSOC);
		echo json_encode($result);

	}catch(PDOException $e){
		echo "ERROR::".$e->getMessage();
	}

?>