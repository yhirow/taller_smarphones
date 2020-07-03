<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2014-2017    Carlos Garcia Gomez  neorazorx@gmail.com
 * Copyright (C) 2014-2015    Francesc Pineda Segarra  shawe.ewahs@gmail.com
 * Copyright (C) 2015-2017    Luis Miguel Pérez Romero  luismipr@gmail.com
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

require_once 'plugins/facturacion_base/extras/fbase_controller.php';

class ventas_servicio extends fbase_controller
{

    public $agente;
    public $albaranserv;
    public $cliente;
    public $cliente_s;
    public $divisa;
    public $ejercicio;
    public $estado;
    public $fabricante;
    public $familia;
    public $forma_pago;
    public $impuesto;
    public $nuevo_servicio_url;
    public $pais;
    public $servicio;
    public $serie;
    public $setup;
    public $historico;
    public $factura;

    public function __construct()
    {
        parent::__construct(__CLASS__, ucfirst(FS_SERVICIO), 'ventas', FALSE, FALSE);
    }

    protected function private_core()
    {
        parent::private_core();
        $this->ppage = $this->page->get('ventas_servicios');

        $this->agente = new agente();
        $this->albaranserv = FALSE;
        $this->cliente = new cliente();
        $this->cliente_s = FALSE;
        $this->divisa = new divisa();
        $this->ejercicio = new ejercicio();
        $this->estado = new estado_servicio();
        $this->fabricante = new fabricante();
        $this->familia = new familia();
        $this->forma_pago = new forma_pago();
        $this->impuesto = new impuesto();
        $this->pais = new pais();
        $this->serie = new serie();

        $this->cargar_config();

        /**
         * Comprobamos si el usuario tiene acceso a nueva_venta,
         * necesario para poder añadir líneas.
         */
        $this->nuevo_servicio_url = FALSE;
        if ($this->user->have_access_to('nueva_venta', FALSE)) {
            $nuevoserv = $this->page->get('nueva_venta');
            if ($nuevoserv) {
                $this->nuevo_servicio_url = $nuevoserv->url();
            }
        }

        //NUEVO?
        if (isset($_REQUEST['nuevo'])) {
            $this->new_message('Nuevo servicio creado correctamente');
        }

        $this->servicio = FALSE;
        $servicio = new servicio_cliente();
        if (isset($_POST['idservicio'])) {
            $this->servicio = $servicio->get($_POST['idservicio']);
            $this->modificar();
        } else if (isset($_GET['id'])) {
            $this->servicio = $servicio->get($_GET['id']);
        }

        if ($this->servicio) {
            $this->page->title = $this->servicio->codigo;

            /// cargamos el agente
            if ($this->servicio->codagente) {
                $age0 = new agente();
                $this->agente = $age0->get($this->servicio->codagente);
                if (!$this->agente) {
                    $this->agente = new agente();
                }
            } else {
                $this->agente = $this->user->get_agente();
            }

            /// cargamos el cliente
            $this->cliente_s = $this->cliente->get($this->servicio->codcliente);

            if (isset($_GET['genalbaran']) && !$this->servicio->idalbaran) {
                $this->generar_albaran();
            }

            $this->modificar_detalles();
            $this->get_historico();
        } else {
            $this->new_error_msg("¡" . ucfirst(FS_SERVICIO) . " de cliente no encontrado!");
        }
    }

