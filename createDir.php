<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;
	use OSS\Http\RequestCore;
	use OSS\Http\ResponseCore;

	$bucket=$_GET['bucket'];
	$parentPath=$_GET['parentPath'];
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style>

		span{
			font-size:35px;
			font-weight: bold;
			margin-left: 10px;
		}

		form{
			margin-top: 12%;
			margin-left: 18%;
			font-size: 30px;
			font-weight: bold;
		}

		input{
			height: 35px;
			width: 380px;
			font-size: 20px;
			font-weight: bold;
		}

		input[type='submit']{
			width: 200px;
			margin-top: 80px;
			margin-left: 150px;
			height: 50px;
		}

		input[type='button']{
			width: 200px;
			margin-top: 80px;
			margin-left: 100px;
			height: 50px;
		}

	</style>
</head>
<body>
	<form method="get">
		<span>输入创建目录名：</span><input type="text" name="dirName"><br>
		<input type="hidden" name="bucket" value=<?php echo $bucket;?>>
		<input type="hidden" name="parentPath" value=<?php echo $parentPath;?>>
		<input type="submit" name="ok" value="确认创建">
		<input type="button" name="back" value="返回" onclick=location='showBucketFile.php?query=ok&bucket=<?php echo $bucket; ?>&parentPath=<?php echo $parentPath; ?>'>
	</form>
</body>
</html>
<?php
	if(isset($_GET['ok'])){

		$dirName=$_GET['dirName'];

		//反序列化
		$handle=fopen("./serialize.txt","r+");
		$serialize=fread($handle,filesize("./serialize.txt"));
		fclose($handle);
		$ossClient=unserialize($serialize);

		if( (!strpos($dirName,'/'))&&($dirName!='..') ){//判断目录名是否符合要求
			$object=$parentPath.$dirName.'/';
			$exist = $ossClient->doesObjectExist($bucket,$object);
			if($exist){
				echo "<script>alert('无法创建同名目录');</script>";
			}
			else{
				$ossClient->uploadFile($bucket,$object,'serialize.txt');
			echo "<script>alert('创建成功');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath}';</script>";
			}
		}
		else{
			echo "<script>alert('目录名不合要求');</script>";
		}
	}
?>