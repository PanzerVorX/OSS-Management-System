<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
	
		body{
			background-color: #272822;
		}
		
		h1{
			color: #26A3DB;
		}

		audio{
			margin-top: 12%;
			margin-left: 20%;
			width: 800px;
		}

		video{
			margin-top: 0%;
			margin-left: 20%;
			width: 800px;
			height:400px;
		}

		input{
			width: 150px;
			height: 40px;
			font-size: 20px;
			font-weight: bold;
		}

		.buttonDiv{
			position: relative;
			left: 30%;
			margin-top: 3%;
		}

		.buttonDiv input{
			margin-left: 80px;
			border-radius: 2px;
		} 

	</style>
</head>
<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;
	use OSS\Http\RequestCore;
	use OSS\Http\ResponseCore;
	
	$bucket=$_GET['bucket'];
	$object=$_GET['object'];
	$parentPath=$_GET['parentPath'];

	if(strpos($object,'/')){//判断是否不为根目录文件
		$showName=substr($object,strrpos($object,'/')+1);
	}
	else{
		$showName=$object;
	}

	//反序列化
	$handle=fopen("./serialize.txt","r+");
	$serialize=fread($handle,filesize("./serialize.txt"));
	fclose($handle);
	$ossClient=unserialize($serialize);
	try{	
		$timeout = 86400;//设置URL的有效期为一天
		$signedUrl = $ossClient->signUrl($bucket,$object, $timeout);
	}
	catch(OssException $e){
		echo "<script>alert('获取链接失败');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath};</script>";
	}

	if(isset($_GET['operationAudio'])){
?>
<body>
	<h1>音频文件名：<?php echo $showName;?></h1>
	<form method="get" action="downloadFile.php">
		<audio id='audio' src='<?php echo $signedUrl;?>' controls></audio>
		<input type="hidden" name="bucket" value=<?php echo $bucket; ?>>
		<input type="hidden" name="filePath" value=<?php echo $object; ?>>
		<input type="hidden" name="parentPath" value=<?php echo $parentPath; ?>>
		<div class="buttonDiv">
			<input type="submit" name="downloadAudio" value="下载文件">
			<input type="button" name="back" value="返回" onclick=location='showBucketFile.php?query=ok&bucket=<?php echo $bucket;?>&parentPath=<?php echo $parentPath;?>'>
		</div>
	</form>
</body>
</html>
<?php
	}

	if(isset($_GET['operationVideo'])){
?>
<body>
	<h1>视频文件名：<?php echo $showName;?></h1>
	<form method="get" action="downloadFile.php">
		<video height="350px" width="600px" src='<?php echo $signedUrl; ?>' controls='controls' autoplay></video>
		<input type="hidden" name="bucket" value=<?php echo $bucket; ?>>
		<input type="hidden" name="filePath" value=<?php echo $object; ?>>
		<input type="hidden" name="parentPath" value=<?php echo $parentPath; ?>>
		<div class="buttonDiv">
			<input type="submit" name="downloadAudio" value="下载文件">
			<input type="button" name="back" value="返回" onclick=location='showBucketFile.php?query=ok&bucket=<?php echo $bucket;?>&parentPath=<?php echo $parentPath;?>'>
		</div>
	</form>
</body>
</html>
<?php
	}
?>

