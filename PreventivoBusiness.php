<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/22/14
 * Time: 5:29 PM
 */

class PreventivoBusiness {

    const TIPO_PREVENTIVO=0;
    private  $customer;

    private  $partenza;
    private  $destinazione;

    private $lista_item;
    private $lista_servizi_accessori_partenza;
    private $lista_servizi_accessori_destinazione;
    private $lista_servizi_istantaneo;

    private $stato;
    protected $importo;
    private $tipo;

    protected  $id_preventivo;
    private $data_preventivo;
    private $data_sopraluogo;
    private $data_trasloco;

    private $id_trasportatore;
    private $id_traslocatore_partenza;
    private $id_traslocatore_destinazione;
    private $id_depositario;

    private $id_cliente;
    private $id_agenzia;

    private $giorni_deposito;
    private $lista_voci_extra;

    private $note;
    private $note_interne;
    private  $flag_sopraluogo;
    //protected $data_sopraluogo;

    private $log;

    private $importo_commessa_trasportatore;
    private $importo_commessa_depositario;
    private $importo_commessa_traslocatore_partenza;
    private $importo_commessa_traslocatore_destinazione;

    private $imponibile;
    private $iva;
    private $mc;

    public function __construct()
    {
        $this->log = new KLogger('traslocabile.txt',KLogger::DEBUG);

    }

    //set id cliente del preventivo
    public function setCliente(Customer $cliente)
    {
        $this->customer = $cliente;
        $this->id_cliente = $cliente->id_cliente;
    }

    public function setIdCliente($id_cliente)
    {
        $this->id_cliente = $id_cliente;
        if ($this->customer) { $this->customer->setIdCliente($id_cliente); }
    }




    public function setPartenza(Indirizzo $indirizzo)
    {
        $this->partenza = $indirizzo;
    }

    public function setDestinazione(Indirizzo $indirizzo)
    {
        $this->destinazione = $indirizzo;

    }

    public function setItems($lista)
    {
        $this->lista_item = $lista;
    }


    public function setServiziAccessoriPartenza($lista_servizi)
    {
        $this->lista_servizi_accessori_partenza = $lista_servizi;
    }

    public function getServiziAccessoriPartenza()
    {
        return $this->lista_servizi_accessori_partenza ;
    }

    public function setServiziAccessoriDestinazione($lista_servizi)
    {
        $this->lista_servizi_accessori_destinazione = $lista_servizi;
    }

    public function getServiziAccessoriDestinazione()
    {
        return $this->lista_servizi_accessori_destinazione ;
    }

    public function setServiziIstantaneo($lista_servizi)
    {
        $this->lista_servizi_istantaneo = $lista_servizi;
    }

    public function setListaVociExtra($lista_voci)
    {
        $this->lista_voci_extra = $lista_voci;
    }

    public function getListaVociExtra()
    {
        return $this->lista_voci_extra ;
    }

    public function setStato($stato)
    {
        $this->stato = $stato;
    }

    public function setImporto($valore)
    {
        $this->importo = $valore;
    }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function setFlagSopraluogo($flag)
    {
        $this->flag_sopraluogo = $flag;
    }

    public function getNote() { return $this->note; }
    public function getFlagSopraluogo() { return $this->flag_sopraluogo; }


    public function save() {
        $id_preventivo = $this->id_preventivo;
        $this->_deleteOldRecords($id_preventivo);

        if ($id_preventivo!='')
        {
            $this->_savePreventivo($id_preventivo);
        }
        else
        {
            $id_preventivo = $this->_savePreventivo();
        }

        $this->_saveItems($id_preventivo);
        $this->_saveServiziIstantaneo($id_preventivo);

        $this->_saveServiziAccessiPartenza($id_preventivo);
        $this->_saveServiziAccessiDestinazione($id_preventivo);
        $this->_saveGiorniDeposito($id_preventivo);
        $this->_saveVociPreventivoExtra($id_preventivo);

        //Aggiorna le commesse se per caso qualche item è stato combiato nel preventivatore
        if ($this->tipo == Ordine::TIPO_ORDINE)
            $this->updateCommesse();

    }

    /**
     * Cancella tutti i record nel db prima di risalvare il preventivo stesso
     * @param $id_preventivo da cancellare
     */
    private function _deleteOldRecords($id_preventivo)
    {
        $con = DBUtils::getConnection();
        $sql = "DELETE FORM preventivi WHERE id_preventivo=".$id_preventivo;
        $res = mysql_query($sql);

        $sql ="DELETE FROM items_preventivo WHERE id_preventivo=".$id_preventivo;
        $res = mysql_query($sql);

        $sql ="DELETE FROM parametri_arredi_preventivo WHERE id_preventivo=".$id_preventivo;
        $res = mysql_query($sql);

        $sql ="DELETE FROM servizi_istantaneo_preventivo WHERE id_preventivo=".$id_preventivo;
        $res = mysql_query($sql);

        $sql ="DELETE FROM servizi_accessori_aggravanti_preventivo WHERE id_preventivo=".$id_preventivo;
        $res = mysql_query($sql);

        $sql ="DELETE FROM deposito WHERE id_preventivo=".$id_preventivo;
        $res = mysql_query($sql);

        $sql ="DELETE FROM voci_preventivo_extra WHERE id_preventivo=".$id_preventivo;
        $res = mysql_query($sql);

        DBUtils::closeConnection($con);
        return;

    }

