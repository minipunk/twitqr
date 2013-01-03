<?php
/**
   * @package twitqr
   * @version   1.0
   * @author    Minipunk
   * @copyright (C) 2013 Minipunk
   *
   * @license        Apache License Version 2.0, see LICENSE.md
   * 3/JAN/2012
   *
 */
 
$file = "respondido.txt"; // Para no responder dos veces al mismo, almacena el ID del tweet

// compruebo que existe el archivo txt y si no existe lo crea
if(!file_exists($file)){
    $since_id = 0;
} else {
    $handle2 = fopen($file, 'r');
    
    if(filesize($file)==0){
        $since_id = 0;
    } else {
        $since_id = fread($handle2, filesize($file));
    }
    fclose($handle2);    
}
require ('src/tmhOAuth.php'); // utilizo la biblioteca de https://github.com/themattharris
require ('src/tmhUtilities.php'); // utilizo la biblioteca de https://github.com/themattharris

$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => 'KEY DE APP',
  'consumer_secret' => 'SECRET DE APP',
  'user_token'      => 'TOKEN DE USUARIO',
  'user_secret'     => 'SECRET DE USUARIO',
));

$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/mentions_timeline.json')); // recupero menciones de twitter

$mentions = json_decode($tmhOAuth->response['response']); // Decodifico el archivo JSON

  $tweetqr = $mentions[0]->text; //utilizo solamente la última mención
  $since_id = $mentions[0]->id; // id del último tweet

   if(($mentions->id)>$since_id){
            $since_id = $mentions->id; // compruebo que la mención es nueva
       
      $handle = fopen($file, 'w+'); // abro y leo el archivo de texto con el ID del tweet
      fwrite($handle, $since_id); // Escribo el nuevo id
      fclose($handle); // cierro el archivo de texto
    
	  
	$cambia  = array(' ');
	$replace = array('+');
	$tweetqr = str_replace($cambia, $replace, $tweetqr); // reemplazo espacios por signo +
 
$paths = '/'; // ruta del hosting donde almacenar la foto
$filep = "https://chart.googleapis.com/chart?chs=545x545&cht=qr&chl=$tweeqr"; // genero la imagen con la api de google
 
$ftp_server = 'ftp.dominioa.es'; // dirección ftp al servidor
 
$ftp_user_name = 'user'; // usuario del FTP
 
$ftp_user_pass = 'pass'; // contraseña del FTP
 
$name = 'foto.jpg'; // Nombre con que se guardará la imagen
 
$conn_id = ftp_connect($ftp_server); // CONECTO AL SERVIDOR
 
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);// ENVIO EL LOGIN
 
// CHEKEO LOS RESULTADOS
if ((!$conn_id) || (!$login_result)) {
       echo "Error en la conexión FTP!";
       echo "No se ha podido conectar a $ftp_server con el usuario $ftp_user_name....";
       exit;
   } else {
       echo "Conectado a $ftp_server, con el usuario $ftp_user_name"."..... Se ha guardado la imagen generada";
   }
 
$upload = ftp_put($conn_id, $paths.'public_html/'.$name, $filep, FTP_BINARY);// SUBO EL ARCHIVO DE IMAGEN
 
// CHEKEO LA SUBIDA
if (!$upload) {
       echo "Error al subir la imagen por FTP!";
   } else {
       echo "Guardada la imagen $name en $ftp_server ";
   }
 
ftp_close($conn_id);	// CIERRO LA CONEXIÓN	

$filename = "foto.jpg"; // ruta de la imagen que publicamos en twitter (tiene que estar en el mismo directorio que este archivo).
$handle = fopen($filename, "rb");
$image = fread($handle, filesize($filename));
fclose($handle);
$status = "@$user aquí tienes tu QR "; //FRASE A TWITTEAR
 
 // Publico el tweet con la imagen
$code = $tmhOAuth->request('POST', 'https://api.twitter.com/1.1/statuses/update_with_media.json',
  array(
    'media[]' => "{$image};type=image/jpeg;filename={$filename}" ,
   "status"   => ' '.$status 
  ),
  true, // uso auth
  true  // multipart
);
}
?>
