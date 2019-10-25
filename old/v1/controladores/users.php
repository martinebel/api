<?php

require_once('datos/ConexionBD.php');

class users
{
    // Datos de la tabla "usuarios"
    const NOMBRE_TABLA = "users";
    const USERID = "userID";
    const FIRSTNAME = "firstName";
    const LASTNAME = "lastName";
    const COUNTRY = "country";
    const EMAIL = "email";
    const PASSWORD = "password";
    const APIKEY = "apiKey";
	const ACCOUNT_TYPE = "accountType";
    const STATE = "state";
    const USERTYPE = "userType";
    const VERIFIED_PROFILE = "verifiedProfile";
    
    const ESTADO_CREACION_EXITOSA = 1;
    const ESTADO_CREACION_FALLIDA = 2;
    const ESTADO_ERROR_BD = 3;
    const ESTADO_AUSENCIA_CLAVE_API = 4;
    const ESTADO_CLAVE_NO_AUTORIZADA = 5;
    const ESTADO_URL_INCORRECTA = 6;
    const ESTADO_FALLA_DESCONOCIDA = 7;
    const ESTADO_PARAMETROS_INCORRECTOS = 8;
	
	static $codigo = '';
	
   public static function get($peticion)
    {
        $userID = self::autorizar();

        if (empty($peticion[0]))
		{
		return self::getUsers();
		}
        else
		{
            return self::getUsers($peticion[0]);
		}

    }
	
