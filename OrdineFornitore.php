<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/12/14
 * Time: 2:59 PM
 */

class OrdineFornitore
{
    //public $id;
    public $id_ordine_cliente;
    public $id_ordine_fornitore;
    public $id_fornitore;
    public $importo;
    public $imponibile;
    public $iva;
    public $totale_mc;
    public $saldato;
    public $data_ordine;
    private $log;
    public $tipologia_servizio;

    const TIPO_SERVIZIO_TRASPORTO=0;
    const TIPO_SERVIZIO_TRASLOCO_PARTENZA=1;
    const TIPO_SERVIZIO_TRASLOCO_DESTINAZIONE=2;
    const TIPO_SERVIZIO_DEPOSITO=3;

    /**
     * @param $id_ordine_cliente
     * @param $id_fornitore
     * @param $importo
     * @param $totale_mc
     * @param $data_ordine
     * @param $tipologia_servizio
     */
    public function __construct($id_ordine_cliente, $id_fornitore, $importo, $imponibile, $iva, $totale_mc , $data_ordine , $tipologia_servizio )
    {
        $this->id_ordine_cliente = $id_ordine_cliente;
        $this->id_fornitore = $id_fornitore;
        $this->importo = $importo;
        $this->totale_mc = $totale_mc;
        $this->data_ordine = $data_ordine;
        $this->tipologia_servizio = $tipologia_servizio;
        $this->imponibile = $imponibile;
        $this->iva = $iva;

        $this->log = new KLogger('traslocabile.txt',KLogger::DEBUG);
    }


    /** Ritorna l'ordine ricercato
     * @param $id_ordine
     * @param $id_fornitore
     * @return null|OrdineFornitore
     */
    public static function load($id_ordine_fornitore, $id_fornitore)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM ordini_fornitori WHERE id_ordine_fornitore=".$id_ordine_fornitore." AND id_fornitore=".$id_fornitore;
        $res = mysql_query($sql);
        $ordine = null;
        while ($row = mysql_fetch_object($res))
        {
            $ordine = new OrdineFornitore($row->id_ordine_cliente, $id_fornitore, $row->importo, $row->imponibile, $row->iva, $row->totale_mc, $row->data_ordine, $row->tipologia_servizio);
            $ordine->id_ordine_fornitore = $id_ordine_fornitore;
            //$ordine->id = $row->id;
        }
        DBUtils::closeConnection($con);
        return $ordine;
    }

    public function save()
    {
        /*
        if (!$this->_update())
            $this->_insert();
        */
        $this->_insert();
    }


    private function _insert()
    {
        $con = DBUtils::getConnection();
        $id_ordine_cliente = $this->id_ordine_cliente;
        $id_fornitore = $this->id_fornitore;
        $importo = $this->importo;
        $totale_mc = $this->totale_mc;
        $data_ordine = $this->data_ordine;
        $tipologia_servizio = $this->tipologia_servizio;
        $imponibile = $this->imponibile;
        $iva = $this->iva;
        $sql = "INSERT INTO ordini_fornitori (id_ordine_cliente, id_fornitore, importo, totale_mc, data_ordine, tipologia_servizio,
        imponibile, iva)
                VALUES ('$id_ordine_cliente', '$id_fornitore', '$importo', '$totale_mc', '$data_ordine', '$tipologia_servizio',
                '$imponibile', '$iva')
                ON DUPLICATE KEY
                UPDATE importo='" . $this->importo."' , totale_mc='".$this->totale_mc."',
                imponibile='$imponibile', iva='$iva'";
        $res = mysql_query($sql);
        if (!$res) $this->log->LogError("OrdineFornitore->_insert()->SQL: ".$sql);
        DBUtils::closeConnection($con);
    }

    public function getNumeroFattura()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT numero_fattura, anno FROM ordini_fatture_passive WHERE id_ordine_fornitore=".$this->id_ordine_fornitore;
        $res = mysql_query($sql);
        $numero_fattura = null;
        $anno = null;
        while ($row = mysql_fetch_object($res))
        {
            $numero_fattura = $row->numero_fattura;
            $anno = $row->anno;
        }

        DBUtils::closeConnection($con);

        if ($numero_fattura && $anno)
            return array('numero_fattura'=>$numero_fattura, 'anno'=>$anno);
        else
            return null;
    }


}