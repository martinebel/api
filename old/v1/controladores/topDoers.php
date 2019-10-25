<?php
class topDoers
{
	    public static function get($peticion)
    {
		//echo $peticion[1];
		 if (!empty($peticion[1])){
			  return self::getDoers(NULL,$peticion[1]);
		 }
		 else{
			 return self::getDoers( $peticion[0],NULL);
		 }
		
	}
	
	 public static function put($peticion)
    {
	
		 if (!empty($peticion[0])){
			 return self::becomePremium( $peticion[0]);
		 }
		
	}
	
	
	private function becomePremium($doerID)
	{
		$comando="update users set accountType='Premium' where apiKey='".$doerID."'";

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
	
	private function getDoers( $idDoer = NULL,$location=NULL)
	{
		if(!$idDoer)
		{
			$location2=str_replace(",","",$location);
				$condition.=" and (";
				$i=0;$listlocation = explode(" ", $location2);
				foreach($listlocation as $cat){
					$cat = trim($cat);
						if($i==0){
							$condition.= "doers.city like '%".$cat."%' OR doers.state like '%".$cat."%' ";
							}
							else{
							$condition.= " or doers.city like '%".$cat."%' OR doers.state like '%".$cat."%' ";
 							}
 							$i++;
				}
				$condition.=")";
			$comando = "SELECT doers.*,users.firstName,users.lastName,users.apikey FROM `users` inner join doers on users.userID=doers.userID WHERE users.userType<>'Client' and users.accountType='Premium' and users.state<>'Inactive' ".$condition;
			}
			else
			{
				$comando="SELECT doers.*,users.firstName,users.lastName,users.apikey FROM `users` inner join doers on users.userID=doers.userID doers.doerID=".$idDoer;
			}

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
               
           //echo $comando;

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        // "estado" => self::ESTADO_EXITO,
                        "topDoers" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
	}
}
?>