<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 8/7/14
 * Time: 7:50 AM
 */

class ParametriPreventivoBusiness {

    public $mc_trasportati;
    public $peso;
    public $sede_logistica;
    public $giorni_deposito;
    public $piani_da_salire = 0;
    public $montaggio;
    public $montaggio_in_locali_preggio;
    public $pagamento_contrassegno;
    public $margine_traslocabile;

    public $lista_voci_extra;

    public $lista_servizi_partenza;
    public $lista_servizi_destinazione;

    public $sconto;

    public $indirizzo_partenza;
    public $indirizzo_destinazione;

    const trazione_fino_sede_loggistica="trazione_fino_sede_loggistica";
    const tariffa_scarico_ricarico_fino_sede_loggistica = "tariffa_scarico_ricarico_fino_sede_loggistica";
    const giacenza = "giacenza";
    const trazione_sede_logistica_cliente_finale_entro_30km = "trazione_sede_logistica_cliente_finale_entro_30km";
    const trazione_sede_logistica_cliente_finale_entro_da_30km_a_200km = "trazione_sede_logistica_cliente_finale_entro_da_30km_a_200km";
    const tariffa_scarico_sede_cliente = "tariffa_scarico_sede_cliente";
    const tariffa_salita_al_piano = "tariffa_salita_al_piano";
    const tariffa_montaggio_sede_cliente = "tariffa_montaggio_sede_cliente";
    const pagamento_assegni = "pagamento_assegni";
    const trazione_mobiliere_sede_cliente = "trazione_mobiliere_sede_cliente";
    public $mc_mese;

    public $parametri = array();

} 