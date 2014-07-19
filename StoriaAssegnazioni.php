<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/27/14
 * Time: 1:49 PM
 */

class StoriaAssegnazioni {


    public static function addHistoryAssegnazioneTrasportatore($id_preventivo, $id_trasportatore, $stato=null, $descrizione=null)
    {
        $con = DBUtils::getConnection();
        $now = date('Y-m-d');
        //verifica se lo stato è uguale allora non lo cambia
        $sql ="SELECT *  FROM history_trasportatori WHERE id_preventivo = '$id_preventivo' AND id_trasportatore='$id_trasportatore' AND stato='$stato' ORDER BY data DESC LIMIT 1";
        //echo "\nSQL: ".$sql;
        $res = mysql_query($sql);
        $found = false;
        while ($row = mysql_fetch_object($res))
        {
            $found = true;
        }
        $found = false;
        if (!$found)
        {
            $sql ="INSERT INTO history_trasportatori (id_preventivo, id_trasportatore, stato, descrizione, data)
        VALUES ('$id_preventivo', '$id_trasportatore',  '$stato','$descrizione', '$now')";
            $res = mysql_query($sql);

        }

        DBUtils::closeConnection($con);
    }

    public static function addHistoryAssegnazioneDepositario($id_preventivo, $id_depositario, $stato=null, $descrizione=null)
    {
        $con = DBUtils::getConnection();
        $now = date('Y-m-d');
        //verifica se lo stato è uguale allora non lo cambia
        $sql ="SELECT *  FROM history_depositario WHERE id_preventivo = '$id_preventivo' AND id_depositario='$id_depositario' AND stato='$stato' ORDER BY data DESC LIMIT 1";
        //echo "\nSQL: ".$sql;
        $res = mysql_query($sql);
        $found = false;
        while ($row = mysql_fetch_object($res))
        {
            $found = true;
        }
        $found = false;
        if (!$found)
        {
            $sql ="INSERT INTO history_depositario (id_preventivo, id_depositario, stato, descrizione, data)
        VALUES ('$id_preventivo', '$id_depositario',  '$stato','$descrizione', '$now')";
            $res = mysql_query($sql);

        }

        DBUtils::closeConnection($con);
    }

    public static function addHistoryAssegnazioneTraslocatorePartenza($id_preventivo, $id_traslocatore, $stato=null, $descrizione=null)
    {
        $con = DBUtils::getConnection();
        $now = date('Y-m-d');
        //verifica se lo stato è uguale allora non lo cambia
        $sql ="SELECT *  FROM history_traslocatori_partenza WHERE id_preventivo = '$id_preventivo' AND id_traslocatore='$id_traslocatore' AND stato='$stato' ORDER BY data DESC LIMIT 1";
        //echo "\nSQL: ".$sql;
        $res = mysql_query($sql);
        $found = false;
        while ($row = mysql_fetch_object($res))
        {
            $found = true;
        }

        $found = false;
        if (!$found)
        {

            $sql ="INSERT INTO history_traslocatori_partenza (id_preventivo, id_traslocatore, stato, descrizione, data)
        VALUES ('$id_preventivo', '$id_traslocatore',  '$stato','$descrizione', '$now')";
        $res = mysql_query($sql);
        }
        DBUtils::closeConnection($con);
    }

    public static function addHistoryAssegnazioneTraslocatoreDestinazione($id_preventivo, $id_traslocatore, $stato=null, $descrizione=null)
    {
        $con = DBUtils::getConnection();
        $now = date('Y-m-d');
        //verifica se lo stato è uguale allora non lo cambia
        $sql ="SELECT *  FROM history_traslocatori_destinazione WHERE id_preventivo = '$id_preventivo' AND id_traslocatore='$id_traslocatore' AND stato='$stato' ORDER BY data DESC LIMIT 1";
        //echo "\nSQL: ".$sql;
        $res = mysql_query($sql);
        $found = false;
        while ($row = mysql_fetch_object($res))
        {
            $found = true;
        }
        $found = false;
        if (!$found)
        {

            $sql ="INSERT INTO history_traslocatori_destinazione (id_preventivo, id_traslocatore, stato, descrizione, data)
        VALUES ('$id_preventivo', '$id_traslocatore',  '$stato','$descrizione', '$now')";
            $res = mysql_query($sql);
        }
        DBUtils::closeConnection($con);
    }

} 