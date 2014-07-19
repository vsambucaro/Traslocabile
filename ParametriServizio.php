<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 11:29 PM
 */

class ParametriServizio {

    const TARIFFA_SMONTAGGIO_IMBALLO_CARICO = "TARIFFA_SMONTAGGIO_IMBALLO_CARICO";
    const TARIFFA_IMBALLO_CARICO = "TARIFFA_IMBALLO_CARICO";
    const TARIFFA_DEPOSITO = "DEPOSITO";
    const TARIFFA_SCARICO = "TARIFFA_SCARICO";
    const TARIFFA_SALITA_AL_PIANO = "TARIFFA_SALITA_AL_PIANO";
    const TARIFFA_MONTAGGIO = "TARIFFA_MONTAGGIO";

    public static function getParametro($nome_parametro)
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM parametri_servizio WHERE servizio='$nome_parametro'";
        $res = mysql_query($sql);
        $prezzo = 0;
        while ($row=mysql_fetch_object($res)) {
            $tariffa_operatore = $row->tariffa_operatore;
            $margine = $row->margine;
            $prezzo = $tariffa_operatore + $margine;
        }
        DBUtils::closeConnection($con);

        return array(
            'prezzo'=> $prezzo,
            'tariffa_operatore'=>$tariffa_operatore,
            'margine'=>$margine
        );
    }

} 