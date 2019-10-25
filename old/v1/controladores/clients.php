<?php

class clients
{

    const NOMBRE_TABLA = "clients";
    const CLIENT_ID = "clientID";
    const IDNUMBER = "IDNumber";
    const IDTYPE = "IDType";
    const ADDRESS = "address";
    const ZIP = "zip";
    const CITY = "city";
    const STATE = "state";
    const PHONE = "phone";
    const MOBILE = "mobile";
    const WEBSITE = "website";
    const SKYPE = "skype";
    const BIRTHDAY = "birthday";
    const ABOUT = "about";
    const IMAGE = "image";
    const USER_ID = "userID";

    const CODIGO_EXITO = 1;
    const ESTADO_EXITO = 1;
    const ESTADO_ERROR = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_ERROR_PARAMETROS = 4;
    const ESTADO_NO_ENCONTRADO = 5;
    
       public static function get($peticion)
    {
        if (empty($peticion[0])){
            return self::getClients();
		}
        else
		{
			$userID = users::autorizarPut($peticion[0]);
            return self::getClients($userID);
		}

    }
    

    public static function post($peticion)
    {
        $userID = users::autorizar();

        $body = file_get_contents('php://input');
        $client = json_decode($body);

        $clientID = clients::create($userID, $client);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "The Client has been created",
            "id" => $clientID
        ];

    }
    
        public static function put($peticion)
    {
        $userID = users::autorizarPut($peticion[0]);
		//echo "ID:".$userID;

        if (!empty($peticion[0])) {
            $body = file_get_contents('php://input');
            $client = json_decode($body);

            if (self::actualizar($userID, $client) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro actualizado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "The Client doesn´t exist", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }
    }


    public static function delete($peticion)
    {
        $userID = users::autorizar();

        if (!empty($peticion[0])) {
            if (self::eliminar($userID, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro eliminado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "El cliente al que intenta acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }

    }

    /**
     * Obtiene la colección de doers o un solo pediatra indicado por el identificador
     * @param int $userID identificador del usuario
     * @param null $clientID identificador del pediatra (Opcional)
     * @return array registros de la tabla doers
     * @throws Exception
     */
    private function getClients($userID)
    {
        try {
            if (!$userID) {
                $comando = "SELECT * FROM " . self::NOMBRE_TABLA ;
                   
                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            
            } else {
                $comando = "SELECT * FROM " . self::NOMBRE_TABLA .
                    " WHERE " . self::USER_ID . "=? ";

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Relacionar idPediatra e idUsuario
                $sentencia->bindParam(1, $userID, PDO::PARAM_INT);
            }

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                       // "estado" => self::ESTADO_EXITO,
                        "clients" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "An error has been ocurred");

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    /**
     * Añade un nuevo pediatra asociado a un usuario
     * @param int $userID identificador del usuario
     * @param mixed $client datos del pediatra
     * @return string identificador del pediatra
     * @throws ExcepcionApi
     */
    private function create($userID, $client){
        
        if ($client) {
            try {
	            
                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();
                
                // Sentencia INSERT
                $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                    self::IDNUMBER . "," .
                    self::IDTYPE . "," .
                    self::ADDRESS . "," .
                    self::ZIP . "," .
                    self::CITY . "," .
                    self::STATE . "," .
                    self::PHONE . "," .
                    self::MOBILE . "," .
                    self::WEBSITE . "," .
                    self::SKYPE . "," .
                    self::BIRTHDAY . "," .
                    self::ABOUT . "," .
                    self::IMAGE . "," .
                    self::USER_ID . ")" .
                    " VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

                $sentencia->bindParam(1, $IDNumber);
                $sentencia->bindParam(2, $IDType);
                $sentencia->bindParam(3, $address);
                $sentencia->bindParam(4, $zip);
                $sentencia->bindParam(5, $city);
                $sentencia->bindParam(6, $state);
                $sentencia->bindParam(7, $phone);
                $sentencia->bindParam(8, $mobile);
                $sentencia->bindParam(9, $website);
                $sentencia->bindParam(10, $skype);
                $sentencia->bindParam(11, $birthday);
                $sentencia->bindParam(12, $about);
                $sentencia->bindParam(13, $image);
                $sentencia->bindParam(14, $userID);
            

                $IDNumber = $client->IDNumber;
                $IDType = $client->IDType;
                $address = $client->address;
                $zip = $client->zip;
                $city = $client->city; 
                $state = $client->state;
                $phone = $client->phone;
                $mobile = $client->mobile;
                $website = $client->website;
                $skype = $client->skype;
                $birthday = $client->birthday;
                $about = $client->about;
                $image = $client->image;
          

                $sentencia->execute();

                // Retornar en el último id insertado
                
				$final = $pdo->lastInsertId();
                $comando = "UPDATE users SET state ='Active' WHERE ".self::USER_ID."=".$userID;
                
                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);
                
                $sentencia->execute();
                
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

    /**
     * Actualiza el pediatra especificado por idUsuario
     * @param int $userID
     * @param object $client objeto con los valores nuevos del pediatra
     * @param int $clientID
     * @return PDOStatement
     * @throws Exception
     */
    private function actualizar($userID, $client)
    {
		
        try {
            // Creando consulta UPDATE
            
                   $consulta = "UPDATE " . self::NOMBRE_TABLA .
                " SET " . self::IDNUMBER . "=?," .
                    self::IDTYPE . "=?," .
                    self::ADDRESS . "=?," .
                    self::ZIP . "=?," .
                    self::CITY . "=?," .
                    self::STATE . "=?," .
                    self::PHONE . "=?," .
                    self::MOBILE . "=?," .
                    self::WEBSITE . "=?," .
                    self::SKYPE . "=?," .
                    self::BIRTHDAY . "=?," .
                    self::ABOUT . "=?," .
                    self::IMAGE . "=?" .
                     " WHERE " . self::USER_ID . "=?";
                     
            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($consulta);
            
                $sentencia->bindParam(1, $IDNumber);
                $sentencia->bindParam(2, $IDType);
                $sentencia->bindParam(3, $address);
                $sentencia->bindParam(4, $zip);
                $sentencia->bindParam(5, $city);
                $sentencia->bindParam(6, $state);
                $sentencia->bindParam(7, $phone);
                $sentencia->bindParam(8, $mobile);
                $sentencia->bindParam(9, $website);
                $sentencia->bindParam(10, $skype);
                $sentencia->bindParam(11, $birthday);
                $sentencia->bindParam(12, $about);
                $sentencia->bindParam(13, $image);
                $sentencia->bindParam(14, $userID);
                

				$IDNumber = $client->IDNumber;
                $IDType = $client->IDType;
                $address = $client->address;
                $zip = $client->zip;
                $city = $client->city; 
                $state = $client->state;
                $phone = $client->phone;
                $mobile = $client->mobile;
                $website = $client->website;
                $skype = $client->skype;
                $birthday = $client->birthday;
                $about = $client->about;
                $image = $client->image;

            // Ejecutar la sentencia
            $sentencia->execute();

            return $sentencia->rowCount();
			
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }


    /**
     * Elimina un pediatra asociado a un usuario
     * @param int $userID identificador del usuario
     * @param int $clientID identificador del pediatra
     * @return bool true si la eliminación se pudo realizar, en caso contrario false
     * @throws Exception excepcion por errores en la base de datos
     */
    private function eliminar($userID, $clientID)
    {
        try {
            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA .
                " WHERE " . self::CLIENT_ID . "=? AND " .
                self::USER_ID . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $clientID);
            $sentencia->bindParam(2, $userID);

            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }
}

