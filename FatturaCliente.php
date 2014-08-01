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

    public function getNumeroFattura() { return $this->numero_fattura; }
    public function getAnno() { return $this->anno; }
    public function getListaOrdini() { return $this->lista_ordini; }
    public function getCliente() { return $this->cliente; }
    public function getImporto() { return $this->importo; }
    public function getImponibile() { return $this->imponibile; }
    public function getIva() { return $this->iva; }
    public function getDataFattura() { return $this->data; }


}