    private function cargar_config()
    {
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
            'cal_inicio' => "09:00",
            'cal_fin' => "20:00",
            'cal_intervalo' => "30",
            'servicios_linea' => 0,
            'servicios_linea1' => 0,
            'servicios_material_linea' => 0,            
            'servicios_material_estado_linea' => 0,
            'servicios_tipomarca_linea' => 0,
            'servicios_nummodelo_linea' => 0,
            'servicios_tipocolor_linea' => 0,
            'servicios_imei1_linea' => 0,
            'servicios_imei2_linea' => 0,
            'servicios_equipo_linea' => 0,
            'servicios_condequipo_linea' => 0,
            'servicios_reparacion_linea' => 0,
            'servicios_esp_reparacion_linea' => 0,
            'servicios_operadortel_linea' => 0,
            'servicios_lcd_linea' => 0,
            'servicios_tipotouch_linea' => 0,
            'servicios_tapa_linea' => 0,
            'servicios_marco_linea' => 0,
            'servicios_tipoboton_linea' => 0,
            'servicios_bateria_linea' => 0,
            'servicios_lentecamara_linea' => 0,
            'servicios_tornillos_linea' => 0,
            'servicios_status_linea' => 0,
            'servicios_accesorios_linea' => 0,
            'servicios_descripcion_linea' => 0,
            'servicios_solucion_linea' => 0,
            'servicios_fechainicio_linea' => 0,
            'servicios_fechafin_linea' => 0,
            'servicios_garantia_linea' => 0,
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
            'st_garantia' => "Garantía"
            ), FALSE
        );
    }

    public function url()
    {
        if (!isset($this->servicio)) {
            return parent::url();
        } else if ($this->servicio) {
            return $this->servicio->url();
        }

        return $this->page->url();
    }

    private function modificar()
    {
        $this->servicio->observaciones = $_POST['observaciones'];

        if (isset($_POST['numero2'])) {
            $this->servicio->numero2 = $_POST['numero2'];
        }

        $this->servicio->codagente = $_POST['codagente'];
        $this->servicio->estado = $_POST['estado'];
        $this->servicio->codpago = $_POST['codpago'];

        if (isset($_POST['material'])) {
            $this->servicio->material = $_POST['material'];
        }

        if (isset($_POST['material_estado'])) {
            $this->servicio->material_estado = $_POST['material_estado'];
        }

        if (isset($_POST['tipomarca'])) {
            $this->servicio->tipomarca = $_POST['tipomarca'];
        }

        if (isset($_POST['nummodelo'])) {
            $this->servicio->nummodelo = $_POST['nummodelo'];
        }

        if (isset($_POST['tipocolor'])) {
            $this->servicio->tipocolor = $_POST['tipocolor'];
        }

        if (isset($_POST['imei1'])) {
            $this->servicio->imei1 = $_POST['imei1'];
        }

        if (isset($_POST['imei2'])) {
            $this->servicio->imei2 = $_POST['imei2'];
        }

        if (isset($_POST['equipo'])) {
            $this->servicio->equipo = $_POST['equipo'];
        }

        if (isset($_POST['condequipo'])) {
            $this->servicio->condequipo = $_POST['condequipo'];
        }

        if (isset($_POST['reparacion'])) {
            $this->servicio->reparacion = $_POST['reparacion'];
        }

        if (isset($_POST['esp_reparacion'])) {
            $this->servicio->esp_reparacion = $_POST['esp_reparacion'];
        }
        if (isset($_POST['operadortel'])) {
            $this->servicio->operadortel = $_POST['operadortel'];
        }
        if (isset($_POST['lcd'])) {
            $this->servicio->lcd = $_POST['lcd'];
        }
        if (isset($_POST['tipotouch'])) {
            $this->servicio->tipotouch = $_POST['tipotouch'];
        }
        if (isset($_POST['tapa'])) {
            $this->servicio->tapa = $_POST['tapa'];
        }
        if (isset($_POST['marco'])) {
            $this->servicio->marco = $_POST['marco'];
        }
        if (isset($_POST['tipoboton'])) {
            $this->servicio->tipoboton = $_POST['tipoboton'];
        }
        if (isset($_POST['bateria'])) {
            $this->servicio->bateria = $_POST['bateria'];
        }
        if (isset($_POST['lentecamara'])) {
            $this->servicio->lentecamara = $_POST['lentecamara'];
        }
        if (isset($_POST['tornillos'])) {
            $this->servicio->tornillos = $_POST['tornillos'];
        }
        if (isset($_POST['status'])) {
            $this->servicio->status = $_POST['status'];
        }

        if (isset($_POST['accesorios'])) {
            $this->servicio->accesorios = $_POST['accesorios'];
        }

        if (isset($_POST['descripcion'])) {
            $this->servicio->descripcion = $_POST['descripcion'];
        }

        if (isset($_POST['solucion'])) {
            $this->servicio->solucion = $_POST['solucion'];
        }

        $this->servicio->fechainicio = Date('d-m-Y H:i');
        if (isset($_POST['fechainicio'])) {
            $this->servicio->fechainicio = $_POST['fechainicio'];
        }

        if (isset($_POST['fechafin'])) {
            $this->servicio->fechafin = $_POST['fechafin'];
        } else {
            $this->servicio->fechafin = date('Y-m-d H:i', strtotime($this->servicio->fechainicio . '+ ' . $this->setup['cal_intervalo'] . 'minutes'));
        }

        if ($this->servicio->editable()) {
            $this->servicio->garantia = isset($_POST['garantia']);
            $this->servicio->prioridad = $_POST['prioridad'];

            /// si no hay codejercicio; lo buscamos:
            if (!$this->servicio->codejercicio) {
                $ejercicio = $this->ejercicio->get_by_fecha($this->servicio->fecha);
                if ($ejercicio) {
                    $this->servicio->codejercicio = $ejercicio->codejercicio;
                }
            }

            /// obtenemos los datos del ejercicio para acotar la fecha
            $eje0 = $this->ejercicio->get($this->servicio->codejercicio);
            if ($eje0) {
                $this->servicio->fecha = $eje0->get_best_fecha($_POST['fecha'], TRUE);
                $this->servicio->hora = $_POST['hora'];
            } else {
                $this->new_error_msg('No se encuentra el ejercicio asociado al ' . FS_SERVICIO);
            }

            /// ¿cambiamos el cliente?
            if ($_POST['cliente'] != $this->servicio->codcliente || $this->servicio->cifnif == '') {
                if (isset($_POST['cliente'])) {
                    $cliente = $this->cliente->get($_POST['cliente']);
                } else {
                    $cliente = $this->servicio->codcliente;
                }

                if ($cliente) {
                    foreach ($cliente->get_direcciones() as $d) {
                        if ($d->domfacturacion) {
                            $this->servicio->codcliente = $cliente->codcliente;
                            $this->servicio->cifnif = $cliente->cifnif;
                            $this->servicio->nombrecliente = $cliente->razonsocial;
                            $this->servicio->apartado = $d->apartado;
                            $this->servicio->ciudad = $d->ciudad;
                            $this->servicio->coddir = $d->id;
                            $this->servicio->codpais = $d->codpais;
                            $this->servicio->codpostal = $d->codpostal;
                            $this->servicio->direccion = $d->direccion;
                            $this->servicio->provincia = $d->provincia;
                            break;
                        }
                    }
                } else {
                    die('No se ha encontrado el cliente.');
                }
            } else {
                $this->servicio->nombrecliente = $_POST['nombrecliente'];
                $this->servicio->cifnif = $_POST['cifnif'];
                $this->servicio->codpais = $_POST['codpais'];
                $this->servicio->provincia = $_POST['provincia'];
                $this->servicio->ciudad = $_POST['ciudad'];
                $this->servicio->codpostal = $_POST['codpostal'];
                $this->servicio->direccion = $_POST['direccion'];

                $cliente = $this->cliente->get($this->servicio->codcliente);
            }

            $serie = $this->serie->get($this->servicio->codserie);

            /// ¿cambiamos la serie?
            if ($_POST['serie'] != $this->servicio->codserie) {
                $serie2 = $this->serie->get($_POST['serie']);
                if ($serie2) {
                    $this->servicio->codserie = $serie2->codserie;
                    $this->servicio->new_codigo();

                    $serie = $serie2;
                }
            }

            /// ¿Cambiamos la divisa?
            if ($_POST['divisa'] != $this->servicio->coddivisa) {
                $divisa = $this->divisa->get($_POST['divisa']);
                if ($divisa) {
                    $this->servicio->coddivisa = $divisa->coddivisa;
                    $this->servicio->tasaconv = $divisa->tasaconv;
                }
            } else if ($_POST['tasaconv'] != '') {
                $this->servicio->tasaconv = floatval($_POST['tasaconv']);
            }

            if (!$this->servicio->idalbaran) {
                if (isset($_POST['numlineas'])) {
                    $numlineas = intval($_POST['numlineas']);

                    $this->servicio->neto = 0;
                    $this->servicio->totaliva = 0;
                    $this->servicio->totalirpf = 0;
                    $this->servicio->totalrecargo = 0;
                    $this->servicio->irpf = 0;

                    $lineas = $this->servicio->get_lineas();
                    $articulo = new articulo();

                    /// eliminamos las líneas que no encontremos en el $_POST
                    foreach ($lineas as $l) {
                        $encontrada = FALSE;
                        for ($num = 0; $num <= $numlineas; $num++) {
                            if (isset($_POST['idlinea_' . $num]) && $l->idlinea == intval($_POST['idlinea_' . $num])) {
                                $encontrada = TRUE;
                                break;
                            }
                        }
                        if (!$encontrada && !$l->delete()) {
                            $this->new_error_msg("¡Imposible eliminar la línea del artículo " . $l->referencia . "!");
                        }
                    }
                }


                $regimeniva = 'general';
                if ($cliente) {
                    $regimeniva = $cliente->regimeniva;
                }

                /// modificamos y/o añadimos las demás líneas
                for ($num = 0; $num <= $numlineas; $num++) {
                    $encontrada = FALSE;
                    if (isset($_POST['idlinea_' . $num])) {
                        foreach ($lineas as $k => $value) {
                            /// modificamos la línea
                            if ($value->idlinea == intval($_POST['idlinea_' . $num])) {
                                $encontrada = TRUE;
                                $lineas[$k]->cantidad = floatval($_POST['cantidad_' . $num]);
                                $lineas[$k]->pvpunitario = floatval($_POST['pvp_' . $num]);
                                $lineas[$k]->dtopor = floatval(fs_filter_input_post('dto_' . $num, 0));
                                $lineas[$k]->pvpsindto = $value->cantidad * $value->pvpunitario;
                                $lineas[$k]->pvptotal = $value->cantidad * $value->pvpunitario * (100 - $value->dtopor) / 100;
                                $lineas[$k]->descripcion = $_POST['desc_' . $num];

                                $lineas[$k]->codimpuesto = NULL;
                                $lineas[$k]->iva = 0;
                                $lineas[$k]->recargo = 0;
                                $lineas[$k]->irpf = floatval(fs_filter_input_post('irpf_' . $num, 0));
                                if (!$serie->siniva && $regimeniva != 'Exento') {
                                    $imp0 = $this->impuesto->get_by_iva($_POST['iva_' . $num]);
                                    if ($imp0) {
                                        $lineas[$k]->codimpuesto = $imp0->codimpuesto;
                                    }

                                    $lineas[$k]->iva = floatval($_POST['iva_' . $num]);
                                    $lineas[$k]->recargo = floatval(fs_filter_input_post('recargo_' . $num, 0));
                                }

                                if ($lineas[$k]->save()) {
                                    if ($value->irpf > $this->servicio->irpf) {
                                        $this->servicio->irpf = $value->irpf;
                                    }
                                } else {
                                    $this->new_error_msg("¡Imposible modificar la línea del artículo " . $value->referencia . "!");
                                }

                                break;
                            }
                        }

                        /// añadimos la línea
                        if (!$encontrada && intval($_POST['idlinea_' . $num]) == -1 && isset($_POST['referencia_' . $num])) {
                            $linea = new linea_servicio_cliente();
                            $linea->idservicio = $this->servicio->idservicio;
                            $linea->descripcion = $_POST['desc_' . $num];

                            if (!$serie->siniva && $regimeniva != 'Exento') {
                                $imp0 = $this->impuesto->get_by_iva($_POST['iva_' . $num]);
                                if ($imp0) {
                                    $linea->codimpuesto = $imp0->codimpuesto;
                                }

                                $linea->iva = floatval($_POST['iva_' . $num]);
                                $linea->recargo = floatval(fs_filter_input_post('recargo_' . $num, 0));
                            }

                            $linea->irpf = floatval(fs_filter_input_post('irpf_' . $num, 0));
                            $linea->cantidad = floatval($_POST['cantidad_' . $num]);
                            $linea->pvpunitario = floatval($_POST['pvp_' . $num]);
                            $linea->dtopor = floatval(fs_filter_input_post('dto_' . $num, 0));
                            $linea->pvpsindto = ($linea->cantidad * $linea->pvpunitario);
                            $linea->pvptotal = ($linea->cantidad * $linea->pvpunitario * (100 - $linea->dtopor) / 100);

                            $art0 = $articulo->get($_POST['referencia_' . $num]);
                            if ($art0) {
                                $linea->referencia = $art0->referencia;
                                if ($_POST['codcombinacion_' . $num]) {
                                    $linea->codcombinacion = $_POST['codcombinacion_' . $num];
                                }
                            }

                            if ($linea->save()) {
                                if ($linea->irpf > $this->servicio->irpf) {
                                    $this->servicio->irpf = $linea->irpf;
                                }
                            } else {
                                $this->new_error_msg("¡Imposible guardar la línea del artículo " . $linea->referencia . "!");
                            }
                        }
                    }
                }

                /// obtenemos los subtotales por impuesto
                foreach ($this->fbase_get_subtotales_documento($this->servicio->get_lineas()) as $subt) {
                    $this->servicio->neto += $subt['neto'];
                    $this->servicio->totaliva += $subt['iva'];
                    $this->servicio->totalirpf += $subt['irpf'];
                    $this->servicio->totalrecargo += $subt['recargo'];
                }

                $this->servicio->total = round($this->servicio->neto + $this->servicio->totaliva - $this->servicio->totalirpf + $this->servicio->totalrecargo, FS_NF0);

                if (abs(floatval($_POST['atotal']) - $this->servicio->total) >= .02) {
                    $this->new_error_msg("El total difiere entre el controlador y la vista (" . $this->servicio->total .
                        " frente a " . $_POST['atotal'] . "). Debes informar del error.");
                }
            }
        }

        if ($this->servicio->save()) {
            $this->new_message(ucfirst(FS_SERVICIO) . " modificado correctamente.");
            $this->new_change(ucfirst(FS_SERVICIO) . ' Cliente ' . $this->servicio->codigo, $this->servicio->url());
        } else {
            $this->new_error_msg("¡Imposible modificar el " . FS_SERVICIO . "!");
        }

        if ($this->servicio->idestado != $_POST['estado']) {
            /// si tiene el mismo estado no tiene que hacer nada sino tiene que añadir un detalle
            $this->servicio->idestado = $_POST['estado'];
            $this->agrega_detalle_estado($_POST['estado']);

            foreach ($this->estado->all() as $est) {
                if ($est->id == $this->servicio->idestado) {
                    if ($est->albaran) {
                        if (!$this->servicio->idalbaran) {
                            $this->generar_albaran();
                        } else {
                            $this->new_error_msg('Este ' . FS_SERVICIO . ' ya tiene <a href="index.php?page=ventas_albaran&id='
                                . $this->servicio->idalbaran . '">' . FS_ALBARAN . ' </a> generado');
                        }
                    }
                    break;
                }
            }

            $this->servicio->save();
        }
    }

    private function modificar_detalles()
    {
        if (isset($_POST['detalle'])) {
            $this->agrega_detalle();
        } else if (isset($_GET['delete_detalle'])) {
            $det0 = new detalle_servicio();
            $detalle = $det0->get($_GET['delete_detalle']);
            if ($detalle) {
                if ($detalle->delete()) {
                    $this->new_message('Detalle eliminado correctamente.');
                } else {
                    $this->new_error_msg('Error al eliminar el detalle.');
                }
            } else {
                $this->new_error_msg('Detalle no encontrado.');
            }
        }
    }

    private function generar_albaran()
    {
        $albaran = new albaran_cliente();
        $albaran->apartado = $this->servicio->apartado;
        $albaran->cifnif = $this->servicio->cifnif;
        $albaran->ciudad = $this->servicio->ciudad;
        $albaran->codagente = $this->servicio->codagente;

        if ($this->servicio->codalmacen) {
            $albaran->codalmacen = $this->servicio->codalmacen;
        } else {
            $albaran->codalmacen = $this->empresa->codalmacen;
        }

        $albaran->codcliente = $this->servicio->codcliente;
        $albaran->coddir = $this->servicio->coddir;
        $albaran->coddivisa = $this->servicio->coddivisa;
        $albaran->tasaconv = $this->servicio->tasaconv;
        $albaran->codpago = $this->servicio->codpago;
        $albaran->codpais = $this->servicio->codpais;
        $albaran->codpostal = $this->servicio->codpostal;
        $albaran->codserie = $this->servicio->codserie;
        $albaran->direccion = $this->servicio->direccion;
        $albaran->neto = $this->servicio->neto;
        $albaran->netosindto = $this->servicio->neto;
        $albaran->nombrecliente = $this->servicio->nombrecliente;
        $albaran->observaciones = $this->servicio->observaciones;
        $albaran->provincia = $this->servicio->provincia;
        $albaran->total = $this->servicio->total;
        $albaran->totaliva = $this->servicio->totaliva;
        $albaran->irpf = $this->servicio->irpf;
        $albaran->porcomision = $this->servicio->porcomision;
        $albaran->totalirpf = $this->servicio->totalirpf;
        $albaran->totalrecargo = $this->servicio->totalrecargo;

        /**
         * Obtenemos el ejercicio para la fecha de hoy (puede que
         * no sea el mismo ejercicio que el del servicio, por ejemplo
         * si hemos cambiado de año)
         */
        $eje0 = $this->ejercicio->get_by_fecha($albaran->fecha);
        if ($eje0) {
            $albaran->codejercicio = $eje0->codejercicio;
        }

        if (!fs_generar_numero2($albaran)) {
            $albaran->numero2 = $this->servicio->numero2;
        }

        if (!$eje0) {
            $this->new_error_msg("Ejercicio no encontrado.");
        } else if (!$eje0->abierto()) {
            $this->new_error_msg("El ejercicio está cerrado.");
        } else if ($albaran->save()) {
            $this->new_message("El " . FS_ALBARAN . " " . $albaran->codigo . " ha sido creado correctamente.");

            /// cogemos el albaran asociado
            $this->albaranserv = $albaran;

            $continuar = TRUE;
            $art0 = new articulo();
            $i = 0;
            foreach ($this->servicio->get_lineas() as $l) {
                $n = new linea_albaran_cliente();
                $n->idalbaran = $albaran->idalbaran;
                $n->cantidad = $l->cantidad;
                $n->codcombinacion = $l->codcombinacion;
                $n->codimpuesto = $l->codimpuesto;
                $n->descripcion = $l->descripcion;
                if ($i == 0 && $this->setup['servicios_linea'] && $this->setup['servicios_linea1']) {
                    $n->descripcion .= "\n";

                    if ($this->setup['servicios_material_linea']) {
                        $n->descripcion .= $this->setup['st_material'] . ": \n" . $this->servicio->material . "\n\n";
                    }                   
                    if ($this->setup['servicios_material_estado_linea']) {
                        $n->descripcion .= $this->setup['st_material_estado'] . ": \n" . $this->servicio->material_estado . "\n\n";
                    }
                     if ($this->setup['servicios_tipomarca_linea']) {
                        $n->descripcion .= $this->setup['st_tipomarca'] . ": \n" . $this->servicio->tipomarca . "\n\n";
                    }
                     if ($this->setup['servicios_nummodelo_linea']) {
                        $n->descripcion .= $this->setup['st_nummodelo'] . ": \n" . $this->servicio->nummodelo . "\n\n";
                    }
                     if ($this->setup['servicios_tipocolor_linea']) {
                        $n->descripcion .= $this->setup['st_tipocolor'] . ": \n" . $this->servicio->tipocolor . "\n\n";
                    }
                     if ($this->setup['servicios_imei1_linea']) {
                        $n->descripcion .= $this->setup['st_imei1'] . ": \n" . $this->servicio->imei1 . "\n\n";
                    }
                     if ($this->setup['servicios_imei2_linea']) {
                        $n->descripcion .= $this->setup['st_imei2'] . ": \n" . $this->servicio->imei2 . "\n\n";
                    }
                     if ($this->setup['servicios_equipo_linea']) {
                        $n->descripcion .= $this->setup['st_equipo'] . ": \n" . $this->servicio->equipo . "\n\n";
                    }
                     if ($this->setup['servicios_condequipo_linea']) {
                        $n->descripcion .= $this->setup['st_condequipo'] . ": \n" . $this->servicio->condequipo . "\n\n";
                    }
                     if ($this->setup['servicios_reparacion_linea']) {
                        $n->descripcion .= $this->setup['st_reparacion'] . ": \n" . $this->servicio->reparacion . "\n\n";
                    }
                     if ($this->setup['servicios_esp_reparacion_linea']) {
                        $n->descripcion .= $this->setup['st_esp_reparacion'] . ": \n" . $this->servicio->esp_reparacion . "\n\n";
                    }
                    if ($this->setup['servicios_operadortel_linea']) {
                        $n->descripcion .= $this->setup['st_operadortel'] . ": \n" . $this->servicio->operadortel . "\n\n";
                    }
                     if ($this->setup['servicios_lcd_linea']) {
                        $n->descripcion .= $this->setup['st_lcd'] . ": \n" . $this->servicio->lcd . "\n\n";
                    }
                     if ($this->setup['servicios_tipotouch_linea']) {
                        $n->descripcion .= $this->setup['st_tipotouch'] . ": \n" . $this->servicio->tipotouch . "\n\n";
                    }
                     if ($this->setup['servicios_tapa_linea']) {
                        $n->descripcion .= $this->setup['st_tapa'] . ": \n" . $this->servicio->tapa . "\n\n";
                    }
                     if ($this->setup['servicios_marco_linea']) {
                        $n->descripcion .= $this->setup['st_marco'] . ": \n" . $this->servicio->marco . "\n\n";
                    }
                     if ($this->setup['servicios_tipoboton_linea']) {
                        $n->descripcion .= $this->setup['st_tipoboton'] . ": \n" . $this->servicio->tipoboton . "\n\n";
                    }
                     if ($this->setup['servicios_bateria_linea']) {
                        $n->descripcion .= $this->setup['st_bateria'] . ": \n" . $this->servicio->bateria . "\n\n";
                    }
                     if ($this->setup['servicios_lentecamara_linea']) {
                        $n->descripcion .= $this->setup['st_lentecamara'] . ": \n" . $this->servicio->lentecamara . "\n\n";
                    }
                     if ($this->setup['servicios_tornillos_linea']) {
                        $n->descripcion .= $this->setup['st_tornillos'] . ": \n" . $this->servicio->tornillos . "\n\n";
                    }
                     if ($this->setup['servicios_status_linea']) {
                        $n->descripcion .= $this->setup['st_status'] . ": \n" . $this->servicio->status . "\n\n";
                    }
                    if ($this->setup['servicios_accesorios_linea']) {
                        $n->descripcion .= $this->setup['st_accesorios'] . ": \n" . $this->servicio->accesorios . "\n\n";
                    }
                    if ($this->setup['servicios_descripcion_linea']) {
                        $n->descripcion .= $this->setup['st_descripcion'] . ": \n" . $this->servicio->descripcion . "\n\n";
                    }
                    if ($this->setup['servicios_solucion_linea']) {
                        $n->descripcion .= $this->setup['st_solucion'] . ": \n" . $this->servicio->solucion . "\n\n";
                    }
                    if ($this->setup['servicios_fechainicio_linea']) {
                        $n->descripcion .= $this->setup['st_fechainicio'] . ": \n" . $this->servicio->fechainicio . "   ";
                    }
                    if ($this->setup['servicios_fechafin_linea']) {
                        $n->descripcion .= $this->setup['st_fechafin'] . ": \n" . $this->servicio->fechafin . "   ";
                    }
                    if ($this->setup['servicios_garantia_linea']) {
                        $n->descripcion .= $this->setup['st_garantia'] . ": \n" . $this->servicio->garantia . "\n\n";
                    }
                }

                $n->dtopor = $l->dtopor;
                $n->irpf = $l->irpf;
                $n->iva = $l->iva;
                $n->pvpsindto = $l->pvpsindto;
                $n->pvptotal = $l->pvptotal;
                $n->pvpunitario = $l->pvpunitario;
                $n->recargo = $l->recargo;
                $n->referencia = $l->referencia;
                $i++;

                if ($n->save()) {
                    /// descontamos del stock
                    if (!is_null($n->referencia)) {
                        $articulo = $art0->get($n->referencia);
                        if ($articulo) {
                            $articulo->sum_stock($albaran->codalmacen, 0 - $l->cantidad, FALSE, $l->codcombinacion);
                        }
                    }
                } else {
                    $continuar = FALSE;
                    $this->new_error_msg("¡Imposible guardar la línea el artículo " . $n->referencia . "! ");
                    break;
                }
            }

            if ($this->setup['servicios_linea'] && !$this->setup['servicios_linea1']) {
                /// generamos la linea con detalles del servicio
                if ($this->setup['servicios_linea']) {

                    $ns = new linea_albaran_cliente();
                    $ns->idalbaran = $albaran->idalbaran;
                    $ns->cantidad = '0';

                    /// usamos el impuestos por defecto
                    $imp0 = new impuesto();
                    foreach ($imp0->all() as $imp) {
                        if ($imp->is_default()) {
                            $ns->codimpuesto = $imp->codimpuesto;
                            $ns->iva = $imp->iva;
                            break;
                        }
                    }

                    $ns->descripcion = FS_SERVICIO . ": " . $this->servicio->codigo . " Fecha: " . $this->servicio->fecha . "\n";
                    if ($this->setup['servicios_material_linea']) {
                        $ns->descripcion .= $this->setup['st_material'] . ": " . $this->servicio->material . "\n";
                    }                    
                    if ($this->setup['servicios_material_estado_linea']) {
                        $ns->descripcion .= $this->setup['st_material_estado'] . ": " . $this->servicio->material_estado . "\n";
                    }
                    if ($this->setup['servicios_tipomarca_linea']) {
                        $ns->descripcion .= $this->setup['st_tipomarca'] . ": " . $this->servicio->tipomarca . "\n";
                    }
                    if ($this->setup['servicios_nummodelo_linea']) {
                        $ns->descripcion .= $this->setup['st_nummodelo'] . ": " . $this->servicio->nummodelo . "\n";
                    }
                    if ($this->setup['servicios_tipocolor_linea']) {
                        $ns->descripcion .= $this->setup['st_tipocolor'] . ": " . $this->servicio->tipocolor . "\n";
                    }
                    if ($this->setup['servicios_imei1_linea']) {
                        $ns->descripcion .= $this->setup['st_imei1'] . ": " . $this->servicio->imei1 . "\n";
                    }
                    if ($this->setup['servicios_imei2_linea']) {
                        $ns->descripcion .= $this->setup['st_imei2'] . ": " . $this->servicio->imei2 . "\n";
                    }
                    if ($this->setup['servicios_equipo_linea']) {
                        $ns->descripcion .= $this->setup['st_equipo'] . ": " . $this->servicio->equipo . "\n";
                    }
                    if ($this->setup['servicios_condequipo_linea']) {
                        $ns->descripcion .= $this->setup['st_condequipo'] . ": " . $this->servicio->condequipo . "\n";
                    }
                    if ($this->setup['servicios_reparacion_linea']) {
                        $ns->descripcion .= $this->setup['st_reparacion'] . ": " . $this->servicio->reparacion . "\n";
                    }
                    if ($this->setup['servicios_esp_reparacion_linea']) {
                        $ns->descripcion .= $this->setup['st_esp_reparacion'] . ": " . $this->servicio->esp_reparacion . "\n";
                    }
                    if ($this->setup['servicios_operadortel_linea']) {
                        $n->descripcion .= $this->setup['st_operadortel'] . ": " . $this->servicio->operadortel . "\n";
                    }
                     if ($this->setup['servicios_lcd_linea']) {
                        $n->descripcion .= $this->setup['st_lcd'] . ": " . $this->servicio->lcd . "\n";
                    }
                     if ($this->setup['servicios_tipotouch_linea']) {
                        $n->descripcion .= $this->setup['st_tipotouch'] . ": " . $this->servicio->tipotouch . "\n";
                    }
                     if ($this->setup['servicios_tapa_linea']) {
                        $n->descripcion .= $this->setup['st_tapa'] . ": " . $this->servicio->tapa . "\n";
                    }
                     if ($this->setup['servicios_marco_linea']) {
                        $n->descripcion .= $this->setup['st_marco'] . ": " . $this->servicio->marco . "\n";
                    }
                     if ($this->setup['servicios_tipoboton_linea']) {
                        $n->descripcion .= $this->setup['st_tipoboton'] . ": " . $this->servicio->tipoboton . "\n";
                    }
                     if ($this->setup['servicios_bateria_linea']) {
                        $n->descripcion .= $this->setup['st_bateria'] . ": " . $this->servicio->bateria . "\n";
                    }
                     if ($this->setup['servicios_lentecamara_linea']) {
                        $n->descripcion .= $this->setup['st_lentecamara'] . ": " . $this->servicio->lentecamara . "\n";
                    }
                     if ($this->setup['servicios_tornillos_linea']) {
                        $n->descripcion .= $this->setup['st_tornillos'] . ": " . $this->servicio->tornillos . "\n";
                    }
                     if ($this->setup['servicios_status_linea']) {
                        $n->descripcion .= $this->setup['st_status'] . ": " . $this->servicio->status . "\n";
                    }
                    if ($this->setup['servicios_accesorios_linea']) {
                        $ns->descripcion .= $this->setup['st_accesorios'] . ": " . $this->servicio->accesorios . "\n";
                    }
                    if ($this->setup['servicios_descripcion_linea']) {
                        $ns->descripcion .= $this->setup['st_descripcion'] . ": " . $this->servicio->descripcion . "\n";
                    }
                    if ($this->setup['servicios_solucion_linea']) {
                        $ns->descripcion .= $this->setup['st_solucion'] . ": " . $this->servicio->solucion . "\n";
                    }
                    if ($this->setup['servicios_fechainicio_linea']) {
                        $ns->descripcion .= $this->setup['st_fechainicio'] . ": " . $this->servicio->fechainicio . "   ";
                    }
                    if ($this->setup['servicios_fechafin_linea']) {
                        $ns->descripcion .= $this->setup['st_fechafin'] . ": " . $this->servicio->fechafin . "   ";
                    }
                    if ($this->setup['servicios_garantia_linea']) {
                        $ns->descripcion .= $this->setup['st_garantia'] . ": " . $this->servicio->garantia . "\n";
                    }

                    $ns->dtopor = '0';
                    $ns->irpf = '0';
                    $ns->pvpsindto = '0';
                    $ns->pvptotal = '0';
                    $ns->pvpunitario = '0';
                    $ns->recargo = '0';
                    $ns->referencia = '';
                    $ns->save();
                }
            }
            
            if ($continuar) {
                $this->servicio->idalbaran = $albaran->idalbaran;
                $this->servicio->save();
            } else if ($albaran->delete()) {
                $this->new_error_msg("El " . FS_ALBARAN . $albaran->codigo . " se ha borrado.", TRUE);
            } else {
                $this->new_error_msg("¡Imposible borrar el " . FS_ALBARAN . "!");
            }
        } else {
            $this->new_error_msg("¡Imposible guardar el " . FS_ALBARAN . "!");
        }
    }

    public function listar_servicio_detalle()
    {
        $detalle = new detalle_servicio();
        return $detalle->all_from_servicio($this->servicio->idservicio);
    }

    private function agrega_detalle()
    {
        $detalle = new detalle_servicio();
        $detalle->descripcion = $_POST['detalle'];
        $detalle->idservicio = $this->servicio->idservicio;
        $detalle->nick = $this->user->nick;

        if ($detalle->save()) {
            $this->new_message('Detalle guardados correctamente.');
        } else {
            $this->new_error_msg('Imposible guardar el detalle.');
        }
    }

    private function agrega_detalle_estado($id)
    {
        $this->estado = new estado_servicio();
        $estado = $this->estado->get($id);
        if ($estado) {
            $detalle = new detalle_servicio();
            $detalle->descripcion = "Se ha cambiado el estado a: " . $estado->descripcion;
            $detalle->idservicio = $this->servicio->idservicio;
            $detalle->nick = $this->user->nick;

            if ($detalle->save()) {
                $this->new_message('Detalle guardados correctamente.');
            } else {
                $this->new_error_msg('Imposible guardar el detalle.');
            }
        }
    }

    private function get_historico()
    {
        $this->historico = array();
        $orden = 0;
        $this->factura = FALSE;

        if ($this->servicio->idalbaran) {
            /// albaran
            $sql = "SELECT * FROM albaranescli WHERE idalbaran = " . $this->servicio->var2str($this->servicio->idalbaran)
                . " ORDER BY idalbaran ASC;";

            $data = $this->db->select($sql);
            if ($data) {
                foreach ($data as $d) {
                    $albaran = new albaran_cliente($d);
                    $this->historico[] = array(
                        'orden' => $orden,
                        'documento' => FS_ALBARAN,
                        'modelo' => $albaran
                    );
                    $orden++;

                    if ($albaran->idfactura) {
                        /// factura
                        $sql2 = "SELECT * FROM facturascli WHERE idfactura = " . $albaran->var2str($albaran->idfactura)
                            . " ORDER BY idfactura ASC;";

                        $data2 = $this->db->select($sql2);
                        if ($data2) {
                            $this->factura = true;
                            foreach ($data2 as $d2) {
                                $factura = new factura_cliente($d2);
                                $this->historico[] = array(
                                    'orden' => $orden,
                                    'documento' => 'factura',
                                    'modelo' => $factura
                                );
                                $orden++;
                            }
                        }
                    }
                }
            }
        }
    }
}
