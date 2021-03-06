<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2015-2017    Carlos Garcia Gomez         neorazorx@gmail.com
 * Copyright (C) 2020         Isai Ramos  isai@pixcel.mx
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of imprimir_rapido
 *
 * @author carlos
 */
class imprimir_rapido_horizontal extends fs_controller
{

    public $agente;
    public $cliente;
    public $servicio;
    public $setup;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Imprimir Rápido Horizontal', 'Servicio', FALSE, FALSE);
    }

    protected function private_core()
    {
        $this->agente = FALSE;
        $this->cliente = FALSE;
        $this->servicio = FALSE;

        /// cargamos la configuración de servicios
        $fsvar = new fs_var();
        $this->setup = $fsvar->array_get(
            array(
            'servicios_diasfin' => 10,
            'servicios_material' => 0,            
            'servicios_mostrar_material' => 0,            
            'servicios_material_estado' => 0,
            'servicios_mostrar_material_estado' => 0,
            'servicios_tipomarca' => 0,
            'servicios_mostrar_tipomarca' => 0,
            'servicios_nummodelo' => 0,
            'servicios_mostrar_nummodelo' => 0,
            'servicios_tipocolor' => 0,
            'servicios_mostrar_tipocolor' => 0,
            'servicios_imei1' => 0,
            'servicios_mostrar_imei1' => 0,
            'servicios_imei2' => 0,
            'servicios_mostrar_imei2' => 0,
            'servicios_equipo' => 0,
            'servicios_mostrar_equipo' => 0,
            'servicios_condequipo' => 0,
            'servicios_mostrar_condequipo' => 0,
            'servicios_reparacion' => 0,
            'servicios_mostrar_reparacion' => 0,
            'servicios_esp_reparacion' => 0,
            'servicios_mostrar_esp_reparacion' => 0,
            'servicios_operadortel' => 0,
            'servicios_mostrar_operadortel' => 0,
            'servicios_lcd' => 0,
            'servicios_mostrar_lcd' => 0,
            'servicios_tipotouch' => 0,
            'servicios_mostrar_tipotouch' => 0,
            'servicios_tapa' => 0,
            'servicios_mostrar_tapa' => 0,
            'servicios_marco' => 0,
            'servicios_mostrar_marco' => 0,
            'servicios_tipoboton' => 0,
            'servicios_mostrar_tipoboton' => 0,
            'servicios_bateria' => 0,
            'servicios_mostrar_bateria' => 0,
            'servicios_lentecamara' => 0,
            'servicios_mostrar_lentecamara' => 0,
            'servicios_tornillos' => 0,
            'servicios_mostrar_tornillos' => 0,
            'servicios_status' => 0,
            'servicios_mostrar_status' => 0,
            'servicios_accesorios' => 0,
            'servicios_mostrar_accesorios' => 0,            
            'servicios_descripcion' => 0,
            'servicios_mostrar_descripcion' => 0,
            'servicios_solucion' => 0,
            'servicios_mostrar_solucion' => 0,
            'servicios_fechafin' => 0,
            'servicios_mostrar_fechafin' => 0,
            'servicios_fechainicio' => 0,
            'servicios_mostrar_fechainicio' => 0,
            'servicios_mostrar_garantia' => 0,
            'servicios_garantia' => 0,
            'servicios_condiciones' => "Condiciones del deposito:\nLos presupuestos realizados tienen una" .
            " validez de 15 días.\nUna vez avisado al cliente para que recoja el producto este dispondrá" .
            " de un plazo máximo de 2 meses para recogerlo, de no ser así y no haber aviso por parte del" .
            " cliente se empezará a cobrar 1 euro al día por gastos de almacenaje.\nLos accesorios y" .
            " productos externos al equipo no especificados en este documento no podrán ser reclamados en" .
            " caso de disconformidad con el técnico.",
            'st_servicio' => "Servicio",
            'st_servicios' => "Servicios",
            'st_material' => "Defecto Reportado por Usuario",
            'st_material_estado' => "Diagnostico Rapido",
            'st_tipomarca' => "Marca",
            'st_nummodelo' => "Modelo",
            'st_tipocolor' => "Color",
            'st_imei1' => "ESN / IMEI 1",
            'st_imei2' => "ESN / IMEI 2",
            'st_equipo' => "Tipo de Equipo",
            'st_condequipo' => "Condicion de Equipo",
            'st_reparacion' => "Tipo de Reparacion",
            'st_esp_reparacion' => "Especificacion de Reparacion",
            'st_operadortel' => "Operador Telefonico",
            'st_lcd' => "Display / LCD",
            'st_tipotouch' => "Touch",
            'st_tapa' => "Tapa",
            'st_marco' => "Marco/Bisel",
            'st_tipoboton' => "Botones",
            'st_bateria' => "Bateria",
            'st_lentecamara' => "Lente Camara",
            'st_tornillos' => "Tornillos",
            'st_status' => "Status",
            'st_accesorios' => "Accesorios que entrega",
            'st_descripcion' => "Descripción de la averia",
            'st_solucion' => "Solución",
            'st_fechainicio' => "Fecha de Inicio",
            'st_fechafin' => "Fecha de finalización",
            'st_garantía' => "Garantía"
            ), FALSE
        );

        if (isset($_REQUEST['id'])) {
            $serv = new servicio_cliente();
            $this->servicio = $serv->get($_REQUEST['id']);
        }

        if ($this->servicio) {
            $this->agente = $this->user->get_agente();

            $cliente = new cliente();
            $this->cliente = $cliente->get($this->servicio->codcliente);
        }

        $this->share_extensions();
    }

    public function listar_prioridad()
    {
        $prioridad = array();

        /**
         * En servicio_servicio::prioridad() nos devuelve un array con todos los prioridades,
         * pero como queremos también el id, pues hay que hacer este bucle para sacarlos.
         */
        foreach ($this->servicio->prioridad() as $i => $value) {
            $prioridad[] = array('id_prioridad' => $i, 'nombre_prioridad' => $value);
        }

        return $prioridad;
    }

    public function condiciones()
    {
        return nl2br($this->setup['servicios_condiciones']);
    }

    private function share_extensions()
    {
        $extensiones = array(
            array(
                'name' => 'imprimir_servicio_sin_detalles_horizontal',
                'page_from' => __CLASS__,
                'page_to' => 'ventas_servicio',
                'type' => 'pdf',
                'text' => '2 ' . ucfirst(FS_SERVICIO) . ' sin líneas en 1 página',
                'params' => ''
            ),
        );
        foreach ($extensiones as $ext) {
            $fsext = new fs_extension($ext);
            if (!$fsext->save()) {
                $this->new_error_msg('Error al guardar la extensión ' . $ext['name']);
            }
        }
    }
}
