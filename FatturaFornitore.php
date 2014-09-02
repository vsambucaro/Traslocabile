<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/26/14
 * Time: 8:58 AM
 */

class FatturaFornitore {

    private $numero_fattura;
    private $anno;
    private $lista_ordini;
    private $fornitore;
    private $importo = 0;
    private $imponibile = 0;
    private $iva = 0;
    private $data;
    private $flag_validata = 0;
    private $stato_pagamento = 0;

    const FATTURA_SALDATA = 1;
    const FATTURA_NON_SALDATA = 0;



    public function __construct($id_fornitore, $numero_fattura , $anno)
    {
        $this->loadDettaglio($id_fornitore, $numero_fattura, $anno);
    }

    private function loadDettaglio($id_fornitore, $numero_fattura, $anno)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT fa.*  FROM fatture_passive fa  WHERE numero_fattura = '$numero_fattura' AND anno=$anno AND id_fornitore = $id_fornitore";
        $res = mysql_query($sql);

        //Carica intestazione
        while ($row = mysql_fetch_object($res))
        {

            $this->numero_fattura = $row->numero_fattura;
            $this->anno = $row->anno;
            $this->data = $row->data;
            $this->flag_validata = $row->flag_validata;
            $this->stato_pagamento = $row->stato_pagamento;

            $fornitore = new Fornitore($row->id_fornitore);
            $fornitore->ragione_sociale = $row->ragione_sociale;
            $fornitore->indirizzo = $row->indirizzo;
            $fornitore->cap = $row->cap;
            $fornitore->citta = $row->citta;
            $fornitore->codice_fiscale = $row->cf;
            $fornitore->provincia = $row->provincia;
            $fornitore->piva = $row->piva;

            $this->fornitore = $fornitore;

            $this->imponibile = $row->imponibile;
            $this->importo = $row->importo;
            $this->iva = $row->iva;
        }
        DBUtils::closeConnection($con);

        $this->lista_ordini = $this->loadOrdiniFattura($numero_fattura, $anno, $this->fornitore->id_fornitore);

/*
        foreach ($this->lista_ordini as $ordine)
        {
            $this->importo += $ordine->getImporto();
            $this->imponibile += $ordine->getImponibile();
            $this->iva += $ordine->getIva();
        }
*/

    }

    private function loadOrdiniFattura($numero_fattura, $anno, $id_fornitore)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM ordini_fatture_passive  WHERE numero_fattura = '$numero_fattura' AND anno=$anno";
        $res = mysql_query($sql);

        //Carica intestazione
        $lista_ordini = array();
        while ($row = mysql_fetch_object($res))
        {
                        $ordine = OrdineFornitore::load($row->id_ordine_fornitore, $id_fornitore);
                        $lista_ordini[] = $ordine;
        }

        DBUtils::getConnection($con);

        return $lista_ordini;
    }

    //Indica se la fattura è stata verificata da traslocabile e quindi validata o meno
    public function setFlagValidata($flag = true)
    {
        $con = DBUtils::getConnection();
        $numero_fattura = $this->numero_fattura ;
        $anno = $this->anno;
        $id_fornitore = $this->fornitore->id_fornitore;

        $sql = "UPDATE fatture_passive SET flag_validata='$flag' WHERE numero_fattura = '$numero_fattura' AND anno=$anno AND id_fornitore = $id_fornitore";
        $res = mysql_query($sql);

        DBUtils::closeConnection($con);

    }


    public function addPagamento(Pagamento $pagamento)
    {
        $con = DBUtils::getConnection();

        $numero_fattura = $this->numero_fattura;
        $anno = $this->anno;
        $importo = $pagamento->importo;
        $data = $pagamento->data;
        $descrizione = $pagamento->descrizione;
        $id_fornitore = $this->fornitore->id_fornitore;

        $sql ="INSERT INTO pagamenti_fornitori (numero_fattura, id_fornitore, importo, data, descrizione, anno)
        VALUES ('$numero_fattura', '$id_fornitore', '$importo','$data','$descrizione', '$anno')";
        $res = mysql_query($sql);

        DBUtils::closeConnection($con);

        if ($this->statoSaldoFattura()<=0)
        {
            //aggiorna lo stato ordine a saldato
            $con = DBUtils::getConnection();
            $sql = "UPDATE fatture_passive SET stato_pagamento=1 WHERE numero_fattura='".$this->numero_fattura."' AND id_fornitore=".$id_fornitore." AND anno=$anno";
            $res = mysql_query($sql);
            DBUtils::closeConnection($con);
            $this->stato_pagamento = $this::FATTURA_SALDATA;
        }

    }

    //Metodo per capire se la fattura è stata saldata o meno
    private function statoSaldoFattura()
    {

        $saldo = $this->importo;
        $id_fornitore = $this->fornitore->id_fornitore;
        $anno = $this->anno;
        $con = DBUtils::getConnection();

        $sql = "SELECT SUM(importo) as totale FROM pagamenti_fornitori WHERE numero_fattura='".$this->numero_fattura."' AND id_fornitore=".$id_fornitore." AND anno=$anno";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
            $saldo -= $row->totale;

        DBUtils::closeConnection($con);

        return $saldo;
    }


    public function setFatturaSaldata()
    {
        $id_fornitore = $this->fornitore->id_fornitore;
        $anno = $this->anno;
        $con = DBUtils::getConnection();
        $sql = "UPDATE fatture_passive SET stato_pagamento=1 WHERE numero_fattura='".$this->numero_fattura."' AND id_fornitore=".$id_fornitore." AND anno=$anno";
        //echo "\nSQL: ".$sql;
        $res = mysql_query($sql);
        DBUtils::closeConnection($con);
        $this->stato_pagamento = $this::FATTURA_SALDATA;


    }
    /**
     * @return array Lista pagamenti per specifica fattura
     */
    public function getListaPagamenti()
    {
        $con = DBUtils::getConnection();
        $id_fornitore = $this->fornitore->id_fornitore;
        $anno = $this->anno;

        $sql = "SELECT * FROM pagamenti_fornitori WHERE numero_fattura=".$this->numero_fattura." AND id_fornitore=".$id_fornitore." AND anno=$anno";
        $res = mysql_query($sql);
        $lista = array();
        while ($row = mysql_fetch_object($res))
            $lista[] = new Pagamento($row->importo, $row->data, $row->descrizione);

        DBUtils::closeConnection($con);
        return $lista;
    }


    public function getNumeroFattura() { return $this->numero_fattura; }
    public function getAnno() { return $this->anno; }
    public function getListaOrdini() { return $this->lista_ordini; }
    public function getFornitore() { return $this->fornitore; }
    public function getImporto() { return $this->importo; }
    public function getImponibile() { return $this->imponibile; }
    public function getIva() { return $this->iva; }
    public function getDataFattura() { return $this->data; }
    public function getFlagValidata() { return $this->flag_validata;}
    public function getStatoPagamento() { return $this->stato_pagamento; }



}