<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/21/14
 * Time: 9:56 PM
 */

require_once "Bootstrap.php";

class Fatture {

    private $log;


    public function __construct()
    {
        $this->log = new KLogger('traslocabile.txt',KLogger::DEBUG);
    }

    private  function getProgressivoFattura()
    {

        $con = DBUtils::getConnection();
        //$res = mysql_query('SELECT `getNextCustomSeq`("seq_spedizione", "S01") AS `getNextCustomSeq`');
        $year = date('Y');
        $res = mysql_query('SELECT `getNextCustomSeq`("seq_fatturazione", "$year") AS `getNextCustomSeq`');
        if($res === FALSE){ die(mysql_error()); }
        while ($row = mysql_fetch_object($res)) {

            $result = $row->getNextCustomSeq;
            $pos = strpos($result, '-');
            $result = substr($result, $pos+1);
        }

        DBUtils::closeConnection($con);
        //$wcli= "W".substr($result, 1);
        return $result;
    }

    public  function createFattura($id_ordine, Customer $cliente)
    {
        if (!$cliente)
        {
            $this->log->LogError("Creazione Fattura fallita causa cliente non valido");
            return null;
        }

        if (!$id_ordine)
        {
            $this->log->LogError("Creazione Fattura fallita causa id_ordine non valido");
            return null;

        }
        $ordine = new Ordine($id_ordine);
        if (!$ordine)
        {
            $this->log->LogError("Creazione Fattura fallita causa ordine non valido");
            return null;

        }

        $progressivo_fattura = $this->getProgressivoFattura();

        if ($this->creaFattura($ordine, $cliente, $progressivo_fattura))
            return $progressivo_fattura;

        return null;
    }

    private function creaFattura(Ordine $ordine, Customer $cliente, $progressivo_fattura)
    {
        $con = DBUtils::getConnection();
        $anno = date('Y');
        $id_ordine = $ordine->getId();
        $id_cliente = $cliente->id_cliente;
        $data = date('Y-m-d');
        $importo = $ordine->getImporto();
        $ragione_sociale = $cliente->ragione_sociale;
        $indirizo = $cliente->indirizzo;
        $cap = $cliente->cap;
        $citta = $cliente->citta;
        $provincia = $cliente->provincia;
        $imponibile = $ordine->getImponibile();
        $iva = $ordine->getIva();
        $piva = $cliente->piva;
        $cf = $cliente->codice_fiscale;

        $sql ="INSERT INTO fatture_attive (id_ordine, id_cliente, data, numero_fattura,
        importo , ragione_sociale, indirizzo, cap, citta, provincia, imponibile, iva, piva, cf, anno)
        VALUES ('$id_ordine', '$id_cliente', '$data', '$progressivo_fattura',
        '$importo','$ragione_sociale', '$indirizo', '$cap', '$citta' ,
        '$provincia', '$imponibile' , '$iva' ,'$piva', '$cf' , '$anno')";

        $res = mysql_query($sql);
        if (!$res)
        {
            $this->log->LogError("Creazione Fattura fallita: ".$sql);
            return false;
        }

        DBUtils::closeConnection($con);
        return true;
    }
} 