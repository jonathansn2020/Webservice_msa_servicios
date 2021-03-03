<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

require_once './php-jwt-master/src/JWT.php';
use Firebase\JWT\JWT;


class ApiController extends CI_Controller{

    public function __construct(){
        parent::__construct();      
        $this->load->model('Api/ApiModel');
        $this->load->database(); 
    }   
    
    public function LoginUsuarios(){

        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        
        $data = file_get_contents('php://input');
        $json_data_usuario = json_decode($data, true);             
                    
        $datosusu = $this->ApiModel->ValidarUsuario($json_data_usuario['codigousuario']);
        
        if(isset($datosusu)){            
            if($datosusu->USU_Cod_usuario == $json_data_usuario['codigousuario'] && password_verify($json_data_usuario['contrasena'], $datosusu->USU_Contrasena)){
                if($datosusu->USU_Estado == "Activo"){
                    $time = time();
                    $key = "glacorsacmsawebservice2021";
                    
                    $payload = array(                               
                        "iat"  => $time, 
                        "exp"  => $time + (60 * 60),
                        "data" => [
                            "codigousuario"     => $datosusu->USU_Cod_usuario,
                            "usuario"           => $datosusu->USU_Nombre_corrido,
                            "descripcionrol"    => $datosusu->ROL_Descripcion_rol,
                            "estadousuario"     => $datosusu->USU_Estado
                        ]                    
                    );
               
                    $jwt = JWT::encode($payload, $key);
                    //$data = JWT::decode($jwt, $key, array('HS256'));           
                
                    echo json_encode(array("message" => "ACCESO CORRECTO","token" => $jwt));    
                }
                else{
                    echo json_encode(array("message" => "LA CUENTA DE USUARIO SE ENCUENTRA INACTIVA Y NO PUEDE ACCEDER AL API; COMUNIQUESE CON EL ADMINISTRADOR DEL SISTEMA."));
                }              
                                
            }   
            else{
                echo json_encode(array("error" => "USUARIO Y/O CONTRASEÑA INCORRECTOS"));
            }        
                   
        }
        else{
            echo json_encode(array("error" => "USUARIO Y/O CONTRASEÑA INCORRECTOS"));
        }

    }    
    
