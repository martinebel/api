<?php
class jobsforyou
{
	    public static function get($peticion)
    {
		 $clientID = users::autorizar();
		 if (empty($peticion[0])){
			  return self::getJobs($clientID);
		 }
		 else{
			 return self::getJobs($clientID, $peticion[0]);
		 }
		
	}
	
	private function getJobs($clientID, $idJob = NULL)
	{
		//obtener las skills del doerID
		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select skills from doers where userID=".$clientID);
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
			$skills=$row['skills'];
		}
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
			
			
		//buscar trabajos que se adapten
		if(!$idJob){$comando = "SELECT jobs.*,users.firstName,users.lastName FROM `jobs` inner join users on users.userID=jobs.clientID WHERE doerID is null ".$condition;}else{$comando="select * from jobs where jobID=".$idJob;}

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
               
            

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        // "estado" => self::ESTADO_EXITO,
                        "topJobs" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
	}
}
?>