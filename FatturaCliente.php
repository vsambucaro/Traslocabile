<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/26/14
 * Time: 8:58 AM
 */

class FatturaCliente {

    private $numero_fattura;
    private $anno;
    private $lista_ordini;
    private $cliente;
    private $importo = 0;
    private $imponibile = 0;
    private $iva = 0;
    private $data;
    private $stato_pagamento = 0;

    const FATTURA_SALDATA = 1;
    const FATTURA_NON_SALDATA = 0;


    private $tipologia_cliente;

    public function __construct($numero_fattura , $anno)
    {
        $this->loadDettaglio($numero_fattura, $anno);
    }

    private function loadDettaglio($numero_fattura, $anno)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT fa.*  FROM fatture_attive fa  WHERE numero_fattura = '$numero_fattura' AND anno=$anno";
        $res = mysql_query($sql);

        //Carica intestazione
        while ($row = mysql_fetch_object($res))
        {

            $this->numero_fattura = $row->numero_fattura;
            $this->anno = $row->anno;
            $this->data = $row->data;
            $this->tipologia_cliente = $row->tipologia_cliente;
            $this->stato_pagamento = $row->stato_pagamento;

            $customer = new Customer($row->id_cliente);
            $customer->ragione_sociale = $row->ragione_sociale;
            $customer->indirizzo = $row->indirizzo;
            $customer->cap = $row->cap;
            $customer->citta = $row->citta;
            $customer->codice_fiscale = $row->cf;
            $customer->provincia = $row->provincia;
            $customer->piva = $row->piva;
            $customer->tipologia_cliente = $row->tipologia_cliente;

            $this->cliente = $customer;
        }
        DBUtils::closeConnection($con);

        $this->lista_ordini = $this->loadOrdiniFattura($numero_fattura, $anno, $customer->tipologia_cliente);

        foreach ($this->lista_ordini as $ordine)
        {
            $this->importo += $ordine->getImporto();
            $this->imponibile += $ordine->getImponibile();
            $this->iva += $ordine->getIva();
        }

    }

    private function loadOrdiniFattura($numero_fattura, $anno, $tipologia_cliente)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM ordini_fatture_attive  WHERE numero_fattura = '$numero_fattura' AND anno=$anno";
        $res = mysql_query($sql);

        //Carica intestazione
        $lista_ordini = array();
        while ($row = mysql_fetch_object($res))
        {
                switch ($tipologia_cliente)
                {
                    case Customer::CLIENTE_CONSUMER:
                        $ordine = new OrdineCliente($row->id_ordine);
                        $lista_ordini[] = $ordine;
                        break;
                    case Customer::CLIENTE_BUSINESS:
                        $ordine = new OrdineClienteBusiness($row->id_ordine);
                        $lista_ordini[] = $ordine;
                }
        }

        DBUtils::getConnection($con);

        return $lista_ordini;
    }


    /**
     * @param Pagamento $pagamento oggetto contenenti gli estremi del pagamento dell'utente
     */
    public function addPagamento(Pagamento $pagamento)
    {
        if ($this->tipologia_cliente == Customer::CLIENTE_BUSINESS)
                $this->addPagamentoClienteBusiness($pagamento);
        else
            $this->addPagamentoClientePrivato($pagamento);

    }



    /**
     * segna il pagamento per una fattura del cliente privato
     */

    private function addPagamentoClientePrivato(Pagamento $pagamento)
    {

        $id_ordine = $this->getOrdineByFattura($this->numero_fattura, $this->anno);
        $ordine = new OrdineCliente($id_ordine);
        $saldata = $ordine->addPagamentoCliente($pagamento);

        if ($saldata)
            $this->setFatturaSaldata();
    }

    /**
     * Usate per il caso consumer per capire l'id ordine associato ad un certa fattura
     * @param $numero_fattura
     * @param $anno
     *
     * @return il numero di ordine della fattura specificata
     */
    private function getOrdineByFattura($numero_fattura, $anno)
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT id_ordine FROM ordini_fatture_attive WHERE numero_fattura='".$numero_fattura."' AND anno=$anno";
        $res = mysql_query($sql);
        $id_ordine = null;
        while ($row = mysql_fetch_object($res))
            $id_ordine = $row->id_ordine;
        DBUtils::closeConnection($con);

        return $id_ordine;
    }

    private function addPagamentoClienteBusiness(Pagamento $pagamento)
    {
        $con = DBUtils::getConnection();

        $numero_fattura = $this->numero_fattura;
        $anno = $this->anno;
        $importo = $pagamento->importo;
        $data = $pagamento->data;
        $descrizione = $pagamento->descrizione;
        $id_cliente = $this->cliente->id_cliente;

        $sql ="INSERT INTO pagamenti_clienti_business (numero_fattura, id_cliente, importo, data, descrizione, anno)
        VALUES ('$numero_fattura', '$id_cliente', '$importo','$data','$descrizione', $anno)";
        //echo "\nSQL: ".$sql;
        $res = mysql_query($sql);


        $sql = "SELECT sum(importo) as somma FROM pagamenti_clienti_business WHERE numero_fattura='$numero_fattura' AND id_cliente=$id_cliente AND anno=$anno";
        $res = mysql_query($sql);
        //echo "\nSQL: ".$sql;
        $totale_pagato = 0;
        while ($row = mysql_fetch_object($res))
            $totale_pagato = $row->somma;

        DBUtils::closeConnection($con);

        if ($totale_pagato>=$this->importo)
            $this->setFatturaSaldata();

    }

    private function setFatturaSaldata()
    {
        $id_cliente = $this->cliente->id_cliente;
        $anno = $this->anno;
        $con = DBUtils::getConnection();
        $sql = "UPDATE fatture_attive SET stato_pagamento=1 WHERE numero_fattura='".$this->numero_fattura."' AND id_cliente=".$id_cliente." AND anno=$anno";
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
        $id_cliente = $this->cliente->id_cliente;
        $anno = $this->anno;

        $sql = "SELECT * FROM pagamenti_clienti WHERE numero_fattura=".$this->numero_fattura." AND id_cliente=".$id_cliente." AND anno=$anno";
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
    public function getCliente() { return $this->cliente; }
    public function getImporto() { return $this->importo; }
    public function getImponibile() { return $this->imponibile; }
    public function getIva() { return $this->iva; }
    public function getDataFattura() { return $this->data; }
    public function getStatoPagamento() { return $this->stato_pagamento; }


}