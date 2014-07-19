<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 10:46 AM
 */

abstract class Servizio
{
    const ID='ID';
    const SERVIZIO='SERVIZIO';
    const COSTO='COSTO';
    const PERCENTUALE='COSTO';
    const VALORE_ASSOLUTO='VALORE_ASSOLUTO';
    const MARGINE='MARGINE';

    const SERVIZIO_PARTENZA=0;
    const SERVIZIO_DESTINAZIONE=1;

    protected $record = array();

    public  $id_riga;

    /**
     * @param $nome_campo
     * @return mixed ritorna il valore del campo specificato
     */
    public function getCampo($nome_campo) {
        return $this->record[$nome_campo];
    }


} 