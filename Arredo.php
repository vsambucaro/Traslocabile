<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 10:20 AM
 */

abstract class Arredo {
    const ID='ID';
    const AMBIENTE='AMBIENTE';
    const ARREDO='ARREDO';
    const VARIANTE='VARIANTE';
    const DIMENSIONI_DA_RICHIEDERE='DIMENSIONI_DA_RICHIEDERE';
    const DIM_A='DIM_A';
    const DIM_L='DIM_L';
    const DIM_P='DIM_P';
    const SMONTABILE='SMONTABILE';
    const CONTENITORE='CONTENITORE';
    const IMBALLABILE='IMBALLABILE';

    //PARAMETRO_B
    const MONTATO_PIENO='MONTATO_PIENO';
    const MONTATO_VUOTO='MONTATO_VUOTO';
    const SMONTATO_PIENO='SMONTATO_PIENO';
    const SMONTATO_VUOTO='SMONTATO_VUOTO';

    //attributi variabili
    const METRI_LINEARI='METRI LINEARI';
    const POSTI_A_SEDERE='POSTI A SEDERE';
    const LARGHEZZA='LARGHEZZA';
    const NUMERO_ANTE='NUMERO ANTE';
    const NUMERO_POSTI='NUMERO POSTI';


    protected $record = array();
    protected $parte_variabile=array();
    protected $parametro_b = null;

    private $qta=1; //quantità di default = 1

    protected $servizio_montaggio = 0;
    protected $servizio_smontaggio = 0;
    protected $servizio_imballaggio = 0;

    private $id_riga = 0;

    /**
     * @param $nome_campo
     * @return mixed ritorna il valore del campo specificato
     */
    public function getCampo($nome_campo) {
        return $this->record[$nome_campo];
    }

    public function setCampo($nome_campo, $valore) {
        return $this->record[$nome_campo] = $valore;
    }

    public function setParteVariabile($nome_campo, $valore)
    {
        $this->parte_variabile[$nome_campo]=$valore;
    }

    public function getParteVariabile($nome_campo)
    {
        if (array_key_exists($nome_campo, $this->parte_variabile))
            return $this->parte_variabile[$nome_campo];
        else
            return null;
    }

    public function getListaPartiVariabili() { return $this->parte_variabile; }

    //abstract public function getMC();

    //visualizza l'oggetto in formato stringa
    public function toString()
    {
        $str = 'ID:'.$this->record[$this::ID].', '.
            'AMBIENTE:'.$this->record[$this::AMBIENTE].', '.
            'ARREDO:'.$this->record[$this::ARREDO].', '.
            'VARIANTE:'.$this->record[$this::VARIANTE].', '.
            'DIMENSIONI_DA_RICHIEDERE:'.$this->record[$this::DIMENSIONI_DA_RICHIEDERE].', '.
            'DIM_A:'.$this->record[$this::DIM_A].', '.
            'DIM_L:'.$this->record[$this::DIM_L].', '.
            'DIM_P:'.$this->record[$this::DIM_P].', ';

        return $str;
    }

    public function setParametroB($parametro_b)
    {
        $this->parametro_b = $parametro_b;
    }

    public function getParametroB() { return $this->parametro_b; }
    //visualizza l'oggetto in formato stringa

    public function setQta($qta) { $this->qta = $qta; }
    public function getQta() { return $this->qta; }

    public function setIdRiga($id) { $this->id_riga = $id; }
    public function getIdRiga() { return $this->id_riga; }

    public function setServizioMontaggio($value) { $this->servizio_montaggio = $value; }
    public function setServizioSmontaggio($value) { $this->servizio_smontaggio = $value; }
    public function setServizioImballaggio($value) { $this->servizio_imballaggio = $value; }

    public function getServizioMontaggio() { return $this->servizio_montaggio; }
    public function getServizioSmontaggio() { return $this->servizio_smontaggio; }
    public function getServizioImballaggio() { return $this->servizio_imballaggio; }


} 