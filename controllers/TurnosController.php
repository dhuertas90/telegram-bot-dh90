<?php


class TurnosController {

    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
    }


    public function mensajeBot(){
        $returnArray        = true;
        $rawData            = file_get_contents('php://input');
        $response           = json_decode($rawData, $returnArray);
        $id_del_chat        = $response['message']['chat']['id'];
        // Obtener comando (y sus posibles parametros)
        $regExp = '#^(\/[a-zA-Z0-9\/]+?)(\ .*?)$#i';

        $tmp = preg_match($regExp, $response['message']['text'], $aResults);

        if (isset($aResults[1])) {
            $cmd            = trim($aResults[1]);
            $cmd_params     = trim($aResults[2]);
        } else {
            $cmd            = trim($response['message']['text']);
            $cmd_params     = '';
        }

        $msg                                =   array();
        $msg['chat_id']                     =   $response['message']['chat']['id'];
        $msg['text']                        =   null;
        $msg['disable_web_page_preview']    =   true;
        $msg['reply_to_message_id']         =   $response['message']['message_id'];
        $msg['reply_markup']                =   null;
        switch ($cmd) {
            case '/start':
                $msg['text']                =   'Hola ' . $response['message']['from']['first_name'] . PHP_EOL;
                $msg['text']               .=   '¿Como puedo ayudarte? Puedes utilizar el comando /help';
                $msg['reply_to_message_id'] =   null;
                break;
            case '/help':
                $msg['text']                 =  'Los comandos disponibles son estos:' . PHP_EOL;
                $msg['text']                .=  '/start Inicializa el bot'.PHP_EOL;
                $msg['text']                .=  '/turnos dd/mm/aaaa Muestra los turnos disponibles' . PHP_EOL;
                $msg['text']                .=  '/reservar dni dd/mm/aaaa hh:mm Permite reservar un turno para la fecha y horario indicada' . PHP_EOL;
                $msg['text']                .=  '/help Muestra esta ayuda' . PHP_EOL;
                $msg['reply_to_message_id']  =  null;
                break;
            case '/reservar':
                $response_reserva = $response['message']['text'];
                
                $datos = explode(" ", $cmd_params);
                
                //descomponer parametros
                $dni        =   $datos[0];
                $dni        =   intval($dni);
                $fecha      =   $datos[1];
                $horario    =   $datos[2];
                
                $f      =   explode("/", $fecha);
                $dia    =   $f[0];
                $mes    =   $f[1];
                $año    =   $f[2];

                $h          =   explode(":", $horario);
                $hora       =   $h[0];
                $minutos    =   $h[1];           
                //fin

                $fecharevisar = array('dia'=>$dia, 'mes'=>$mes, 'año'=>$año);
                $horariosDisponibles = $this->getTurnosDisponibles($fecharevisar);
                if(!($horariosDisponibles == '')){
                    $datosreservacion = array('dia'=>$dia, 'mes'=>$mes, 'año'=>$año, 'documento'=>$dni, 'hora'=>$hora, 'minutos'=>$minutos);
                    $id_turno = $this->postReservar($datosreservacion);
                    if(!($id_turno == '')){
                        $msg['text']    = 'Te confirmamos el turno para: '. PHP_EOL;
                        $msg['text']   .= $cmd_params;
                        $msg['text']   .= '. Numero de turno: ';
                        $msg['text']   .= $id_turno;
                    }
                    else{
                        $msg['text']    = 'No existe turnos disponibles para este horario: ';
                        $msg['text']   .= $horario;
                    }
                }
                else{
                    $msg['text']    = 'No existe turnos disponibles para esta fecha: ';
                    $msg['text']   .= $fecha;
                }
                break;
            case '/turnos':
                $response_fecha = $response['message']['text'];
                
                $datos = explode("/", $cmd_params);
                //descomponer fecha
                $dia = $datos[0];
                $mes = $datos[1];
                $año = $datos[2];
                //fin
                
                $fecha = array('dia'=>$dia, 'mes'=>$mes, 'año'=>$año);
                $horariosDisponibles = $this->getTurnosDisponibles($fecha);
                
                if(!($horariosDisponibles == '')){
                    $msg['text']    =   'Los turnos disponibles son: ';
                    $msg['text']   .=   $horariosDisponibles;
                }
                else{
                    $msg['text']    =   'No existen turnos para el ';
                    $msg['text']   .=   $cmd_params;
                }
                break;
            default:
                $msg['text']    =   'Lo siento, no es un comando válido.' . PHP_EOL;
                $msg['text']   .=   'Prueba /help para ver la lista de comandos disponibles';
                break;
        }
        $url = 'https://api.telegram.org/bot493696957:AAGsrZOASSwOwUAB5csiY5BRrIxd6jjSPv0/sendMessage';
        $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($msg)
            )
        );

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        exit(0);
    }
   }
