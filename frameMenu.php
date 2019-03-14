<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<style type="text/css">
		
		body{
			background-color:#6495ED;
		}

		span{
			font-size:35px;
			font-weight: bold;
			margin-left: 10px;
		}

		a{
			text-decoration: none;
			color: #000;
		}

		a:hover{
			text-decoration: underline;
			color: #8470FF;
		}

		input[type='checkbox']{
			width: 20px;
			height: 20px;
		}

		.splitLine{
			background-color: #888;
			height: 5px;
		}
		
		input[type='submit']{
			margin-left: 8px;
			border-radius: 10px;
			background-color: #7FFFD4;
		}

		input[type='button']{
			margin-left: 8px;
			border-radius: 10px;
			background-color: #7FFFD4;
		}

	</style>
</body>
</html>
<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;

	//反序列化
	$handle=fopen("./serialize.txt","r+");
	$serialize=fread($handle,filesize("./serialize.txt"));
	fclose($handle);
	$ossClient=unserialize($serialize);

	try{
		$bucketListInfo = $ossClient->listBuckets();
	}
	catch(OssException $e){
		echo "<script>alert('列举存储空间报错');</script>";
	}

	echo "<form method='post' action='operationBucket.php' target='frameOperation'>";
	echo "<span>存储空间<sapn>";
	//表单内被选中的提交按钮(submit)在提交参数时屏蔽其它提交按钮元素（不会传递其它提交按钮的参数）
	echo "<input type='submit' name='create' value='创建'>";
	echo "<input type='submit' name='delete' value='删除'>";
	echo "<input type='button' name='refresh' value='刷新' onclick=location=''>";
	echo "<div class='splitLine'></div>";
	$bucketList = $bucketListInfo->getBucketList();
	foreach($bucketList as $bucket) {
	     $bucketName=$bucket->getName();
	     echo "<input type='checkbox' name='bucket[]' value={$bucketName}><a href='showBucketFile.php?query=ok&bucket={$bucketName}&parentPath=' target='frameOperation'>{$bucketName}</a><br>";
	}
	echo "<form>";
?>