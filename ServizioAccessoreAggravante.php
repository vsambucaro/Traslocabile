<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 11:29 PM
 */

class ServizioAccessoreAggravante extends Servizio {

    public function __construct( $id_servizio )
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM servizi_accessori_aggravanti WHERE id='$id_servizio'";
        $res = mysql_query($sql);
        $found = 0;
        while ($row=mysql_fetch_object($res)) {
            $this->record[$this::ID] = $row->id;
            $this->record[$this::SERVIZIO] = $row->servizio;
            $this->record[$this::PERCENTUALE] = $row->percentuale;
            $this->record[$this::VALORE_ASSOLUTO] = $row->valore_assoluto;
            $this->record[$this::MARGINE] = $row->margine;
            $found = true;

        }

        DBUtils::closeConnection($con);

        if (!$found) {
            throw new Exception('id_servizio non trovato');
        }
    }

    //per far in modo che l'operatore possa per altri servizi generici impostare una %
    public function setPercentuale($valore)
    {
        $this->record[$this::PERCENTUALE] = $valore;
    }

    //per far in modo che l'operatore possa per altri servizi generici impostare un valore assoluto
    public function setValoreAssoluto($valore)
    {
        $this->record[$this::VALORE_ASSOLUTO] = $valore;
    }

    //per far in modo che l'operatore possa per altri servizi generici impostare un margine
    public function setMargine($valore)
    {
        $this->record[$this::MARGINE] = $valore;
    }

} 