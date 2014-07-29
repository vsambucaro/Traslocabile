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
    public $id_ordine;
    public $id_fornitore;
    public $importo;
    public $totale_mc;
    public $saldato;
    public $data_ordine;
    private $log;

    public function __construct($id_ordine, $id_fornitore, $importo, $totale_mc, $data_ordine)
    {
        $this->id_ordine = $id_ordine;
        $this->id_fornitore = $id_fornitore;
        $this->importo = $importo;
        $this->totale_mc = $totale_mc;
        $this->data_ordine = $data_ordine;

        $this->log = new KLogger('traslocabile.txt',KLogger::DEBUG);
    }

    /** Ritorna l'ordine ricercato
     * @param $id_ordine
     * @param $id_fornitore
     * @return null|OrdineFornitore
     */
    public static function load($id_ordine, $id_fornitore)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM ordini_fornitori WHERE id_ordine=".$id_ordine." AND id_fornitore=".$id_fornitore;
        $res = mysql_query($sql);
        $ordine = null;
        while ($row = mysql_fetch_object($res))
        {
            $ordine = new OrdineFornitore($id_ordine, $id_fornitore, $row->importo, $row->totale_mc, $row->data_ordine);
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
        $id_ordine = $this->id_ordine;
        $id_fornitore = $this->id_fornitore;
        $importo = $this->importo;
        $totale_mc = $this->totale_mc;
        $data_ordine = $this->data_ordine;
        $sql = "INSERT INTO ordini_fornitori (id_ordine, id_fornitore, importo, totale_mc, data_ordine)
                VALUES ('$id_ordine', '$id_fornitore', '$importo', '$totale_mc', '$data_ordine')
                ON DUPLICATE KEY
                UPDATE importo='" . $this->importo."' , totale_mc='".$this->totale_mc."'";
        $res = mysql_query($sql);
        if (!$res) $this->log->LogError("OrdineFornitore->_insert()->SQL: ".$sql);
        DBUtils::closeConnection($con);
    }

    public function addPagamentoFornitore(Pagamento $pagamento, $id_fornitore)
    {
        $con = DBUtils::getConnection();

        $id_ordine = $this->id_preventivo;
        $importo = $pagamento->importo;
        $data = $pagamento->data;
        $descrizione = $pagamento->descrizione;

        $sql ="INSERT INTO pagamenti_fornitori (id_ordine, id_fornitore, importo, data, descrizione)
        VALUES ('$id_ordine', '$id_fornitore', '$importo','$data','$descrizione')";
        $res = mysql_query($sql);
        $ret = false;
        if ($res) $ret = mysql_insert_id();

        if ($this->getSaldoFornitore($$id_fornitore)<=0)
        {
            //aggiorna lo stato ordine a saldato
            $con = DBUtils::getConnection();
            $sql = "UPDATE ordini_fornitori SET saldato=1 WHERE id_ordine=".$this->id_preventivo." AND id_fornitore=".$id_fornitore;
            $res = mysql_query($sql);
            $ret = $res;
        }

        DBUtils::closeConnection($con);


        return $ret;
    }


    public function getSaldoFornitore($id_fornitore)
    {
        $saldo = $this->importo;

        $con = DBUtils::getConnection();

        $sql = "SELECT SUM(importo) as totale FROM pagamenti_fornitori WHERE id_ordine=".$this->id_ordine." AND id_fornitore=".$id_fornitore;
        $res = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
            $saldo -= $row->totale;

        DBUtils::closeConnection($con);

        return $saldo;
    }

    public function getListaPagamentiFornitore($id_fornitore)
    {
        $con = DBUtils::getConnection();

        $sql = "SELECT * FROM pagamenti_fornitori WHERE id_ordine=".$this->id_ordine." AND id_fornitore=".$id_fornitore;
        $res = mysql_query($sql);
        $lista = array();
        while ($row = mysql_fetch_object($res))
            $lista[] = new Pagamento($row->importo, $row->data, $row->descrizione);

        DBUtils::closeConnection($con);
        return $lista;
    }
} 