    //crea il record preventivo e ritorna l'ID del preventivo
    private function _savePreventivo($id_preventivo = null) {

        $con = DBUtils::getConnection();
        $data = date('Y-m-d');
        $id_cliente = $this->id_cliente;
        if ($this->customer)
            $email_cliente = $this->customer->email;
        else
            $email_cliente = '';

        $citta_partenza = $this->partenza->citta;
        $provincia_partenza = $this->partenza->provincia;
        $cap_partenza = $this->partenza->cap;
        $indirizzo_partenza = $this->partenza->indirizzo;
        $partenza_codice_citta = $this->partenza->codice_citta;
        $partenza_codice_provincia = $this->partenza->codice_provincia;


        $citta_destinazione = $this->destinazione->citta;
        $provincia_destinazione = $this->destinazione->provincia;
        $cap_destinazione = $this->destinazione->cap;
        $indirizzo_destinazone = $this->destinazione->indirizzo;

        $destinazione_codice_citta = $this->destinazione->codice_citta;
        $destinazione_codice_provincia = $this->destinazione->codice_provincia;

        $data_sopraluogo = $this->data_sopraluogo;
        $data_trasloco = $this->data_trasloco;
        $id_agenzia = $this->id_agenzia;
        $flag_sopraluogo = $this->flag_sopraluogo;
        $note = $this->note;
        $imponibile = $this->imponibile;
        $iva = $this->iva;


        $sql ="INSERT INTO preventivi (data, id_cliente, partenza_cap, partenza_citta, partenza_provincia, partenza_indirizzo, destinazione_cap, destinazione_citta, destinazione_provincia,
destinazione_indirizzo, importo, stato, email_cliente, id_trasportatore, id_traslocatore_partenza, id_traslocatore_destinazione,
data_sopraluogo, data_trasloco, id_agenzia, flag_sopraluogo, note, id_depositario, importo_commessa_trasportatore, importo_commessa_depositario, importo_commessa_traslocatore_partenza,
importo_commessa_traslocatore_destinazione, imponibile, iva, partenza_codice_citta, partenza_codice_provincia,
destinazione_codice_provincia, destinazione_codice_citta, tipologia_cliente, mc, note_interne)
        VALUES ('$data', '$id_cliente', '$cap_partenza',
        '$citta_partenza', '$provincia_partenza', '$indirizzo_partenza',
         '$cap_destinazione',
         '$citta_destinazione', '$provincia_destinazione', '$indirizzo_destinazone',
         '$this->importo', '$this->stato', '$email_cliente',
         '$this->id_trasportatore', '$this->id_traslocatore_partenza', '$this->id_traslocatore_destinazione',
         '$data_sopraluogo', '$data_trasloco', '$id_agenzia',
         '$flag_sopraluogo', '$note', '$this->id_depositario',
         '$this->importo_commessa_trasportatore', '$this->importo_commessa_depositario', '$this->importo_commessa_traslocatore_partenza', '$this->importo_commessa_traslocatore_destinazione',
         '$imponibile','$iva',
         '$partenza_codice_citta', '$partenza_codice_provincia',
         '$destinazione_codice_provincia', '$destinazione_codice_citta', '1','$this->mc', $this->note_interne
        )";

        //se il preventivo già c'è significa che lo sto salvando e quindi riuso lo stesso id
        if ($id_preventivo)
        {
            $data= $this->data_preventivo;
            $sql ="UPDATE preventivi SET
          data='$data',
          id_cliente = '$id_cliente',
          partenza_cap = '$cap_partenza',
          partenza_citta = '$citta_partenza',
          partenza_provincia = '$provincia_partenza',
          partenza_indirizzo = '$indirizzo_partenza',
          destinazione_cap = '$cap_destinazione',
          destinazione_citta = '$citta_destinazione',
          destinazione_provincia = '$provincia_destinazione',
          destinazione_indirizzo = '$indirizzo_destinazone',
          importo = '$this->importo',
          stato = '$this->stato',
          email_cliente = '$email_cliente',
          id_trasportatore = '$this->id_trasportatore',
          id_traslocatore_partenza = '$this->id_traslocatore_partenza',
          id_traslocatore_destinazione = '$this->id_traslocatore_destinazione',
          id_depositario = '$this->id_depositario',
          flag_sopraluogo = '$this->flag_sopraluogo',
          note = '$this->note',
          data_sopraluogo = '$data_sopraluogo',
          data_trasloco = '$data_trasloco',
          importo_commessa_trasportatore = '$this->importo_commessa_trasportatore',
          importo_commessa_depositario = '$this->importo_commessa_depositario',
          importo_commessa_traslocatore_partenza = '$this->importo_commessa_traslocatore_partenza',
          importo_commessa_traslocatore_destinazione = '$this->importo_commessa_traslocatore_destinazione',
          id_agenzia = '$id_agenzia',
          imponibile = '$imponibile',
          iva = '$iva',
          partenza_codice_citta = '$partenza_codice_citta',
          partenza_codice_provincia = '$partenza_codice_provincia',
          destinazione_codice_citta = '$destinazione_codice_citta',
          destinazione_codice_provincia = '$destinazione_codice_provincia',
          tipologia_cliente='1',
          mc='$this->mc',
          note_interne = '$this->note_interne'
          WHERE id_preventivo='$id_preventivo'
        ";

        }

        $res = mysql_query($sql);

        if (!$res) {
            die ("ERRORE: ".$sql);
        }
        if (!$id_preventivo)
            $id_preventivo = mysql_insert_id();

