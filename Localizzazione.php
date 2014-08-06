<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 8/6/14
 * Time: 9:34 AM
 */

class Localizzazione {

    private $id_localizzazione;
    private $descrizione;

    public static function getListaLocalizzazione()
    {
        $lista = array();
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM localizzazione";
        $res  = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
        {
            $lista[$row->id] = $row->descrizione;
        }

        DBUtils::closeConnection($con);

        return $lista;
    }


    public static function getListaTipoLocalizzazione($id_localizzazione)
    {
        $lista = array();
        $con = DBUtils::getConnection();
        $sql = "SELECT DISTINCT(a.id_tipo), t.descrizione FROM associazione_localizzazione_tipo_piano a
        inner join localizzazione_tipo t
        on t.id_tipo = a.id_tipo
        AND a.id_localizzazione = $id_localizzazione";

        $res  = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
        {
            $lista[$row->id_tipo] = $row->descrizione;
        }

        DBUtils::closeConnection($con);

        return $lista;
    }


    public static function getListaPiano($id_localizzazione, $id_tipo)
    {
        $lista = array();
        $con = DBUtils::getConnection();
        $sql = "SELECT DISTINCT(a.id_piano), t.descrizione FROM associazione_localizzazione_tipo_piano a
        inner join localizzazione_tipo_piano t
        on t.id_localizzazione_tipo_piano = a.id_piano
        AND a.id_tipo = $id_tipo
        AND a.id_localizzazione = $id_localizzazione";

        $res  = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
        {
            $lista[$row->id_piano] = $row->descrizione;
        }

        DBUtils::closeConnection($con);

        return $lista;
    }




} 