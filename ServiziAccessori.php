<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 10:09 AM
 */

class ServiziAccessori extends Servizi {

    /**
     * Ritorna l'id del servizio dato il nome
     * @param $nome_servizio nome del servizio da ricercare
     */
    public function getListaServizi() {
        $con = DBUtils::getConnection();
        $sql ="SELECT id FROM servizi_accessori_aggravanti";
        $res = mysql_query($sql);
        $ret = array();
        while ($row=mysql_fetch_object($res)) {
            $ret[]=$row->id;
        }
        DBUtils::closeConnection($con);
        if (!$ret) {
            throw new Exception('Nessun Servizio valido');
        }
        $result = array();
        foreach ($ret as $id)
            $result[] = new ServizioAccessoreAggravante($id);

        return $result;
    }



} 