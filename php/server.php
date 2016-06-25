<?php
	// error_reporting(E_ALL ^ E_NOTICE);
	date_default_timezone_set('PRC');
	ob_implicit_flush();   //关闭缓冲区  直接输出到浏览器

	$server_socket = new Sock('10.230.51.70',8421);
	$server_socket->run();

	class Sock {
		public $sockets;
		public $users;
		public $master;

		private $recv_data = array();   //已经接收的数据
		private $data_len = array();    //数据总长度
		private $recv_len = array();    //接收数据总长度
		private $arr = array();        //加密key
		private $n = array();


		public function __construct($address,$port){
			$this->master = $this->webSocket($address,$port);   //返回服务器端监听套接字
			$this->sockets = array($this->master); 

		}

		function webSocket($address,$port){
			//创建服务器端监听套接字  返回创建的套接字
			$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('ERROR::create');   
			socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
			socket_bind($server,$address,$port) or die('ERROR::bind') ;    //绑定监听端口
			socket_listen($server) or die('ERROR::listen');
			$this->_echo('Server Started : '.date('Y-m-d H:i:s'));
			$this->_echo('Listening on   : '.$address.' port '.$port);
			return $server;    //成功创建则返回服务器端监听套接字
		}


		function run(){
			while(true){   //接收客户端接进来的套接字
				$changes = $this->sockets;
				
				//阻塞接收 $write 传入数组   阻塞接收 每当change变化向下执行
				socket_select($changes,$write=NULL,$except=NULL,NULL); 
				foreach ($changes as $sock) {   //$change 为标识为可读性的套接口
					if($sock == $this->master){  //只是接入套接字 尚未发送数据 因而只需要更新用户 并且初始化用户套接字
						$client = socket_accept($this->master) or die('ERROR::accept');  //接收客户端套接字
						$this->sockets[] = $client;
						$key = uniqid() ; //基于时间微妙数生成唯一的user key id
						$this->users[$key] = array(
							'socket'=> $client,    
							'shake'=>'false'  //初始化该用户的握手信息为false
						);
						
					 }else{
					 	$len=0;
						$buffer='';
						do{
							$l=socket_recv($sock,$buf,1024,0);    
							$len+=$l;
							$buffer.=$buf;
						}while($l==1024);

						$k=$this->search($sock);
					
					 	if($this->users[$k]['shake'] == 'false'){    //尚未握手
					 		//echo "ddd";
							$this->shakeHand($k,$buffer);

						}else{
							$buffer = $this->unwrap($buffer);   //将二进制数据流--》字符串
							if($buffer==false){
								continue;
							}
							$this->_echo($buffer);   //黑窗口输出发送过来的数据

							$this->send($this->users[$k]['socket'],$buffer);
						}

					}
					 

				}

				
			}
		}

		function send($client,$buffer){   //把数据回传到客户端 
			if(empty($client) || empty($buffer)) {
	 			return false;
	 		}
	 		echo "send: ";
	 		$buffer2 = $this->wrap($buffer);    //将字符串转化成二进制流
	 		//socket_write($client, $buffer2, strlen($buffer2));   //将缓冲区数据发送到客户端
	 		//将数据传送至所有连接进来的客户端
	 		foreach ($this->users as $k => $v) {
	 			socket_write($v['socket'], $buffer2, strlen($buffer2));   //将缓冲区数据发送到客户端
	 		}
	 		return true;
		}

		public function wrap($string) {    //将字符串转化为二进制流
	 		$frame = array();
	 		$frame[0] = "81";
	 		$len = strlen($string);
	 		$frame[1] = $len < 16 ? "0".dechex($len) : dechex($len);
	 		$frame[2] = $this->ord_hex($string);
	 		$data = implode("",$frame);
	 		return pack("H*", $data);
	 	}

	 	private function ord_hex($data) {
	 		$msg = "";
	 		$ll = strlen($data);
	 		for ($i= 0; $i< $ll; $i++) {
	 			$msg .= dechex(ord($data{$i}));    //十进制转化为16进制
	 		}
	 	
	 		return $msg;
	 	}

		function shakeHand($k,$buffer){
			$buf  = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);
			$key  = trim(substr($buf,0,strpos($buf,"\r\n")));
		
			$new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
			
			$new_message = "HTTP/1.1 101 Switching Protocols\r\n";
			$new_message .= "Upgrade: websocket\r\n";
			$new_message .= "Sec-WebSocket-Version: 13\r\n";
			$new_message .= "Connection: Upgrade\r\n";
			$new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
			
			socket_write($this->users[$k]['socket'],$new_message,strlen($new_message));
			$this->users[$k]['shake']='true';
			return true;
			
		}

		public function unwrap($buffer) {
	 		$mask = array();
	 		$data = "";
	 		$msg = unpack("H*", $buffer);   //函数从二进制字符串对数据进行解包。采取16进制
	 		
	 		$head = substr($msg[1],0,2);    //81
	 		
	 	
	 		if (hexdec($head{1}) === 8) {
	 			$data = false;
	 		} else if (hexdec($head{1}) === 1) {
	 			$mask[] = hexdec(substr($msg[1],4,2));
	 			$mask[] = hexdec(substr($msg[1],6,2));
	 			$mask[] = hexdec(substr($msg[1],8,2));
	 			$mask[] = hexdec(substr($msg[1],10,2));
	 		
	 			$s = 12;
	 			$e = strlen($msg[1])-2;
	 			$n = 0;
	 			for ($i= $s; $i<= $e; $i+= 2) {
	 				$data .= chr($mask[$n%4]^hexdec(substr($msg[1],$i,2)));   //chr  从ACSII返回字符
	 				$n++;
	 			}
	 		}
	 		
	 		return $data;
	 	}


		// function close($k){
		// 	socket_close($this->users[$k]['socket']);
		// 	unset($this->users[$k]);
		// 	$this->sockets=array($this->master);
		// 	foreach($this->users as $v){
		// 		$this->sockets[]=$v['socket'];
		// 	}
		// 	$this->e("key:$k close");
		// }
	
		function search($sock){
			foreach ($this->users as $k=>$v){
				if($sock==$v['socket'])
				return $k;
			}
			return false;
		}
	

		function _echo($str){
		
			$str=$str."\n";

			echo iconv('utf-8','gbk//IGNORE',$str);
		} 


		


	}




?>