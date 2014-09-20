<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/22/14
 * Time: 11:45 AM
 */

class OrdineBusiness extends PreventivoBusiness {

  const TIPO_ORDINE=1;
    private $id_ordine;

    /**
     * costruisce l'oggetto ordine a partire dall'id_ordine
     * @param $id_ordine
     */
    public function __construct( $id_ordine)
    {
        $this->id_ordine = $id_ordine;
        $this->load($id_ordine);
    }

    /**
     * @return il saldo ancora da pagare da parte del cliente
     */
    public function getSaldoCliente()
    {
        $saldo = $this->importo;

        $con = DBUtils::getConnection();

        $sql = "SELECT SUM(importo) as totale FROM pagamenti_clienti WHERE id_ordine=".$this->id_preventivo;
        $res = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
            $saldo -= $row->totale;

        DBUtils::closeConnection($con);

        return $saldo;
    }

    /**
     * @return array con la lista dei pagamenti effettuati dal cliente
     */
    public function getListaPagamentiCliente()
    {
        $con = DBUtils::getConnection();

        $sql = "SELECT * FROM pagamenti_clienti_business WHERE id_ordine=".$this->id_preventivo;
        $res = mysql_query($sql);
        $lista = array();
        while ($row = mysql_fetch_object($res))
            $lista[] = new Pagamento($row->importo, $row->data, $row->descrizione);

        DBUtils::closeConnection($con);
        return $lista;
    }

    /**
     * @param Pagamento $pagamento oggetto contenenti gli estremi del pagamento dell'utente
     */
    public function addPagamentoCliente(Pagamento $pagamento)
    {
        $con = DBUtils::getConnection();

        $id_ordine = $this->id_preventivo;
        $importo = $pagamento->importo;
        $data = $pagamento->data;
        $descrizione = $pagamento->descrizione;

        $sql ="INSERT INTO pagamenti_clienti (id_ordine, importo, data, descrizione)
        VALUES ('$id_ordine', '$importo','$data','$descrizione')";
        $res = mysql_query($sql);
        $ret = false;
        if ($res) $ret = mysql_insert_id();


        if ($this->getSaldoCliente()<=0)
        {
            //aggiorna lo stato ordine a saldato
            $con = DBUtils::getConnection();
            $sql = "UPDATE preventivi SET saldato_cliente=1 WHERE id_preventivo=".$this->id_preventivo;

            $res = mysql_query($sql);
            $ret = $res;
        }

        DBUtils::closeConnection($con);


        return $ret;

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


        if ($this->getSaldoFornitore($id_fornitore)<=0)
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

        $sql = "SELECT SUM(importo) as totale FROM pagamenti_fornitori WHERE id_ordine=".$this->id_preventivo." AND id_fornitore=".$id_fornitore;
        $res = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
            $saldo -= $row->totale;

        DBUtils::closeConnection($con);

        return $saldo;
    }

    public function getListaPagamentiFornitore($id_fornitore)
    {
        $con = DBUtils::getConnection();

        $sql = "SELECT * FROM pagamenti_fornitori WHERE id_ordine=".$this->id_preventivo." AND id_fornitore=".$id_fornitore;
        $res = mysql_query($sql);
        $lista = array();
        while ($row = mysql_fetch_object($res))
            $lista[] = new Pagamento($row->importo, $row->data, $row->descrizione);

        DBUtils::closeConnection($con);
        return $lista;
    }

    public function getNumeroFattura()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT numero_fattura, anno FROM ordini_fatture_attive WHERE id_ordine=".$this->id_ordine;
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