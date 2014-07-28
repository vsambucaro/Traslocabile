<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/26/14
 * Time: 8:58 AM
 */

class Fattura {

    private $numero_fattura;
    private $anno;
    private $id_ordine;
    private $cliente;
    private $importo;
    private $imponibile;
    private $iva;
    private $data;

    private $tipologia_cliente;

    public function __construct($numero_fattura , $anno)
    {
        $this->loadDettaglio($numero_fattura, $anno);
    }

    private function loadDettaglio($numero_fattura, $anno)
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT fa.* , v.tipologia_cliente FROM fatture_attive fa , preventivi v WHERE numero_fattura = '$numero_fattura' AND anno=$anno AND v.id_preventivo = fa.id_ordine";
        $res = mysql_query($sql);

        while ($row = mysql_fetch_object($res))
        {

            $this->numero_fattura = $row->numero_fattura;
            $this->anno = $row->anno;
            $this->id_ordine = $row->id_ordine;
            $this->importo = $row->importo;
            $this->imponibile = $row->imponibile;
            $this->iva = $this->importo - $this->imponibile;
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


            $this->cliente = $customer;
        }

        DBUtils::closeConnection($con);

    }

    public function getNumeroFattura() { return $this->numero_fattura; }
    public function getAnno() { return $this->anno; }
    public function getIdOrdine() { return $this->id_ordine; }
    public function getCliente() { return $this->cliente; }
    public function getImporto() { return $this->importo; }
    public function getImponibile() { return $this->imponibile; }
    public function getIva() { return $this->iva; }
    public function getDataFattura() { return $this->data; }


}