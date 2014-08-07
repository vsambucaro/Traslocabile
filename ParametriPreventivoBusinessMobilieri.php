<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 8/7/14
 * Time: 8:08 AM
 */

class ParametriPreventivoBusinessMobilieri extends ParametriPreventivoBusiness {

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

    public $parametri = array();

    public function __construct()
    {
        $this->load('MOBILIERI');
    }

    /**
     * CARICA I parametri specifici per l'algoritmo
     * @param $tipo_parametri
     */
    private function load($tipo_parametri)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM parametri_business WHERE tipo='$tipo_parametri'";

        $res = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
        {
            $parametro = $row->nome_parametro;
            $valore = $row->valore;
            $this->parametri[$parametro] = $valore;
        }

        DBUtils::closeConnection($con);
    }


} 