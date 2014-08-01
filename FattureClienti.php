<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/21/14
 * Time: 9:56 PM
 */

require_once "Bootstrap.php";

class FattureClienti {

    const FILTRO_PERIODO_DAL="DAL";
    const FILTRO_PERIODO_AL="AL";
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

    public  function creaNuovaFattura($lista_ordini, Customer $cliente, $data_fattura = null)
    {
        if (!$cliente)
        {
            $this->log->LogError("Creazione Fattura fallita causa cliente non valido");
            return null;
        }

        if (!$lista_ordini || empty($lista_ordini))
        {
            $this->log->logError("Lista ordini della fattura è vuota o nulla");
            return false;
        }


        $progressivo_fattura = $this->getProgressivoFattura();

        if ($this->creaFattura($lista_ordini, $cliente, $progressivo_fattura, $data_fattura))
            return $progressivo_fattura;

        return null;
    }

    private function creaFattura($lista_ordini, Customer $cliente, $progressivo_fattura , $data_fattura = null)
    {

        $con = DBUtils::getConnection();
        $anno = date('Y');

        $importo = 0;
        $imponibile = 0;
        $iva = 0;
        //INSERISCE IL DETTAGLIO
        foreach ($lista_ordini as $ordine)
        {
            $id_ordine = $ordine->getId();
            $sql = "INSERT INTO ordini_fatture_attive (numero_fattura, id_ordine, anno)
            VALUES ('$progressivo_fattura', '$id_ordine', $anno)";
            $res = mysql_query($sql);
            if (!$res)
            {
                $this->log->LogError("Creazione ordini_fatture_attive fallito: ".$sql);
                DBUtils::closeConnection($con);
                return false;
            }

            $importo += $ordine->getImporto();
            $imponibile += $ordine->getImponibile();
            $iva += $ordine->getIva();

        }

        $id_cliente = $cliente->id_cliente;
        if (!$data_fattura)
            $data_fattura = date('Y-m-d');

        $ragione_sociale = $cliente->ragione_sociale;
        $indirizo = $cliente->indirizzo;
        $cap = $cliente->cap;
        $citta = $cliente->citta;
        $provincia = $cliente->provincia;
        $piva = $cliente->piva;
        $cf = $cliente->codice_fiscale;
        $tipologia_cliente = $cliente->tipologia_cliente;


        $sql ="INSERT INTO fatture_attive (id_cliente, data, numero_fattura,
        importo , ragione_sociale, indirizzo, cap, citta, provincia, imponibile, iva, piva, cf, anno, tipologia_cliente)
        VALUES ('$id_cliente', '$data_fattura', '$progressivo_fattura',
        '$importo','$ragione_sociale', '$indirizo', '$cap', '$citta' ,
        '$provincia', '$imponibile' , '$iva' ,'$piva', '$cf' , '$anno', '$tipologia_cliente')";

        $res = mysql_query($sql);
        if (!$res)
        {
            $this->log->LogError("Creazione Fattura fallita: ".$sql);
            DBUtils::closeConnection($con);
            return false;
        }


        DBUtils::closeConnection($con);
        return true;
    }




} 