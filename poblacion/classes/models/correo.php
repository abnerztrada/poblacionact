<?php
namespace tool_poblacion\models;

// require(dirname(dirname(__FILE__)).'/config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->libdir.'/moodlelib.php');

/**
 *
 */
class correo
{

  public function __construct()
  {
    // code...
  }

  public function correo_envio(){
      global $DB;
      $contador = 0; 
      //Querys de fechas y course
      $query = "Select @s:=@s + 1 id_au, c.id, c.shortname, DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fecha,
      DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fechainicio,
      DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.startdate, '%Y-%m-%d'), INTERVAL -5 HOUR),'%d/%m/%Y') AS fechainicioc,
      DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(c.enddate, '%Y-%m-%d'), INTERVAL -5 HOUR),'%d/%m/%Y') AS fechafinc,
      DATE_FORMAT(DATE_ADD(FROM_UNIXTIME(cd.value, '%Y-%m-%d %H:%i'), INTERVAL -5 HOUR),'%d/%m/%Y %H:%i') AS fecha_avance
      FROM (select @s:=0) as s, mdl_course c
      INNER JOIN mdl_customfield_data cd ON cd.instanceid = c.id
      where c.visible = 1 and cd.fieldid  IN (38,39,40,41,42) and c.id = 241"; /*IN (38,39,40,41,42)*/
      $result = $DB->get_records_sql($query);

      $url = 'https://calidad.laucmi.telefonicaed.pe/local/rep/report.php?id=';

      foreach ($result as $it) {
        // Url con id
        $urltemp = $url.$it->id;
        //fecha avance 
        $fechaavance = $it->fecha_avance; 

        //Fecha inicio
        $fechainicio = $it->fechainicio; 
        $fechainicioc = $it->fechainicioc; 

        //Fecha fin
        $fechafinc = $it->fechafinc; 

        //Query que valida los correos de los stackholder
        $query2 = "SELECT  @s:=@s + 1 id_auto, c.id, concat(u.firstname,' ', u.lastname) as nombre, u.email, c.shortname, c.fullname,
                  asg.roleid, asg.userid, r.shortname as stakholder FROM
                  (select @s:=0) as s,
                  mdl_user u
                  INNER JOIN mdl_role_assignments as asg on asg.userid = u.id
                  INNER JOIN mdl_context as con on asg.contextid = con.id
                  INNER JOIN mdl_course c on con.instanceid = c.id
                  INNER JOIN mdl_role r on asg.roleid = r.id
                  where c.shortname = '$it->shortname' and r.shortname = 'stakeholder'";
        $result2 = $DB->get_records_sql($query2);

        echo '<pre>';
          print_r($result2);
        echo '</pre>';
         
        foreach ($result2 as $it2) {
          $body = $urltemp;
          $emailuser->email = $it2->email;
          $emailuser->id = -99;
          $emailuser->maildisplay = true;
          $emailuser->mailformat = 1;
          $nombre = $it2->nombre;
          $subject = $it2->fullname;

          date_default_timezone_set("America/Guatemala");
          $fechaAct = date("d/m/Y H:i"); // H:i Hora y minuto
          
          //Imagen 
          $String ="<img src='http://54.161.158.96/local/img/banner.jpg'"; 

          //Texto de Estatus de cumplimiento del curso
          $string1 ="";
          $string1 .= $String."\n";
          $string1 .= "<br>"; 
          $string1 .= "<br>"; 
          $string1 .= "<div style='color: orange; font-size: 18px; font-family: Century Gothic;'> $nombre </div>";
          $string1 .= "<br>";
          $string1 .= "<div style= 'color: black; font-size: 16px; font-family: Century Gothic;'> En el siguiente enlace $body encontrarás el estatus de cumplimiento de los participantes inscritos al curso: <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'> $subject. </span> </div>";
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Emisión del reporte: <span style= 'color: orange; font-size: 16px; font-family: Century Gothic;'> $fechaAct horas. </span> \n </div> \n";
          $string1 .= "<br>"; 
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Recuerda que los participantes que tienen asistencia son aquellos que han completado el programa de aprendizaje y han realizado la encuesta de satisfacción. </div>";
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Cualquier duda o comentario puedes escribirnos a cmi-laucmi@somoscmi.com \n </div>";
          $string1 .= "<br>"; 
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> Atentamente, \n </div>";
          $string1 .= "<div style='color: black; font-size: 16px; font-family: Century Gothic;'> laUcmi \n </div>";

          //Comparaciones de fechas para el envio del correo electronico
          if($fechaAct == $fechaavance){
              $email = email_to_user($emailuser,'laUcmi','Estatus de cumplimiento '.$subject, $string1);
              echo "Correo enviado";
          }else{
              echo "Correo no enviado";
          } 
        }
      }
    }
  }
?>

