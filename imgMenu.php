<?php
	if(isset($_GET['operationImg'])){
		$bucket=$_GET['bucket'];
		$object=$_GET['object'];
		$parentPath=$_GET['parentPath'];
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		
		body{
			background-color: #444;
		}

		td{
			height: 40px;
		}
		
		input[type='submit']{
			font-size: 18px;			
			font-weight: bold;
			width: 90px;
			height: 30px;
			background-color: #343D46;
			border-radius: 10px;
			position:relative;
			left: 50px;
			top:26px;
		}

		input[type='button']{
			font-size: 18px;			
			font-weight: bold;
			width: 90px;
			height: 30px;
			background-color: #343D46;
			border-radius: 10px;
			position:relative;
			left: 100px;
			top:26px;
		}

	</style>
</head>
<body>
	<!--具有指定跳转方式target的标签：<form>与<a>-->
	<form method="post" action="imgShow.php" target="imgShow">
		<table>
		<input type="hidden" name="bucket" value=<?php echo $bucket;?>>
		<input type="hidden" name="object" value=<?php echo $object;?>>
		<tr><td colspan="2"><span>图片裁剪：</span></td></tr>
		<tr><td><span>起点x：</span></td><td><input type="text" name="image_crop_x"></td></tr>
		<tr><td><span>起点y：</span></td><td><input type="text" name="image_crop_y"></td></tr>
		<tr><td>裁剪宽度：</td><td><input type="text" name="image_crop_w"></td></tr>
		<tr><td>裁剪高度：</td><td><input type="text" name="image_crop_h"></td></tr>
		<tr><td colspan="2"><span>图片缩放：</span></td></tr>
		<tr><td><span>宽：</span></td><td><input type="text" name="image_resize_w"></td></tr>
		<tr><td><span>高：</span></td><td><input type="text" name="image_resize_h"></td></tr>
		<tr><td><span>图片旋转：</span></td><td><input type="text" name="image_rotate"></td></tr>
		<tr><td><span>图片锐化：</span></td>
			<td>
				<select name="image_sharpen">
					<option value="0" selected>关闭</option>
					<option value="1">开启</option>
				</select>
			</td>
		</tr>
		<tr><td><span>图片格式：</span></td>
			<td>
				<select name="image_format">
					<option value="0" selected>请选择</option>
					<option>jpg</option>
					<option>png</option>
					<option>webp</option>
					<option>bmp</option>
				</select>
			</td>
		</tr>
		<tr><td>图片水印：</td>
			<td><input type="text" name="image_watermark"></td>
		</tr>
		<!--<frameset>框架中实现一个<frame>通过按钮指定另一个<frame>跳转到指定页面可将按钮加在<a>中-->
		<tr><td><input type="submit" name="setImg" value="处理图片"></td><td><a href="showBucketFile.php?query=ok&bucket=<?php echo $bucket;?>&parentPath=<?php echo $parentPath; ?>" target="frameOperation"><input type="button" name="back" value="返回"></a></td></tr>
		</table>
	</form>
</body>
</html>