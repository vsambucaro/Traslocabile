<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/30/14
 * Time: 8:45 AM
 */

require_once "Assegnazione.php";

class AssegnazioneTraslocatoreDestinazione extends Assegnazione {

    public $id_traslocatore;

    public function load($id)
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM history_traslocatori_destinazione WHERE id=$id";
        $res = mysql_query($sql);

        while ($row=mysql_fetch_object($res)) {
            $this->id =$row->id;
            $this->id_traslocatore =$row->id_traslocatore;
            $this->descrizione =$row->descrizione;
            $this->stato =$row->stato;
            $this->data =$row->data;
        }
        DBUtils::closeConnection($con);

    }
} 