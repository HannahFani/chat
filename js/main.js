$(function(){
	
	//showChat();   //显示chat界面
	showEnterBox();
	

	//头像读取
	$('.file').change(function(event) {
		
		if(typeof FileReader == 'undefined'){
			alert('您的浏览器不支持FileReader,请更换Chrome或者Firefox浏览器！');
			return false;
		}

		//图像文件读取
		var file = document.getElementById('file').files[0];
		var pattern = /^image\/\w+/;
		if(!pattern.test(file.type)){
			alert('图片格式错误，请重新选择！');

		}else{
			var reader = new FileReader();
			reader.readAsDataURL(file);
			reader.onload = function(){
				$('.file_img').attr('src',this.result);
			}
		}



	});


	//控制输入框
	$('.sendmsg').focus(function(event) {
		if($(this).val()=='在这里输入内容'){
			$(this).val('');
			$(this).css({
				'font-style':'normal',
				'color':'black'
			});
		}
	}).blur(function(event) {
		/* Act on the event */
		if($(this).val()==''){
			$(this).val('在这里输入内容');
			$(this).css({
				'font-style':'italic',
				'color':'#ccc'
			});
		}
	});

	function hideChat(){
		$('#wrapper').css('display','none');
		
	}
	function showChat(){
		$('#wrapper').css('display','block');
		$('#modal').css('display','none');
		$('#enter_box').css('display','none');


	}
	function hideEnterBox(){
		$('#modal').css('display','none');
		$('#enter_box').css('display','none');
	}
	function showEnterBox(){
		$('#wrapper').css('display','none');
		$('#modal').css('display','block');
		$('#enter_box').css('display','block');
	}




	//开始建立一个客户端socket
	var url = 'ws://10.230.51.70:8421',  
	    socket = null,  //初始化一个soket供client用户使用
	    username = '';


	function clientSocket(_username){
		username = _username;
		socket = new WebSocket(url);
		socket.onopen = function(){   //连接建立
			
			if(socket.readyState == 1){
				var time = getDatetime();
				var logintime = time.formatdate;
				var logintimestamp = time.timestamp;

				socket.send('enter&'+username+'&'+logintime+'&'+logintimestamp);

			}
		}
		socket.onmessage = function(msg){

			var msgstr = msg.data,
			    obj = splitStr(msgstr);   //obj为保存发送过来的信息对象  有type类型  name发送人  datetime发送时间  属性
			if(obj.type == 'enter') //说明是用户加入
			{	
				//显示欢迎加入提示
				var tmp_enter = '<div class="user_enter"><span class="welcome">欢迎&nbsp;</span><span class="user_enter_name">'+obj.name+'</span><span class="come">&nbsp;进入聊天室&nbsp;&nbsp;&nbsp;</span><span class="enterdate">'+obj.datetime+'</span></div>';

				$('.content').append(tmp_enter);

				var currentusername = $('.user_name').text();	
				if(currentusername != obj.name ){
					//刷新在线用户
					currentUsers();
				}else{
					//已经初始化过了一次  不用刷新当前列表  否则有bug
				}

							
			}else if(obj.type == 'msg'){
				var currentusername = $('.user_name').text();		

				var tmp_msg = '';
				if(currentusername == obj.name){   //当前界面用户在发送消息  页面消息居右
					tmp_msg = '<div class="content_info"><span data-t="1" class="c_users_img_r"></span><span class="c_users_name_r">'+obj.name+'</span><span class="c_users_time_r">'+obj.datetime+'</span><span class="c_users_content_r">'+obj.msg+'</span></div>';
					$('.content').append(tmp_msg);

					var img = $('.user_img').css('background-image');
					$('.content').find('.c_users_img_r:last').css('background-image',img);

				}else{
					tmp_msg = '<div class="content_info"><span class="c_users_img"></span><span class="c_users_name">'+obj.name+'</span><span class="c_users_time">'+obj.datetime+'</span><span class="c_users_content">'+obj.msg+'</span></div>';
					$('.content').append(tmp_msg);

					$.ajax({
						url:'php/getImgSrc.php',
						type:'POST',
						data:{
							username2: obj.name,
							entertime2: obj.entertime,
						},
						success:function(data){
							data = data.replace(/\\/g,'/');
							data = data.replace(/^\.\.\//,'');   //相对地址转化
							var url = 'url('+data+')';
							$('.content').find('.c_users_img:last').css('background-image',url);

						}
					});

				}


			}else if(obj.type == 'exit'){   //用户退出   通知所有客户端
				//alert(msgstr);
				var tmp_msg = '<div class="user_exit_info"><span class="user_exit_name">'+ obj.name +'</span><span class="come">&nbsp;退出聊天室&nbsp&nbsp</span><span class="exitdate">'+ obj.exit_time_format +'</span></div>';
				$('.content').append(tmp_msg);

				//刷新当前用户
				currentUsers();

			}



			$('.content').scrollTop($('.content')[0].scrollHeight);   //每次输出消息都将滚动条至底部
			
		}

		socket.onclose = function(){
			//连接关闭
			alert('服务器端已关闭连接，用户无法发送数据！');
		}


	}


	$('.sendbtn').click(function(){
		sendmsg();

	});

	$('.sendmsg').keypress(function(event){
		if(event.keyCode == 13){   //回车控制发送消息
			sendmsg();
		}
	});



	function sendmsg(){
		var username = $('.user_name').text();
		var msg = $('.sendmsg').val();   //获取输入框中的内容
		if(msg=='在这里输入内容'){
			alert('请输入发送内容！');
			return false;
		}
		var msgtime = getDatetime();

		//获取该发消息的用户登录时间
		//ajax  得到数据库中 的 登录时间戳的值  
		$.ajax({
			url:'php/getEntertime.php',
			type:'POST',
			data:{
				username: username,
			},
			success:function(data){
				socket.send('msg&'+username+'&'+msg+'&'+msgtime.formatdate+'&'+data);  //data是该发消息用户的登录时间戳


				// $('.sendmsg').val('在这里输入内容');
				// $('.sendmsg').css({
				// 	'font-style':'italic',
				// 	'color':'#ccc'
				// });
				$('.sendmsg').val('');
				$('.sendmsg').focus();
			}
		});

		
		
	}

	$('.enter').click(function(event) {
		if($('.input_username').val() == ''){
			alert('请输入聊天用户名！');
		}else{
			
			var nametmp = $.trim($('.input_username').val());
			showChat();

			$('.user_name').text(nametmp);    //更新主页名字



			var time1 = getDatetime();       
			$('.user_date').text(time1.formatdate);   //更新登录时间

			$('.user_enter').val(time1.timestamp);    //隐藏域设置当前用户登录的时间戳

			var srctmp = '';

			//将用户图片上传至本地
			$('#form_file').ajaxSubmit({
				url:'php/userimg.php',
				type:'POST',
				data:{
					username: nametmp,
					entertime: time1.timestamp,
				},
				success:function(responseText,status){

					var imgsrc = responseText;   //返回本地地址

					$.ajax({      
						url:'php/config.php',
						type:'POST',
						data:{
							username1: nametmp,
							entertime1: time1.timestamp,
							src: imgsrc, 
						},
						success:function(){
							setImgSrc(nametmp,time1.timestamp,$('.user_img'));   //设置用户图片	

							//显示在线用户
							currentUsers();	

							clientSocket(nametmp);   //创建客户端套接字			
						}
							
					});


					
				}
			});


			
			//clientSocket(nametmp);   //创建客户端套接字
		}

		
	});


    


	function currentUsers(){
	//进入界面  更新在线用户
		$('.sidebar_left_child').empty();
		$.ajax({
			url:'php/getcurrentusers.php',
			type:'POST',
			dataType:'json',
			success:function(data){
									//读取 用户名称  头像
				for(var i = 0;i<data.length;i++){
					var leftdom = '<div class="users_info"><span class="users_img"></span><span class="users_name">'+data[i].username+'</span></div>';
					$('.sidebar_left_child').append(leftdom);
					var src = data[i].src;

					src = src.replace(/\\/g,'/');
					src = src.replace(/^\.\.\//,'');   //相对地址转化
										//alert(src);
					var url = 'url('+src+')';
					$('.sidebar_left_child').find('.users_img').eq(i).css({
						'background':url,
						'background-position': 'center',
						'background-size': 'cover',
					});
				}
							
				if(data.length > 12){
					$('.sidebar_left').css({
						'overflow':'scroll',
						'overflow-x':'hidden',
					});
					$('.sidebar_left').scrollTop($('.sidebar_left')[0].scrollHeight);
				}else{
					$('.sidebar_left').css({
						'overflow':'scroll',
						'overflow-x':'hidden',
						'overflow-y':'hidden',
					});
				}

			}
		});	

	}
	

	function setImgSrc(username,entertime,dom){
		
		$.ajax({
			url:'php/getImgSrc.php',
			type:'POST',
			data:{
				username2: username,
				entertime2: entertime,
			},
			success:function(data){
				data = data.replace(/\\/g,'/');
				data = data.replace(/^\.\.\//,'');   //相对地址转化
				var url = 'url('+data+')';
				dom.css({
					'background':url,
					'background-position': 'center',
					'background-size': 'cover'
				});

			}
		});

	}


	function getDatetime() {
		var date = new Date(),
				year = date.getFullYear(),
				month = date.getMonth()+1,
				day = date.getDate(),
				hours = date.getHours(),
				minutes = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes(),
				seconds = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds();

		var obj = {};
		obj.formatdate = year+'年'+month+'月'+day+'日 '+hours+':'+minutes+':'+seconds;
		obj.timestamp = date.getTime();

		return obj;
		
	}


	function splitStr(str) {
		if (!str) {
			return false;
		}

		var obj = {};
		var arr = str.split('&');
		
		if(arr[0] == 'enter'){
			obj.type = arr[0];
			obj.name = arr[1];
			obj.datetime = arr[2];
			obj.entertime = arr[3];
		}else if(arr[0] == 'msg'){
			obj.type = arr[0];
			obj.name = arr[1];
			obj.msg = arr[2];
			obj.datetime = arr[3];
			obj.entertime = arr[4];

		}else if(arr[0] == 'exit'){
			obj.type = arr[0];
			obj.name = arr[1];
			obj.exit_time_format = arr[2];
			obj.exit_time_timestamp = arr[3];
		}
		

		return obj;



	}


	$('.title_clear').click(function(){
		$('.content').empty();
	});

	
	//退出聊天室
	$('.user_exit').click(function(event) {
		/* Act on the event */
		var exit  = window.confirm('确定离开聊天室吗？');
		if(exit){   //确认离开聊天室
			var exit_username = $('.user_name').text();
			var exit_entertime = $('.user_enter').val();
			var exit_time = getDatetime();
			//alert($('.user_name').text()+":::"+$('.user_enter').val());
			$.ajax({
				url:'php/exit.php',
				type:'POST',
				data:{
					exit_username: $('.user_name').text(),
					exit_entertime: $('.user_enter').val(),
				},
				success:function(){
					//发送给服务器 告诉其他客户端  该用户离开聊天室
					//socket.send('enter&'+username+'&'+logintime+'&'+logintimestamp);
					//alert('exit&'+exit_username+'&'+exit_time.formatdate+'&'+exit_time.timestamp);
					socket.send('exit&'+exit_username+'&'+exit_time.formatdate+'&'+exit_time.timestamp+'&***');
					location.reload();

				}

			});
			
			
		}
	});

});
