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
			    			//对需要分层遍历并操作的文件结构可对相对父层进行递归遍历
			    			//若规定只能清空目录中内部文件/目录才能删除目录时应对目录进行递归删除
			    			deleteFile($ossClient,$bucket,$object);
			    		}
			    	}
			    }
			}
			$ossClient->deleteObject($bucket,$parentPath);
		}
		catch (Exception $e) {//解决阿里云对象存储SDK限制递归删除文件次数导致无法递归完全的问题：在catch中调用递归，直到递归完全为止 
			//catch中调递归————只许成功不许失败
			deleteFile($ossClient,$bucket,$parentPath);
		}
	}

	if(isset($_GET['deleteFile'])){

		$parentPath=$_GET['filePath'];
		$bucket=$_GET['bucket'];

		$tempStr=rtrim($parentPath,'/');
		$isRootDir=strpos($tempStr,'/');

		if($isRootDir){
			$returnPosition=substr($tempStr,0,strrpos($tempStr,'/')+1);
		}
		else{
			$returnPosition='';
		}

		//反序列化
		$handle=fopen("./serialize.txt","r+");
		$serialize=fread($handle,filesize("./serialize.txt"));
		fclose($handle);
		$ossClient=unserialize($serialize);

		deleteFile($ossClient,$bucket,$parentPath);
		echo "<script>alert('删除文件成功');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$returnPosition}'</script>";
	}	
?>