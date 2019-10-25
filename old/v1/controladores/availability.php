<?php

class availability
{
	
    const NOMBRE_TABLA = "availability";
    const DOER_ID = "doerID";
    const DAYS = "days";
    const HR_FROM = "hrFrom";
    const HR_TO = "hrTo";
    const DISTANCE = "distance";
    const UNAVAILABLE = "unavailable_date";
    const WHOLE_WEEK = "wholeWeek";
    const THIS_WEEK = "thisWeek";
    const EMERGENCY = "emergency";
    
    const CODIGO_EXITO = 1;
    const ESTADO_EXITO = 1;
    const ESTADO_ERROR = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_ERROR_PARAMETROS = 4;
    const ESTADO_NO_ENCONTRADO = 5;
	
	private function getDoerID($usernumber){
		
        $comando = "SELECT doerID FROM doers WHERE userID=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $usernumber);

        if ($sentencia->execute()) {
            $resultado = $sentencia->fetch();
            return $resultado['doerID'];
            
        } else
            return null;
    }

	
    public static function get($peticion)
    {
	    
	    $usernumber = users::autorizar();
		$doerID=self::getDoerID($usernumber);
	
        if (empty($peticion[0]))
            return self::getAvailability($doerID);
        else
            return self::getAvailability($doerID, $peticion[0]);

    }

    public static function post($peticion)
    {
        $usernumber = users::autorizar();
		$doerID=self::getDoerID($usernumber);

        $body = file_get_contents('php://input');
        $availability = json_decode($body);

        $doerID = availability::create($doerID, $availability);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "Availability succesfully saved"
        ];

    }

    public static function put($peticion)
    {
        $usernumber = users::autorizarPut($peticion[0]);
		$doerID=self::getDoerID($usernumber);


        if (!empty($peticion[0])) {
            $body = file_get_contents('php://input');
            $availability = json_decode($body);

            if (self::update($doerID, $availability, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Availability has been updated"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "The availability doesn´t exist", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "id doesn´t exist", 422);
        }
    }

    public static function delete($peticion)
    {
        $usernumber = users::autorizar();
		$doerID=self::getDoerID($usernumber);


        if (!empty($peticion[0])) {
            if (self::eliminar($doerID, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "The availability has been deleted"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "Availability not found", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "id doesn´t exist", 422);
        }

    }

    /**
     * Obtiene la colección de lugares de trabajo o un solo lugar de trabjao indicado por el identificador
     * @param int $idUsuario identificador del usuario
     * @param null $idLugar identificador del lugar de trabajo (Opcional)
     * @return array registros de la tabla lugarestrabajo
     * @throws Exception
     */
    private function getAvailability($doerID)
    {
        try {
                $comando = "SELECT * FROM " . self::NOMBRE_TABLA .
                    " WHERE " . self::DOER_ID . "=?";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Ligar idUsuario
                $sentencia->bindParam(1, $doerID, PDO::PARAM_INT);

				// Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "estado" => self::ESTADO_EXITO,
                        "data" => $sentencia->fetchAll(PDO::FETCH_ASSOC),
                        "userID" => $doerID
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    /**
     * Añade un nuevo lugar de trabajo asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param mixed $Lugar datos del lugar de trabajo
     * @return string identificador del lugar de trabajo
     * @throws ExcepcionApi
     */
    
    private function create($doerID, $availability){
	    
            try {

                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

                // Sentencia INSERT
                $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                    self::DAYS . "," .
                    self::HR_FROM . "," .
                    self::HR_TO . "," .
                    self::DISTANCE . "," .
                    self::WHOLE_WEEK . "," .
                    self::UNAVAILABLE . "," .
                    self::THIS_WEEK . "," .
					self::EMERGENCY . "," .
                    self::DOER_ID . ")" .
                    " VALUES(?,?,?,?,?,?,?,?,?)";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

                $sentencia->bindParam(1, $days);
                $sentencia->bindParam(2, $hrFrom);
                $sentencia->bindParam(3, $hrTo);
                $sentencia->bindParam(4, $distance);
                $sentencia->bindParam(5, $wholeWeek);
                $sentencia->bindParam(6, $unavailable_date);
                $sentencia->bindParam(7, $thisWeek);
                $sentencia->bindParam(8, $emergency);
                $sentencia->bindParam(9, $doerID);



                $days = $availability->days;
                $hrFrom = $availability->hrFrom.$availability->minFrom;
                $hrTo = $availability->hrTo.$availability->minTo;
                $distance = $availability->distance;
                $wholeWeek = $availability->wholeWeek;
                $thisWeek = $availability->thisWeek;
                $unavailable_date = $availability->unavailable_date;
                $emergency = $availability->emergency;
            
                $sentencia->execute();

                // Retornar en el último id insertado
                return $pdo->lastInsertId();

            } catch (PDOException $e) {
                throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
            }
        
    }

    /**
     * Actualiza el lugar de trabajo especificado por idUsuario
     * @param int $idUsuario
     * @param object $lugar objeto con los valores nuevos del lugar de trabajo
     * @param int $idLugar
     * @return PDOStatement
     * @throws Exception
     */
    private function update($doerID, $availability)
    {		
        try {
            // Creando consulta UPDATE
            
            $consulta = "UPDATE " . self::NOMBRE_TABLA .
                " SET " . self::DAYS . "=?," .
                self::HR_FROM . "=?," .
				self::HR_TO . "=?," .
				self::DISTANCE . "=?," .
				self::WHOLE_WEEK . "=?," .
				self::THIS_WEEK . "=?," .
				self::UNAVAILABLE . "=?," .
				self::EMERGENCY . "=? " .
                " WHERE " . self::DOER_ID . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($consulta);
            
            $sentencia->bindParam(1, $days);
            $sentencia->bindParam(2, $hrFrom);
            $sentencia->bindParam(3, $hrTo);
            $sentencia->bindParam(4, $distance);
            $sentencia->bindParam(5, $wholeWeek);
            $sentencia->bindParam(6, $thisWeek);
            $sentencia->bindParam(7, $unavailable_date);
            $sentencia->bindParam(8, $emergency);
            $sentencia->bindParam(9, $doerID);
            
            $days = $availability->days;
            $hrFrom = $availability->hrFrom.$availability->minFrom;
                $hrTo = $availability->hrTo.$availability->minTo;
            $distance = $availability->distance;
            $wholeWeek = $availability->wholeWeek;
            $thisWeek = $availability->thisWeek;
            $unavailable_date = $availability->unavailable_date;
            $emergency = $availability->emergency;

            // Ejecutar la sentencia
            $sentencia->execute();

            return 1;

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }


    /**
     * Elimina un lugar de trabajo asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param int $idLugar identificador del lugar de trabajo
     * @return bool true si la eliminación se pudo realizar, en caso contrario false
     * @throws Exception excepcion por errores en la base de datos
     */
    private function eliminar($doerID)
    {
        try {
            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA .
                " WHERE " . self::DOER_ID . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $doerID);

            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }
}

