<?php

require_once('datos/ConexionBD.php');

class jobsforyou_search
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
			 return self::ortografia( $peticion[0]);
		 }
		
	}
	
	private function ortografia($worda)
	{
		$bien=array();$mal=array();
		$words=array();
		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select name from categories");
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
		array_push($words, strtolower($row['name']));
 	}
 		//print_r ($words);exit();
 
		// input misspelled word
		$listskills = explode(",", $worda);
			foreach($listskills as $cat){
		$input = strtolower($cat);

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
}
	}
	 http_response_code(200);
    return
                    [
                        "bien" => $bien,
						"mal"=>$mal
                    ];
	}
	
	
	    public static function post(){

        $body = file_get_contents('php://input');
        $filters = json_decode($body);
		
		return  self::results($filters);
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
        $education = $filters->education;
        $experience = $filters->experience;
		
 $condition=" and (";
		$i=0;
		$listskills = explode(",", $skills);
				foreach($listskills as $cat){
					$cat = trim($cat);
						if($i==0){
							$condition.= "requiredSkills like '%".$cat."%'";
							}
							else{
							$condition.= " or requiredSkills like '%".$cat."%'";
 							}
 							$i++;
							}
			$condition.=")";
			
			if ($location != ""){
				$condition.=" and (location like '%".$location."%')";
			}
			
			if ($education != "Any"){
				$condition.=" and (education like '%".$education."%')";
			}
			
			if ($experience != "Any"){
				$condition.=" and (experience like '%".$experience."%')";
			}
			
			
		//buscar trabajos que se adapten
		$comando = "SELECT jobs.*,users.firstName,users.lastName FROM `jobs` inner join users on users.userID=jobs.clientID WHERE 1 ".$condition;
//echo $comando;
                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
               
            

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        // "estado" => self::ESTADO_EXITO,
                        "searchJobs" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
            
    }

}