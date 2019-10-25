<?php
/*SHOW DOER IN MAP API*/

require_once('../datos/ConexionBD.php');

if(isset($_REQUEST['action']))
{
	//get geolocation data from doer address
	$url = 'http://maps.google.com/maps/api/geocode/json?address='.str_replace('-','+',str_replace(' ','+',$_REQUEST['dir'])).'&sensor=false';
}
else
{
	$doerID=$_REQUEST['doerID'];

//get doer address
$comando = "SELECT users.*,doers.address,doers.city,doers.state from doers inner join users on users.userID=doers.userID WHERE doerID=?";
        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
        $sentencia->bindParam(1, $doerID);
$sentencia->execute();
            $resultado = $sentencia->fetch();
            $address= $resultado['address'];
			$city= $resultado['city'];
			$state= $resultado['state'];
			$doerName=$resultado['firstName'].' '.$resultado['lastName'];
			
//get geolocation data from doer address
	$url = 'http://maps.google.com/maps/api/geocode/json?address='.str_replace(' ','+',$address).'+'.str_replace(' ','+',$city).'+'.str_replace(' ','+',$state).'&sensor=false';
}

	
$obj = json_decode(file_get_contents($url), true);
$lat= $obj['results'][0]['geometry']['location']['lat'];
$lon= $obj['results'][0]['geometry']['location']['lng'];
$geo_data = array( 'latitude' => "$lat", 'longitude' => "$lon", 'doerName' => $doerName );

echo json_encode($geo_data);

?>