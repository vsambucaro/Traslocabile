<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/21/14
 * Time: 9:56 PM
 */

require_once "Bootstrap.php";

class FattureFornitori {

    const FILTRO_PERIODO_DAL="DAL";
    const FILTRO_PERIODO_AL="AL";
    private $log;



    public function __construct()
    {
        $this->log = new KLogger('traslocabile.txt',KLogger::DEBUG);
    }


    public  function registraNuovaFattura($lista_ordini, Fornitore $fornitore, $data_fattura = null, $numero_fattura, $anno = null)
    {
        if (!$fornitore)
        {
            $this->log->LogError("Creazione Fattura fallita causa fornitore non valido");
            return null;
        }

        if (!$numero_fattura)
        {
            $this->log->LogError("Creazione Fattura fallita causa numero fattura non valido");
            return null;
        }



        if (!$lista_ordini || empty($lista_ordini))
        {
            $this->log->logError("Lista ordini della fattura è vuota o nulla");
            return false;
        }



        return ($this->_creaFattura($lista_ordini, $fornitore, $data_fattura, $numero_fattura, $anno));

    }

    private function _creaFattura($lista_ordini, Fornitore $fornitore, $data_fattura = null, $numero_fattura, $anno = null)
    {

        $con = DBUtils::getConnection();
        if (!$anno)
            $anno = date('Y');

        $importo = 0;
        $imponibile = 0;
        $iva = 0;
        //INSERISCE IL DETTAGLIO
        foreach ($lista_ordini as $ordine)
        {
            $id_ordine_fornitore = $ordine->id_ordine_fornitore;
            $sql = "INSERT INTO ordini_fatture_passive (numero_fattura, id_ordine_fornitore, anno)
            VALUES ('$numero_fattura', '$id_ordine_fornitore', $anno)";
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


        if (!$data_fattura)
            $data_fattura = date('Y-m-d');

        $ragione_sociale = $fornitore->ragione_sociale;
        $indirizo = $fornitore->indirizzo;
        $cap = $fornitore->cap;
        $citta = $fornitore->citta;
        $provincia = $fornitore->provincia;
        $piva = $fornitore->piva;
        $cf = $fornitore->codice_fiscale;
        $id_fornitore = $fornitore->id_fornitore;



        $sql ="INSERT INTO fatture_passive (id_fornitore, data, numero_fattura,
        importo , ragione_sociale, indirizzo, cap, citta, provincia, imponibile, iva, piva, cf, anno)
        VALUES ('$id_fornitore', '$data_fattura', '$numero_fattura',
        '$importo','$ragione_sociale', '$indirizo', '$cap', '$citta' ,
        '$provincia', '$imponibile' , '$iva' ,'$piva', '$cf' , '$anno')";

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