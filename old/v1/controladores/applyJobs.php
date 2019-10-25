<?php
class applyJobs
{
	 const NOMBRE_TABLA = "applyJobs";
    const JOBID = "jobID";
    const DOERID = "doerID";
	    const CODIGO_EXITO = 1;
    const ESTADO_EXITO = 1;
    const ESTADO_ERROR = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_ERROR_PARAMETROS = 4;
    const ESTADO_NO_ENCONTRADO = 5;
	
	public static function get($peticion)
    {
			 return self::getApply($peticion[0]);
	}
	
	   public static function post($peticion)
    {
        

        $body = file_get_contents('php://input');
        $job = json_decode($body);

        $notifID = self::crear($job);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "The Apply has been created",
            "id" => $notifID
        ];

    }
	
	    public static function put($peticion)
    {
        

       /* if (!empty($peticion[0])) {
            $body = file_get_contents('php://input');
            $notif = json_decode($body);

            if (self::actualizar($peticion[0], $notif) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro actualizado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "The Doer doesn´t exist", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }*/
    }
	
	 private function crear($job)
    {
        if ($job) {
            try {
	            
                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
                
                // Sentencia INSERT
                $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                    self::JOBID . "," .
                    self::DOERID . ")".
                    " VALUES(?,?)";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

                $sentencia->bindParam(1, $jobID);
                $sentencia->bindParam(2, $doerID);
            

                $jobID = $job->jobID;
                $doerID = $job->doerID;
          
                $sentencia->execute();
                

                // Retornar en el último id insertado
                
				$final = $pdo->lastInsertId();
                
                return $final;

            } catch (PDOException $e) {
                throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
            }
        } else {
            throw new ExcepcionApi(
                self::ESTADO_ERROR_PARAMETROS,
                utf8_encode("Error en existencia o sintaxis de parámetros"));
        }

    }
	
	 private function actualizar($notifID, $notif)
    {
        if ($notif) {
            try {
	            
                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
                
                // Sentencia INSERT
                $comando = "UPDATE " . self::NOMBRE_TABLA . " SET " .
                     self::READED . " =? WHERE ".self::NOTIFICATION_ID."=?";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

                $sentencia->bindParam(1, $readed);
                $sentencia->bindParam(2, $notifID);
            

                $readed = $notif->readed;
          
                $sentencia->execute();
                

                // Retornar en el último id insertado
                
				
                
                return 1;

            } catch (PDOException $e) {
                throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
            }
        } else {
            throw new ExcepcionApi(
                self::ESTADO_ERROR_PARAMETROS,
                utf8_encode("Error en existencia o sintaxis de parámetros"));
        }

    }
	
	private function getApply($idApply)
	{	
	
				$comando="SELECT users.firstName,users.lastName,users.apikey,doers.* FROM `applyJobs` inner join users on users.apikey=applyJobs.doerID inner join doers on doers.userID=users.userID where applyJobs.jobID=".$idApply;
			

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
               
            

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        // "estado" => self::ESTADO_EXITO,
                        "doers" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
	}
}
?>