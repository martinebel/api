<?php
$res = array();
	$res['archivo'] = array();
	$res['archivo']['nombre'] = "";
	$res['archivo']['success'] = 0;
	
if(!isset($_REQUEST['claveApi'])){
		$res['archivo']['nombre'] = "Falta la clave de acceso Api.";
	}elseif(!isset($_REQUEST['tipo'])){
		$res['archivo']['nombre'] = "Falta el parametro tipo";
	}else{
		$res['archivo']['tipo'] = $_REQUEST['tipo'];
		$host="localhost"; //replace with database hostname 
		$username="root"; //replace with database username 
		$password="fun5BCTKduLw1LIe"; //replace with database password 
		$db_name="pediatricapp"; //replace with database name
		$new_image_name =basename( $_FILES['imagen']['name']) . "_" . $_REQUEST['tipo'] . ".jpg";
		
		$con = mysql_connect("$host", "$username", "$password")or die("cannot connect"); 
		mysql_select_db("$db_name")or die("cannot select DB");
		$query = "SELECT idUsuario FROM usuarios WHERE claveApi = '" . $_REQUEST['claveApi'] . "'";
		$sql = mysql_query($query);
		if($sql && ($row = mysql_fetch_array($sql))){
			$id = $row['idUsuario'];
			$new_image_name = $id . "_" . $_REQUEST['tipo'] . ".jpg";
		}else{
			echo 'error en sql:', $query; 
		}
		
		if(move_uploaded_file( $_FILES['imagen']['tmp_name'], "../fotos/".$new_image_name)){
			$res['archivo']['nombre'] = $new_image_name;
			$res['archivo']['success'] = 1;
			
		}		
	}		

	echo json_encode($res);
	