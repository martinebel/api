<?php
class cms
{
	    public static function get($peticion)
    {
		 if (empty($peticion[1])){
			  return self::getCms( $peticion[0]);
		 }
		 else{
			 return self::getCms( $peticion[0], $peticion[1]);
		 }
		
	}
	
	private function getCms($lang, $idCms = NULL)
	{
		if(!$idCms){$comando = "SELECT * from cms_detail where lang=".$lang;}else{$comando="select * from cms_detail where lang=".$lang." and idCms=".$idCms;}

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
               
            

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        // "estado" => self::ESTADO_EXITO,
                        "cms" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
	}
}
?>