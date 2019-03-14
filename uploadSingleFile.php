<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;
	
	if(isset($_POST['upload'])){

		//反序列化
		$handle=fopen("./serialize.txt","r+");
		$serialize=fread($handle,filesize("./serialize.txt"));
		fclose($handle);
		$ossClient=unserialize($serialize);

		$bucket=$_POST['bucket'];
		$parentPath=$_POST['parentPath'];
		
		//注意后端与前端代码执行顺序：后端代码在服务端执行，前端代码由服务端返回给客户端执行
		//防止使用后端代码进行条件判断时通过前端代码跳转页面停止脚本运行来排除异常情况：判断之后的后端代码仍先于前端代码执行
		if(!$_FILES['uploadFile']['tmp_name']){
			echo "<script>alert('未选择文件');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath}'</script>";
		}
		else{
			if($_FILES["uploadFile"]["error"]>0){//通过文件上传后的错误代码值判断上传状态
				echo "<script>alert('上传出错------错误：'.{$_FILES['uploadFile']['error']});location='frameOperation.php'</script>";
			}
			else{
				$temp_filename=$_FILES['uploadFile']['tmp_name'];//临时文件名
				$filename=$_FILES['uploadFile']['name'];
				if(is_uploaded_file($temp_filename)){//是否为post方式
					if(move_uploaded_file($temp_filename,$filename)){
						try{
							$ossClient->uploadFile($bucket,$parentPath.$filename,$filename);
							unlink($filename);
							echo "<script>alert('上传至存储空间成功');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath}'</script>";
						}
						catch(Exception $e){
							echo "<script>alert('上传至存储空间失败');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath}'</script>";
						}
						
					}
					else{
						echo "<script>alert('转移文件出错');location='showBucketFile.php?query=ok&bucket={$bucket}&parentPath={$parentPath}'</script>";
					}
				}
			}
		}	
	}
	elseif(!isset($_POST['bucket'])){
		echo "<script>alert('文件过大，请选择多文件上传');location='frameOperation.php'</script>";
	}
?>