        $this->id_preventivo = $id_preventivo;
        return $id_preventivo;
    }

    private function _saveItems($id_preventivo)
    {
        $con = DBUtils::getConnection();
        if ($this->lista_item)
        foreach ($this->lista_item as $item) {
            //print_r($item);
            $descrizione = $item->descrizione;
            $qta = $item->qta;
            $dim_A = $item->altezza;
            $dim_L = $item->lunghezza;
            $dim_P = $item->profondita;
            $mc = $item->mc;
            $sql ="INSERT INTO items_preventivo (descrizione, id_preventivo, qta, dim_A, dim_P, dim_L , mc)
             VALUES ('$descrizione', '$id_preventivo', '$qta', '$dim_A','$dim_P','$dim_L', '$mc')";
            $res = mysql_query($sql);
        }

        DBUtils::closeConnection($con);
    }

    private function _saveVociPreventivoExtra($id_preventivo)
    {
        $con = DBUtils::getConnection();
        if ($this->lista_voci_extra)
            foreach ($this->lista_voci_extra as $voce) {
                $segno = $voce->getSegno();
                $descrizione = $voce->getDescrizione();
                $valore = $voce->getValore();
                $sql ="INSERT INTO voci_preventivo_extra (id_preventivo, descrizione, segno, valore)
              VALUES ('$id_preventivo', '$descrizione', '$segno', '$valore')";

                $res = mysql_query($sql);
            }

        DBUtils::closeConnection($con);


    }

    private function _saveServiziIstantaneo($id_preventivo)
    {
        $con = DBUtils::getConnection();
        if ($this->lista_servizi_istantaneo)
            foreach ($this->lista_servizi_istantaneo as $servizio) {
                $id_servizio = $servizio->getCampo(ServizioIstantaneo::ID);

                $sql ="INSERT INTO servizi_istantaneo_preventivo (id_servizio, id_preventivo)
              VALUES ('$id_servizio', '$id_preventivo')";

                $res = mysql_query($sql);
            }

            DBUtils::closeConnection($con);


    }

    private function _saveServiziAccessiPartenza($id_preventivo)
    {
        $con = DBUtils::getConnection();
        if ($this->lista_servizi_accessori_partenza)
            foreach ($this->lista_servizi_accessori_partenza as $servizio) {
                $id_servizio = $servizio->getCampo(ServizioIstantaneo::ID);
                $tipo = Servizio::SERVIZIO_PARTENZA;
                $percentuale = $servizio->getCampo(Servizio::PERCENTUALE);
                $valore_assoluto = $servizio->getCampo(Servizio::VALORE_ASSOLUTO);
                $margine = $servizio->getCampo(Servizio::MARGINE);
                $sql ="INSERT INTO servizi_accessori_aggravanti_preventivo (id_servizio, id_preventivo, tipo, percentuale, valore_assoluto, margine)
              VALUES ('$id_servizio', '$id_preventivo', '$tipo','$percentuale', '$valore_assoluto','$margine')";

                $res = mysql_query($sql);
            }

        DBUtils::closeConnection($con);

    }



    private function _saveServiziAccessiDestinazione($id_preventivo)
    {
        {
            $con = DBUtils::getConnection();
            if ($this->lista_servizi_accessori_destinazione)
                foreach ($this->lista_servizi_accessori_destinazione as $servizio) {
                    $id_servizio = $servizio->getCampo(ServizioIstantaneo::ID);
                    $tipo = Servizio::SERVIZIO_DESTINAZIONE;
                    $percentuale = $servizio->getCampo(Servizio::PERCENTUALE);
                    $valore_assoluto = $servizio->getCampo(Servizio::VALORE_ASSOLUTO);
                    $margine = $servizio->getCampo(Servizio::MARGINE);

                    $sql ="INSERT INTO servizi_accessori_aggravanti_preventivo (id_servizio, id_preventivo, tipo, percentuale, valore_assoluto, margine)
              VALUES ('$id_servizio', '$id_preventivo', '$tipo','$percentuale', '$valore_assoluto','$margine')";

                    $res = mysql_query($sql);
                }

            DBUtils::closeConnection($con);

        }

    }

    /**
     * Carica il preventivo indicato
     * @param $id_preventivo del preventivo da caricare
     */
    public function load($id_preventivo)
    {
        //echo "\nInizio Caricamento ";
        $found = $this->_loadPreventivo($id_preventivo);
        $this->_loadItems($id_preventivo);
        $this->_loadServiziIstantaneoPreventivo($id_preventivo);

        //echo "\nCaricamento Preventivo OK";

        return $found;
    }

    public function loadDettaglio($id_preventivo)
    {
        //echo "\nInizio Caricamento ";
        $found = $this->_loadPreventivo($id_preventivo);
        $this->_loadItems($id_preventivo);
        $this->_loadServiziDettaglio($id_preventivo);
        $this->_loadDeposito($id_preventivo);
        $this->_loadVociPreventivoExtra($id_preventivo);

        //echo "\nCaricamento Preventivo OK";
        return $found;

    }

    private function _loadPreventivo($id_preventivo) {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM preventivi WHERE id_preventivo=$id_preventivo";
        $res = mysql_query($sql);
        //$res = $con->query($sql);
        $found = 0;
        while ($row=mysql_fetch_object($res)) {
            $this->id_preventivo = $row->id_preventivo;
            $this->data_preventivo = $row->data;
            $this->customer = new Customer($row->id_cliente, $row->email_cliente);
            $this->partenza = new Indirizzo($row->partenza_indirizzo, $row->partenza_citta,
                $row->partenza_provincia, $row->partenza_cap, $row->partenza_codice_citta, $row->partenza_codice_provincia);
            $this->destinazione = new Indirizzo($row->destinazione_indirizzo, $row->destinazione_citta,
                $row->destinazione_provincia, $row->destinazione_cap, $row->destinazione_codice_citta, $row->destinazione_codice_provincia);
            $this->setStato($row->stato);
            $this->data_sopraluogo = $row->data_sopraluogo;
            $this->data_trasloco = $row->data_trasloco;
            $this->importo = $row->importo;
            $this->id_agenzia = $row->id_agenzia;
            $this->id_trasportatore = $row->id_trasportatore;
            $this->id_traslocatore_partenza = $row->id_traslocatore_partenza;
            $this->id_traslocatore_destinazione = $row->id_traslocatore_destinazione;
            $this->id_depositario = $row->id_depositario;
            $this->note = $row->note;
            $this->flag_sopraluogo = $row->flag_sopraluogo;
            $this->importo_commessa_depositario = $row->importo_commessa_depositario;
            $this->importo_commessa_trasportatore = $row->importo_commessa_trasportatore;
            $this->importo_commessa_traslocatore_partenza = $row->importo_commessa_traslocatore_partenza;
            $this->importo_commessa_traslocatore_destinazione = $row->importo_commessa_traslocatore_destinazione;
            $this->id_cliente = $row->id_cliente;
            $this->imponibile = $row->imponibile;
            $this->iva = $row->iva;
            $this->mc = $row->mc;
            $this->note_interne = $row->note_interne;
            $this->tipo = $row->tipo;


            $found = true;
        }
        //echo "\nINDIRIZZO: ".$id_preventivo.", found: ".$found;
        //print_r($this->partenza);
        DBUtils::closeConnection($con);
        return $found;
    }

    private function _loadVociPreventivoExtra($id_preventivo) {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM voci_preventivo_extra WHERE id_preventivo=$id_preventivo ORDER BY id ASC";
        $res = mysql_query($sql);
        $found = 0;
        $this->lista_voci_extra = array();

        while ($row=mysql_fetch_object($res))
        {
            //crea l'oggetto VocePreventivoExtra
            $voce = new VocePreventivoExtra($row->segno, $row->descrizione, $row->valore);

            $this->lista_voci_extra[] = $voce;
            $found = true;
        }

        DBUtils::closeConnection($con);
        return $found;
    }

    private function _loadItems($id_preventivo) {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM items_preventivo WHERE id_preventivo=$id_preventivo";
        $res = mysql_query($sql);
        $found = false;
        while ($row=mysql_fetch_object($res))
        {
            //crea l'oggetto Arredo
            $item = new ItemPreventivatoreBusiness($row->descrizione);
            $item->lunghezza = $row->dim_L;
            $item->altezza = $row->dim_A;
            $item->profondita = $row->dim_P;
            $item->qta = $row->qta;
            $item->mc = $row->mc;
            $this->lista_item[] = $item;
            $found = true;
        }

        DBUtils::closeConnection($con);
        return $found;
    }


    private function _loadServiziIstantaneoPreventivo($id_preventivo) {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM servizi_istantaneo_preventivo WHERE id_preventivo=$id_preventivo";
        $res = mysql_query($sql);
        $found = 0;
        while ($row=mysql_fetch_object($res))
        {
            //crea l'oggetto Arredo
            $servizio = new ServizioIstantaneo($row->id_servizio);

            $this->lista_servizi_istantaneo[] = $servizio;
            $found = true;
        }

        DBUtils::closeConnection($con);
        return $found;
    }

    private function _loadServiziDettaglio($id_preventivo) {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM servizi_accessori_aggravanti_preventivo WHERE id_preventivo=$id_preventivo";
        $res = mysql_query($sql);
        $found = 0;
        while ($row=mysql_fetch_object($res))
        {
            //crea l'oggetto Servizio
            $servizio = new ServizioAccessoreAggravante($row->id_servizio);
            $servizio->setMargine($row->margine);
            $servizio->setPercentuale($row->percentuale);
            $servizio->setValoreAssoluto($row->valore_assoluto);

            if ($row->tipo == Servizio::SERVIZIO_DESTINAZIONE)
                $this->lista_servizi_accessori_destinazione[] = $servizio;
            else
                $this->lista_servizi_accessori_partenza[] = $servizio;
            $found = true;
        }

        DBUtils::closeConnection($con);
        return $found;
    }


    private function _loadDeposito($id_preventivo) {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM deposito WHERE id_preventivo=$id_preventivo";
        $res = mysql_query($sql);
        while ($row=mysql_fetch_object($res))
        {
            $this->giorni_deposito = $row->numero_giorni;
        }
        DBUtils::closeConnection($con);

    }



    public function getItems()
    {
        return $this->lista_item;
    }

    public function getCliente()
    {
        return $this->customer;
    }

    public function getIndirizzoPartenza()
    {
        return $this->partenza;
    }

    public function getIndirizzoDestinazione()
    {
        return $this->destinazione;
    }

    public function getListaServiziIstantaneo()
    {
        return $this->lista_servizi_istantaneo;
    }

    public function getStato()
    {
        return $this->stato;
    }

    public function getDataSopraluogo() { return $this->data_sopraluogo; }
    public function getDataTrasloco() { return $this->data_trasloco; }
    public function getDataPreventivo() { return $this->data_preventivo; }


    public function setDataTrasloco($data) { $this->data_trasloco = $data; }
    public function setDataSopraluogo($data) { $this->data_sopraluogo = $data; }


    /**
     * Assegna il lavoro al trasportatore
     * @param $id_trasportatore del trasportatore a cui è assegnato il preventivo/ordine
     */
    public function setIdTrasportatore($id_trasportatore)
    {
        StoriaAssegnazioni::addHistoryAssegnazioneTrasportatore($this->id_preventivo, $id_trasportatore, 'ASSEGNATO');
        $this->id_trasportatore = $id_trasportatore;
        $this->save();
    }

    public function confirmTrasportatore()
    {
        StoriaAssegnazioni::addHistoryAssegnazioneTrasportatore($this->id_preventivo, $this->id_trasportatore, 'ACCETTATO');
        $this->save();
    }

    /**
     * Rimuove il lavoro al trasportatore
     */
    public function removeIdTrasportatore($note)
    {
        StoriaAssegnazioni::addHistoryAssegnazioneTrasportatore($this->id_preventivo, $this->id_trasportatore, 'RIFIUTATO', $note);
        $this->id_trasportatore = null;
        $this->save();
    }

    /**
     * Assegna il lavoro al depositario
     * @param $id_depositario del depositario a cui è assegnato il preventivo/ordine
     */
    public function setIdDepositario($id_depositario)
    {
        StoriaAssegnazioni::addHistoryAssegnazioneDepositario($this->id_preventivo, $id_depositario, 'ASSEGNATO');
        $this->id_depositario = $id_depositario;
        $this->save();
    }

    public function confirmDepositario()
    {
        StoriaAssegnazioni::addHistoryAssegnazioneDepositario($this->id_preventivo, $this->id_depositario, 'ACCETTATO');
        $this->save();
    }

    /**
     * Rimuove il lavoro al depositario
     */
    public function removeIdDepositario($note)
    {
        StoriaAssegnazioni::addHistoryAssegnazioneDepositario($this->id_preventivo, $this->id_depositario, 'RIFIUTATO', $note);
        $this->id_depositario = null;
        $this->save();
    }

    /**
     * Assegna il lavoro al traslocagore
     * @param $id_traslocatore del traslocatore a cui è assegnato il preventivo/ordine per la partenza
     */
    public function setIdTraslocatorePartenza($id_traslocatore)
    {
        StoriaAssegnazioni::addHistoryAssegnazioneTraslocatorePartenza($this->id_preventivo, $id_traslocatore, 'ASSEGNATO');
        $this->id_traslocatore_partenza = $id_traslocatore;
        $this->save();

    }

    public function confirmTraslocatorePartenza()
    {
        StoriaAssegnazioni::addHistoryAssegnazioneTraslocatorePartenza($this->id_preventivo, $this->id_traslocatore_partenza, 'ACCETTATO');

        $this->save();

    }

    /**
     * Rimuove il lavoro al trasportatore
     */
    public function removeIdTraslocatorePartenza($note)
    {
        StoriaAssegnazioni::addHistoryAssegnazioneTraslocatorePartenza($this->id_preventivo, $this->id_traslocatore_partenza, 'RIFIUTATO', $note);
        $this->id_traslocatore_partenza = null;
        $this->save();

    }

    /**
     * Assegna il lavoro al traslocatore
     * @param $id_traslocatore del traslocatore a cui è assegnato il preventivo/ordine per la destinazione
     */
    public function setIdTraslocatoreDestinazione($id_traslocatore)
    {
        StoriaAssegnazioni::addHistoryAssegnazioneTraslocatoreDestinazione($this->id_preventivo, $id_traslocatore, 'ASSEGNATO');
        $this->id_traslocatore_destinazione = $id_traslocatore;
        $this->save();

    }

    public function confirmTraslocatoreDestinazione()
    {
        StoriaAssegnazioni::addHistoryAssegnazioneTraslocatoreDestinazione($this->id_preventivo, $this->id_traslocatore_destinazione, 'ACCETTATO');
        $this->save();

    }

    /**
     * Rimuove il lavoro al trasportatore
     */
    public function removeIdTraslocatoreDestinazione($note)
    {
        StoriaAssegnazioni::addHistoryAssegnazioneTraslocatoreDestinazione($this->id_preventivo, $this->id_traslocatore_destinazione, 'RIFIUTATO', $note);
        $this->id_traslocatore_destinazione = null;
        $this->save();

    }

    public function getId() { return $this->id_preventivo; }

    //TODO metodo per tornare l'oggetyto preventivatore di dettaglio per poterlo modificare

    public function getStoriaAssegnazioniTrasportatori()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT id FROM history_trasportatori WHERE id_preventivo=$this->id_preventivo";
        //echo "\nSQL1: ".$sql."\n";
        $res = mysql_query($sql);
        $found = 0;
        $result = array();

        while ($row=mysql_fetch_object($res))
        {
            //crea l'oggetto Arredo

            $obj = new AssegnazioneTrasportatore();
            $obj->load($row->id);

            $result[] = $obj;
        }

        DBUtils::closeConnection($con);
        return $result;

    }

    public function getStoriaAssegnazioniDepositario()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT id FROM history_depositario WHERE id_preventivo=$this->id_preventivo";
        //echo "\nSQL1: ".$sql."\n";
        $res = mysql_query($sql);
        $found = 0;
        $result = array();

        while ($row=mysql_fetch_object($res))
        {
            //crea l'oggetto Arredo

            $obj = new AssegnazioneDepositario();
            $obj->load($row->id);

            $result[] = $obj;
        }

        DBUtils::closeConnection($con);
        return $result;

    }

    public function getStoriaAssegnazioniTraslocatoriPartenza()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT id FROM history_traslocatori_partenza WHERE id_preventivo=$this->id_preventivo";
        //echo "\nSQL1: ".$sql."\n";
        $res = mysql_query($sql);
        $found = 0;
        $result = array();

        while ($row=mysql_fetch_object($res))
        {
            //crea l'oggetto Arredo

            $obj = new AssegnazioneTraslocatorePartenza();
            $obj->load($row->id);

            $result[] = $obj;
        }

        DBUtils::closeConnection($con);
        return $result;
    }

    public function getStoriaAssegnazioniTraslocatoriDestinazione()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT id FROM history_traslocatori_destinazione WHERE id_preventivo=$this->id_preventivo";
        //echo "\nSQL1: ".$sql."\n";
        $res = mysql_query($sql);
        $found = 0;
        $result = array();

        while ($row=mysql_fetch_object($res))
        {
            //crea l'oggetto Arredo

            $obj = new AssegnazioneTraslocatoreDestinazione();
            $obj->load($row->id);

            $result[] = $obj;
        }

        DBUtils::closeConnection($con);
        return $result;
    }

    /**
     * ritorna il prevenetivatore di dettaglio
     */

    public function getPreventivatore()
    {

        $preventivatore = new PreventivatoreBusiness();

        //carica gli arredi
        if ($this->lista_item)
            foreach ($this->lista_item as $item)
                $preventivatore->addItem($item);

        //carica i servizi
        if ($this->lista_servizi_accessori_partenza)
            foreach ($this->lista_servizi_accessori_partenza as $servizio)
                $preventivatore->addServizioByItem($servizio, Servizio::SERVIZIO_PARTENZA);
                //$preventivatore->addServizioById($servizio->getId(), Servizio::SERVIZIO_PARTENZA);

        if ($this->lista_servizi_accessori_destinazione)
            foreach ($this->lista_servizi_accessori_destinazione as $servizio)
                $preventivatore->addServizioByItem($servizio, Servizio::SERVIZIO_DESTINAZIONE);
                //$preventivatore->addServizioById($servizio->getId(), Servizio::SERVIZIO_DESTINAZIONE);

        if ($this->lista_voci_extra)
            foreach ($this->lista_voci_extra as $voce)
                $preventivatore->addVocePreventivoExtra($voce);



        //carica altri parametri
        $preventivatore->setIndirizzoPartenza($this->partenza);
        $preventivatore->setIndirizzoDestinazione($this->destinazione);
        $preventivatore->setGiorniDeposito($this->giorni_deposito);
        $preventivatore->setNote($this->note);
        $preventivatore->setFlagSopraluogo($this->flag_sopraluogo);

        $preventivatore->setReferencePreventivo($this);
        //$preventivatore->setCliente($this->customer); TODO

        return $preventivatore;

    }


    public function getImporto() { return $this->importo; }

    public function getAgenzia() { return $this->id_agenzia;}
    public function setAgenzia($id_agenzia) { $this->id_agenzia = $id_agenzia; }
    public function getIdTrasportatore() { return $this->id_trasportatore;}
    public function getIdDepositario() { return $this->id_depositario;}
    public function getIdTraslocatoreDestinazione() { return $this->id_traslocatore_destinazione;}
    public function getIdTraslocatorePartenza() { return $this->id_traslocatore_partenza;}

    public function setGiorniDeposito($numero_giorni) { $this->giorni_deposito = $numero_giorni; }
    public function getGiorniDeposito() { return $this->giorni_deposito; }

    public function getImportoCommessaTrasportatore() { return $this->importo_commessa_trasportatore;}
    public function getImportoCommessaDepositario() { return $this->importo_commessa_depositario;}
    public function getImportoCommessaTraslocatorePartenza() { return $this->importo_commessa_traslocatore_partenza;}
    public function getImportoCommessaTraslocatoreDestinazione() { return $this->importo_commessa_traslocatore_destinazione;}
    public function getProvvigioneAgenzia() { return $this->importo_commessa_trasportatore * Parametri::getProvvigioneAgenzia();}



    public function getStatoAccettazioneOperatori()
    {
        $con = DBUtils::getConnection();
        if ($this->id_depositario !=0) {
        $sql ="SELECT p.id_trasportatore, p.id_traslocatore_partenza, p.id_traslocatore_destinazione , ht.stato, htp.stato, htd.stato, hd.stato FROM preventivi p, history_trasportatori ht, history_depositario hd,history_traslocatori_partenza htp, history_traslocatori_destinazione htd WHERE
               p.id_preventivo = ht.id_preventivo AND
               p.id_preventivo = htp.id_preventivo AND
               p.id_preventivo = htd.id_preventivo AND
               p.id_trasportatore = ht.id_trasportatore AND
               p.id_traslocatore_partenza = htp.id_traslocatore AND
               p.id_traslocatore_destinazione = htd.id_traslocatore AND
               p.id_depositario = hd.id_depositario AND
               ht.stato = 'ACCETTATO' AND
               htp.stato = 'ACCETTATO' AND
               hd.stato = 'ACCETTATO' AND
               htd.stato = 'ACCETTATO' AND
               p.id_preventivo = $this->id_preventivo";
        }
        else
        {
            $sql ="SELECT p.id_trasportatore, p.id_traslocatore_partenza, p.id_traslocatore_destinazione , ht.stato, htp.stato, htd.stato FROM preventivi p, history_trasportatori ht, history_traslocatori_partenza htp, history_traslocatori_destinazione htd WHERE
               p.id_preventivo = ht.id_preventivo AND
               p.id_preventivo = htp.id_preventivo AND
               p.id_preventivo = htd.id_preventivo AND
               p.id_trasportatore = ht.id_trasportatore AND
               p.id_traslocatore_partenza = htp.id_traslocatore AND
               p.id_traslocatore_destinazione = htd.id_traslocatore AND
               ht.stato = 'ACCETTATO' AND
               htp.stato = 'ACCETTATO' AND
               htd.stato = 'ACCETTATO' AND
               p.id_preventivo = $this->id_preventivo
               ";
        }
        //echo "\nQUERY: ".$sql;
        $res = mysql_query($sql);
        $found = false;
        while ($row = mysql_fetch_object($res))
        {
            $found = true;
        }
        DBUtils::closeConnection($con);
        return $found;
    }

    private function _saveGiorniDeposito($id_preventivo) {
        $con = DBUtils::getConnection();
        $num_giorni = $this->giorni_deposito;

        $sql ="INSERT INTO deposito (id_preventivo, numero_giorni)
        VALUES ('$id_preventivo', '$num_giorni'
        )";

        $res = mysql_query($sql);
        if (!$res) {
            die ("ERRORE: ".$sql);
        }
    }


    /**
     * Trasforma un preventivo in ordine
     * @return OrdineCliente
     */
    public function changeToOrdine()
    {

        //crea la tabella degli ordini verso i fornitori
        //Rielabora
        $preventivatore = $this->getPreventivatore();
        $result = $preventivatore->elabora();
        $imponibile = $result['prezzo_cliente_senza_iva'];
        $iva = $result['prezzo_cliente_con_iva'] - $result['prezzo_cliente_senza_iva'];
        $importo_trasportatore = $result['costo_trazione'];
        $importo_depositario =  $result['deposito'];
        $importo_traslocatore_partenza =  $result['costo_scarico_totale'] ;
        $importo_traslocatore_destinazione =  $result['costo_salita_piano_totale'] + $result['costo_montaggio_totale'] + $result['costo_scarico_ricarico_hub_totale'];

        $totali = array();
        $totali[$this->id_trasportatore] = 0;
        $totali[$this->id_depositario] = 0;
        $totali[$this->id_traslocatore_destinazione] = 0;
        $totali[$this->id_traslocatore_partenza] = 0;

        $totaliMC = array();
        $totaliMC[$this->id_trasportatore] = 0;
        $totaliMC[$this->id_depositario] = 0;
        $totaliMC[$this->id_traslocatore_destinazione] = 0;
        $totaliMC[$this->id_traslocatore_partenza] = 0;


        $totali[$this->id_trasportatore] = $totali[$this->id_trasportatore] + $importo_trasportatore;
        $totali[$this->id_depositario] = $totali[$this->id_depositario] + $importo_depositario;
        $totali[$this->id_traslocatore_partenza] = $totali[$this->id_traslocatore_partenza] + $importo_traslocatore_partenza;
        $totali[$this->id_traslocatore_destinazione] = $totali[$this->id_traslocatore_destinazione] + $importo_traslocatore_destinazione;


        $mc = $preventivatore->getMC();

        $totaliMC[$this->id_trasportatore] = $totaliMC[$this->id_trasportatore] + $mc;
        $totaliMC[$this->id_depositario] = $totaliMC[$this->id_depositario] + $mc;
        $totaliMC[$this->id_traslocatore_destinazione] = $totaliMC[$this->id_traslocatore_destinazione] + $mc;
        $totaliMC[$this->id_traslocatore_partenza]  = $totaliMC[$this->id_traslocatore_partenza] + $mc;

        $ordine_trasportatore = new OrdineFornitore($this->id_preventivo, $this->id_trasportatore, $totali[$this->id_trasportatore], $totaliMC[$this->id_trasportatore], date('Y-m-d'));
        $ordine_trasportatore->save();

        $ordine_traslocatore_partenza = new OrdineFornitore($this->id_preventivo, $this->id_traslocatore_partenza, $totali[$this->id_traslocatore_partenza], $totaliMC[$this->id_traslocatore_destinazione], date('Y-m-d'));
        $ordine_traslocatore_partenza->save();

        $ordine_traslocatore_destinazione = new OrdineFornitore($this->id_preventivo, $this->id_traslocatore_destinazione, $totali[$this->id_traslocatore_destinazione], $totaliMC[$this->id_traslocatore_partenza], date('Y-m-d'));
        $ordine_traslocatore_destinazione->save();

        if ($this->giorni_deposito>10) {
            $ordine_depositario = new OrdineFornitore($this->id_preventivo, $this->id_depositario, $totali[$this->id_depositario], $totaliMC[$this->id_depositario], date('Y-m-d'));
            $ordine_depositario->save();
        }



        $data_ordine = date('Y-m-d');
        $con = DBUtils::getConnection();
        $sql ="UPDATE preventivi SET tipo=".OrdineCliente::TIPO_ORDINE." , data='".$data_ordine."' ,
         importo_commessa_trasportatore ='".$totali[$this->id_trasportatore]."',
         importo_commessa_traslocatore_partenza ='".$totali[$this->id_traslocatore_partenza]."',
         importo_commessa_traslocatore_destinazione ='".$totali[$this->id_traslocatore_destinazione]."',
         importo_commessa_depositario ='".$totali[$this->id_depositario]."',
         imponibile ='".$imponibile."',
         iva ='".$iva."'
        WHERE id_preventivo=".$this->id_preventivo;

        $res = mysql_query($sql);
        //echo "\nSQL: ".$sql;
        DBUtils::closeConnection($con);

        return new OrdineCliente($this->id_preventivo);
    }

    public function setImponibile($imponibile) { $this->imponibile = $imponibile; }
    public function setIva($iva) { $this->iva = $iva; }

    public function getImponibile() { return $this->imponibile; }
    public function getIva() { return $this->iva; }

    public function setMC($mc) { $this->mc = $mc; }
    public function getMC() { return $this->mc; }

    public function getNoteInterne() { return $this->note_interne; }
    public function setNoteInterne($note)
    {
        $this->note_interne = $note;
    }


    private function updateCommesse()
    {

        //crea la tabella degli ordini verso i fornitori
        //Rielabora
        $preventivatore = $this->getPreventivatore();
        $result = $preventivatore->elabora();
        $imponibile = $result['prezzo_cliente_senza_iva'];
        $iva = $result['prezzo_cliente_con_iva'] - $result['prezzo_cliente_senza_iva'];
        $importo_trasportatore = $result['costo_trazione'];
        $importo_depositario =  $result['deposito'];
        $importo_traslocatore_partenza =  $result['costo_servizio_smontaggio_imballo_carico'] + $result['costo_servizio_imballo_carico'];
        $importo_traslocatore_destinazione =  $result['costo_servizio_scarico'] + $result['costo_servizio_salita'] + $result['costo_servizio_montaggio'];

        $totali = array();
        $totali[$this->id_trasportatore] = 0;
        $totali[$this->id_depositario] = 0;
        $totali[$this->id_traslocatore_destinazione] = 0;
        $totali[$this->id_traslocatore_partenza] = 0;

        $totaliMC = array();
        $totaliMC[$this->id_trasportatore] = 0;
        $totaliMC[$this->id_depositario] = 0;
        $totaliMC[$this->id_traslocatore_destinazione] = 0;
        $totaliMC[$this->id_traslocatore_partenza] = 0;


        $totali[$this->id_trasportatore] = $totali[$this->id_trasportatore] + $importo_trasportatore;
        $totali[$this->id_depositario] = $totali[$this->id_depositario] + $importo_depositario;
        $totali[$this->id_traslocatore_partenza] = $totali[$this->id_traslocatore_partenza] + $importo_traslocatore_partenza;
        $totali[$this->id_traslocatore_destinazione] = $totali[$this->id_traslocatore_destinazione] + $importo_traslocatore_destinazione;


        $mc = $preventivatore->getDettaglioMC();

        $totaliMC[$this->id_trasportatore] = $totaliMC[$this->id_trasportatore] + $mc['mc_da_trasportare'];
        $totaliMC[$this->id_depositario] = $totaliMC[$this->id_depositario] + $mc['mc_da_trasportare'];
        $totaliMC[$this->id_traslocatore_destinazione] = $totaliMC[$this->id_traslocatore_destinazione] + $mc['mc_smontaggio'] + $mc['mc_no_smontaggio'];
        $totaliMC[$this->id_traslocatore_partenza]  = $totaliMC[$this->id_traslocatore_partenza] + $mc['mc_da_rimontare'] + $mc['mc_scarico_salita_piano'];


        $ordine_trasportatore = new OrdineFornitore($this->id_preventivo, $this->id_trasportatore, $totali[$this->id_trasportatore], $totaliMC[$this->id_trasportatore], date('Y-m-d'));
        $ordine_trasportatore->save();

        $ordine_traslocatore_partenza = new OrdineFornitore($this->id_preventivo, $this->id_traslocatore_partenza, $totali[$this->id_traslocatore_partenza], $totaliMC[$this->id_traslocatore_destinazione], date('Y-m-d'));
        $ordine_traslocatore_partenza->save();

        $ordine_traslocatore_destinazione = new OrdineFornitore($this->id_preventivo, $this->id_traslocatore_destinazione, $totali[$this->id_traslocatore_destinazione], $totaliMC[$this->id_traslocatore_partenza], date('Y-m-d'));
        $ordine_traslocatore_destinazione->save();

        if ($this->giorni_deposito> 10)
        {
            $ordine_depositario = new OrdineFornitore($this->id_preventivo, $this->id_depositario, $totali[$this->id_depositario], $totaliMC[$this->id_depositario], date('Y-m-d'));
            $ordine_depositario->save();

        }

        $con = DBUtils::getConnection();
        $sql ="UPDATE preventivi SET
         importo_commessa_trasportatore ='".$totali[$this->id_trasportatore]."',
         importo_commessa_traslocatore_partenza ='".$totali[$this->id_traslocatore_partenza]."',
         importo_commessa_traslocatore_destinazione ='".$totali[$this->id_traslocatore_destinazione]."',
         importo_commessa_depositario ='".$totali[$this->id_depositario]."',
         imponibile ='".$imponibile."',
         iva ='".$iva."'
        WHERE id_preventivo=".$this->id_preventivo;

        $res = mysql_query($sql);


        DBUtils::closeConnection($con);

    }
}