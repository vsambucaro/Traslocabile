<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 10:47 AM
 */



class ServizioIstantaneo extends Servizio {

    public function __construct( $id_servizio )
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM servizi_istantaneo WHERE id='$id_servizio'";
        $res = mysql_query($sql);
        $found = 0;
        while ($row=mysql_fetch_object($res)) {
            $this->record[$this::ID] = $row->id;
            $this->record[$this::SERVIZIO] = $row->servizio;
            $this->record[$this::COSTO] = $row->costo;
            $found = true;

        }

        DBUtils::closeConnection($con);

        if (!$found) {
            throw new Exception('id_servizio non trovato');
        }
    }
} 