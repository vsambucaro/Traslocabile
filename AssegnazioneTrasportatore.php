<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/30/14
 * Time: 8:45 AM
 */

require_once "Assegnazione.php";
class AssegnazioneTrasportatore extends Assegnazione {

    public $id_trasportatore;

    public function load($id)
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM history_trasportatori WHERE id=$id";
        //echo "\nSQL ".$sql;
        $res = mysql_query($sql);

        while ($row=mysql_fetch_object($res)) {
            $this->id =$row->id;
            $this->id_trasportatore =$row->id_trasportatore;
            $this->descrizione =$row->descrizione;
            $this->stato =$row->stato;
            $this->data =$row->data;
        }
        DBUtils::closeConnection($con);

    }
} 