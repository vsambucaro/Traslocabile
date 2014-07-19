<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 4:22 PM
 */

class Parametri {

    public static function getMargine()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT valore FROM parametri WHERE UPPER(nome)='MARGINE'";
        $res = mysql_query($sql);
        $valore = 0;
        while ($row=mysql_fetch_object($res)) {
            $valore = intval($row->valore)/100;
        }
        DBUtils::closeConnection($con);
        return $valore;
    }

    public static function getIVA()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT valore FROM parametri WHERE UPPER(nome)='IVA'";
        $res = mysql_query($sql);
        $valore = 0;
        while ($row=mysql_fetch_object($res)) {
            $valore = intval($row->valore)/100;
        }
        DBUtils::closeConnection($con);
        return $valore;
    }

    public static function getAggiustamentoMezzi()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT valore FROM parametri WHERE UPPER(nome)='AGGIUSTAMENTO_MEZZI'";
        $res = mysql_query($sql);
        $valore = 0;
        while ($row=mysql_fetch_object($res)) {
            $valore = intval($row->valore)/100;
        }
        DBUtils::closeConnection($con);
        return $valore;
    }

    public static function getProvvigioneAgenzia()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT valore FROM parametri WHERE UPPER(nome)='PROVVIGIONE_AGENZIA'";
        $res = mysql_query($sql);
        $valore = 0;
        while ($row=mysql_fetch_object($res)) {
            $valore = intval($row->valore)/100;
        }
        DBUtils::closeConnection($con);
        return $valore;
    }

} 