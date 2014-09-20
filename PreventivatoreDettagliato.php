<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 10:57 AM
 */


class PreventivatoreDettagliato extends Preventivatore
{

    private $mc_smontaggio;
    private $mc_no_smontaggio;
    private $mc_da_trasportare;
    private $mc_da_rimontare;
    private $mc_scarico_salita_piano;

    const  COSTO_FORNITORE = 0;
    const  COSTO_CLIENTE = 1;

    private $lista_voci_extra = array();
    private $preventivo = null;

    public $importo_commessa_trasportatore;
    public $importo_commessa_depositario;
    public $importo_commessa_traslocatore_partenza;
    public $importo_commessa_traslocatore_destinazione;

    public $partenza_localizzazione;
    public $partenza_localizzazione_tipo;
    public $partenza_localizzazione_tipo_piano;

    public $destinazione_localizzazione;
    public $destinazione_localizzazione_tipo;
    public $destinazione_localizzazione_tipo_piano;



    public function setLocalizzazionePartenza($id_localizzazione, $id_tipo, $id_piano)
    {
        $this->partenza_localizzazione = $id_localizzazione;
        $this->partenza_localizzazione_tipo = $id_tipo;
        $this->partenza_localizzazione_tipo_piano = $id_piano;
    }


    public function setLocalizzazioneDestinazione($id_localizzazione, $id_tipo, $id_piano)
    {
        $this->destinazione_localizzazione = $id_localizzazione;
        $this->destinazione_localizzazione_tipo = $id_tipo;
        $this->destinazione_localizzazione_tipo_piano = $id_piano;
    }

    /**
     * @param $id_arredo
     * @param null $parte_variabile
     * @param int $qta
     * @param $parametro_B -> Arredo::MONTATO_PIENO; Arredo::MONTATO_VUOTO; Arredo::SMONTATO_PIENO; Arredo::SMONTATO_VUOTO
     * @param null $dim_A
     * @param null $dim_P
     * @param null $dim_L
     * @return int
     */
    public function addArredoById($id_arredo, $parte_variabile=null, $qta=1, $parametro_B = null, $dim_A = null, $dim_P = null, $dim_L = null,
        $descrizione = null, $flag_servizio_montaggio = 0, $flag_servizio_smontaggio = 0, $flag_servizio_imballaggio = 0)
     {
         //crea l'oggetto Arredo
         $arredo = new ArredoDettagliato($id_arredo);

         //aggiunge le parti varibili per il calcolo dei mc
         if ($parte_variabile)
         {
             foreach ($parte_variabile as $key=>$value)
             {
                 //echo "\nKey: ".$key.", value: ".$value."\n";
                 $arredo->setParteVariabile(strtoupper($key), $value);
             }
         }

         //configura il parametro B
         if ($parametro_B)
         {
             $arredo->setParametroB($parametro_B);
         }

         $arredo->setQta($qta);

         if ($dim_A != null) $arredo->setCampo(Arredo::DIM_A, $dim_A);
         if ($dim_P != null) $arredo->setCampo(Arredo::DIM_P, $dim_P);
         if ($dim_L != null) $arredo->setCampo(Arredo::DIM_L, $dim_L);

         $arredo->setServizioImballaggio($flag_servizio_imballaggio);
         $arredo->setServizioSmontaggio($flag_servizio_smontaggio);
         $arredo->setServizioMontaggio($flag_servizio_montaggio);
         $arredo->setCampo(Arredo::DESCRIZIONE, $descrizione);

         $this->lista_arredi[] = $arredo;

         return $arredo->getMC();
     }

    public function addArredoByItem(ArredoDettagliato $arredo)
    {
        $this->lista_arredi[] = $arredo;
        return $arredo->getMC();
    }

    /**
     * Metodo usato per aggiornare la lista degli arredi quando si cancella un item
     * @param $lista la nuova lista arredi
     */
    public function setListaArredi($lista)
    {
        $this->lista_arredi = $lista;
    }


    /**
     * Metodo usato per rimuovere un arredo dalla lista
     * @param $row indice dell'elemento da rimuovere dalla lista degli arredi
     */
    public function removeArredoByRow($row)
    {
        unset($this->lista_arredi[$row]);
    }


    public function addServizioById($id_servizio, $tipo)
    {
        $servizio = new ServizioAccessoreAggravante($id_servizio);
        if ($tipo == Servizio::SERVIZIO_PARTENZA)
            $this->lista_servizi_partenza[] = $servizio;
        else
            $this->lista_servizi_destinazione[] = $servizio;
    }

