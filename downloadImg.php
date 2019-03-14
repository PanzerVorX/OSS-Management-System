<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;

	$bucket=$_POST['bucket'];
	$object=$_POST['object'];
	$style=$_POST['style'];
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
			margin-left: 250px;
			height: 50px;
		}

	</style>
</head>
<body>
	<form method="post">
		<span>输入保存地址：</span><input type="text" name="saveFilePrefix"><br>
		<input type="hidden" name="bucket" value=<?php echo $bucket;?>>
		<input type="hidden" name="object" value=<?php echo $object;?>>
		<input type="hidden" name="style" value=<?php echo $style;?>>
		<input type="submit" name="ok" value="开始下载">
	</form>
</body>
</html>
<?php
	if(isset($_POST['ok'])){

		$saveFilePrefix=$_POST['saveFilePrefix'];

		if(strpos($saveFilePrefix,':')>0){
			$saveFilePrefix=substr($saveFilePrefix,strpos($saveFilePrefix,':')-1);
		}

		//反序列化oss对象
		$handle=fopen("./serialize.txt","r+");
		$serialize=fread($handle,filesize("./serialize.txt"));
		fclose($handle);
		$ossClient=unserialize($serialize);

		if(file_exists($saveFilePrefix) && is_dir($saveFilePrefix)){
			
			$isNotRootImg=strrpos($object,'/');//判断是否不为根目录下的文件
			if($isNotRootImg){
				$fileName=substr($object,strrpos($object,'/')+1);
			}
			else{
				$fileName=$object;
			}

			$isSetFormat=strpos($style,'format');//判断是否设置图片格式转换
			if($isSetFormat){
				$fileName= substr($fileName,0,strrpos($fileName,'.')+1).substr($style,strpos($style,'format')+7);
			}
			try{
				$options = array(
				    OssClient::OSS_FILE_DOWNLOAD => $saveFilePrefix.'/'.$fileName,
				    OssClient::OSS_PROCESS => $style);
				$ossClient->getObject($bucket, $object, $options);
				echo "<script>alert('下载成功');</script>";
			}
			catch(Exception $e){
				echo "<script>alert('下载失败');</script>";
			}
		}
		else{
			echo "<script>alert('保存地址输入错误');</script>";
		}
	}
?>