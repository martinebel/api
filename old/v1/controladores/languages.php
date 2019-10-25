<?php

class languages
{
    public static function get($peticion)
    {

        if ($peticion[0] == 'getLanguages') {
            return self::getLanguages();
	} else if ($peticion[0] == 'getTraduction') {
            return self::getTraduction($peticion[1],$peticion[2]); //idLang,Elemento
        } 
        else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }
	
	
	private function getLanguages()
	{
		   $comando = "SELECT * FROM langs where status=1";
			 $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
 if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "languages" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            }
	}
	
		private function getTraduction($lang,$elemento)
	{
		   $comando = "SELECT name,value FROM traductions where lang=".$lang." and page='".$elemento."'";
			 $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
 if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "traduction" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            }
	}

}
