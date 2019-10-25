<?php
class topJobs
{
	    public static function get($peticion)
    {
		 if (empty($peticion[0])){
			  return self::getJobs();
		 }
		 else{
			 return self::getJobs( $peticion[0]);
		 }
		
	}
	
		 public static function put($peticion)
    {
	
		 if (!empty($peticion[0])){
			 return self::becomePremium( $peticion[0]);
		 }
		
	}
	
	
	private function becomePremium($jobID)
	{
		$comando="update jobs set highlightedJob='Yes' where jobID='".$jobID."'";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
               
            

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "estado" => "Exito"
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
	}
	
	private function getJobs( $idJob = NULL)
	{
		if(!$idJob){$comando = "SELECT jobs.*,users.firstName,users.lastName FROM `jobs` inner join users on users.userID=jobs.clientID WHERE doerID is null and highlightedJob='Yes'";}else{$comando="select * from jobs where jobID=".$idJob;}

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