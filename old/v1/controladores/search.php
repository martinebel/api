<?php

require_once('datos/ConexionBD.php');

class search
{
    // Datos de la tabla "usuarios"
  
    
    const ESTADO_CREACION_EXITOSA = 1;
    const ESTADO_CREACION_FALLIDA = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_AUSENCIA_CLAVE_API = 4;
    const ESTADO_CLAVE_NO_AUTORIZADA = 5;
    const ESTADO_URL_INCORRECTA = 6;
    const ESTADO_FALLA_DESCONOCIDA = 7;
    const ESTADO_PARAMETROS_INCORRECTOS = 8;
    
	
	  public static function get($peticion)
    {
		 if (empty($peticion[0])){
			  return false;
		 }
		 else{
			 return self::ortografia( $peticion[0],$peticion[1]);
		 }
		
	}
	
	private function ortografia($worda,$lang)
	{
		$bien=array();$mal=array();$correccion=array();
		$words=array();
		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select name from categories_lang where idLang=".$lang);
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
		array_push($words, strtolower($row['name']));
 	}
 		//print_r ($words);exit();
 $contador=0;
		// input misspelled word
		$listskills = explode(",", $worda);
			foreach($listskills as $cat){
		$input = strtolower($cat);
		$input=trim($input);
		
		//primero buscar una similitud con la tabla de similitudes
		if(is_numeric($input)){
		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select name from categories_lang where idLang=".$lang." and idCategory=".$input);
		
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
		$input=strtolower($row['name']);
 	}
		}
		
