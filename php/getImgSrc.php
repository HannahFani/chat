<?php
	$dsn = "mysql:dbname=chat;host=localhost";
	$dbname = 'root';
	$dbpasswd = '19950809';

	try{
		$pdo = new PDO($dsn,$dbname,$dbpasswd);   //连接成功

	}catch(PDOException $e){
		echo "ERROR::".$e->getMessage();
	}


	try{   //表useimg       id username entertime src
		$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);  //修改错误模式

		$username = $_POST['username2'];
		$entertime = $_POST['entertime2'];
		
		$sql = "SELECT src FROM useimg WHERE username = :username and entertime = :entertime";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':username',$username);
		$stmt->bindParam(':entertime',$entertime);

		$stmt->execute();

		//$n = $stmt->rowCount();
		$result = $stmt-> fetch(PDO::FETCH_ASSOC);
		echo $result['src'];

	}catch(PDOException $e){
		echo "ERROR::".$e->getMessage();
	}

?>