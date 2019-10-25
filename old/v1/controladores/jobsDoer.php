<?php

class jobsDoer
{

    const NOMBRE_TABLA = "jobs";
    const JOB_ID = "jobID";
    const TITLE = "title";
    const DESCRIPTION = "description";
    const DATE = "date";
    const LOCATION = "location";
    const TYPE = "type";
    const EXPERIENCE = "experience";
    const EDUCATION = "education";
    const REQUIRED_SKILLS = "requiredSkills";
    const PRICE_HR_MAX = "priceHrMax";
    const CURRENCY = "currency";
    const REPUTATION = "reputation";
    const SHOW_NAME = "showName";
    const URGENT_HIRING = "urgentHiring";
    const HIGHLIGHTED_JOB = "highlightedJob";
    const STATUS = "status";
    const CLIENT_ID = "clientID";
    const DOER_ID = "doerID";
    
    const CODIGO_EXITO = 1;
    const ESTADO_EXITO = 1;
    const ESTADO_ERROR = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_ERROR_PARAMETROS = 4;
    const ESTADO_NO_ENCONTRADO = 5;

    public static function get($peticion)
    {
        $clientID = users::autorizar();

        if (empty($peticion[0]))
            return self::getJobs($clientID);
        else
            return self::getJobs($clientID, $peticion[0]);

    }

    public static function post($peticion)
    {
        $clientID = users::autorizar();

        $body = file_get_contents('php://input');
        $job = json_decode($body);

        $jobID = jobs::create($clientID, $job);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "The Job has been created successfully",
            "id" => $jobID
        ];

    }

    public static function put($peticion)
    {
        $clientID = users::autorizar();

        if (!empty($peticion[0])) {
            $body = file_get_contents('php://input');
            $job = json_decode($body);

            if (self::actualizar($clientID, $job, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "The Job has been updated successfully"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "The Job doesn´t exist", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }
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
    private function getJobs($clientID, $jobID = NULL)
    {
        try {
            if (!$jobID) {
				$myapi="";
				//get my own apikey
				$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select * from users where userID=".$clientID);
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
		$myapi=$row['apiKey'];
 	}
	
                $comando = "SELECT distinct jobs.*,users.firstName,users.lastName FROM applyJobs inner join jobs on jobs.jobID=applyJobs.jobID inner join users on users.userID=jobs.clientID WHERE applyJobs.doerID='".$myapi."'";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idUsuario
               

            } else {
                $comando = "SELECT jobs.*,users.firstName,users.lastName FROM " . self::NOMBRE_TABLA .
                    " inner join users on users.userID=jobs.clientID WHERE " . self::JOB_ID . "=? AND " .
                    self::DOER_ID . "=?";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idReceta e idUsuario
                $sentencia->bindParam(1, $jobID, PDO::PARAM_INT);
                $sentencia->bindParam(2, $clientID, PDO::PARAM_INT);
            }

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "status" => self::ESTADO_EXITO,
                        "jobs" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
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
    private function create($clientID, $job)
    {
        if ($job) {
            try {

                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

                // Sentencia INSERT
                $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                    self::TITLE . "," .
                    self::DESCRIPTION . "," .
                    self::DATE . "," .
                    self::LOCATION . "," .
                    self::TYPE . "," .
                    self::EXPERIENCE . "," .
                    self::EDUCATION . "," .
                    self::REQUIRED_SKILLS . "," .
                    self::PRICE_HR_MAX . "," .
                    self::CURRENCY . "," .
                    self::REPUTATION . "," .
                    self::SHOW_NAME . "," .
                    self::URGENT_HIRING . "," .
                    self::HIGHLIGHTED_JOB . "," .
                    self::DOER_ID . "," .
                    self::CLIENT_ID . ")" .
                    " VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

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
                $sentencia->bindParam(15, $doerID);
                $sentencia->bindParam(16, $clientID);


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
                $doerID = $job->doerID;

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
