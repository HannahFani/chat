<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>chat</title>
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/jquery.form.js"></script>
	<script type="text/javascript" src="js/main.js"></script>
	<link rel='icon' href='img/ico/chat1.ico' type='image/x-ico' />
	<link rel="stylesheet" type="text/css" href="css/style.css">	

</head>
<body>
	<div id="modal"></div>
	<div id="enter_box">
		<span class="choose_userimg" title="请选择一个头像">
			<img class="file_img" src="img/ico/chooseuser.png">
			<form id="form_file">
				<input class="file" id="file" name="file" type="file" >
			</form>		
		</span>
		<br>
		<input class="input_username" type="text">
		<span class="enter"></span>
	</div>
	<div id="wrapper">
		<div class="sidebar_left">
			
			<div class="sidebar_title">在线用户</div>
			<div class="sidebar_left_child">
				<!-- <div class="users_info">
					<span class="users_img"></span>
					<span class="users_name"></span>
				</div> -->
				<!-- <div class="users_info">
					<span class="users_img"></span>
					<span class="users_name"></span>
				</div>
				<div class="users_info">
					<span class="users_img"></span>
					<span class="users_name"></span>
				</div> -->
			</div>
			
			
		</div>
		<div class="chatpanel_right">
			<div class="top_userinfo">
				<span class="user_img"></span>
				<span class="user_name"></span>
				<span class="user_date"></span>
				<input class="user_enter" type="hidden" value="111">
				<span class="user_exit">退出聊天室</span>
			</div>
			<div class="center_chatcontent">
				<div class="center_title">
					<span class="title_msg">消息显示</span>
					<span class="title_clear">清屏</span>
				</div>
				<div class="content">
					<!-- <div class="user_exit_info">
						<span class="user_exit_name">---</span>
						<span class="come">&nbsp;退出聊天室&nbsp&nbsp</span>
						<span class="exitdate"></span>
					</div> -->
					
					
				</div>

			</div>
			<div class="bottom_sendmsg">
				<div class="choose">编辑消息</div>
				<textarea class="sendmsg" cols="135" rows="6" >在这里输入内容</textarea>
				<button class="sendbtn">发送</button>
			</div>
		</div>
		
	</div>
	
</body>
</html>