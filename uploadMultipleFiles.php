<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;

	function uploadMultipleFiles($ossClient,$bucket,$parentPath,$localFilePath){
		$fileName=substr($localFilePath,strrpos($localFilePath,'/')+1);
		if(is_dir($localFilePath)){//判断是否为目录
	        try{
	        	$parentPath=$parentPath.$fileName.'/';//上传后的目录路径
				$ossClient->uploadFile($bucket,$parentPath,'serialize.txt');
				$handle=opendir($localFilePath);
				while($f = readdir($handle)){//遍历目录子文件
					
					if($f=="." || $f==".."){//跳过特殊目录.和..
			           continue;
			        }
			        
			        $filePath=$localFilePath."/".$f;//设置子文件的完整本地路径

			        //判断是否是文件
			        if(is_file($filePath)){
			        	$ossClient->uploadFile($bucket,$parentPath.$f,$filePath);
			        }

			        //判断是否是目录
			        if(is_dir($filePath)){
			            uploadMultipleFiles($ossClient,$bucket,$parentPath,$filePath);
			        }
				}
				closedir($handle);
			}
			catch(Exception $e){
				var_dump($e);
			}
	    }
	    elseif(is_file($localFilePath)){
	    	try{
	        	$parentPath=$parentPath.$fileName;
				$ossClient->uploadFile($bucket,$parentPath,$localFilePath);
			}
			catch(Exception $e){
				var_dump($e);
			}
	    }
	}

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
			margin-left: 250px;
			height: 50px;
		}

		input[type='button']{
			width: 200px;
			margin-top: 80px;
			margin-left: 50px;
			height: 50px;
		}

	</style>
</head>
<body>
	<form method="get">
		<span>输入上传文件的本地路径：</span><input type="text" name="localFilePath"><br>
		<input type="hidden" name="bucket" value=<?php echo $bucket;?>>
		<input type="hidden" name="parentPath" value=<?php echo $parentPath;?>>
		<input type="submit" name="ok" value="开始上传">
		<input type="button" name="back" value="返回" onclick=location='showBucketFile.php?query=ok&bucket=<?php echo $bucket; ?>&parentPath=<?php echo $parentPath; ?>'>
	</form>
</body>
</html>
<?php
	if(isset($_GET['ok'])){

		$localFilePath=$_GET['localFilePath'];
		
		if(strpos($localFilePath,':')>0){
			$localFilePath=substr($localFilePath,strpos($localFilePath,':')-1);
		}
	
		if(!file_exists($localFilePath)){
			echo "<script>alert('文件不存在');</script>";
		}

		$localFilePath=str_replace('\\','/',$localFilePath);
		
		//反序列化
		$handle=fopen("./serialize.txt","r+");
		$serialize=fread($handle,filesize("./serialize.txt"));
		fclose($handle);
		$ossClient=unserialize($serialize);

		try {
			uploadMultipleFiles($ossClient,$bucket,$parentPath,$localFilePath);
			echo "<script>alert('上传成功');</script>";
		} 
		catch (Exception $e) {
			echo "<script>alert('上传失败');</script>";
		}
		
	}
?>
