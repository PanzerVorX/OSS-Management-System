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
</head>
	<frameset cols="30%,*">
		<!--向<frameset>中<frame>指向的页面间接传递参数可通过<frameset>页面将接收参数设置为其下属的<frame>指向页面的参数（get方式）-->
		<frame frameborder="0" src="imgMenu.php?operationImg=ok&bucket=<?php echo $bucket;?>&object=<?php echo $object;?>&parentPath=<?php echo $parentPath; ?>" name="imgMenu" scrolling="no" noresize><!--主菜单页面-->
		<frame frameborder="0" src="imgShow.php?operationImg=ok&bucket=<?php echo $bucket;?>&object=<?php echo $object;?>" name='imgShow'><!--操作页面-->
	</frameset>
</html>