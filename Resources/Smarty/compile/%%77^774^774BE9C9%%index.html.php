<?php /* Smarty version 2.6.25, created on 2013-01-09 17:40:30
         compiled from index.html */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>上传信息</title>
</head>
<body>
<?php echo $this->_tpl_vars['user']['name']; ?>

<form name="info" method="post" action="/index" >
<table border="1">
	<tr>
		<td>姓&nbsp;&nbsp;&nbsp;&nbsp;名:</td>
		<td><input name="name" type="text" style="width:300px" value=""/></td>
	</tr>
	<tr>
		<td>身份证号：</td>
		<td><input name="id_number" type="text" style="width:300px" value=""/></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><input name="Submit2" type="submit" value="下一步" /></td>
	</tr>
</table>
</form>
</body>
</html>