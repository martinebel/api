<?php
class sendEmail
{
	    public static function get($peticion)
    {
		 if (!empty($peticion[0])){
			 switch($peticion[0])
			 {
				 case 'login':
				  return self::login( $peticion[1]);
				 break;
				 case 'infoDoers':
				 return self::infoDoers( $peticion[1],$peticion[2]);
				 break;
				 case 'other':
				 return self::other( $peticion[1],$peticion[2]);
				 break;
			 }
			 
		 }
		
	}
	
	private function infoDoers($email,$doerList)
	{
		
$doerList=trim($doerList, ",");

// the message
$msg ='<html><head></head><body><h3>Hi! This is your requested information.</h3><br>
<table><theader><tr><th>Name</th><th>Skills</th><th>Contact</th><th>About</th><th>Availability</th></tr></thead></tbody>';
$cats = explode(",", $doerList);
foreach($cats as $cat) {
    $cat = trim($cat);
    $query="select doers.*,availability.days,availability.hrFrom,availability.hrTo,users.firstName,users.lastName,users.email from doers inner join availability on availability.doerID = doers.doerID inner join users on users.userID=doers.userID where users.apikey='".$cat."'";
  $stmt = $dbh->prepare("$query");
        $stmt->execute(); 
          $result = $stmt->fetchAll();
        foreach($result as $row)
{
	$msg.='<tr><td><strong>'.$row['firstName']." ".$row['lastName'].'</strong></td>
	<td>'.$row['skills'].'</td><td>Email: '.$row['email'].'<br>Phone: '.$row['phone'].'</td>
	<td>'.$row['about'].'</td><td>'.$row['days'].'<br>From '.substr(str_pad($row['hrFrom'],4,'0', STR_PAD_LEFT),0,2).":".substr(str_pad($row['hrFrom'],4,'0', STR_PAD_LEFT),2).'<br>To '.substr(str_pad($row['hrTo'],4,'0', STR_PAD_LEFT),0,2).":".substr(str_pad($row['hrTo'],4,'0',STR_PAD_LEFT),2).'</td></tr>';
}
}
$msg.='</tbody></table></body></html>';
// send email
//mail($email,"Doers Information",$msg);
$to = $email;
$from = "test@wonerz.com\r\n";
$subject = "Doers Information";
$headers = "From:" . $from;
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
mail($to,$subject,$msg,$headers);
	}
	
	private function login($email)
	{
						//mandar email
		// the message
$msg = "<html><body><h3>Welcome to Doers Index!</h3><p>Now, you can login whenever you want.</p></body></html>";
// use wordwrap() if lines are longer than 70 characters
$msg = wordwrap($msg,70);
$headers = "From: no-reply@doers.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
// send email
mail($email,"Doers Index Registration",$msg,$headers); 
               
                http_response_code(200);
                return
                    [
                        // "estado" => self::ESTADO_EXITO,
                        "sendEmail" => "ok"
                    ];
	}
	
	private function other($email,$msg)
	{
						//mandar email
		// the message
$msg = "<html><body><p>".$msg."</p></body></html>";
// use wordwrap() if lines are longer than 70 characters
$msg = wordwrap($msg,70);
$headers = "From: no-reply@doers.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
// send email
mail($email,"Doers Index",$msg,$headers); 
               
                http_response_code(200);
                return
                    [
                        // "estado" => self::ESTADO_EXITO,
                        "sendEmail" => "ok"
                    ];
	}
}
?>