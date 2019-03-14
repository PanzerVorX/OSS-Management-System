<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;

	function deleteFile($ossClient,$bucket,$parentPath){

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
			    			$ossClient->deleteObject($bucket,$object);
			    		}
			    	}
			    	else{
			    		$dirName=substr($tempStr,0,strpos($tempStr,'/')+1);
			    		if(!in_array($dirName,$dirArr)){
			    			$dirArr[]=$dirName;
			    			deleteFile($ossClient,$bucket,$object);
			    		}
			    	}
			    }
			}
			$ossClient->deleteObject($bucket,$parentPath);
		} 
		catch (Exception $e) {//catch中调递归————只许成功不许失败
			deleteFile($ossClient,$bucket,$parentPath);
		}
	}

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

	$bucket=$_POST['bucket'];
	$parentPath=@$_POST['parentPath'];
	$fileArr=@$_POST['fileArr'];

	if((!isset($_POST['ok']))&&(!$fileArr)){//若未选择文件
		echo "<script>alert('未选择文件');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath}'</script>";
	}

	if(isset($_POST['batchDownload'])){//判断是否为批量下载
		//对于无法直接传递的值（如数组）可序列化为本地文件进行共享
		//序列化文件数组并存入本地文件
		$serializeArr=serialize($fileArr);
		$arrHandle=fopen('serializeArr.txt','w+');
		fwrite($arrHandle,$serializeArr);
		fclose($arrHandle);
	}

	if (isset($_POST['batchDelete'])) {

		//反序列化oss对象
		$handle=fopen("./serialize.txt","r+");
		$serialize=fread($handle,filesize("./serialize.txt"));
		fclose($handle);
		$ossClient=unserialize($serialize);

		try{
			foreach ($fileArr as $key => $value) {
				deleteFile($ossClient,$bucket,$value);			
			}
			echo "<script>alert('批量删除文件成功');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath}';</script>";
		}
		catch(Exception $e){
			echo "<script>alert('批量删除文件失败');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath}';</script>";
		}
	}
	else{
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
		<input type="hidden" name="parentPath" value=<?php echo $parentPath;?>>
		<input type="submit" name="ok" value="开始下载">
	</form>
</body>
</html>
<?php
	}

	if(isset($_POST['ok'])){
		$saveFilePrefix=$_POST['saveFilePrefix'];

		//反序列化oss对象
		$handle=fopen("./serialize.txt","r+");
		$serialize=fread($handle,filesize("./serialize.txt"));
		fclose($handle);
		$ossClient=unserialize($serialize);

		//反序列化文件数组
		$arrHandle=fopen("./serializeArr.txt","r+");
		$serializeArr=fread($arrHandle,filesize("./serializeArr.txt"));
		fclose($arrHandle);
		unlink('./serializeArr.txt');
		$fileArr=unserialize($serializeArr);

		if(file_exists($saveFilePrefix) && is_dir($saveFilePrefix)){
			foreach ($fileArr as $key => $value) {
				try{
					downloadFile($ossClient,$bucket,$value,$saveFilePrefix);
					echo "<script>alert('批量下载成功');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath}';</script>";
				}
				catch(Exception $e){
					echo "<script>alert('批量下载失败');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath}';</script>";
				}
			}
		}
		else{
			echo "<script>alert('保存地址不正确，需重新输入！');</script>";
		}
	}
?>