	 private function getUsers($clientID = NULL)
    {
         if ($clientID) {
                $comando = "SELECT * FROM " . self::NOMBRE_TABLA ." WHERE userID=?";
                    $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
                // Relacionar idPediatra e idUsuario
                $sentencia->bindParam(1, $clientID, PDO::PARAM_INT);
                // Preparar sentencia
               
            }
			else{
				  $comando = "SELECT * FROM " . self::NOMBRE_TABLA ;
                    $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
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
    }
	
    public static function post($peticion)
    {

        if ($peticion[0] == 'signup') {
            return self::registrar();
        } else if ($peticion[0] == 'signin') {
            return self::loguear();
        } else if ($peticion[0] == 'exists') {
            return self::loguearyaExiste();
        }
		else if ($peticion[0] == 'signinFB') {
            return self::loguearFB();
        }
		else if ($peticion[0] == 'signupFB') {
            return self::registrarFB();
        }
        else {
            throw new ExcepcionApi(self::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }

	
	

    /**
     * Crea un nuevo usuario en la base de datos
     */
    private function registrar()
    {
       
        $cuerpo = file_get_contents('php://input');
        $usuario = json_decode($cuerpo);

        $resultado = self::crear($usuario);
		
        switch ($resultado) {
           
            case self::ESTADO_CREACION_FALLIDA:
                throw new ExcepcionApi(self::ESTADO_CREACION_FALLIDA, "Ha ocurrido un error");
                break;
            default:
                  http_response_code(200);
                return $resultado;
        }
    }

	    /**
     * Crea un nuevo usuario en la base de datos
     */
    private function registrarFB()
    {
       
        $cuerpo = file_get_contents('php://input');
        $usuario = json_decode($cuerpo);

        $resultado = self::crearFB($usuario);
		
        switch ($resultado) {
           
            case self::ESTADO_CREACION_FALLIDA:
                throw new ExcepcionApi(self::ESTADO_CREACION_FALLIDA, "Ha ocurrido un error");
                break;
            default:
                  http_response_code(200);
                return $resultado;
        }
    }

        private function loguearyaExiste()
    {
         $respuesta = array();
         $cuerpo = file_get_contents('php://input');
		 $usuario = json_decode($cuerpo);
		 $email=$usuario->email;
 

        try {
            $usuarioBD = self::obtenerUsuarioPorEmail($email);

            if ($usuarioBD != NULL) {
                http_response_code(200);
                $respuesta["email"] = $usuarioBD["email"];
                $respuesta["claveApi"] = $usuarioBD["claveApi"];
                return ["usuario" => $respuesta];
            } else {
                throw new ExcepcionApi(self::ESTADO_FALLA_DESCONOCIDA,
                    "Ha ocurrido un error");
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    /**
     * Crea un nuevo usuario en la tabla "usuario"
     * @param mixed $datosUsuario columnas del registro
     * @return int codigo para determinar si la inserción fue exitosa
     */
    private function crear($datosUsuario)
    
    {    
        $firstName = $datosUsuario->firstName;
        $lastName = $datosUsuario->lastName;
        $country = $datosUsuario->country;
        $email = $datosUsuario->email;
        $password = md5($datosUsuario->password);
        $apiKey = self::generarClaveApi();
		$verification = self::generarVerification();
		if(isset($datosUsuario->dual))
		{
			  $userType = "Dual";
		}
		else
		{
			  $userType = $datosUsuario->userType;
		}
      
        $accountType = $datosUsuario->accountType;
		$verifiedProfile = $datosUsuario->verifiedProfile;
		$registerDate=date('Y-m-d h:i:s');
		

        

//	 mail('fvernazza@wonerz.com', 'Password', self::$email, $password);

        try {

            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia INSERT
            $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                self::FIRSTNAME . "," .
                self::LASTNAME . "," .
                self::COUNTRY . "," .
                self::EMAIL . "," .
                self::PASSWORD . "," .
                self::APIKEY . ", " .
                self::USERTYPE .
				", verification, registerDate)" .
                " VALUES(?,?,?,?,?,?,?,?,?)";

            $sentencia = $pdo->prepare($comando);

            $sentencia->bindParam(1, $firstName);
            $sentencia->bindParam(2, $lastName);
            $sentencia->bindParam(3, $country);
            $sentencia->bindParam(4, $email);
            $sentencia->bindParam(5, $password);
            $sentencia->bindParam(6, $apiKey);
            $sentencia->bindParam(7, $userType);
			$sentencia->bindParam(8, $verification);
			$sentencia->bindParam(9, $registerDate);

            $resultado = $sentencia->execute();
	

            if ($resultado) {
				$respuesta["apiKey"] = $apiKey;
                $respuesta["lastName"] = $lastName;
                $respuesta["firstName"] = $firstName;
                $respuesta["userType"] = $userType;
				$respuesta["email"] = $email;
                return ["user" => $respuesta];
            } else {
                return self::ESTADO_CREACION_FALLIDA;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }

    }

	 private function crearFB($datosUsuario)
    
    {    

        $firstName = $datosUsuario->firstName;
        $lastName = $datosUsuario->lastName;
		$country='AR';
        $email = $datosUsuario->email;
		$password=self::generarClaveApi();
        $apiKey = self::generarClaveApi();
        $userType = $datosUsuario->userType;
        $accountType = $datosUsuario->accountType;
		$verifiedProfile = $datosUsuario->verifiedProfile;
		$verification = self::generarVerification();
		
        try {

            $pdo = ConexionBD::obtenerInstancia()->obtenerBD();

            // Sentencia INSERT
            $comando = "INSERT INTO " . self::NOMBRE_TABLA . " ( " .
                self::FIRSTNAME . "," .
                self::LASTNAME . "," .
                self::COUNTRY . "," .
                self::EMAIL . "," .
                self::PASSWORD . "," .
                self::APIKEY . "," .
                 self::USERTYPE .
				", verification)" .
                " VALUES(?,?,?,?,?,?,?,?)";

            $sentencia = $pdo->prepare($comando);

            $sentencia->bindParam(1, $firstName);
            $sentencia->bindParam(2, $lastName);
            $sentencia->bindParam(3, $country);
            $sentencia->bindParam(4, $email);
            $sentencia->bindParam(5, $password);
            $sentencia->bindParam(6, $apiKey);
            $sentencia->bindParam(7, $userType);
			$sentencia->bindParam(8, $verification);

            $resultado = $sentencia->execute();

            if ($resultado) {
				$respuesta["apiKey"] = $apiKey;
                $respuesta["lastName"] = $lastName;
                $respuesta["firstName"] = $firstName;
                $respuesta["userType"] = $userType;
				$respuesta["email"] = $email;
                return ["user" => $respuesta];
            } else {
                return self::ESTADO_CREACION_FALLIDA;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }
	
    /**
     * Protege la contraseña con un algoritmo de encriptado
     * @param $contrasenaPlana
     * @return bool|null|string
     */

    private function generarClaveApi()
    {
        return md5(microtime() . rand());
    }
	
	private function generarVerification()
    {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 6; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
    }
	
	 private function loguearFB()
    {
    
        $respuesta = array();

        $body = file_get_contents('php://input');
        $usuario = json_decode($body);

        $email = $usuario->email;

            $usuarioBD = self::obtenerUsuarioPorEmail($email);

            if ($usuarioBD != NULL) {
                http_response_code(200);
                $respuesta["firstName"] = $usuarioBD["firstName"];
                $respuesta["lastName"] = $usuarioBD["lastName"];
                $respuesta["country"] = $usuarioBD["country"];
                $respuesta["email"] = $usuarioBD["email"];
                $respuesta["apiKey"] = $usuarioBD["apiKey"];
                $respuesta["accountType"] = $usuarioBD["accountType"];
                $respuesta["state"] = $usuarioBD["state"];
                $respuesta["userType"] = $usuarioBD["userType"];
                $respuesta["verifiedProfile"] = $usuarioBD["verifiedProfile"];
                return ["user" => $respuesta];
            } else {
                throw new ExcepcionApi(self::ESTADO_FALLA_DESCONOCIDA,
                    "Ha ocurrido un error");
            }
        
    }
	
    private function loguear()
    {
    
        $respuesta = array();

        $body = file_get_contents('php://input');
        $usuario = json_decode($body);

        $email = $usuario->email;
        $password = md5($usuario->password);


        if (self::autenticar($email, $password)) {
            $usuarioBD = self::obtenerUsuarioPorEmail($email);

            if ($usuarioBD != NULL) {
                http_response_code(200);
                $respuesta["firstName"] = $usuarioBD["firstName"];
                $respuesta["lastName"] = $usuarioBD["lastName"];
                $respuesta["country"] = $usuarioBD["country"];
                $respuesta["email"] = $usuarioBD["email"];
                $respuesta["apiKey"] = $usuarioBD["apiKey"];
                $respuesta["accountType"] = $usuarioBD["accountType"];
                $respuesta["state"] = $usuarioBD["state"];
                $respuesta["userType"] = $usuarioBD["userType"];
                $respuesta["verifiedProfile"] = $usuarioBD["verifiedProfile"];
                return ["user" => $respuesta];
            } else {
                throw new ExcepcionApi(self::ESTADO_FALLA_DESCONOCIDA,
                    "Ha ocurrido un error");
            }
        } else {
            throw new ExcepcionApi(self::ESTADO_PARAMETROS_INCORRECTOS,
                utf8_encode("Wrong Email or Password"));
        }
    }

    private function autenticar($email, $password)
    {
        $comando = "SELECT password FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::EMAIL . "=?";

        try {

            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

            $sentencia->bindParam(1, $email);

            $sentencia->execute();

            if ($sentencia) {
                $resultado = $sentencia->fetch();

                if ($password == $resultado['password']){
                    return true;
                } else return false;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            throw new ExcepcionApi(self::ESTADO_ERROR_BD, $e->getMessage());
        }
    }

    private function obtenerUsuarioPorEmail($email)
    {
        $comando = "SELECT " .
            self::FIRSTNAME . "," .
            self::LASTNAME . "," .
            self::COUNTRY . "," .
            self::EMAIL . "," .
            self::PASSWORD . "," .
            self::STATE . "," .
            self::USERTYPE . "," .
            self::ACCOUNT_TYPE . "," .
            self::VERIFIED_PROFILE . "," .
            self::APIKEY .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::EMAIL . "=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $email);

        if ($sentencia->execute())
            return $sentencia->fetch(PDO::FETCH_ASSOC);
        else
            return null;
    }

    /**
     * Otorga los permisos a un usuario para que acceda a los recursos
     * @return null o el id del usuario autorizado
     * @throws Exception
     */
      public static function autorizar()
    {
		
		
        $cabeceras = apache_request_headers();
	//print_r($cabeceras);
        if (isset($cabeceras["Authorization"]) || isset($cabeceras["authorization"])) {

            $claveApi = (isset($cabeceras["Authorization"])) ? $cabeceras["Authorization"] : $cabeceras["authorization"];

            if (users::validarClaveApi($claveApi)) {
                return users::obtenerIdUsuario($claveApi);
            } else {
                throw new ExcepcionApi(
                    self::ESTADO_CLAVE_NO_AUTORIZADA, "Clave de API no autorizada", 401);
            }

        } else {
            throw new ExcepcionApi(
                self::ESTADO_AUSENCIA_CLAVE_API,
                utf8_encode("Se requiere Clave del API para autenticación"));
        }
    }
	
	
	   public static function autorizarPut($claveApi)
    {
            if (users::validarClaveApi($claveApi)) {
                return users::obtenerIdUsuario($claveApi);
            } else {
                throw new ExcepcionApi(
                    self::ESTADO_CLAVE_NO_AUTORIZADA, "Clave de API no autorizada", 401);
            }

        
    }


    /**
     * Comprueba la existencia de la clave para la api
     * @param $claveApi
     * @return bool true si existe o false en caso contrario
     */
    private function validarClaveApi($claveApi)
    {
        $comando = "SELECT COUNT(" . self::USERID . ")" .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::APIKEY . "=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $claveApi);

        $sentencia->execute();

        return $sentencia->fetchColumn(0) > 0;
    }

    /**
     * Obtiene el valor de la columna "idUsuario" basado en la clave de api
     * @param $claveApi
     * @return null si este no fue encontrado
     */
    private function obtenerIdUsuario($claveApi)
    {
        $comando = "SELECT " . self::USERID .
            " FROM " . self::NOMBRE_TABLA .
            " WHERE " . self::APIKEY . "=?";

        $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);

        $sentencia->bindParam(1, $claveApi);

        if ($sentencia->execute()) {
            $resultado = $sentencia->fetch();
            return $resultado['userID'];
        } else
            return null;
    }
}