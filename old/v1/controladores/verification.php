<?php
class verification
{
	  public static function post($peticion)
    {
        $userID = users::autorizar();

        $body = file_get_contents('php://input');
        $data = json_decode($body);

       switch($peticion[0])
	   {
		   case 'sendEmail': return self::sendEmail($data,$userID);break;
		   case 'sendSMS': return self::sendSMS($data,$userID);break;
		   case 'verify': return self::verify($data,$userID);break;
	   }

    }
	
	 public static function get()
    {
        $userID = users::autorizar();
 return self::getStatus($userID);
		  

    }
	
	private function getStatus($userID)
	{
		$codigo="";
		//obtener codigo
		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select verification from users where userID=".$userID);
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
			$codigo=$row['verification'];
		}
		
		if($codigo=="")
			{
			http_response_code(200);
                return
                    [
                        "status" => "verified"
                    ];
		}
		else
		{
			 throw new ExcepcionApi(self::ESTADO_PARAMETROS_INCORRECTOS,
                utf8_encode("Verified"));
		}
	}
	
	private function verify( $data,$userID)
	{
		$codigo="";
		//obtener codigo
		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select verification from users where userID=".$userID);
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
			$codigo=$row['verification'];
		}
		
		//comprobar el codigo
		if(strtolower($data->code)==strtolower($codigo))
		{
			$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("update users set verification='' where userID=".$userID);
		$sentencia->execute();
			http_response_code(200);
                return
                    [
                        "status" => "verified"
                    ];
		}
		else
		{
			 throw new ExcepcionApi(self::ESTADO_PARAMETROS_INCORRECTOS,
                utf8_encode("Wrong Code"));
		}
               
	}
	
	
	
	private function sendSMS( $data,$userID)
	{
		$codigo="";
		//obtener codigo
		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select verification from users where userID=".$userID);
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
			$codigo=$row['verification'];
		}
		
// send sms
  $url = 'http://servicio.smsmasivos.com.ar/enviar_sms.asp';
       $data = array('api' => '1', 'usuario' => 'SMSDEMO65074', 'clave' => 'SMSDEMO65074538', 'tos' => $data->number, 'texto' => 'Doers Index Code: '.$codigo);

     // use key 'http' even if you send the request to https://...
       $options = array(
      'http' => array(
      'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
      'method'  => 'POST',
      'content' => http_build_query($data),
              )
              );
         $context  = stream_context_create($options);
         $result = file_get_contents($url, false, $context);
        
               
            

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "sms" => "sended"
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
	}
	
	
	
	private function sendEmail( $data,$userID)
	{
		$codigo="";
		//obtener codigo
		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare("select verification from users where userID=".$userID);
		$sentencia->execute();
		while ($row = $sentencia->fetch(PDO::FETCH_ASSOC)) {
			$codigo=$row['verification'];
		}
		
		//mandar email
		// the message
$msg = "<html><body><h3>Doers Index Account Verification</h3><p>This is your verification code. Please write it on verification page. Also, you can copy and paste.</p><h4><strong>".$codigo."</strong></h4></body></html>";
// use wordwrap() if lines are longer than 70 characters
$msg = wordwrap($msg,70);
$headers = "From: no-reply@doers.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
// send email
mail($data->email,"Doers Index Verification",$msg,$headers); 
               
            

            // Ejecutar sentencia preparada
            if ($sentencia->execute()) {
                http_response_code(200);
                return
                    [
                        "email" => "sended"
                    ];
            } else
                throw new ExcepcionApi(self::ESTADO_ERROR, "Se ha producido un error");
	}
}
?>