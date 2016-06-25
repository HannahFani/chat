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

		$username = $_POST['username'];
		//$entertime = $_POST['entertime2'];
		
		$sql = "SELECT entertime FROM useimg WHERE username = :username";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':username',$username);
		
		$stmt->execute();

		//$n = $stmt->rowCount();
		$result = $stmt-> fetch(PDO::FETCH_ASSOC);
		echo $result['entertime'];

	}catch(PDOException $e){
		echo "ERROR::".$e->getMessage();
	}

?>