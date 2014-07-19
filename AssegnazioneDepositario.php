<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/30/14
 * Time: 8:45 AM
 */

require_once "Assegnazione.php";
class AssegnazioneDepositario extends Assegnazione {

    public $id_depositario;

    public function load($id)
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM history_depositario WHERE id=$id";
        //echo "\nSQL ".$sql;
        $res = mysql_query($sql);

        while ($row=mysql_fetch_object($res)) {
            $this->id =$row->id;
            $this->id_depositario =$row->id_depositario;
            $this->descrizione =$row->descrizione;
            $this->stato =$row->stato;
            $this->data =$row->data;
        }
        DBUtils::closeConnection($con);

    }
} 