    function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { 
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();            
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public function ApiCargarServicios(){        

        
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
        date_default_timezone_set("America/Lima");       
                
        $data = file_get_contents('php://input');
        $json_data = json_decode($data, true);   
        $token = $this->getBearerToken();       
        
        if($token){
            if(count($json_data) > 0){
                for($i = 0;$i < count($json_data); $i++){
                    $mensaje_errores = [];               
                    $contador = 0;
                    
                    if(empty($json_data[$i]['codpedido'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La orden de servicio número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['codcliente'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El código del cliente número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['tiporden'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El tipo de orden número '.($i+1).' es obligatorio';            
                    }  
                        
                    if(empty($json_data[$i]['fecentregasolicitada'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La fecha de entrega solicitada número '.($i+1).' es obligatorio';            
                    }    
                    
                    if(empty($json_data[$i]['sedeorigen'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El código de la sede origen número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['direcorigen'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La dirección de origen número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['distritorigen'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El distrito de origen número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['ubigeorigen'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El ubigeo origen número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['codzonaorigen'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El código de zona de origen número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['atencionorigen'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La atención de origen número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['sededestino'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El código de la sede destino número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['direcdestino'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La dirección de destino número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['distritodestino'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El distrito de destino número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['ubigeodestino'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El ubigeo destino número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['codzonadestino'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El código de zona de destino número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['atenciondestino'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La atención de destino número '.($i+1).' es obligatorio';            
                    }
        
                    if(empty($json_data[$i]['descripcionproducto'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La descripción del producto número '.($i+1).' es obligatorio';            
                    }         
                                
                    if(count($mensaje_errores) == 0){
                        $nombre_cliente = $this->ApiModel->GET_Nombre_corrido_cliente($json_data[$i]['codcliente']);
                        $orden_registrada = $this->ApiModel->GET_Orden_servicio($json_data[$i]['codpedido']);
                        if($orden_registrada){
    
                            $fecha_actualizar = date("Y-m-d");
                            $hora_actualizar = date("H:i:s");
    
                            $servicios_data = array(
                                'SRV_Orden_servicio'                 => $json_data[$i]['codpedido'],
                                'SRV_Tipo_orden'                     => $json_data[$i]['tiporden'],                            
                                'SRV_Fec_entrega_solicitada'         => $json_data[$i]['fecentregasolicitada'],
                                'SRV_Hora_entrega_solicitada'        => $json_data[$i]['horaentregasolicitada'],                           
                                'SRV_Sede_origen'                    => $json_data[$i]['sedeorigen'], 
                                'SRV_Direccion_origen'               => $json_data[$i]['direcorigen'], 
                                'SRV_Distrito_origen'                => $json_data[$i]['distritorigen'],  
                                'SRV_Ubigeo_origen'                  => $json_data[$i]['ubigeorigen'], 
                                'SRV_Codigo_zona_origen'             => $json_data[$i]['codzonaorigen'], 
                                'SRV_Atencion_origen'                => $json_data[$i]['atencionorigen'], 
                                'SRV_Sede_destino'                   => $json_data[$i]['sededestino'],
                                'SRV_Direccion_destino'              => $json_data[$i]['direcdestino'], 
                                'SRV_Distrito_destino'               => $json_data[$i]['distritodestino'], 
                                'SRV_Ubigeo_destino'                 => $json_data[$i]['ubigeodestino'],
                                'SRV_Codigo_zona_destino'            => $json_data[$i]['codzonadestino'], 
                                'SRV_Atencion_destino'               => $json_data[$i]['atenciondestino'],    
                                'SRV_Descripcion_producto'           => $json_data[$i]['descripcionproducto'],
                                'SRV_Actualizado_por'                => $nombre_cliente,  
                                'SRV_Fecha_actualizado'              => $fecha_actualizar,
                                'SRV_Hora_actualizacion'             => $hora_actualizar             
                                                                                                                                  
                            );  
    
                            $this->ApiModel->PUT_Carga_servicios($servicios_data['SRV_Orden_servicio'], $servicios_data);
                            $fecha_actualizar = date("d-m-Y", strtotime($fecha_actualizar));  
                            echo json_encode(array("mensaje" => "LOS DATOS DEL SERVICIO {$servicios_data['SRV_Orden_servicio']} FUERON ACTUALIZADOS CORRECTAMENTE EL {$fecha_actualizar} A LAS {$hora_actualizar}"));
                           
                        }
                        else{
                            $fecha_creacion = date("Y-m-d");
                            $hora_creacion = date("H:i:s");                       
    
                            $servicios_data = array(
                                'SRV_Orden_servicio'                 => $json_data[$i]['codpedido'],
                                'CLI_Cod_cliente'                    => $json_data[$i]['codcliente'],
                                'SRV_Tipo_orden'                     => $json_data[$i]['tiporden'],
                                'SRV_Fecha_registro'                 => $fecha_creacion,
                                'SRV_Hora_registro'                  => $hora_creacion,
                                'SRV_Fec_entrega_solicitada'         => $json_data[$i]['fecentregasolicitada'],
                                'SRV_Hora_entrega_solicitada'        => $json_data[$i]['horaentregasolicitada'],                            
                                'SRV_Estado_pedido'                  => "01",  
                                'SRV_Sede_origen'                    => $json_data[$i]['sedeorigen'], 
                                'SRV_Direccion_origen'               => $json_data[$i]['direcorigen'], 
                                'SRV_Distrito_origen'                => $json_data[$i]['distritorigen'],  
                                'SRV_Ubigeo_origen'                  => $json_data[$i]['ubigeorigen'], 
                                'SRV_Codigo_zona_origen'             => $json_data[$i]['codzonaorigen'], 
                                'SRV_Atencion_origen'                => $json_data[$i]['atencionorigen'], 
                                'SRV_Sede_destino'                   => $json_data[$i]['sededestino'],
                                'SRV_Direccion_destino'              => $json_data[$i]['direcdestino'], 
                                'SRV_Distrito_destino'               => $json_data[$i]['distritodestino'], 
                                'SRV_Ubigeo_destino'                 => $json_data[$i]['ubigeodestino'],
                                'SRV_Codigo_zona_destino'            => $json_data[$i]['codzonadestino'], 
                                'SRV_Atencion_destino'               => $json_data[$i]['atenciondestino'],    
                                'SRV_Descripcion_producto'           => $json_data[$i]['descripcionproducto'],  
                                'SRV_Codigo_conductor'               => $json_data[$i]['codigoconductor'], 
                                'SRV_Creado_por'                     => $nombre_cliente, 
                                'SRV_Fecha_creacion'                 => $fecha_creacion, 
                                'SRV_Hora_creacion'                  => $hora_creacion,  
                                'SRV_Actualizado_por'                => $nombre_cliente,  
                                'SRV_Fecha_actualizado'              => $fecha_creacion,
                                'SRV_Hora_actualizacion'             => $hora_creacion          
                                                         
                            );         
                                
                            
                            $this->ApiModel->POST_Carga_servicios($servicios_data);  
                            $fecha_creacion = date("d-m-Y", strtotime($fecha_creacion));                 
                            echo json_encode(array("mensaje" => "LOS DATOS DEL SERVICIO {$servicios_data['SRV_Orden_servicio']} FUERON INSERTADOS CORRECTAMENTE EL {$fecha_creacion} A LAS {$hora_creacion}"));
                        }      
                        
                    }
                    else{
                        echo json_encode(array('error' => $mensaje_errores));
                    }
        
                    
                    
                }
            }
            else{
                echo json_encode(array('error' => "ERROR AL LLAMAR AL API, NO HAY DATOS DE SERVICIOS PARA SER CARGADOS!"));
            }    
        }
        else{
            echo json_encode(array("message" => "NO PUEDE ACCEDER AL RECURSO DEL MSA_SERVICIO"));
        }    

       } 
        
         
}       

           
      
         
   

        
               
        
       
   