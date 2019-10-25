<?php

class comments
{

    
    const CODIGO_EXITO = 1;
    const ESTADO_EXITO = 1;
    const ESTADO_ERROR = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_ERROR_PARAMETROS = 4;
    const ESTADO_NO_ENCONTRADO = 5;

    public static function get($peticion)
    {
            return self::getComments($peticion[0]);

    }

    public static function post($peticion)
    {
        $clientID = users::autorizar();

        $body = file_get_contents('php://input');
        $comment = json_decode($body);

        $commentID = self::create($clientID, $comment);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "The Comment has been created successfully",
            "id" => $commentID
        ];

    }


    public static function delete($peticion)
    {
        $clientID = users::autorizar();

        if (!empty($peticion[0])) {
            if (self::eliminar($clientID, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro eliminado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "El trabajo al que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }

    }

    /**
     * Obtiene la colección de recetas o un solo receta indicado por el identificador
     * @param int $idUsuario identificador del usuario
     * @param null $idReceta identificador del receta (Opcional)
     * @return array registros de la tabla receta
     * @throws Exception
     */
    private function getComments($doerID)
    {
        try {
                $comando = "SELECT * from comments where destination='".$doerID."' order by id desc";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                
            

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "status" => self::ESTADO_EXITO,
                        "comments" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    /**
     * Añade un nuevo Receta asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param mixed $Receta datos del Receta
     * @return string identificador del Receta
     * @throws ExcepcionApi
     */
    private function create($clientID, $comment)
    {
        if ($comment) {
            try {

                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

                // Sentencia INSERT
                $comando = "INSERT INTO comments VALUES(NULL,?,?,?,?,?,NULL)";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

                $sentencia->bindParam(1, $sender);
                $sentencia->bindParam(2, $destination);
                $sentencia->bindParam(3, $title);
                $sentencia->bindParam(4, $message);
                $sentencia->bindParam(5, $rating);


                $sender = $comment->sender;
                $destination = $comment->destination;
                $title = $comment->title;
                $message = $comment->message;
                $rating = $comment->rating;

                $sentencia->execute();

                // Retornar en el último id insertado
                return $pdo->lastInsertId();

            } catch (PDOException $e) {
                throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
            }
        } else {
            throw new ExcepcionApi(
                self::ESTADO_ERROR_PARAMETROS,
                utf8_encode("Error en existencia o sintaxis de parámetros"));
        }

    }

    /**
     * Actualiza el receta especificado por idUsuario
     * @param int $idUsuario
     * @param object $receta objeto con los valores nuevos del receta
     * @param int $idReceta
     * @return PDOStatement
     * @throws Exception
     */
    private function actualizar($clientID, $job, $jobID)
    {
        try {
	        
	        
            // Creando consulta UPDATE
            $consulta = "UPDATE " . self::NOMBRE_TABLA .
                " SET " . self::TITLE . "=?," .
                    self::DESCRIPTION . "=?," .
                    self::DATE . "=?," .
                    self::LOCATION . "=?," .
                    self::TYPE . "=?," .
                    self::EXPERIENCE . "=?," .
                    self::EDUCATION . "=?," .
                    self::REQUIRED_SKILLS . "=?," .
                    self::PRICE_HR_MAX . "=?," .
                    self::CURRENCY . "=?," .
                    self::REPUTATION . "=?," .
                    self::SHOW_NAME . "=?," .
                    self::URGENT_HIRING . "=?," .
                    self::HIGHLIGHTED_JOB . "=?," .
                    self::STATUS . "=?," .
                    self::DOER_ID . "=?" .
                " WHERE " . self::JOB_ID . "=? AND " . self::CLIENT_ID . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($consulta);
			
			$sentencia->bindParam(1, $title);
            $sentencia->bindParam(2, $description);
            $sentencia->bindParam(3, $date);
            $sentencia->bindParam(4, $location);
            $sentencia->bindParam(5, $type);
            $sentencia->bindParam(6, $experience);
            $sentencia->bindParam(7, $education);
            $sentencia->bindParam(8, $requiredSkills);
            $sentencia->bindParam(9, $priceHrMax);
			$sentencia->bindParam(10, $currency);
			$sentencia->bindParam(11, $reputation);
            $sentencia->bindParam(12, $showName);
            $sentencia->bindParam(13, $urgentHiring);
            $sentencia->bindParam(14, $highlightedJob);
            $sentencia->bindParam(15, $status);
            $sentencia->bindParam(16, $doerID);
            $sentencia->bindParam(17, $jobID);
            $sentencia->bindParam(18, $clientID);
                
           
				$title = $job->title;
                $description = $job->description;
                $date = $job->date;
                $location = $job->location;
                $type = $job->type;
                $experience = $job->experience;
                $education = $job->education;
                $requiredSkills = $job->requiredSkills;
                $priceHrMax = $job->priceHrMax;
                $currency = $job->currency;
                $reputation = $job->reputation;
                $showName = $job->showName;
                $urgentHiring = $job->urgentHiring;
                $highlightedJob = $job->highlightedJob;
                $status = $job->status;
                $doerID = $job->doerID;
                

            // Ejecutar la sentencia
            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }


    /**
     * Elimina un receta asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param int $idReceta identificador del receta
     * @return bool true si la eliminación se pudo realizar, en caso contrario false
     * @throws Exception excepcion por errores en la base de datos
     */
    private function eliminar($clientID, $jobID)
    {
        try {
            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA .
                " WHERE " . self::JOB_ID . "=? AND " .
                self::CLIENT_ID . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $jobID);
            $sentencia->bindParam(2, $clientID);

            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }
}
