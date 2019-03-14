<?php
	require "aliyun-oss-php-sdk-2.3.0/autoload.php";
	use OSS\OssClient;
	use OSS\Core\OssException;

	//反序列化
	$handle=fopen("./serialize.txt","r+");
	$serialize=fread($handle,filesize("./serialize.txt"));
	fclose($handle);
	$ossClient=unserialize($serialize);

	if(isset($_GET['operationImg'])){
		$bucket=$_GET['bucket'];
		$object=$_GET['object'];

		//获取原图信息
		$imageInfoFile='imgInfo.txt';
		$options = array(OssClient::OSS_FILE_DOWNLOAD => $imageInfoFile,OssClient::OSS_PROCESS => "image/info" );
		$ossClient->getObject($bucket, $object, $options);

		$handle=fopen($imageInfoFile,'r+');
		$imageInfo=fread($handle,filesize($imageInfoFile));
		fclose($handle);
		unlink($imageInfoFile);

		//从原图信息文件中获取原图尺寸
		preg_match('/ImageHeight(\D+)?\d+/',$imageInfo,$arr);
		preg_match('/\d+/',$arr[0],$tempArr);
		$imageHeight=$tempArr[0];
		preg_match('/ImageWidth(\D+)?\d+/',$imageInfo,$arr);
		preg_match('/\d+/',$arr[0],$tempArr);
		$imageWidth=$tempArr[0];

		//设置缩放尺寸参数为原图大小
		$imageHeight='h_'.$imageHeight;
		$imageWidth='w_'.$imageWidth;

		$style="image/resize,m_lfit,{$imageHeight},{$imageWidth}";
		$timeout = 3600;
		$options = array(OssClient::OSS_PROCESS => $style);
		$signedUrl = $ossClient->signUrl($bucket,$object, $timeout, "GET", $options);
		//echo $signedUrl;
		echo "<center><img src={$signedUrl}></center>";
	}

	if(isset($_POST['setImg'])){

		$bucket=$_POST['bucket'];
		$object=$_POST['object'];
		$image_crop_x=$_POST['image_crop_x'];//裁剪起点x坐标
		$image_crop_y=$_POST['image_crop_y'];//裁剪起点y坐标
		$image_crop_w=$_POST['image_crop_w'];//裁剪宽度
		$image_crop_h=$_POST['image_crop_h'];//裁剪高度
		$image_resize_w=$_POST['image_resize_w'];//缩放宽
		$image_resize_h=$_POST['image_resize_h'];//缩放高
		$image_rotate=$_POST['image_rotate'];//旋转角度
		$image_sharpen=$_POST['image_sharpen'];//锐化选择
		$image_format=$_POST['image_format'];//图片转换格式
		$image_watermark=$_POST['image_watermark'];//图片水印

		//获取原图信息
		$imageInfoFile='imgInfo.txt';
		$options = array(OssClient::OSS_FILE_DOWNLOAD => $imageInfoFile,OssClient::OSS_PROCESS => "image/info" );
		$ossClient->getObject($bucket, $object, $options);

		$handle=fopen($imageInfoFile,'r+');
		$imageInfo=fread($handle,filesize($imageInfoFile));
		fclose($handle);
		unlink($imageInfoFile);

		//对于需通过较繁琐的字符串处理才能获取字符串中的指定信息时可考虑使用正则匹配简化处理过程
		//从原图信息文件中获取原图尺寸
		preg_match('/ImageHeight(\D+)?\d+/',$imageInfo,$arr);
		preg_match('/\d+/',$arr[0],$tempArr);
		$imageHeight=$tempArr[0];
		preg_match('/ImageWidth(\D+)?\d+/',$imageInfo,$arr);
		preg_match('/\d+/',$arr[0],$tempArr);
		$imageWidth=$tempArr[0];

		if($image_resize_w||$image_resize_h){
			if((!is_numeric($image_resize_w))||(!is_numeric($image_resize_h))){
				echo "<script>alert('缩放宽高必须为数字');location='frameOperation.php';</script>";
			}
			elseif(($image_resize_w>3*$imageWidth)||($image_resize_h>3*$imageHeight)||($image_resize_w<=0)||($image_resize_h<=0)){
				echo "<script>alert('缩放范围错误');location='frameOperation.php';</script>";
			}
			$style="image/resize,m_fixed,h_{$image_resize_h},w_{$image_resize_w}";
		}
		else{
			$style="image/resize,m_fixed,h_{$imageHeight},w_{$imageWidth}";
		}

		if($image_crop_x||$image_crop_y){
			if((!is_numeric($image_crop_x))||(!is_numeric($image_crop_y))){
				echo "<script>alert('裁剪起点坐标必须为数字');location='frameOperation.php';</script>";
			}
			elseif(($image_crop_x>$imageWidth)||($image_crop_y>$imageHeight)||($image_crop_x<0)||($image_crop_y<0)){
				echo "<script>alert('裁剪范围错误');location='frameOperation.php';</script>";
			}
			elseif((($image_crop_x+$image_crop_w)>$imageWidth)||(($image_crop_y+$image_crop_h)>$imageHeight)||($image_crop_w<=0)||($image_crop_h<=0)){
				echo "<script>alert('裁剪范围错误');location='frameOperation.php';</script>";
			}
			$style.="/crop,w_{$image_crop_w},h_{$image_crop_h},x_{$image_crop_x},y_{$image_crop_y},r_1";
		}

		if($image_rotate){
			if(!is_numeric($image_rotate)){
				echo "<script>alert('旋转角度必须为数字');location='frameOperation.php';</script>";
			}
			elseif(($image_rotate>360)||($image_rotate<0)){
				echo "<script>alert('旋转角度范围错误');location='frameOperation.php';</script>"; 
			}
			$style.="/rotate,{$image_rotate}";
		}

		if($image_sharpen){
			$style.="/sharpen,100";
		}

		if($image_watermark){
			$base64Str=base64_encode($image_watermark);
			$style.="/watermark,text_{$base64Str}";
		}

		if($image_format){
			$style.="/format,{$image_format}";
		}

		$timeout = 3600;
		$options = array(OssClient::OSS_PROCESS => $style);
		$signedUrl = $ossClient->signUrl($bucket,$object, $timeout, "GET", $options);
		echo "<center><img src={$signedUrl}></center>";
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		input{
			width: 120px;
			height: 40px;
			font-size: 20px;
			font-weight: bold;
			border-radius: 2px;
			position: absolute;
			right: 45%;
			bottom: 30px;
		}
	</style>
</head>
<body>
	<form method="post" action="downloadImg.php">
		<input type="hidden" name="bucket" value=<?php echo $bucket;?>>
		<input type="hidden" name="object" value=<?php echo $object;?>>
		<input type="hidden" name="style" value=<?php echo $style;?>>
		<input type="submit" name="downloadImg" value="下载图片">
	</form>
</body>
</html>