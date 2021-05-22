<?php

class ApiModel extends CI_Model{

    private $table = "ms_servicios";
    private $table_id = "SRV_Orden_servicio";

    public function ValidarUsuario($codusu){

        $DB = $this->load->database('sec_db', TRUE);

        $DB->select();
        $DB->from('ms_usuarios u');

        $DB->join('ms_rolesh h', 'h.ROL_Codigo_rol = u.ROL_Codigo_rol');

        $DB->where("USU_Cod_usuario", $codusu);

        $resultado = $DB->get();
        return $resultado->row();

    }

    public function ValidarCodigoServicio($codpedido){
      $resultado = $this->db->query("SELECT SRV_Orden_servicio FROM ms_servicios WHERE SRV_Orden_servicio='$codpedido'");
      $row = $resultado->row();
      if(isset($row->SRV_Orden_servicio)){
        return $row->SRV_Orden_servicio;
      }
    }

    public function POST_Carga_servicios($data){
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function GET_Orden_servicio($orden){
        $sql = $this->db->query("SELECT SRV_Orden_servicio FROM ms_servicios WHERE SRV_Orden_servicio = '$orden'");
        $idservicio = $sql->row();
        if(isset($idservicio->SRV_Orden_servicio)){
            return $idservicio->SRV_Orden_servicio;
        }
    }

    public function GET_Nombre_corrido_cliente($codcliente){
        $sql = $this->db->query("SELECT CLI_Nombre_corrido FROM ms_cliente WHERE CLI_Cod_cliente = '$codcliente'");
        $nombrecli = $sql->row();
        if(isset($nombrecli->CLI_Nombre_corrido)){
            return $nombrecli->CLI_Nombre_corrido;
        }
    }

    public function GET_Email_cliente($codcliente){
        $sql = $this->db->query("SELECT CLI_Email_principal FROM ms_cliente WHERE CLI_Cod_cliente = '$codcliente'");
        $emailcli = $sql->row();
        if(isset($emailcli->CLI_Email_principal)){
            return $emailcli->CLI_Email_principal;
        }
    }

    public function GET_Sedes($idsede){

        $this->db->select('SD_Tipo_via,SD_Nombre_via,SD_Numero,SD_Interior,SD_Departamento, SD_Provincia, SD_Distrito,SD_Codigo_ubigeo,SD_Responsable,SD_Telefono1');
        $this->db->from('ms_sedes');
        $this->db->where('SD_Codigo_sede', $idsede);
        $resultado = $this->db->get();
        return $resultado->row();

    }

    public function GET_Ubicacion_Zonas($id_depa, $id_prov, $id_dist){

        $this->db->select('UBG_Zona_ecommerce, UBG_Zona_guiada');
        $this->db->from('ms_ubigeo');
        $this->db->where("UBG_Departamento=$id_depa AND UBG_Provincia=$id_prov AND UBG_Distrito=$id_dist");
        $this->db->order_by('UBG_Descripcion', 'ASC');
        $resultado = $this->db->get();

        return $resultado->row();
    }

    public function BuscarPersonalxZonaAsignado($codigoespzona){

        $sql = $this->db->query("SELECT TB_Valor_alfa1 FROM ms_tabla WHERE TB_Codigo_especifico = '$codigoespzona'");

        $resultado = $sql->row();

        return $resultado->TB_Valor_alfa1;

    }

    public function BuscarDatosConductorAsignadoServ($idpers){
        $sql = $this->db->query("SELECT p.PRS_Cod_personal, v.VEH_Placa
                                FROM ms_personal p INNER JOIN ms_vehiculos v
                                ON v.PRS_Cod_personal = p.PRS_Cod_personal
                                AND p.PRS_Cod_personal ='$idpers'");

        $resultado = $sql->row();
        return $resultado;
    }

    public function listarDatosdeUbigeo($ubigeo){
      $sql = $this->db->query("SELECT UBG_Zona_ecommerce, UBG_Zona_guiada, UBG_Departamento,
         UBG_Provincia, UBG_Distrito, UBG_Descripcion, UBG_Cod_ubigeo, UBG_Codigo_postal FROM ms_ubigeo WHERE UBG_Cod_ubigeo='$ubigeo'");
      $distrito = $sql->row();
      return $distrito;
    }

    public function CodigoUbicacion($codigo){
        $sql = $this->db->query("SELECT SD_Codigo_sede FROM ms_sedes WHERE SD_Codigo_sede = '$codigo'");
        $resultado = $sql->row();
        if(isset($resultado->SD_Codigo_sede)){
          return $resultado->SD_Codigo_sede;
        }
    }

    public function CorrelativoUbicacion($codigo){
        $sql = $this->db->query("SELECT CONCAT(LPAD((MAX(SUBSTRING(SD_Codigo_sede,5))+1),3,'0')) as 'codigo' FROM ms_sedes WHERE SD_Codigo_sede LIKE '$codigo%'");
        $correlativo = $sql->row();
        if(isset($correlativo->codigo)){
          return $correlativo->codigo;
        }
    }

    public function listarDistritos($idep, $idpro){

        $this->db->select();
        $this->db->from('ms_ubigeo');
        $this->db->where("UBG_Departamento=$idep AND UBG_Provincia=$idpro");
        $this->db->order_by('UBG_Descripcion', 'ASC');
        $resultado = $this->db->get();
        return $resultado->result();

    }
    public function InsertarSedesCliente($datasd){

        $this->db->insert('ms_sedes', $datasd);
        return $this->db->insert_id();

    }
    public function GET_Estado_Pedido($codpedido){
      $this->db->select("SRV_Orden_servicio, SRV_Estado_pedido");
      $this->db->from('ms_servicios');
      $this->db->where("SRV_Orden_servicio", $codpedido);
      $resultado = $this->db->get();
      return $resultado->row();
    }

    public function EstadoOrdenServicio(){

        $this->db->select();
        $this->db->from('ms_tabla');
        $this->db->where('TB_Id_tabla=33 AND TB_Codigo_especifico IS NOT NULL');
        $resultado = $this->db->get();

        return $resultado->result();

    }

}
