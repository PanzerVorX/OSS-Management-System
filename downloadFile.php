<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;

	function downloadFile($ossClient,$bucket,$parentPath,$saveFilePrefix){

		if(strrpos($parentPath,'/')==(strlen($parentPath)-1)){//判断是否为目录
			$str=substr($parentPath,0,strrpos($parentPath,'/'));
			if(strpos($str,'/')){//判断是否为根目录中的文件夹
				 $dirName=substr($str,strrpos($str,'/')+1); 
				 $saveFilePrefix=$saveFilePrefix.'/'.$dirName;
				 /*
				 if(!$saveFilePrefix){
				 	$saveFilePrefix=$saveFilePrefix.$dirName;
				 }
				 else{
				 	$saveFilePrefix=$saveFilePrefix.'/'.$dirName;
				 }
				 */
			}
			else{
				$dirName=substr($parentPath,0,strpos($parentPath,'/'));
				$saveFilePrefix=$saveFilePrefix.'/'.$dirName;
				/*
				if(!$saveFilePrefix){
				 	$saveFilePrefix=$saveFilePrefix.$dirName;
				 }
				 else{
				 	$saveFilePrefix=$saveFilePrefix.'/'.$dirName;
				 }
				 */
			}

			mkdir($saveFilePrefix);

			$prefix = $parentPath;
			$delimiter = '';
			$nextMarker = '';
			$maxkeys = 1000;
			$options = array(
			    'delimiter' => $delimiter,
			    'prefix' => $prefix,
			    'max-keys' => $maxkeys,
			    'marker' => $nextMarker,
			);

			try {
			    $listObjectInfo = $ossClient->listObjects($bucket, $options);
			    $objectList = $listObjectInfo->getObjectList();
			    if (!empty($objectList)) {
			    	$dirArr=array();
				    foreach ($objectList as $objectInfo) {
				    	$object=$objectInfo->getKey();

				    	//获取文件路径除去父目录之后的部分
				    	if($parentPath){
				    		$tempStr=substr($object,strlen($parentPath));
				    	}
				    	else{
				    		$tempStr=$object;
				    	}

				    	$isDir=strpos($tempStr,'/');//判断是否是文件
				    	if(!$isDir){
				    		if($tempStr){
				    			$saveFilePath=$saveFilePrefix.'/'.$tempStr;
						    	$options = array(
							        OssClient::OSS_FILE_DOWNLOAD => $saveFilePath
							    );
				    			 $ossClient->getObject($bucket, $object, $options);
				    		}
				    	}
				    	else{
				    		$dirName=substr($tempStr,0,strpos($tempStr,'/')+1);
				    		if(!in_array($dirName,$dirArr)){
				    			$dirArr[]=$dirName;
				    			downloadFile($ossClient,$bucket,$object,$saveFilePrefix);
				    		}
				    	}
				    }
				}
			} 
			catch (Exception $e) {
				var_dump($e);
			}
		}
		else{
			if(strpos($parentPath,'/')){//是否为根目录的文件
				$saveFilePath=$saveFilePrefix.'/'.substr($parentPath,strrpos($parentPath,'/')+1);
			}
			else{
				$saveFilePath=$saveFilePrefix.'/'.$parentPath;
			}
			$options = array(OssClient::OSS_FILE_DOWNLOAD => $saveFilePath);
			$ossClient->getObject($bucket, $parentPath, $options);
		}
	}
	/*
	if(isset($_GET['download'])){

		$bucket=$_GET['bucket'];
		$parentPath=$_GET['filePath'];
		$saveFilePrefix='';
		
		//反序列化
		$handle=fopen("./serialize.txt","r+");
		$serialize=fread($handle,filesize("./serialize.txt"));
		fclose($handle);
		$ossClient=unserialize($serialize);

		downloadFile($ossClient,$bucket,$parentPath,$saveFilePrefix);
		echo "<script>alert('下载成功');location='frameOperation.php';</script>";
	}
	*/
?>

<?php
	$bucket=$_GET['bucket'];
	$object=$_GET['filePath'];
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
		<span>输入保存地址：</span><input type="text" name="saveFilePrefix"><br>
		<!--不同页面中都需要的共用参数需持续保持传递，若存在本体页面提交表单情况应将接收的共用参数存入隐藏域作为本体提交参数传递-->
		<input type="hidden" name="bucket" value=<?php echo $bucket;?>>
		<input type="hidden" name="filePath" value=<?php echo $object;?>>
		<input type="hidden" name="parentPath" value=<?php echo $parentPath;?>>
		<input type="submit" name="ok" value="开始下载">
		<input type="button" name="back" value="返回" onclick=location='showBucketFile.php?query=ok&bucket=<?php echo $bucket; ?>&parentPath=<?php echo $parentPath; ?>'>
	</form>
</body>
</html>
<?php
	if(isset($_GET['ok'])){

		$saveFilePrefix=$_GET['saveFilePrefix'];
		
		if(strpos($saveFilePrefix,':')>0){
			$saveFilePrefix=substr($saveFilePrefix,strpos($saveFilePrefix,':')-1);
		}
		
		if(file_exists($saveFilePrefix) && is_dir($saveFilePrefix)){

			//反序列化
			$handle=fopen("./serialize.txt","r+");
			$serialize=fread($handle,filesize("./serialize.txt"));
			fclose($handle);
			$ossClient=unserialize($serialize);

			try{
				downloadFile($ossClient,$bucket,$object,$saveFilePrefix);
				echo "<script>alert('下载成功');</script>";
			}
			catch(Exception $e){
				echo "<script>alert('下载失败');</script>";
			}
		}
		else{
			echo "<script>alert('保存地址不正确，需重新输入！');</script>";
		}
	}
?>