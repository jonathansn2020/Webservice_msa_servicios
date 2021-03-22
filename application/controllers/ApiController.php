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

        header("Content-Type: application/json; charset=UTF-8");

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
                    header("Authorization: " . $jwt);
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

    public function ApiCargarServicios(){

        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        date_default_timezone_set("America/Lima");

        $data = file_get_contents('php://input');
        $json_data = json_decode($data, true);
        $headers = getallheaders();

        if(isset($headers['Authorization'])){
            //$datatoken = JWT::decode($jwt, $key, array('HS256'));
            if(count($json_data) > 0){
                for($i = 0;$i < count($json_data); $i++){
                    $mensaje_errores = [];
                    $contador = 0;

                    if(empty($json_data[$i]['codigo_cliente'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El código del cliente número '.($i+1).' es obligatorio';
                    }
                    if(empty($json_data[$i]['orden_servicio'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La orden de servicio número '.($i+1).' es obligatorio';
                    }
                    if(empty($json_data[$i]['fecha_registro'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La fecha de registro número '.($i+1).' es obligatorio';
                    }
                    if(empty($json_data[$i]['hora_registro'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La hora de registro número '.($i+1).' es obligatorio';
                    }
                    if(empty($json_data[$i]['fecha_solicitada'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La fecha de entrega solicitada número '.($i+1).' es obligatorio';
                    }
                    if(empty($json_data[$i]['origen'])){
                      if(empty($json_data[$i]['direccion_origen'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La dirección de origen número '.($i+1).' es obligatorio';
                      }
                      if(empty($json_data[$i]['distrito_origen'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El distrito de origen número '.($i+1).' es obligatorio';
                      }
                      if(empty($json_data[$i]['ubigeo_origen'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El ubigeo de origen número '.($i+1).' es obligatorio';
                      }
                      if(empty($json_data[$i]['atencion_origen'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La atención de origen número '.($i+1).' es obligatorio';
                      }
                    }
                    if(empty($json_data[$i]['destino'])){
                      if(empty($json_data[$i]['direccion_destino'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La dirección de destino número '.($i+1).' es obligatorio';
                      }
                      if(empty($json_data[$i]['distrito_destino'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El distrito de destino número '.($i+1).' es obligatorio';
                      }
                      if(empty($json_data[$i]['ubigeo_destino'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'El ubigeo de destino número '.($i+1).' es obligatorio';
                      }
                      if(empty($json_data[$i]['atencion_destino'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La atención de destino número '.($i+1).' es obligatorio';
                      }
                    }
                    if(empty($json_data[$i]['descripcion_producto'])){
                        $contador++;
                        $mensaje_errores[$contador] = 'La descripción del producto número '.($i+1).' es obligatorio';
                    }

                    if(count($mensaje_errores) == 0){

                        $nombre_cliente = $this->ApiModel->GET_Nombre_corrido_cliente($json_data[$i]['codigo_cliente']);
                        $orden_registrada = $this->ApiModel->GET_Orden_servicio($json_data[$i]['orden_servicio']);

                        if(!empty($orden_registrada)){

                            $fecha_actualizar = date("Y-m-d");
                            $hora_actualizar = date("H:i:s");

                            $data_servicios = array(
                                'SRV_Orden_servicio'                 => $json_data[$i]['orden_servicio'],
                                'SRV_Fecha_registro'                 => $json_data[$i]['fecha_registro'],
                                'SRV_Hora_registro'                  => $json_data[$i]['hora_registro'],
                                'SRV_Fec_entrega_solicitada'         => $json_data[$i]['fecha_solicitada'],
                                'SRV_Hora_entrega_solicitada'        => $json_data[$i]['hora_solicitada'],
                                'SRV_Sede_origen'                    => $json_data[$i]['origen'],
                                'SRV_Direccion_origen'               => $json_data[$i]['direccion_origen'],
                                'SRV_Distrito_origen'                => $json_data[$i]['distrito_origen'],
                                'SRV_Ubigeo_origen'                  => $json_data[$i]['ubigeo_origen'],
                                'SRV_Atencion_origen'                => $json_data[$i]['atencion_origen'],
                                'SRV_Sede_destino'                   => $json_data[$i]['destino'],
                                'SRV_Direccion_destino'              => $json_data[$i]['direccion_destino'],
                                'SRV_Distrito_destino'               => $json_data[$i]['distrito_destino'],
                                'SRV_Ubigeo_destino'                 => $json_data[$i]['ubigeo_destino'],
                                'SRV_Atencion_destino'               => $json_data[$i]['atencion_destino'],
                                'SRV_Descripcion_producto'           => $json_data[$i]['descripcion_producto'],
                                'SRV_Actualizado_por'                => $nombre_cliente,
                                'SRV_Fecha_actualizado'              => $fecha_actualizar,
                                'SRV_Hora_actualizacion'             => $hora_actualizar
                            );
                            $horaria = "";
                            if($hora_actualizar >= 12){
                              $horaria = " PM";
                              $horario_actualizado = $hora_actualizar.$horaria;
                            }
                            else{
                              $horaria = " AM";
                              $horario_actualizado = $hora_actualizar.$horaria;
                            }
                            $this->ApiModel->PUT_Carga_servicios($data_servicios['SRV_Orden_servicio'], $data_servicios);
                            $fecha_actualizar = date("d-m-Y", strtotime($fecha_actualizar));
                            echo json_encode(array("mensaje" => "LOS DATOS DEL SERVICIO {$data_servicios['SRV_Orden_servicio']} FUERON ACTUALIZADOS CORRECTAMENTE EL {$fecha_actualizar} A LAS {$horario_actualizado}"));

                        }
                        else{
                            $fecha_creacion = date("Y-m-d");
                            $hora_creacion = date("H:i:s");

                            if(!empty($json_data[$i]['origen']) && !empty($json_data[$i]['destino'])){
                              $datosede_origen   = $this->ApiModel->GET_Sedes($json_data[$i]['origen']);
                              $datosede_destino  = $this->ApiModel->GET_Sedes($json_data[$i]['destino']);

                              $data_sedeorigen = [
                                  "SD_Direccion_origen"               =>  $datosede_origen->SD_Tipo_via.' '.$datosede_origen->SD_Nombre_via.' '.$datosede_origen->SD_Numero,
                                  "SD_Departamento_origen"            =>  $datosede_origen->SD_Departamento,
                                  "SD_Provincia_origen"               =>  $datosede_origen->SD_Provincia,
                                  "SD_Distrito_origen"                =>  $datosede_origen->SD_Distrito,
                                  "SD_Codigo_ubigeo_origen"           =>  $datosede_origen->SD_Codigo_ubigeo,
                                  "SD_Responsable_origen"             =>  $datosede_origen->SD_Responsable
                              ];
                              $data_sededestino = [
                                  "SD_Direccion_destino"              =>  $datosede_destino->SD_Tipo_via.' '.$datosede_destino->SD_Nombre_via.' '.$datosede_destino->SD_Numero,
                                  "SD_Departamento_destino"           =>  $datosede_destino->SD_Departamento,
                                  "SD_Provincia_destino"              =>  $datosede_destino->SD_Provincia,
                                  "SD_Distrito_destino"               =>  $datosede_destino->SD_Distrito,
                                  "SD_Codigo_ubigeo_destino"          =>  $datosede_destino->SD_Codigo_ubigeo,
                                  "SD_Responsable_destino"            =>  $datosede_destino->SD_Responsable
                              ];

                              $distrito_origen = "";
                              $distrito_destino = "";

                              $listadozonas_origen = $this->ApiModel->GET_Ubicacion_Zonas($data_sedeorigen['SD_Departamento_origen'], $data_sedeorigen['SD_Provincia_origen'], $data_sedeorigen['SD_Distrito_origen']);
                              $listadozonas_destino = $this->ApiModel->GET_Ubicacion_Zonas($data_sededestino['SD_Departamento_destino'], $data_sededestino['SD_Provincia_destino'], $data_sededestino['SD_Distrito_destino']);

                              $tipo_orden = "ECOM";
                              $zona_origen = "";
                              $zona_destino = "";
                              $zonaecom_origen    = $listadozonas_origen->UBG_Zona_ecommerce;
                              $zonaecom_destino   = $listadozonas_destino->UBG_Zona_ecommerce;

                              if($tipo_orden == "ECOM"){
                                  if($zonaecom_origen == ""){
                                      $zona_origen = "E99";
                                  }
                                  else{
                                      $zona_origen = $listadozonas_origen->UBG_Zona_ecommerce;
                                  }
                                  if($zonaecom_destino == ""){
                                      $zona_destino = "E99";
                                  }
                                  else{
                                      $zona_destino  = $listadozonas_destino->UBG_Zona_ecommerce;
                                  }
                              }
                              $codigo_personal = "";
                              $placa_vehiculo = "";
                              if($zona_destino != "E99"){
                                $codigopers = $this->ApiModel->BuscarPersonalxZonaAsignado($zona_destino);
                                $conductor_asignado = $this->ApiModel->BuscarDatosConductorAsignadoServ($codigopers);

                                if(isset($conductor_asignado->PRS_Cod_personal) && isset($conductor_asignado->VEH_Placa)){
                                  $codigo_personal = $conductor_asignado->PRS_Cod_personal;
                                  $placa_vehiculo  = $conductor_asignado->VEH_Placa;
                                }
                                else{
                                  $codigo_personal = "PE0007";
                                  $placa_vehiculo = "";
                                }
                              }

                              $data_servicios = array(
                                  'SRV_Orden_servicio'                 => $json_data[$i]['orden_servicio'],
                                  'CLI_Cod_cliente'                    => $json_data[$i]['codigo_cliente'],
                                  'SRV_Tipo_orden'                     => "ECOM",
                                  'SRV_Fecha_registro'                 => $json_data[$i]['fecha_registro'],
                                  'SRV_Hora_registro'                  => $json_data[$i]['hora_registro'],
                                  'SRV_Fec_entrega_solicitada'         => $json_data[$i]['fecha_solicitada'],
                                  'SRV_Hora_entrega_solicitada'        => $json_data[$i]['hora_solicitada'],
                                  'SRV_Fecha_entrega'                  => "0000-00-00",
                                  'SRV_Hora_entrega'                   => "0000-00-00",
                                  'SRV_Estado_pedido'                  => "01",
                                  'SRV_Sede_origen'                    => $json_data[$i]['origen'],
                                  'SRV_Direccion_origen'               => $data_sedeorigen['SD_Direccion_origen'],
                                  'SRV_Distrito_origen'                => $data_sedeorigen['SD_Distrito_origen'],
                                  'SRV_Ubigeo_origen'                  => $data_sedeorigen['SD_Codigo_ubigeo_origen'],
                                  'SRV_Codigo_zona_origen'             => $zona_origen,
                                  'SRV_Atencion_origen'                => $data_sedeorigen['SD_Responsable_origen'],
                                  'SRV_Sede_destino'                   => $json_data[$i]['destino'],
                                  'SRV_Direccion_destino'              => $data_sededestino['SD_Direccion_destino'],
                                  'SRV_Distrito_destino'               => $data_sededestino['SD_Distrito_destino'],
                                  'SRV_Ubigeo_destino'                 => $data_sededestino['SD_Codigo_ubigeo_destino'],
                                  'SRV_Codigo_zona_destino'            => $zona_destino,
                                  'SRV_Atencion_destino'               => $data_sededestino['SD_Responsable_destino'],
                                  'SRV_Descripcion_producto'           => $json_data[$i]['descripcion_producto'],
                                  'SRV_Codigo_conductor'               => $codigo_personal,
                                  'SRV_Placa'                          => $placa_vehiculo,
                                  'SRV_Creado_por'                     => $nombre_cliente,
                                  'SRV_Fecha_creacion'                 => $fecha_creacion,
                                  'SRV_Hora_creacion'                  => $hora_creacion,
                                  'SRV_Actualizado_por'                => $nombre_cliente,
                                  'SRV_Fecha_actualizado'              => $fecha_creacion,
                                  'SRV_Hora_actualizacion'             => $hora_creacion
                              );

                              $horaria = "";
                              if($hora_creacion >= 12){
                                $horaria = " PM";
                                $horario_creado = $hora_creacion.$horaria;
                              }
                              else{
                                $horaria = " AM";
                                $horario_creado = $hora_creacion.$horaria;
                              }

                              $this->ApiModel->POST_Carga_servicios($data_servicios);
                              $fecha_creacion = date("d-m-Y", strtotime($fecha_creacion));
                              echo json_encode(array("mensaje" => "LOS DATOS DEL SERVICIO {$data_servicios['SRV_Orden_servicio']} FUERON INSERTADOS CORRECTAMENTE EL {$fecha_creacion} A LAS {$horario_creado}"));

                            }
                            if(empty($json_data[$i]['origen']) && empty($json_data[$i]['destino'])){

                              $fecha_creacion = date("Y-m-d");
                              $hora_creacion = date("H:i:s");

                              $distrito_origen1 = "";
                              $distrito_destino1 = "";
                              $dataorigen1 = $this->ApiModel->listarDatosdeUbigeo($json_data[$i]['ubigeo_origen']);
                              $distrito_origen1 = $dataorigen1->UBG_Distrito;

                              $datadestino1 = $this->ApiModel->listarDatosdeUbigeo($json_data[$i]['ubigeo_destino']);
                              $distrito_destino1 = $datadestino1->UBG_Distrito;

                              $via_o = strtoupper(rtrim(substr($json_data[$i]['direccion_origen'],strpos($json_data[$i]['direccion_origen'],'.',-5),4)));
                              $nombrevia_o = strtoupper(rtrim(substr($json_data[$i]['direccion_origen'],strpos($json_data[$i]['direccion_origen'],' ',0))));
                              $numerovia_o = strtoupper(ltrim(substr($json_data[$i]['direccion_origen'],strpos($json_data[$i]['direccion_origen'],' ',10))));
                              $departamento_o = $dataorigen1->UBG_Departamento;
                              $provincia_o = $dataorigen1->UBG_Provincia;
                              $distrito_o = $dataorigen1->UBG_Distrito;
                              $ubigeo_o = $dataorigen1->UBG_Cod_ubigeo;
                              $postal_o = $dataorigen1->UBG_Codigo_postal;

                              $correlativo_origen = $this->RegistrarNuevaDireccionOrigen($json_data[$i]['codigo_cliente'],
                              $nombre_cliente, $via_o, $nombrevia_o, $numerovia_o, $departamento_o, $provincia_o, $distrito_o, $ubigeo_o, $postal_o);

                              $via_d = strtoupper(rtrim(substr($json_data[$i]['direccion_destino'],strpos($json_data[$i]['direccion_destino'],'.',-5),4)));
                              $nombrevia_d = strtoupper(rtrim(substr($json_data[$i]['direccion_destino'],strpos($json_data[$i]['direccion_destino'],' ',0))));
                              $numerovia_d = strtoupper(ltrim(substr($json_data[$i]['direccion_destino'],strpos($json_data[$i]['direccion_destino'],' ',10))));
                              $departamento_d = $datadestino1->UBG_Departamento;
                              $provincia_d = $datadestino1->UBG_Provincia;
                              $distrito_d = $datadestino1->UBG_Distrito;
                              $ubigeo_d = $datadestino1->UBG_Cod_ubigeo;
                              $postal_d = $datadestino1->UBG_Codigo_postal;

                              $correlativo_destino = $this->RegistrarNuevaDireccionDestino($json_data[$i]['codigo_cliente'],
                              $nombre_cliente, $via_d, $nombrevia_d, $numerovia_d, $departamento_d, $provincia_d, $distrito_d, $ubigeo_d, $postal_d);

                              $tipo_orden1 = "ECOM";
                              $zona_origen1 = "";
                              $zona_destino1 = "";
                              $zonaecom_origen1    = $dataorigen1->UBG_Zona_ecommerce;
                              $zonaecom_destino1   = $datadestino1->UBG_Zona_ecommerce;

                              if($tipo_orden1 == "ECOM"){
                                  if($zonaecom_origen1 == ""){
                                      $zona_origen1 = "E99";
                                  }
                                  else{
                                      $zona_origen1 = $dataorigen1->UBG_Zona_ecommerce;
                                  }
                                  if($zonaecom_destino1 == ""){
                                      $zona_destino1 = "E99";
                                  }
                                  else{
                                      $zona_destino1  = $datadestino1->UBG_Zona_ecommerce;
                                  }
                              }
                              $codigo_personal1 = "";
                              $placa_vehiculo1 = "";

                              if($zona_destino1 != "E99"){
                                $codigopers = $this->ApiModel->BuscarPersonalxZonaAsignado($zona_destino1);
                                $conductor_asignado = $this->ApiModel->BuscarDatosConductorAsignadoServ($codigopers);

                                if(isset($conductor_asignado->PRS_Cod_personal) && isset($conductor_asignado->VEH_Placa)){
                                  $codigo_personal = $conductor_asignado->PRS_Cod_personal;
                                  $placa_vehiculo  = $conductor_asignado->VEH_Placa;
                                }
                                else{
                                  $codigo_personal = "PE0007";
                                  $placa_vehiculo = "";
                                }
                              }

                              $data_servicios = array(
                                  'SRV_Orden_servicio'                 => $json_data[$i]['orden_servicio'],
                                  'CLI_Cod_cliente'                    => $json_data[$i]['codigo_cliente'],
                                  'SRV_Tipo_orden'                     => "ECOM",
                                  'SRV_Fecha_registro'                 => $json_data[$i]['fecha_registro'],
                                  'SRV_Hora_registro'                  => $json_data[$i]['hora_registro'],
                                  'SRV_Fec_entrega_solicitada'         => $json_data[$i]['fecha_solicitada'],
                                  'SRV_Hora_entrega_solicitada'        => $json_data[$i]['hora_solicitada'],
                                  'SRV_Fecha_entrega'                  => "0000-00-00",
                                  'SRV_Hora_entrega'                   => "0000-00-00",
                                  'SRV_Estado_pedido'                  => "01",
                                  'SRV_Sede_origen'                    => $correlativo_origen,
                                  'SRV_Direccion_origen'               => $json_data[$i]['direccion_origen'],
                                  'SRV_Distrito_origen'                => $distrito_origen1,
                                  'SRV_Ubigeo_origen'                  => $json_data[$i]['ubigeo_origen'],
                                  'SRV_Codigo_zona_origen'             => $zona_origen1,
                                  'SRV_Atencion_origen'                => $json_data[$i]['atencion_origen'],
                                  'SRV_Sede_destino'                   => $correlativo_destino,
                                  'SRV_Direccion_destino'              => $json_data[$i]['direccion_destino'],
                                  'SRV_Distrito_destino'               => $distrito_destino1,
                                  'SRV_Ubigeo_destino'                 => $json_data[$i]['ubigeo_destino'],
                                  'SRV_Codigo_zona_destino'            => $zona_destino1,
                                  'SRV_Atencion_destino'               => $json_data[$i]['atencion_destino'],
                                  'SRV_Descripcion_producto'           => $json_data[$i]['descripcion_producto'],
                                  'SRV_Codigo_conductor'               => $codigo_personal,
                                  'SRV_Placa'                          => $placa_vehiculo,
                                  'SRV_Creado_por'                     => $nombre_cliente,
                                  'SRV_Fecha_creacion'                 => $fecha_creacion,
                                  'SRV_Hora_creacion'                  => $hora_creacion,
                                  'SRV_Actualizado_por'                => $nombre_cliente,
                                  'SRV_Fecha_actualizado'              => $fecha_creacion,
                                  'SRV_Hora_actualizacion'             => $hora_creacion
                              );

                              $horaria = "";
                              if($hora_creacion >= 12){
                                $horaria = " PM";
                                $horario_creado = $hora_creacion.$horaria;
                              }
                              else{
                                $horaria = " AM";
                                $horario_creado = $hora_creacion.$horaria;
                              }

                              $this->ApiModel->POST_Carga_servicios($data_servicios);
                              $fecha_creacion = date("d-m-Y", strtotime($fecha_creacion));
                              echo json_encode(array("mensaje" => "LOS DATOS DEL SERVICIO {$data_servicios['SRV_Orden_servicio']} FUERON INSERTADOS CORRECTAMENTE EL {$fecha_creacion} A LAS {$horario_creado}"));

                            }

                            if(!empty($json_data[$i]['origen']) && empty($json_data[$i]['destino'])){

                              $fecha_creacion = date("Y-m-d");
                              $hora_creacion = date("H:i:s");

                              $datosede_origen   = $this->ApiModel->GET_Sedes($json_data[$i]['origen']);

                              $data_sedeorigen = [
                                  "SD_Direccion_origen"               =>  $datosede_origen->SD_Tipo_via.' '.$datosede_origen->SD_Nombre_via.' '.$datosede_origen->SD_Numero,
                                  "SD_Departamento_origen"            =>  $datosede_origen->SD_Departamento,
                                  "SD_Provincia_origen"               =>  $datosede_origen->SD_Provincia,
                                  "SD_Distrito_origen"                =>  $datosede_origen->SD_Distrito,
                                  "SD_Codigo_ubigeo_origen"           =>  $datosede_origen->SD_Codigo_ubigeo,
                                  "SD_Responsable_origen"             =>  $datosede_origen->SD_Responsable
                              ];

                              $listadozonas_origen = $this->ApiModel->GET_Ubicacion_Zonas($data_sedeorigen['SD_Departamento_origen'], $data_sedeorigen['SD_Provincia_origen'], $data_sedeorigen['SD_Distrito_origen']);

                              $distrito_destino = "";

                              $datadestino = $this->ApiModel->listarDatosdeUbigeo($json_data[$i]['ubigeo_destino']);
                              $distrito_destino = $datadestino1->UBG_Distrito;

                              $via_d = strtoupper(rtrim(substr($json_data[$i]['direccion_destino'],strpos($json_data[$i]['direccion_destino'],'.',-5),4)));
                              $nombrevia_d = strtoupper(rtrim(substr($json_data[$i]['direccion_destino'],strpos($json_data[$i]['direccion_destino'],' ',0))));
                              $numerovia_d = strtoupper(ltrim(substr($json_data[$i]['direccion_destino'],strpos($json_data[$i]['direccion_destino'],' ',10))));
                              $departamento_d = $datadestino1->UBG_Departamento;
                              $provincia_d = $datadestino1->UBG_Provincia;
                              $distrito_d = $datadestino1->UBG_Distrito;
                              $ubigeo_d = $datadestino1->UBG_Cod_ubigeo;
                              $postal_d = $datadestino1->UBG_Codigo_postal;

                              $correlativo_destino = $this->RegistrarNuevaDireccionDestino($json_data[$i]['codigo_cliente'],
                              $nombre_cliente, $via_d, $nombrevia_d, $numerovia_d, $departamento_d, $provincia_d, $distrito_d, $ubigeo_d, $postal_d);

                              $tipo_orden = "ECOM";
                              $zona_origen = "";
                              $zona_destino = "";
                              $zonaecom_origen    = $listadozonas_origen->UBG_Zona_ecommerce;
                              $zonaecom_destino   = $datadestino->UBG_Zona_ecommerce;

                              if($tipo_orden1 == "ECOM"){
                                  if($zonaecom_origen == ""){
                                      $zona_origen = "E99";
                                  }
                                  else{
                                      $zona_origen = $listadozonas_origen->UBG_Zona_ecommerce;
                                  }
                                  if($zonaecom_destino == ""){
                                      $zona_destino = "E99";
                                  }
                                  else{
                                      $zona_destino  = $datadestino->UBG_Zona_ecommerce;
                                  }
                              }

                              $codigo_personal = "";
                              $placa_vehiculo = "";

                              if($zona_destino != "E99"){
                                $codigopers = $this->ApiModel->BuscarPersonalxZonaAsignado($zona_destino);
                                $conductor_asignado = $this->ApiModel->BuscarDatosConductorAsignadoServ($codigopers);

                                if(isset($conductor_asignado->PRS_Cod_personal) && isset($conductor_asignado->VEH_Placa)){
                                  $codigo_personal = $conductor_asignado->PRS_Cod_personal;
                                  $placa_vehiculo  = $conductor_asignado->VEH_Placa;
                                }
                                else{
                                  $codigo_personal = "PE0007";
                                  $placa_vehiculo = "";
                                }
                              }

                              $data_servicios = array(
                                  'SRV_Orden_servicio'                 => $json_data[$i]['orden_servicio'],
                                  'CLI_Cod_cliente'                    => $json_data[$i]['codigo_cliente'],
                                  'SRV_Tipo_orden'                     => "ECOM",
                                  'SRV_Fecha_registro'                 => $json_data[$i]['fecha_registro'],
                                  'SRV_Hora_registro'                  => $json_data[$i]['hora_registro'],
                                  'SRV_Fec_entrega_solicitada'         => $json_data[$i]['fecha_solicitada'],
                                  'SRV_Hora_entrega_solicitada'        => $json_data[$i]['hora_solicitada'],
                                  'SRV_Fecha_entrega'                  => "0000-00-00",
                                  'SRV_Hora_entrega'                   => "0000-00-00",
                                  'SRV_Estado_pedido'                  => "01",
                                  'SRV_Sede_origen'                    => $json_data[$i]['origen'],
                                  'SRV_Direccion_origen'               => $data_sedeorigen['SD_Direccion_origen'],
                                  'SRV_Distrito_origen'                => $data_sedeorigen['SD_Distrito_origen'],
                                  'SRV_Ubigeo_origen'                  => $data_sedeorigen['SD_Codigo_ubigeo_origen'],
                                  'SRV_Codigo_zona_origen'             => $zona_origen,
                                  'SRV_Atencion_origen'                => $data_sedeorigen['SD_Responsable_origen'],
                                  'SRV_Sede_destino'                   => $correlativo_destino,
                                  'SRV_Direccion_destino'              => $json_data[$i]['direccion_destino'],
                                  'SRV_Distrito_destino'               => $distrito_destino,
                                  'SRV_Ubigeo_destino'                 => $json_data[$i]['ubigeo_destino'],
                                  'SRV_Codigo_zona_destino'            => $zona_destino,
                                  'SRV_Atencion_destino'               => $json_data[$i]['atencion_destino'],
                                  'SRV_Descripcion_producto'           => $json_data[$i]['descripcion_producto'],
                                  'SRV_Codigo_conductor'               => $codigo_personal,
                                  'SRV_Placa'                          => $placa_vehiculo,
                                  'SRV_Creado_por'                     => $nombre_cliente,
                                  'SRV_Fecha_creacion'                 => $fecha_creacion,
                                  'SRV_Hora_creacion'                  => $hora_creacion,
                                  'SRV_Actualizado_por'                => $nombre_cliente,
                                  'SRV_Fecha_actualizado'              => $fecha_creacion,
                                  'SRV_Hora_actualizacion'             => $hora_creacion
                              );

                              $horaria = "";
                              if($hora_creacion >= 12){
                                $horaria = " PM";
                                $horario_creado = $hora_creacion.$horaria;
                              }
                              else{
                                $horaria = " AM";
                                $horario_creado = $hora_creacion.$horaria;
                              }

                              $this->ApiModel->POST_Carga_servicios($data_servicios);
                              $fecha_creacion = date("d-m-Y", strtotime($fecha_creacion));
                              echo json_encode(array("mensaje" => "LOS DATOS DEL SERVICIO {$data_servicios['SRV_Orden_servicio']} FUERON INSERTADOS CORRECTAMENTE EL {$fecha_creacion} A LAS {$horario_creado}"));

                            }

                            if(empty($json_data[$i]['origen']) && !empty($json_data[$i]['destino'])){

                              $fecha_creacion = date("Y-m-d");
                              $hora_creacion = date("H:i:s");

                              $distrito_origen = "";

                              $dataorigen = $this->ApiModel->listarDatosdeUbigeo($json_data[$i]['ubigeo_origen']);
                              $distrito_origen = $dataorigen->UBG_Distrito;

                              $datosede_destino   = $this->ApiModel->GET_Sedes($json_data[$i]['destino']);

                              $data_sededestino = [
                                  "SD_Direccion_destino"              =>  $datosede_destino->SD_Tipo_via.' '.$datosede_destino->SD_Nombre_via.' '.$datosede_destino->SD_Numero,
                                  "SD_Departamento_destino"           =>  $datosede_destino->SD_Departamento,
                                  "SD_Provincia_destino"              =>  $datosede_destino->SD_Provincia,
                                  "SD_Distrito_destino"               =>  $datosede_destino->SD_Distrito,
                                  "SD_Codigo_ubigeo_destino"          =>  $datosede_destino->SD_Codigo_ubigeo,
                                  "SD_Responsable_destino"            =>  $datosede_destino->SD_Responsable
                              ];

                              $listadozonas_destino = $this->ApiModel->GET_Ubicacion_Zonas($data_sededestino['SD_Departamento_destino'], $data_sededestino['SD_Provincia_destino'], $data_sededestino['SD_Distrito_destino']);

                              $via_o = strtoupper(rtrim(substr($json_data[$i]['direccion_origen'],strpos($json_data[$i]['direccion_origen'],'.',-5),4)));
                              $nombrevia_o = strtoupper(rtrim(substr($json_data[$i]['direccion_origen'],strpos($json_data[$i]['direccion_origen'],' ',0))));
                              $numerovia_o = strtoupper(ltrim(substr($json_data[$i]['direccion_origen'],strpos($json_data[$i]['direccion_origen'],' ',10))));
                              $departamento_o = $dataorigen->UBG_Departamento;
                              $provincia_o = $dataorigen->UBG_Provincia;
                              $distrito_o = $dataorigen->UBG_Distrito;
                              $ubigeo_o = $dataorigen->UBG_Cod_ubigeo;
                              $postal_o = $dataorigen->UBG_Codigo_postal;

                              $correlativo_origen = $this->RegistrarNuevaDireccionOrigen($json_data[$i]['codigo_cliente'],
                              $nombre_cliente, $via_o, $nombrevia_o, $numerovia_o, $departamento_o, $provincia_o, $distrito_o, $ubigeo_o, $postal_o);

                              $tipo_orden = "ECOM";
                              $zona_origen = "";
                              $zona_destino = "";
                              $zonaecom_origen    = $dataorigen->UBG_Zona_ecommerce;
                              $zonaecom_destino   = $listadozonas_destino->UBG_Zona_ecommerce;

                              if($tipo_orden == "ECOM"){
                                  if($zonaecom_origen == ""){
                                      $zona_origen = "E99";
                                  }
                                  else{
                                      $zona_origen = $dataorigen->UBG_Zona_ecommerce;
                                  }
                                  if($zonaecom_destino == ""){
                                      $zona_destino = "E99";
                                  }
                                  else{
                                      $zona_destino  = $listadozonas_destino->UBG_Zona_ecommerce;
                                  }
                              }

                              $codigo_personal = "";
                              $placa_vehiculo = "";

                              if($zona_destino != "E99"){
                                $codigopers = $this->ApiModel->BuscarPersonalxZonaAsignado($zona_destino);
                                $conductor_asignado = $this->ApiModel->BuscarDatosConductorAsignadoServ($codigopers);

                                if(isset($conductor_asignado->PRS_Cod_personal) && isset($conductor_asignado->VEH_Placa)){
                                  $codigo_personal = $conductor_asignado->PRS_Cod_personal;
                                  $placa_vehiculo  = $conductor_asignado->VEH_Placa;
                                }
                                else{
                                  $codigo_personal = "PE0007";
                                  $placa_vehiculo = "";
                                }
                              }

                              $data_servicios = array(
                                  'SRV_Orden_servicio'                 => $json_data[$i]['orden_servicio'],
                                  'CLI_Cod_cliente'                    => $json_data[$i]['codigo_cliente'],
                                  'SRV_Tipo_orden'                     => "ECOM",
                                  'SRV_Fecha_registro'                 => $json_data[$i]['fecha_registro'],
                                  'SRV_Hora_registro'                  => $json_data[$i]['hora_registro'],
                                  'SRV_Fec_entrega_solicitada'         => $json_data[$i]['fecha_solicitada'],
                                  'SRV_Hora_entrega_solicitada'        => $json_data[$i]['hora_solicitada'],
                                  'SRV_Fecha_entrega'                  => "0000-00-00",
                                  'SRV_Hora_entrega'                   => "0000-00-00",
                                  'SRV_Estado_pedido'                  => "01",
                                  'SRV_Sede_origen'                    => $correlativo_origen,
                                  'SRV_Direccion_origen'               => $json_data[$i]['direccion_origen'],
                                  'SRV_Distrito_origen'                => $distrito_origen,
                                  'SRV_Ubigeo_origen'                  => $json_data[$i]['ubigeo_origen'],
                                  'SRV_Codigo_zona_origen'             => $zona_origen,
                                  'SRV_Atencion_origen'                => $json_data[$i]['atencion_origen'],
                                  'SRV_Sede_destino'                   => $json_data[$i]['destino'],
                                  'SRV_Direccion_destino'              => $data_sededestino['SD_Direccion_destino'],
                                  'SRV_Distrito_destino'               => $data_sededestino['SD_Distrito_destino'],
                                  'SRV_Ubigeo_destino'                 => $data_sededestino['SD_Codigo_ubigeo_destino'],
                                  'SRV_Codigo_zona_destino'            => $zona_destino,
                                  'SRV_Atencion_destino'               => $data_sededestino['SD_Responsable_destino'],
                                  'SRV_Descripcion_producto'           => $json_data[$i]['descripcion_producto'],
                                  'SRV_Codigo_conductor'               => $codigo_personal,
                                  'SRV_Placa'                          => $placa_vehiculo,
                                  'SRV_Creado_por'                     => $nombre_cliente,
                                  'SRV_Fecha_creacion'                 => $fecha_creacion,
                                  'SRV_Hora_creacion'                  => $hora_creacion,
                                  'SRV_Actualizado_por'                => $nombre_cliente,
                                  'SRV_Fecha_actualizado'              => $fecha_creacion,
                                  'SRV_Hora_actualizacion'             => $hora_creacion
                              );

                              $horaria = "";
                              if($hora_creacion >= 12){
                                $horaria = " PM";
                                $horario_creado = $hora_creacion.$horaria;
                              }
                              else{
                                $horaria = " AM";
                                $horario_creado = $hora_creacion.$horaria;
                              }

                              $this->ApiModel->POST_Carga_servicios($data_servicios);
                              $fecha_creacion = date("d-m-Y", strtotime($fecha_creacion));
                              echo json_encode(array("mensaje" => "LOS DATOS DEL SERVICIO {$data_servicios['SRV_Orden_servicio']} FUERON INSERTADOS CORRECTAMENTE EL {$fecha_creacion} A LAS {$horario_creado}"));

                            }
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

     public function RegistrarNuevaDireccionOrigen($codigocliente, $nomcliente, $via, $nombrevia, $numerovia, $departamento, $provincia, $distrito, $ubigeo, $postal){

       date_default_timezone_set("America/Lima");
       $fecha_creado = date("Y-m-d");
       $hora_creado = date("H:i:s");
       $cliente = $codigocliente;
       $codigo = "";
       $correlativo = substr($cliente,2);
       $codigo_lugar = $correlativo.'001';
       $correlativo = $this->ApiModel->CodigoUbicacion($codigo_lugar);
       if(empty($correlativo)){
         $codigo = $codigo_lugar;
       }
       else{
         $codcliente = substr($cliente,2);
         $valor = $this->ApiModel->CorrelativoUbicacion($codcliente);
         $codigo = $codcliente.$valor;
       }
       $datasd = [
           'CLI_Cod_cliente'                    => $codigocliente,
           'SD_Codigo_sede'                     => $codigo,
           'SD_Nombre_sede'                     => "Lugar",
           'SD_Tipo_sede'                       => "12",
           'SD_Responsable'                     => "Sin responsable",
           'SD_Tipo_via'                        => $via,
           'SD_Nombre_via'                      => $nombrevia,
           'SD_Numero'                          => $numerovia,
           'SD_Departamento'                    => $departamento,
           'SD_Provincia'                       => $provincia,
           'SD_Distrito'                        => $distrito,
           'SD_Codigo_ubigeo'                   => $ubigeo,
           'SD_Codigo_postal'                   => $postal,
           'SD_Creado_por'                      => $nomcliente,
           'SD_Fecha_creado'                    => $fecha_creado,
           'SD_Hora_creado'                     => $hora_creado,
           'SD_Actualizado_por'                 => $nomcliente,
           'SD_Fecha_actualizado'               => $fecha_creado,
           'SD_Hora_actualizado'                => $hora_creado,
           'SD_FlagSede'                        => "2"
       ];

       $this->ApiModel->InsertarSedesCliente($datasd);
       return $codigo;

     }

     public function RegistrarNuevaDireccionDestino($codigocliente, $nomcliente, $via, $nombrevia, $numerovia, $departamento, $provincia, $distrito, $ubigeo, $postal){

       date_default_timezone_set("America/Lima");
       $fecha_creado = date("Y-m-d");
       $hora_creado = date("H:i:s");
       $cliente = $codigocliente;
       $codigo = "";
       $correlativo = substr($cliente,2);
       $codigo_lugar = $correlativo.'001';
       $correlativo = $this->ApiModel->CodigoUbicacion($codigo_lugar);
       if(empty($correlativo)){
         $codigo = $codigo_lugar;
       }
       else{
         $codcliente = substr($cliente,2);
         $valor = $this->ApiModel->CorrelativoUbicacion($codcliente);
         $codigo = $codcliente.$valor;
       }
       $datasd = [
           'CLI_Cod_cliente'                    => $codigocliente,
           'SD_Codigo_sede'                     => $codigo,
           'SD_Nombre_sede'                     => "Lugar",
           'SD_Tipo_sede'                       => "12",
           'SD_Responsable'                     => "Sin responsable",
           'SD_Tipo_via'                        => $via,
           'SD_Nombre_via'                      => $nombrevia,
           'SD_Numero'                          => $numerovia,
           'SD_Departamento'                    => $departamento,
           'SD_Provincia'                       => $provincia,
           'SD_Distrito'                        => $distrito,
           'SD_Codigo_ubigeo'                   => $ubigeo,
           'SD_Codigo_postal'                   => $postal,
           'SD_Creado_por'                      => $nomcliente,
           'SD_Fecha_creado'                    => $fecha_creado,
           'SD_Hora_creado'                     => $hora_creado,
           'SD_Actualizado_por'                 => $nomcliente,
           'SD_Fecha_actualizado'               => $fecha_creado,
           'SD_Hora_actualizado'                => $hora_creado,
           'SD_FlagSede'                        => "2"
       ];

       $this->ApiModel->InsertarSedesCliente($datasd);
       return $codigo;

     }

}
