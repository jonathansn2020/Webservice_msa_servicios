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

    public function PUT_Carga_servicios($orden, $data){
        $this->db->where($this->table_id, $orden);
        $this->db->update($this->table, $data);
    }

    public function GET_Sedes($idsede){

        $this->db->select('SD_Tipo_via,SD_Nombre_via,SD_Numero,SD_Interior,SD_Departamento, SD_Provincia, SD_Distrito,SD_Codigo_ubigeo,SD_Responsable');
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
}
