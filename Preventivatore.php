<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 10:27 AM
 */

abstract class Preventivatore {


    protected $km = 0;
    protected $mc = 0;
    protected $costo_servizi = 0;
    protected $costo_trazione = 0;

    protected $prezzo_traslocatore = 0;
    protected $prezzo_cliente_senza_iva = 0;
    protected $prezzo_cliente_con_iva = 0;
    protected $stato = '';
    protected $giorni_deposito=0;

    protected $note;
    protected $note_interne;

    protected $flag_sopraluogo = 0;
    protected $data_sopraluogo;

    protected $data_trasloco; //TODO

    protected $indirizzo_partenza;
    protected $indirizzo_destinazione;

    //lista degli arredi da usare per il calcolo
    protected $lista_arredi = array();

    //lista servizi da usare per il calcolo
    protected $lista_servizi = array();
    protected $lista_servizi_partenza = array();
    protected $lista_servizi_destinazione = array();

    abstract public function addArredoById($id_arredo, $parte_variabile=null, $qta=1, $parametro_B, $dim_A = null, $dim_P = null, $dim_L = null, $flag_servizio_montaggio = 0, $flag_servizio_smontaggio = 0, $flag_servizio_imballaggio = 0);
    abstract public function addServizioById($id_servizio, $tipologia);


    public function setKM($km) { $this->km = $km; }
    public function getKM() { return $this->km; }

    public function getCostoServizi() { return $this->costo_servizi; }
    public function getCostoTrazione() { return $this->costo_trazione; }
    public function getPrezzoTraslocatore() { return $this->prezzo_traslocatore; }
    public function getPrezzoClienteSenzaIva() { return $this->prezzo_cliente_senza_iva; }
    public function getPrezzoClienteConIva() { return $this->prezzo_cliente_con_iva; }
    public function getStato() { return $this->stato; }

    abstract public function elabora();

    abstract public function save(Customer $customer);

    public function setIndirizzoPartenza(Indirizzo $indirizzo)
    {
        $this->indirizzo_partenza = $indirizzo;
    }

    public function setIndirizzoDestinazione(Indirizzo $indirizzo)
    {
        $this->indirizzo_destinazione = $indirizzo;
    }

    public function setStato($stato)
    {
        $this->stato = $stato;
    }

    public function setGiorniDeposito($numero_giorni) { $this->giorni_deposito = $numero_giorni; }
    public function getGiorniDeposito() { return $this->giorni_deposito; }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function setNoteInterne($note)
    {
        $this->note_interne = $note;
    }

    public function setFlagSopraluogo($flag)
    {
        $this->flag_sopraluogo = $flag;
    }

    public function setDataSopraluogo($data)
    {
        $this->data_sopraluogo = $data;
    }

    public function getNote() { return $this->note; }
    public function getNoteInterne() { return $this->note_interne; }
    public function getFlagSopraluogo() { return $this->flag_sopraluogo; }

    public function setDataTrasloco($data) { $this->data_trasloco = $data; }
    public function getDataTrasloco() { return $this->data_trasloco; }


} 