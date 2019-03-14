<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;
	use OSS\Http\RequestCore;
	use OSS\Http\ResponseCore;

	//反序列化
	$handle=fopen("./serialize.txt","r+");
	$serialize=fread($handle,filesize("./serialize.txt"));
	fclose($handle);
	$ossClient=unserialize($serialize);

	if(isset($_POST['save'])){
		$bucket=$_POST['bucket'];
		$object=$_POST['object'];
		$parentPath=$_POST['parentPath'];
		$content=$_POST['text'];

		if(strpos($object,'/')){//判断是否不为根目录文件
			$showName=substr($object,strrpos($object,'/')+1);
		}
		else{
			$showName=$object;
		}

		$handle=fopen($showName,'w+');
		fwrite($handle,$content);
		fclose($handle);
		try{
			$ossClient->uploadFile($bucket,$parentPath.$showName,$showName);
			unlink($showName);
			echo "<script>alert('更新成功');</script>";
		}
		catch(Exception $e){
			echo "<script>alert('更新失败');</script>";
		}	
	}
	elseif(isset($_GET['operationTxt'])){
		$bucket=$_GET['bucket'];
		$object=$_GET['object'];
		$parentPath=$_GET['parentPath'];

		if(strpos($object,'/')){//判断是否不为根目录文件
			$showName=substr($object,strrpos($object,'/')+1);
		}
		else{
			$showName=$object;
		}

		try{	
			$content = $ossClient->getObject($bucket, $object);
		}
		catch(OssException $e){
			echo "<script>alert('获取链接失败');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath};</script>";
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		
		body{
			background-color: #272822;
		}

		textarea{
			font-family: 微软雅黑;
			font-size: 15px;
			font-weight: bold;
			margin: auto;
			width: 98%;
			height: 472px;
			resize: none;
			outline: 8px solid #26A3DB;
		}

		h1{
			font-size: 25px;
			color: #26A3DB;
		}

		input{
			margin: 10px;
			width: 80px;
			height: 30px;
			font-size: 18px;
			font-weight: bold;
			margin-right:50px;
		}
	</style>
</head>
<body>
	<form method="post">
		<h1>在线文本编辑&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;文本文件名：<?php echo $showName;?></h1>
		<center><textarea name="text"><?php echo $content; ?></textarea></center>
		<center>
		<div>
			<input type="submit" name="save" value="保存">
			<input type="button" name="download" value="下载" onclick=location='downloadFile.php?bucket=<?php echo $bucket?>&filePath=<?php echo $object?>'>
			<input type="hidden" name="bucket" value=<?php echo $bucket; ?>>
			<input type="hidden" name="object" value=<?php echo $object; ?>>
			<input type="hidden" name="parentPath" value=<?php echo $parentPath; ?>>
			<input type="button" name="back" value="返回" onclick=location='showBucketFile.php?query=ok&bucket=<?php echo $bucket;?>&parentPath=<?php echo $parentPath;?>'>
		</div>
		</center>
	</form>
</body>
</html>