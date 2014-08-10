<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 8/7/14
 * Time: 8:08 AM
 */

class ParametriPreventivoBusinessSnaidero extends ParametriPreventivoBusiness {

    public $mc_mese;
    public $parametri = array();

    public function __construct()
    {
        $this->load('SNAIDERO');
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