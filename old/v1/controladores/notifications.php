<?php
class notifications
{
	 const NOMBRE_TABLA = "notifications";
    const NOTIFICATION_ID = "id";
    const DESTINATION = "destination";
    const SUBJECT = "subject";
    const MESSAGE = "message";
	const READED = "readed";
	    const CODIGO_EXITO = 1;
    const ESTADO_EXITO = 1;
    const ESTADO_ERROR = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_ERROR_PARAMETROS = 4;
    const ESTADO_NO_ENCONTRADO = 5;
	
	    public static function get($peticion)
    {
		 $clientID = users::autorizar();
		 if (empty($peticion[0])){
			  return self::getNotif($clientID);
		 }
		 else{
			 return self::getNotif($clientID, $peticion[0]);
		 }
		
	}
	
	   public static function post($peticion)
    {
        $userID = users::autorizar();

        $body = file_get_contents('php://input');
        $notif = json_decode($body);

        $notifID = self::crear($userID, $notif);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "The Notification has been created",
            "id" => $notifID
        ];

    }
	
	    public static function put($peticion)
    {
        

        if (!empty($peticion[0])) {
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
        }
    }
	
	 private function crear($userID, $notif)
    {
        if ($notif) {
            try {
	            
                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
                
                // Sentencia INSERT
                $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                    self::DESTINATION . "," .
                    self::SUBJECT . "," .
                    self::MESSAGE . ")".
                    " VALUES(?,?,?)";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

                $sentencia->bindParam(1, $destination);
                $sentencia->bindParam(2, $subject);
                $sentencia->bindParam(3, $message);
            

                $destination = $notif->destination;
                $subject = $notif->subject;
                $message = $notif->message;
          
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
	
	private function getNotif($clientID, $idNotif = NULL)
	{	
		//buscar trabajos que se adapten
		if(!$idNotif)
		{
			$comando = "SELECT notifications.* FROM `users` inner join notifications on users.apikey=notifications.destination where users.userID=".$clientID." order by readed,id desc";
			}
			else
			{
				$comando="SELECT notifications.* FROM `notifications` where id=".$idNotif;
			}

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
               
            

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        // "estado" => self::ESTADO_EXITO,
                        "notifications" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
	}
}
?>