			$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select * from similar_search where search like '%".$input."%' limit 1");
			
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
		//array_push($bien,$input);
		 array_push($mal,$row['result']);
		 $sentencia2 = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select * from categories_lang where name='".$row['result']."'");
		$sentencia2->execute();
		while ($row2 = $sentencia2->fetch(PDO::FETCH_ASSOC)) {
			 array_push($correccion,$row2['idCategory']);
		}
		 $contador++;
 	}
	if($contador==0)
	{
		// array of words to check against

$shortest = -1;

foreach ($words as $word) {
    $lev = levenshtein($input, $word);

    if ($lev == 0) {
        $closest = $word;
        $shortest = 0;
        break;
    }

    if ($lev <= $shortest || $shortest < 0) {
        $closest  = $word;
        $shortest = $lev;
    }
}

//echo "Input word: $input\n";
if ($shortest == 0) {
    array_push($bien,$cat);
} else {
	array_push($mal,$closest);
	$sentencia2 = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select * from categories_lang where name='".$closest."'");
		$sentencia2->execute();
		while ($row2 = $sentencia2->fetch(PDO::FETCH_ASSOC)) {
			 array_push($correccion,$row2['idCategory']);
		}
}
	}
			}
	 http_response_code(200);
    return
                    [
                        "bien" => $bien,
						"mal"=>$mal,
						"correccion"=>$correccion
                    ];
	}
	
	
	    public static function post($peticion){

        $body = file_get_contents('php://input');
        $filters = json_decode($body);
		if (empty($peticion[0])){
			 return  self::results($filters);
		 }
		 else{
			return  self::mobile($filters);
		 }
		
    }
	
	
	  private function mobile($filters)
    
    {    
        $location = $filters->location;
        $skills = $filters->skills;
        
        $iam = $filters->iam;
        $reputation = $filters->reputation;
        $verifiedProfiles = $filters->verifiedProfiles;
        $price = $filters->price;
        $priceRange = $filters->priceRange;
        $availability = $filters->availability;
        $hrFrom = $filters->hrFrom;
		$hrTo = $filters->hrTo;
		$minFrom = $filters->minFrom;
		$minTo = $filters->minTo;
		$days = $filters->days;

        
	        //TODO: falta agregar lo campos verifiedProfiles y reputation a la busqueda
	        

           
            
             $comando = "SELECT doers.*,users.apiKey,users.firstName,users.lastName,users.country,availability.days,availability.hrFrom,availability.hrTo,availability.unavailable_date FROM doers INNER JOIN users ON users.userID=doers.userID inner join availability on availability.doerID = doers.doerID";
			 
			$condition = " WHERE users.state<>'Inactive' ";
			 
			if ($location != ""){
				$location=str_replace(",","",$location);
				$condition.=" and (";
				$i=0;$listlocation = explode(" ", $location);
				foreach($listlocation as $cat){
					$cat = trim($cat);
						if($i==0){
							$condition.= "city like '%".$cat."%' OR doers.state like '%".$cat."%' OR country like '%".$cat."%'";
							}
							else{
							$condition.= " or city like '%".$cat."%' OR doers.state like '%".$cat."%' OR country like '%".$cat."%'";
 							}
 							$i++;
				}
				$condition.=")";	
				//$condition.="and (city like '%".$location."%' OR doers.state like '%".$location."%' OR country like '%".$location."%')";
			}
			
			if ($iam != "50Doer"){
				$condition.="and (iam='".$iam."')";
			}
			
			if ($skills != ""){
				$condition.=" and (";$i=0;$listskills = explode(",", $skills);
				foreach($listskills as $cat){
					$cat = trim($cat);
					$idcat="";
					//buscar id de categoria
					$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select * from categories_lang where name like '%".$cat."%'");
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
		$idcat=$row['idCategory'];
 	}
						if($i==0){
							$condition.= "skills like '%".$idcat."%'";
							}
							else{
							$condition.= " or skills like '%".$idcat."%'";
 							}
 							$i++;
							//save search register
							$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("INSERT INTO `searchs`(`query`, `date`) values('".$idcat."',NULL)");
							$sentencia->execute();
							}
							//$condition.=")";	

$condition.=" or ";$i=0;$listskills = explode(",", $skills);
				foreach($listskills as $cat){
					$listnames = explode(" ", $cat);
					foreach($listnames as $nombre){
					$cat = str_replace(' ','%',$nombre);
						if($i==0){
							$condition.= "firstName like '%".$nombre."%' or lastName like '%".$nombre."%'";
							}
							else{
							$condition.= " or firstName like '%".$nombre."%' or lastName like '%".$nombre."%'";
 							}
 							$i++;
							}
				}
							$condition.=")";	
							} 
			if ($price != ""){	
				$precios=explode(',',$priceRange);
		 $condition.=" and (priceHr BETWEEN ".$precios[0]." AND ".$precios[1].")";
			}
		 
	if ($availability != ""){		 
 $condition.=" and (";
$i=0;
$listdays = explode(",", $days);
foreach($listdays as $day) {
    $day = trim($day);
 if($i==0){
$condition.= "days like '%".$day."%'";
 }
 else
 {
  $condition.= " or days like '%".$day."%'";
 }
$i++;
}
$condition.=")";
	if ($hrFrom != ""){	
 $condition.=" and (hrFrom<=".$hrFrom.$minFrom." and hrTo>=".$hrTo.$minTo.")";
	}
	}
	

 
			if($condition!=" WHERE 1 "){
				$comando.=$condition;
				}
				$comando.=' order by RAND()';
				//echo $comando;
            //$sentencia = $pdo->prepare($comando);
			$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

			if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "finds" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else{
				throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
   					}
            
    }
	
	
	
    /**
     * Crea un nuevo usuario en la tabla "usuario"
     * @param mixed $datosUsuario columnas del registro
     * @return int codigo para determinar si la inserción fue exitosa
     */
    private function results($filters)
    
    {    
        $location = $filters->location;
        $skills = $filters->skills;
        
        $iam = $filters->iam;
        $reputation = $filters->reputation;
        $verifiedProfiles = $filters->verifiedProfiles;
        $price = $filters->price;
        $priceRange = $filters->priceRange;
        $availability = $filters->availability;
        $hrFrom = $filters->hrFrom;
		$hrTo = $filters->hrTo;
		$minFrom = $filters->minFrom;
		$minTo = $filters->minTo;
		$days = $filters->days;

        
	        //TODO: falta agregar lo campos verifiedProfiles y reputation a la busqueda
	        

           
            
             $comando = "SELECT doers.*,users.apiKey,users.firstName,users.lastName,users.country,availability.days,availability.hrFrom,availability.hrTo,availability.unavailable_date FROM doers INNER JOIN users ON users.userID=doers.userID inner join availability on availability.doerID = doers.doerID";
			 
			$condition = " WHERE users.state<>'Inactive' ";
			 
			if ($location != ""){
				$location=str_replace(",","",$location);
				$condition.=" and (";
				$i=0;$listlocation = explode(" ", $location);
				foreach($listlocation as $cat){
					$cat = trim($cat);
						if($i==0){
							$condition.= "city like '%".$cat."%' OR doers.state like '%".$cat."%' OR country like '%".$cat."%'";
							}
							else{
							$condition.= " or city like '%".$cat."%' OR doers.state like '%".$cat."%' OR country like '%".$cat."%'";
 							}
 							$i++;
				}
				$condition.=")";	
				//$condition.="and (city like '%".$location."%' OR doers.state like '%".$location."%' OR country like '%".$location."%')";
			}
			
			if ($iam != "50Doer"){
				$condition.="and (iam='".$iam."')";
			}
			
			if ($skills != ""){
				$condition.=" and (";$i=0;$listskills = explode(",", $skills);
				foreach($listskills as $cat){
					$cat = trim($cat);
						if($i==0){
							$condition.= "skills like '%".$cat."%'";
							}
							else{
							$condition.= " or skills like '%".$cat."%'";
 							}
 							$i++;
							//save search register
							$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("INSERT INTO `searchs`(`query`, `date`) values('".$cat."',NULL)");
							$sentencia->execute();
							}
							//$condition.=")";	

$condition.=" or ";$i=0;$listskills = explode(",", $skills);
				foreach($listskills as $cat){
					$listnames = explode(" ", $cat);
					foreach($listnames as $nombre){
					$cat = str_replace(' ','%',$nombre);
						if($i==0){
							$condition.= "firstName like '%".$nombre."%' or lastName like '%".$nombre."%'";
							}
							else{
							$condition.= " or firstName like '%".$nombre."%' or lastName like '%".$nombre."%'";
 							}
 							$i++;
							}
				}
							$condition.=")";	
							} 
			if ($price != ""){	
				$precios=explode(',',$priceRange);
		 $condition.=" and (priceHr BETWEEN ".$precios[0]." AND ".$precios[1].")";
			}
		 
	if ($availability != ""){		 
 $condition.=" and (";
$i=0;
$listdays = explode(",", $days);
foreach($listdays as $day) {
    $day = trim($day);
 if($i==0){
$condition.= "days like '%".$day."%'";
 }
 else
 {
  $condition.= " or days like '%".$day."%'";
 }
$i++;
}
$condition.=")";
	if ($hrFrom != ""){	
 $condition.=" and (hrFrom<=".$hrFrom.$minFrom." and hrTo>=".$hrTo.$minTo.")";
	}
	}
	

 
			if($condition!=" WHERE 1 "){
				$comando.=$condition;
				}
				$comando.=' order by RAND()';
				//echo $comando;
            //$sentencia = $pdo->prepare($comando);
			$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

			if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "finds" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else{
				throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
   					}
            
    }

}