<?php

class suggestedCategories
{
	
    const NOMBRE_TABLA = "suggestedCategories";
    const ID = "id";
    const NAME = "name";
	const STATUS = "status";
	const APIKEY = "apikey";
	
    
    const CODIGO_EXITO = 1;
    const ESTADO_EXITO = 1;
    const ESTADO_ERROR = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_ERROR_PARAMETROS = 4;
    const ESTADO_NO_ENCONTRADO = 5;



    public static function get($peticion)
    {
            return self::getsuggestedCategories();
    }

    public static function post($peticion)
    {
        $id = users::autorizar();

        $body = file_get_contents('php://input');
        $suggestedCategory = json_decode($body);

        $id = suggestedCategories::create($id, $suggestedCategory);

        http_response_code(201);
        return [
            "estado" => self::CODIGO_EXITO,
            "mensaje" => "category creada",
            "id" => $id
        ];

    }

    public static function put($peticion)
    {
        $id = users::autorizar();

        if (!empty($peticion[0])) {
            $body = file_get_contents('php://input');
            $suggestedCategory = json_decode($body);

            if (self::actualizar($id, $suggestedCategory, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro actualizado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "La categoria a la que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }
    }

    public static function delete($peticion)
    {
        $id = users::autorizar();

        if (!empty($peticion[0])) {
            if (self::eliminar($id, $peticion[0]) > 0) {
                http_response_code(200);
                return [
                    "estado" => self::CODIGO_EXITO,
                    "mensaje" => "Registro eliminado correctamente"
                ];
            } else {
                throw new ExcepcionApi(self::ESTADO_NO_ENCONTRADO,
                    "La consulta a la que intentas acceder no existe", 404);
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_ERROR_PARAMETROS, "Falta id", 422);
        }

    }

    /**
     * Obtiene la colección de conulstas o un solo consulta indicado por el identificador
     * @param int $idUsuario identificador del usuario
     * @param null $idConsulta identificador del consulta (Opcional)
     * @return array registros de la tabla consultas
     * @throws Exception
     */
    private function getsuggestedCategories()
    {
        try {
           
                $comando = "SELECT * FROM " . self::NOMBRE_TABLA;

                // Preparar sentencia
                $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        // "estado" => self::ESTADO_EXITO,
                        "suggestedCategories" => $sentencia->fetchAll(PDO::FETCH_ASSOC)
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    /**
     * Añade una nueva consulta asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param mixed $Consulta datos de la consulta
     * @return string identificador de la consulta
     * @throws ExcepcionApi
     */
    private function create($id, $suggestedCategory)
    {
        if ($suggestedCategory) {
            try {

                $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

                // Sentencia INSERT
                $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                    self::NAME . "," .
					self::APIKEY . "," .
                    self::STATUS . ")" .
                    " VALUES(?,?,?)";

                // Preparar la sentencia
                $sentencia = $pdo->prepare($comando);

                $sentencia->bindParam(1, $name);
                $sentencia->bindParam(2, $apikey);
				$sentencia->bindParam(3, $status);


                $name = $suggestedCategory->name;
                $status = $suggestedCategory->status;
				$apikey = $suggestedCategory->apikey;

                
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
     * Actualiza el consulta especificado por idUsuario
     * @param int $idUsuario
     * @param object $consulta objeto con los valores nuevos de la consulta
     * @param int $idConsulta
     * @return PDOStatement
     * @throws Exception
     */
    private function actualizar($id,$suggestedCategory)
    {		
        try {
            // Creando consulta UPDATE
            $consulta = "UPDATE " . self::NOMBRE_TABLA .
                " SET " . self::NAME . "=?," .
                self::STATUS . "=? " .
                " WHERE " . self::ID . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($consulta);

                $sentencia->bindParam(1, $name);
                $sentencia->bindParam(2, $status);
                $sentencia->bindParam(3, $id);
                

                $name = $suggestedCategory->name;
                $status = $suggestedCategory->status;
                
             
            // Ejecutar la sentencia
            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }


    /**
     * Elimina un consulta asociado a un usuario
     * @param int $idUsuario identificador del usuario
     * @param int $idConsulta identificador del consulta
     * @return bool true si la eliminación se pudo realizar, en caso contrario false
     * @throws Exception excepcion por errores en la base de datos
     */
    private function eliminar($idPediatra, $idPrestadora)
    {
        try {
            // Sentencia DELETE
            $comando = "DELETE FROM " . self::NOMBRE_TABLA .
                " WHERE " . self::ID . "=?";

            // Preparar la sentencia
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $id);

            $sentencia->execute();

            return $sentencia->rowCount();

        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }
}