    /**
     * Elimina un servizio dalla lista dei servizi
     * @param $id_servizio del servizio da rimuovere
     * @param $tipo tipo di servizio Partenza, Destinazione
     */
    public function removeServizioById($id_servizio, $tipo)
    {
        $lista = null;
        if ($tipo == Servizio::SERVIZIO_PARTENZA)
            $lista = $this->lista_servizi_partenza;
        else
            $lista = $this->lista_servizi_destinazione;

        $tmp_lista = array();
        foreach ($lista as $servizio) {
            if ($servizio->getCampo(Servizio::ID)!=$id_servizio)
            {
                $tmp_lista[] = $servizio;
            }
        }

        if ($tipo == Servizio::SERVIZIO_PARTENZA)
            $this->lista_servizi_partenza = $tmp_lista;
        else
            $this->lista_servizi_destinazione = $tmp_lista;
    }

    public function addServizioByItem(ServizioAccessoreAggravante $servizio, $tipo)
    {

        if ($tipo == Servizio::SERVIZIO_PARTENZA)
            $this->lista_servizi_partenza[] = $servizio;
        else
            $this->lista_servizi_destinazione[] = $servizio;
    }

    /*
     * Calcola e ritorna i mc
     */







    public function getDettaglioMC()
    {
        //calcola mc
        $calcolatore = new CalcolatoreDettaglio();
        $calcolatore->km = $this->getKM();
        $calcolatore->lista_arredi = $this->lista_arredi;
        $calcolatore->lista_servizi = $this->lista_servizi;
        $calcolatore->lista_servizi_partenza = $this->lista_servizi_partenza;
        $calcolatore->lista_servizi_destinazione = $this->lista_servizi_destinazione;
        $calcolatore->giorni_deposito = $this->giorni_deposito;
        $calcolatore->lista_voci_extra = $this->lista_voci_extra;

        return $calcolatore->getDettaglioMC();
    }

    private function elaboraInstanteno()
    {

        $calcolatore = new CalcolatoreIstantaneo();
        $calcolatore->km = $this->getKM();
        $calcolatore->lista_arredi = $this->lista_arredi;
        $calcolatore->lista_servizi = $this->lista_servizi;
        $result = $calcolatore->elabora();

        $this->prezzo_cliente_con_iva = round($result['prezzo_cliente_con_iva'], 2);
        $this->prezzo_cliente_senza_iva = round($result['prezzo_cliente_senza_iva'],2);
        $this->mc = round($result['mc'],3);


        return $result;
    }

    private function elaboraDettaglio()
    {

        $calcolatore = new CalcolatoreDettaglio();
        $calcolatore->km = $this->getKM();
        $calcolatore->lista_arredi = $this->lista_arredi;
        $calcolatore->lista_servizi = $this->lista_servizi;
        $calcolatore->lista_servizi_partenza = $this->lista_servizi_partenza;
        $calcolatore->lista_servizi_destinazione = $this->lista_servizi_destinazione;
        $calcolatore->giorni_deposito = $this->giorni_deposito;
        $calcolatore->lista_voci_extra = $this->lista_voci_extra;
        $result = $calcolatore->elabora();

        $this->prezzo_cliente_con_iva = round($result['prezzo_cliente_con_iva'], 2);
        $this->prezzo_cliente_senza_iva = round($result['prezzo_cliente_senza_iva'],2);


        $this->importo_commessa_trasportatore = round($result['tariffa_trasportatore'], 2);
        $this->importo_commessa_traslocatore_partenza = round($result['tariffa_traslocatore_partenza'], 2);
        $this->importo_commessa_traslocatore_destinazione = round($result['tariffa_traslocatore_destinazione'], 2);
        $this->importo_commessa_depositario = round($result['tariffa_depositario'], 2);

        return $result;
    }

    public function elabora($modo_istantaneo = false)
    {
        if ($modo_istantaneo)
            return $this->elaboraInstanteno();
        else
            return $this->elaboraDettaglio();

    }

    /**
     * Salva il preventivo
     */
    public function save(Customer $customer = null) {
        $preventivo = new Preventivo();
        if (!$this->preventivo)
            $preventivo = $this->preventivo;

        if ($customer)
            $preventivo->setCliente($customer);

        $preventivo->setPartenza($this->indirizzo_partenza);
        $preventivo->setDestinazione($this->indirizzo_destinazione);
        $preventivo->setArredi($this->lista_arredi);
        $preventivo->setServiziAccessoriPartenza($this->lista_servizi_partenza);
        $preventivo->setServiziAccessoriDestinazione($this->lista_servizi_destinazione);
        $preventivo->setImporto($this->prezzo_cliente_con_iva);
        $preventivo->setImponibile($this->prezzo_cliente_senza_iva);
        $preventivo->setIva($this->prezzo_cliente_con_iva - $this->prezzo_cliente_senza_iva);
        $preventivo->setMC($this->mc);
        $preventivo->setStato($this->stato);
        $preventivo->setNote($this->note);
        $preventivo->setNoteInterne($this->note_interne);
        $preventivo->setFlagSopraluogo($this->flag_sopraluogo);
        $preventivo->setDataSopraluogo($this->data_sopraluogo);
        $preventivo->setServiziIstantaneo($this->lista_servizi);
        $preventivo->importo_commessa_depositario = $this->importo_commessa_depositario;
        $preventivo->importo_commessa_trasportatore = $this->importo_commessa_trasportatore;
        $preventivo->importo_commessa_traslocatore_partenza = $this->importo_commessa_traslocatore_partenza;
        $preventivo->importo_commessa_traslocatore_destinazione = $this->importo_commessa_traslocatore_destinazione;
        $preventivo->setLocalizzazionePartenza($this->partenza_localizzazione, $this->partenza_localizzazione_tipo, $this->partenza_localizzazione_tipo_piano);
        $preventivo->setLocalizzazioneDestinazione($this->destinazione_localizzazione, $this->destinazione_localizzazione_tipo, $this->destinazione_localizzazione_tipo_piano);
        $preventivo->save();

        return $preventivo;
    }

