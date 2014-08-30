<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/22/14
 * Time: 11:45 AM
 */

class OrdineCliente extends Preventivo {

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

        $sql = "SELECT * FROM pagamenti_clienti WHERE id_ordine=".$this->id_preventivo;
        $res = mysql_query($sql);
        $lista = array();
        while ($row = mysql_fetch_object($res))
            $lista[] = new Pagamento($row->importo, $row->data, $row->descrizione, $row->numero->fattura, $row->anno);

        DBUtils::closeConnection($con);
        return $lista;
    }

    /**
     * @param Pagamento $pagamento oggetto contenenti gli estremi del pagamento dell'utente
     */
    public function addPagamentoCliente(Pagamento $pagamento, $numero_fattura = null, $anno=null)
    {
        $con = DBUtils::getConnection();

        $id_ordine = $this->id_preventivo;
        $importo = $pagamento->importo;
        $data = $pagamento->data;
        $descrizione = $pagamento->descrizione;

        $sql ="INSERT INTO pagamenti_clienti (id_ordine, importo, data, descrizione, numero_fattura, anno)
        VALUES ('$id_ordine', '$importo','$data','$descrizione','$numero_fattura', '$anno')";
        $res = mysql_query($sql);
        $ret = false;


        if ($this->getSaldoCliente()<=0)
        {
            //aggiorna lo stato ordine a saldato
            $con = DBUtils::getConnection();
            $sql = "UPDATE preventivi SET saldato_cliente=1 WHERE id_preventivo=".$this->id_preventivo;
            $res = mysql_query($sql);
            $ret = true;
        }

        DBUtils::closeConnection($con);

        return $ret;
    }

    public function getDataOrdine() { return $this->data_preventivo; }


    public function getNumeroFattura()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT numero_fattura, anno FROM ordini_fatture_attive WHERE id_ordine=".$this->id_ordine;
        $res = mysql_query($sql);
        $lista_fatture = array();
        $numero_fattura = null;
        $anno = null;

        while ($row = mysql_fetch_object($res))
        {
            $numero_fattura = $row->numero_fattura;
            $anno = $row->anno;
            $lista_fatture[] = array('numero_fattura'=>$numero_fattura, 'anno'=>$anno);
        }

        DBUtils::closeConnection($con);

            return $lista_fatture;
    }

    /**
     * serve a indicare che l'ordine è stato tutto fatturato
     */
    public function setStatoFatturazioneCompleto()
    {
        $con = DBUtils::getConnection();
        $sql = "UPDATE preventivi SET stato_fatturazione=1 WHERE id_preventivo=".$this->id_preventivo;
        $res = mysql_query($sql);
        DBUtils::closeConnection($con);
    }
} 