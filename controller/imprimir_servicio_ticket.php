<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2016-2017    Carlos Garcia Gomez  neorazorx@gmail.com
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
 * Description of imprimir_servicio_ticket
 *
 * @author carlos
 */
class imprimir_servicio_ticket extends fs_controller
{

    public $cliente;
    public $servicio;
    public $setup;
    public $terminal;
    public $terminales;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Ticket ' . FS_SERVICIO, 'ventas', FALSE, FALSE);
    }

    protected function private_core()
    {
        $this->share_extension();

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
            'st_material' => "Defecto Reportado",
            'st_material_estado' => "Diagnostico del tecnico",
            'st_tipomarca' => "Marca",
            'st_nummodelo' => "Modelo",
            'st_tipocolor' => "Color",
            'st_imei1' => "IMEI 1",
            'st_imei2' => "IMEI 2",
            'st_equipo' => "Tipo de Equipo",
            'st_condequipo' => "Condicion de Equipo",
            'st_reparacion' => "Tipo de Reparacion",
            'st_esp_reparacion' => "Especificacion de Reparacion",
            'st_accesorios' => "Accesorios que entrega",
            'st_descripcion' => "Descripción de la averia",
            'st_solucion' => "Solución",
            'st_fechainicio' => "Fecha de Inicio",
            'st_fechafin' => "Fecha de finalización",
            'st_garantía' => "Garantía"
            ), FALSE
        );

        /// cargamos el servicios
        $this->servicio = FALSE;
        if (isset($_GET['id'])) {
            $serv0 = new servicio_cliente();
            $this->servicio = $serv0->get($_GET['id']);
        }

        $term0 = new terminal_caja();
        $this->terminales = $term0->all();

        $this->terminal = FALSE;
        if (isset($_GET['terminal'])) {
            $this->terminal = $term0->get($_GET['terminal']);
        }

        if ($this->servicio AND $this->terminal) {
            $cli0 = new cliente();
            $this->cliente = $cli0->get($this->servicio->codcliente);

            $numt = $this->terminal->num_tickets;
            while ($numt > 0) {
                $this->imprimir();
                $this->terminal->save();
                $numt--;
            }
        }
    }

    public function url()
    {
        if ($this->servicio) {
            return parent::url() . '&id=' . $this->servicio->idservicio;
        } else {
            return parent::url();
        }
    }

    private function share_extension()
    {
        $fsext = new fs_extension();
        $fsext->name = 'imprimir_ticket';
        $fsext->from = __CLASS__;
        $fsext->to = 'ventas_servicio';
        $fsext->type = 'pdf';
        $fsext->text = 'Ticket ' . FS_SERVICIO;
        $fsext->save();
    }

    private function imprimir()
    {
        $medio = $this->terminal->anchopapel / 2.5;
        $this->terminal->add_linea_big($this->terminal->center_text($this->terminal->sanitize($this->empresa->nombre), $medio) . "\n");

        if ($this->empresa->lema != '') {
            $this->terminal->add_linea($this->terminal->center_text($this->terminal->sanitize($this->empresa->lema)) . "\n\n");
        } else
            $this->terminal->add_linea("\n");

        $this->terminal->add_linea(
            $this->terminal->center_text($this->terminal->sanitize($this->empresa->direccion) . " - " . $this->terminal->sanitize($this->empresa->ciudad)) . "\n"
        );
        $this->terminal->add_linea($this->terminal->center_text(FS_CIFNIF . ": " . $this->empresa->cifnif));
        $this->terminal->add_linea("\n\n");

        if ($this->empresa->horario != '') {
            $this->terminal->add_linea($this->terminal->center_text($this->terminal->sanitize($this->empresa->horario)) . "\n\n");
        }

        $linea = "\n" . ucfirst(FS_SERVICIO) . ": " . $this->servicio->codigo . "\n";
        $linea .= $this->servicio->fecha . " " . Date('H:i', strtotime($this->servicio->hora)) . "\n";
        $this->terminal->add_linea($linea);
        $this->terminal->add_linea("Empleado: " . $this->servicio->codagente . "\n\n");
        $this->terminal->add_linea("Cliente: " . $this->terminal->sanitize($this->servicio->nombrecliente) . "\n");

        if ($this->cliente) {
            if ($this->cliente->telefono1) {
                $this->terminal->add_linea("Tlf: " . $this->terminal->sanitize($this->cliente->telefono1) . "\n");
            }
            if ($this->cliente->telefono2) {
                $this->terminal->add_linea("Tlf 2: " . $this->terminal->sanitize($this->cliente->telefono2) . "\n");
            }
        }

        if ($this->servicio->material) {
            $this->terminal->add_linea($this->setup['st_material'] . ": " . $this->servicio->material . "\n\n");
        }
        
        if ($this->servicio->tipomarca) {
            $this->terminal->add_linea($this->setup['st_tipomarca'] . ": " . $this->servicio->tipomarca . "\n\n");
        }

        if ($this->servicio->nummodelo) {
            $this->terminal->add_linea($this->setup['st_nummodelo'] . ": " . $this->servicio->nummodelo . "\n\n");
        }

        if ($this->servicio->tipocolor) {
            $this->terminal->add_linea($this->setup['st_tipocolor'] . ": " . $this->servicio->tipocolor . "\n\n");
        }

        if ($this->servicio->imei1) {
            $this->terminal->add_linea($this->setup['st_imei1'] . ": " . $this->servicio->imei1 . "\n\n");
        }

        if ($this->servicio->imei2) {
            $this->terminal->add_linea($this->setup['st_imei2'] . ": " . $this->servicio->imei2 . "\n\n");
        }
        
        if ($this->servicio->equipo) {
            $this->terminal->add_linea($this->setup['st_equipo'] . ": " . $this->servicio->equipo . "\n\n");
        }

        if ($this->servicio->condequipo) {
            $this->terminal->add_linea($this->setup['st_condequipo'] . ": " . $this->servicio->condequipo . "\n\n");
        }

        if ($this->servicio->reparacion) {
            $this->terminal->add_linea($this->setup['st_reparacion'] . ": " . $this->servicio->reparacion . "\n\n");
        }

        if ($this->servicio->esp_reparacion) {
            $this->terminal->add_linea($this->setup['st_esp_reparacion'] . ": " . $this->servicio->esp_reparacion . "\n\n");
        }

        if ($this->servicio->accesorios) {
            $this->terminal->add_linea($this->setup['st_accesorios'] . ": " . $this->servicio->accesorios . "\n\n");
        }

        if ($this->servicio->descripcion) {
            $this->terminal->add_linea($this->setup['st_descripcion'] . ": " . $this->terminal->sanitize($this->servicio->descripcion) . "\n");
        }

        $lineas = $this->servicio->get_lineas();
        if ($lineas) {
            $width = $this->terminal->anchopapel - 15;
            $this->terminal->add_linea(
                sprintf("%3s", "Ud.") . " " .
                sprintf("%-" . $width . "s", "Articulo") . " " .
                sprintf("%10s", "TOTAL") . "\n"
            );
            $this->terminal->add_linea(
                sprintf("%3s", "---") . " " .
                sprintf("%-" . $width . "s", substr("--------------------------------------------------------", 0, $width - 1)) . " " .
                sprintf("%10s", "----------") . "\n"
            );
            foreach ($lineas as $col) {
                $linea = sprintf("%3s", $col->cantidad) . " " . sprintf("%-" . $width . "s", substr($this->terminal->sanitize($col->descripcion), 0, $width - 1)) . " " .
                    sprintf("%10s", $this->show_numero($col->total_iva())) . "\n";

                $this->terminal->add_linea($linea);
            }

            $lineaiguales = '';
            for ($i = 0; $i < $this->terminal->anchopapel; $i++) {
                $lineaiguales .= '=';
            }
            $this->terminal->add_linea($lineaiguales . "\n");
            $this->terminal->add_linea(
                'TOTAL A PAGAR: ' . sprintf("%" . ($this->terminal->anchopapel - 15) . "s", $this->show_precio($this->servicio->total, $this->servicio->coddivisa)) . "\n"
            );
            $this->terminal->add_linea($lineaiguales . "\n");
        }

        $this->terminal->add_linea("\n" . $this->setup['servicios_condiciones']);

        $lineaiguales .= "\n\n\n\n\n\n\n\n";
        $this->terminal->add_linea($lineaiguales);
        $this->terminal->cortar_papel();
    }
}