    /**
     * Metodo da richiamare quando si modifica un item del preventivatore e si vuola anche aggiornare il preventivo stesso
     * @param $preventivo oggetto da aggiornare
     */
    public function updatePreventivo(Preventivo $preventivo = null)
    {
        if (!$preventivo)
            $preventivo = $this->preventivo;

        //$preventivo->setPartenza($this->indirizzo_partenza);
        //$preventivo->setDestinazione($this->indirizzo_destinazione);
        $preventivo->setArredi($this->lista_arredi);
        $preventivo->setServiziAccessoriPartenza($this->lista_servizi_partenza);
        $preventivo->setServiziAccessoriDestinazione($this->lista_servizi_destinazione);
        $preventivo->setListaVociExtra($this->lista_voci_extra);
        $preventivo->setPartenza($this->indirizzo_partenza);
        $preventivo->setDestinazione($this->indirizzo_destinazione);
        $preventivo->setImporto($this->prezzo_cliente_con_iva);
        $preventivo->setImponibile($this->prezzo_cliente_senza_iva);
        $preventivo->setIva($this->prezzo_cliente_con_iva - $this->prezzo_cliente_senza_iva );
        $preventivo->setMC($this->mc);
        $preventivo->setServiziIstantaneo($this->lista_servizi);
        $preventivo->importo_commessa_depositario = $this->importo_commessa_depositario;
        $preventivo->importo_commessa_trasportatore = $this->importo_commessa_trasportatore;
        $preventivo->importo_commessa_traslocatore_partenza = $this->importo_commessa_traslocatore_partenza;
        $preventivo->importo_commessa_traslocatore_destinazione = $this->importo_commessa_traslocatore_destinazione;
        $preventivo->setLocalizzazionePartenza($this->partenza_localizzazione, $this->partenza_localizzazione_tipo, $this->partenza_localizzazione_tipo_piano);
        $preventivo->setLocalizzazioneDestinazione($this->destinazione_localizzazione, $this->destinazione_localizzazione_tipo, $this->destinazione_localizzazione_tipo_piano);


        //$preventivo->setImporto($this->prezzo_cliente_con_iva);
        //$preventivo->setStato($this->stato);
        //$preventivo->save();
    }

    public function setCustomer($id_preventivo, Customer $customer)
    {
        $preventivo = new Preventivo();
        $preventivo->load($id_preventivo);
        $preventivo->setCliente($customer);
        $preventivo->save();
    }


    public function getMC()
    {
        return array('mc_smontaggio' => $this->mc_smontaggio,
            'mc_da_rimontare' => $this->mc_da_rimontare,
            'mc_da_trasportare' => $this->mc_da_trasportare,
            'mc_no_smontaggio' => $this->mc_no_smontaggio,
            'mc_scarico_salita_piano' => $this->mc_scarico_salita_piano
        );
    }

    public function addVocePreventivoExtra(VocePreventivoExtra $voce)
    {
        $this->lista_voci_extra[] = $voce;
    }

    public function getListaVociExtra() { return $this->lista_voci_extra; }

    public function removeVocePreventivoExtraByRow($id)
    {
        unset($this->lista_voci_extra[$id]);
    }

    public function updateVocePreventivoExtraByRow($id, VocePreventivoExtra $voce)
    {
        $this->lista_voci_extra[$id] = $voce;
    }

    public function getServiziAccessoriPartenza()
    {
        return $this->lista_servizi_partenza ;
    }

    public function getServiziAccessoriDestinazione()
    {
        return $this->lista_servizi_destinazione ;
    }

    public function setReferencePreventivo(Preventivo $ref)
    {
        $this->preventivo = $ref;
    }


    public function setListaServiziIstantaneo($lista)
    {
        $this->lista_servizi = $lista;
    }



} 