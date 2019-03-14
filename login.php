<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">

		body{
			background-color: #272822;
			color: #26A3DB;
		}

		form{
			margin-top: 6%;
			font-size: 30px;
			font-weight: bold;
		}

		h1{
			font-size: 50px;
			font-weight: bold;
			margin-bottom: 80px;
		}

		table{
			margin: auto;
		}

		input{
			height: 35px;
			width: 380px;
			font-size: 20px;
			font-weight: bold;
		}

		input[type='submit']{
			width: 200px;
			margin-top: 50px;
			margin-left: 200px;
		}

	</style>
</head>
<body>
	<form method="post">
		<table>
			<tr><th colspan="2"><h1>登录OSS管理系统</h1></th></tr>
			<tr><td>AccessKeyId：</td><td><input type="text" name="AccessKeyId"></td></tr>
			<tr><td>AccessKeySecret：</td><td><input type="password" name="AccessKeySecret"></td></td></tr>
			<tr><td>Endpoint：</td><td><input type="text" name="Endpoint"></td></tr>
			<tr><td colspan="2"><input type="submit" name="ok" value="登录"></td></tr>
		</table>
	</form>
</body>
</html>

<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;
	if(isset($_POST['ok'])){
		$accessKeyId = $_POST['AccessKeyId'];
		$accessKeySecret = $_POST["AccessKeySecret"];
		$endpoint =$_POST['Endpoint'];
		try {
			//创建OSS操作对象
			$ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

			//多页面都需的操作类对象可序列化为共用文件以减少传递构造参数
			//序列化OSS操作对象并存入本地文件
			$serialize=serialize($ossClient);
			$handle=fopen('serialize.txt','w+');
			fwrite($handle,$serialize);
			fclose($handle);
			$bucketListInfo = $ossClient->listBuckets();
			echo "<script>alert('登录成功');location='main.php'</script>";
		} 
		catch (OssException $e) {
			echo "<script>alert('账号信息错误');</script>";
		}
	}
?>