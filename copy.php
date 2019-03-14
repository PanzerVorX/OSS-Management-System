<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;
	use OSS\Http\RequestCore;
	use OSS\Http\ResponseCore;
	use OSS\Core\OssUtil;

	$src_bucket=$_GET['src_bucket'];
	$src_parentPath=$_GET['src_parentPath'];
	$src_object=$_GET['src_object'];

	//反序列化
	$handle=fopen("./serialize.txt","r+");
	$serialize=fread($handle,filesize("./serialize.txt"));
	fclose($handle);
	$ossClient=unserialize($serialize);
	
	//对超过10M的文件进行分片拷贝
	function copyBigFile($ossClient,$bucket,$object,$dst_bucket,$dst_object){
		
		//步骤1：初始化一个分片上传事件，获取uploadId
		$objectMeta = $ossClient->getObjectMeta($bucket, $object);
		$filesize=$objectMeta['content-length'];
		try{
		    //返回uploadId，分片上传事件的唯一标识
		    $uploadId = $ossClient->initiateMultipartUpload($dst_bucket, $dst_object);
		} 
		catch(OssException $e) {
			echo "<script>alert('初始化分片失败');</script>";
		}
			
		//步骤2：上传分片。
		$partSize = 10 * 1024 * 1024;
		$uploadFileSize = $filesize;
		$pieces = $ossClient->generateMultiuploadParts($uploadFileSize, $partSize);
		$responseUploadPart = array();
		$uploadPosition = 0;
		$isCheckMd5 = true;
		
		foreach ($pieces as $i => $piece) {
			$copyId=$i+1;
		    $fromPos = $uploadPosition + (integer)$piece[$ossClient::OSS_SEEK_TO];
		    $toPos = (integer)$piece[$ossClient::OSS_LENGTH] + $fromPos - 1;
		    $upOptions = array(
		        $ossClient::OSS_FILE_UPLOAD => $object,
		        $ossClient::OSS_PART_NUM => ($i + 1),
		        $ossClient::OSS_SEEK_TO => $fromPos,
		        $ossClient::OSS_LENGTH => $toPos - $fromPos + 1,
		        //$ossClient::OSS_CHECK_MD5 => $isCheckMd5,
		    );
			// MD5校验
			/*
		    if ($isCheckMd5) {
		        $contentMd5 = OssUtil::getMd5SumForFile($object, $fromPos, $toPos);
		        $upOptions[$ossClient::OSS_CONTENT_MD5] = $contentMd5;
		    }
		    */
		    try {
				//上传分片
				$responseUploadPart[] =$ossClient->uploadPartCopy( $bucket, $object, $dst_bucket, $dst_object,$copyId, $uploadId);
		    } 
		    catch(OssException $e) {
		    	echo "<script>alert('上传分片part{$i}失败');</script>";
		    }
		}

		//$uploadParts是由每个分片的ETag和分片号（PartNumber）组成的数组。
		$uploadParts = array();
		foreach ($responseUploadPart as $i => $eTag) {
		    $uploadParts[] = array(
		        'PartNumber' => ($i + 1),
		        'ETag' => $eTag,
		    );
		}
		
		//步骤3：组合分片
		try {
			$ossClient->completeMultipartUpload($dst_bucket, $dst_object, $uploadId, $uploadParts);
		}  
		catch(OssException $e) {
			echo "<script>alert('组合分片失败');</script>";
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		
		select,option{
			width: 200px;
			height: 50px;
			font-size: 32px;
			font-weight: bold;
		}

		span{
			font-size: 32px;
			font-weight: bold;
		}

		form{
			margin-left: 50px;
			margin-top: 200px;
		}

		.rightSpan{
			margin-left: 30px;
		}

		input[type='text']{
			width: 300px;
			height: 35px;
			font-size: 32px;
			font-weight: bold;	
		}

		input[type='submit']{
			width: 200px;
			height: 35px;
			font-size: 25px;
			font-weight: bold;
		}

		input[type='button']{
			width: 200px;
			height: 35px;
			font-size: 25px;
			font-weight: bold;
		}

		.button{
			margin-left: 200px;
			margin-top: 140px;
		}

		.button input{
			margin-left: 90px;
		}

	</style>
</head>
<body>
		<!--复用界面中显示内容的前端代码应一直保持输出显示-->
		<form method='get'>
		<span>拷贝至的存储空间：</span>
		<select name='dst_bucket'>
		<?php
			$bucketListInfo = $ossClient->listBuckets();
			$bucketList = $bucketListInfo->getBucketList();
			foreach($bucketList as $bucket) {
		     	$bucketName=$bucket->getName();
		     	echo "<option>{$bucketName}</option>";
			}
		?>
		</select>
		<span class='rightSpan'>拷贝至的目录：</span>
		<input type='text' name='dst_parentPath' placeholder='为空则为根目录'>
		<div class='button'>
		<input type='submit' name='ok' value='开始拷贝'>
		<input type='button' name='back' value='返回' onclick=location='showBucketFile.php?query=ok&bucket=<?php echo $src_bucket;?>&parentPath=<?php echo $src_parentPath; ?>'>
		<input type='hidden' name='src_bucket' value=<?php echo $src_bucket; ?>>
		<input type='hidden' name='src_object' value=<?php echo $src_object; ?>>
		<input type='hidden' name='src_parentPath' value=<?php echo $src_parentPath; ?>>
		</div>
		<form>
</body>
</html>
<?php
	
	function copyFile($ossClient,$src_bucket,$src_object,$dst_bucket,$dst_parentPath){
		if(strrpos($src_object,'/')==(strlen($src_object)-1)){//判断是否为目录
			$str=substr($src_object,0,strrpos($src_object,'/'));
			if(strpos($str,'/')){//判断是否为根目录中的文件夹
				 $dirName=substr($str,strrpos($str,'/')+1); 
				 $dst_parentPath=$dst_parentPath.$dirName.'/';
			}
			else{
				$dirName=substr($src_object,0,strpos($src_object,'/'));
				$dst_parentPath=$dst_parentPath.$dirName.'/';
				/*
				if(!$saveFilePrefix){
				 	$saveFilePrefix=$saveFilePrefix.$dirName;
				 }
				 else{
				 	$saveFilePrefix=$saveFilePrefix.'/'.$dirName;
				 }
				 */
			}

			$ossClient->uploadFile($dst_bucket,$dst_parentPath,'serialize.txt');

			$prefix = $src_object;
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
			    $listObjectInfo = $ossClient->listObjects($src_bucket, $options);
			    $objectList = $listObjectInfo->getObjectList();
			    if (!empty($objectList)) {
			    	$dirArr=array();
				    foreach ($objectList as $objectInfo) {
				    	$object=$objectInfo->getKey();

				    	//获取文件路径除去父目录之后的部分
				    	if($src_object){
				    		$tempStr=substr($object,strlen($src_object));
				    	}
				    	else{
				    		$tempStr=$object;
				    	}

				    	$isDir=strpos($tempStr,'/');//判断是否是文件
				    	if(!$isDir){
				    		if($tempStr){
				    			$dst_object=$dst_parentPath.$tempStr;
				    			$objectMeta = $ossClient->getObjectMeta($src_bucket, $object);
				    			$filesize=$objectMeta['content-length'];
				    			if($filesize>(10 * 1024 * 1024)){//大于10M则分片拷贝
				    				copyBigFile($ossClient,$src_bucket,$object,$dst_bucket,$dst_object);
				    			}
				    			else{
				    				$ossClient->copyObject($src_bucket, $object, $dst_bucket, $dst_object);
				    			}
				    		}
				    	}
				    	else{
				    		$dirName=substr($tempStr,0,strpos($tempStr,'/')+1);
				    		if(!in_array($dirName,$dirArr)){
				    			$dirArr[]=$dirName;
				    			copyFile($ossClient,$src_bucket,$object,$dst_bucket,$dst_parentPath);
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
			if(strpos($src_bucket,'/')){//是否为根目录的文件
				$dst_object=$dst_parentPath.substr($src_object,strrpos($src_object,'/')+1);
			}
			else{
				$dst_object=$dst_parentPath.$src_object;
			}
			$objectMeta = $ossClient->getObjectMeta($src_bucket, $src_object);
			$filesize=$objectMeta['content-length'];
			if($filesize>(10 * 1024 * 1024)){//大于10M则分片拷贝
				copyBigFile($ossClient,$src_bucket,$src_object,$dst_bucket,$dst_object);
			}
			else{
				$ossClient->copyObject($src_bucket, $src_object, $dst_bucket, $dst_object);
			}
		}
	}

	if(isset($_GET['ok'])){
		$dst_bucket=$_GET['dst_bucket'];
		$dst_parentPath=$_GET['dst_parentPath'];
		$dst_parentPath=($dst_parentPath)?($dst_parentPath.'/'):'';
		if(($dst_parentPath && $ossClient->doesObjectExist($dst_bucket,$dst_parentPath)) || $dst_parentPath==''){
			copyFile($ossClient,$src_bucket,$src_object,$dst_bucket,$dst_parentPath);
			echo "<script>alert('拷贝成功');</script>";
		}
		else{
			echo "<script>alert('目的目录不存在');location='?copy=ok&src_bucket={$src_bucket}&src_parentPath={$src_parentPath}&src_object={$src_object}';</script>";
		}
	}
?>
