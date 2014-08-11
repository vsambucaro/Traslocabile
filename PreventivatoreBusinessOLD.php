<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/23/14
 * Time: 9:58 PM
 */


class PreventivatoreBusiness {

    const TIPO_ALGORITMO_STANDARD = 0;
    const TIPO_ALGORITMO_NON_STANDARD = 1;
    private $lista_item = array();
    private $sconto = 0;
    private $tipo_algoritmo;
    private $km = 0;
    private $stato = '';

    private $lista_servizi_partenza = array();
    private $lista_servizi_destinazione = array();
    private $indirizzo_partenza;
    private $indirizzo_destinazione;
    private $lista_voci_extra = array();

    private $giorni_deposito=0;
    private $note;
    protected $note_interne;
    private  $flag_sopraluogo = 0;
    private  $data_sopraluogo;
    private  $data_trasloco; //TODO

    private  $prezzo_cliente_senza_iva = 0;
    private $prezzo_cliente_con_iva = 0;
    private $mc;

    //reference al preventivo
    private $preventivo = null;

    public function setIndirizzoPartenza(Indirizzo $indirizzo)
    {
        $this->indirizzo_partenza = $indirizzo;
    }

    public function setIndirizzoDestinazione(Indirizzo $indirizzo)
    {
        $this->indirizzo_destinazione = $indirizzo;
    }

    public function setAlgoritmo($tipo){ $this->tipo_algoritmo = $tipo; }

    public function addItem(ItemPreventivatoreBusiness $item)
    {
        if ($item->mc == 0) {
            $item->mc = $item->altezza * $item->lunghezza * $item->profondita;
        }

        $this->lista_item[] = $item;
        return $item->mc;
    }

    public function removeItemAtRow($position)
    {
        unset($this->lista_item[$position]);
    }

    public function setListaItem($lista) { $this->lista_item = $lista; }
    public function getListaItem() { return $this->lista_item;}

    public function setSconto($sconto) { $this->sconto = $sconto; }

    private function _getCostoServizioDeposito($mc, $giorni , $tipo_costo = PreventivatoreDettagliato::COSTO_CLIENTE)
    {
        $tariffa = ParametriServizio::getParametro(ParametriServizio::TARIFFA_DEPOSITO);
        if ($tipo_costo == PreventivatoreDettagliato::COSTO_CLIENTE)
            $costo = $mc * $tariffa['prezzo'] * $giorni;
        else
            $costo =$mc * $tariffa['tariffa_operatore'] * $giorni;

        return $costo;
    }

    public function elabora()
    {
        $mc_mese = $this->getMCMese(date('Y'));

        $mc_attuali = $this->getMC();
        $this->mc = $mc_attuali;
        //calcola KM
        $calcolatoreDistanza = new CalcolatoreDistanza();
        $info = $calcolatoreDistanza->getDrivingInformationV2($this->indirizzo_partenza->toGoogleAddress(),
            $this->indirizzo_destinazione->toGoogleAddress());
        $this->setKM($info['distance']);

        $costo_scarico_ricarico_hub = TariffeSnaidero::getCostoScaricoRicaricoHub($mc_mese, $mc_attuali, $this->tipo_algoritmo);
        $costo_trazione = TariffeSnaidero::getCostoTrazione($mc_mese,  $this->km, $mc_attuali, $this->tipo_algoritmo);
        $costo_scarico = TariffeSnaidero::getCostoScarico($mc_mese, $mc_attuali, $this->tipo_algoritmo);
        $costo_salita_piano = TariffeSnaidero::getCostoSalitaPiano($mc_mese, $mc_attuali, $this->tipo_algoritmo);
        $costo_montaggio = TariffeSnaidero::getCostoMontaggio($mc_mese, $mc_attuali, $this->tipo_algoritmo);

        $valore_voci_extra  = 0;
        foreach ($this->lista_voci_extra as $voce) {
            if ($voce->getSegno() == VocePreventivoExtra::POSITIVO)
                $valore_voci_extra += $voce->getValore();
            else
                $valore_voci_extra -= $voce->getValore();
        }

        if ($this->tipo_algoritmo == PreventivatoreBusiness::TIPO_ALGORITMO_STANDARD)
        {
            $costo_montaggio_totale = $costo_montaggio * $mc_attuali;
            $costo_salita_piano_totale = $costo_salita_piano * $mc_attuali;
            $costo_scarico_totale = $costo_scarico * $mc_attuali;
            $costo_trazione_totale = $costo_trazione * $mc_attuali;
            $costo_scarico_ricarico_hub_totale = $costo_scarico_ricarico_hub * $mc_attuali;
            $deposito = $this->_getCostoServizioDeposito($mc_attuali, $this->giorni_deposito);

            $costo_servizi = $costo_montaggio_totale + $costo_salita_piano_totale + $costo_scarico_totale +
                $costo_trazione_totale + $costo_scarico_ricarico_hub_totale + $deposito;

            //Aggiungi le aggravanti
            $costo_servizi_accessori_partenza = $this->_getCostoServiziAccessoriPartenza($costo_servizi);
            $costo_servizi_accessori_destinazione = $this->_getCostoServiziAccessoriDestinazione($costo_servizi);

            $costo_complessivo = $costo_servizi + + $costo_servizi_accessori_partenza['valore_percentuale'] +
                $costo_servizi_accessori_partenza['valore_assoluto'] +
                $costo_servizi_accessori_destinazione['valore_percentuale'] +
                $costo_servizi_accessori_destinazione['valore_assoluto'] +
                + $valore_voci_extra;

            $prezzo_cliente = $costo_complessivo * (1- $this->sconto/100);

            $prezzo_cliente_con_iva = $prezzo_cliente * (1 + Parametri::getIVA());

            $result = array('costo_montaggio_totale'=>$costo_montaggio_totale,
                'costo_salita_piano_totale'=>$costo_salita_piano_totale,
                'costo_scarico_totale'=>$costo_scarico_totale,
                'deposito'=>$deposito,
                'costo_trazione'=>$costo_trazione_totale,
                'costo_scarico_ricarico_hub_totale'=>$costo_scarico_ricarico_hub_totale,
                'costo_servizi_accessori_partenza'=>$costo_servizi_accessori_partenza,
                'costo_servizi_accessori_destinazione'=>$costo_servizi_accessori_destinazione,
                'prezzo_cliente_senza_iva'=>$prezzo_cliente,
                'prezzo_cliente_con_iva'=>$prezzo_cliente_con_iva
            );

            $this->prezzo_cliente_con_iva = $prezzo_cliente_con_iva;
            $this->prezzo_cliente_senza_iva = $prezzo_cliente;
            return $result;

        }

        return 0;

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

    public function getMC()
    {
        $mc = 0;
        foreach ($this->lista_item as $item) {
            $mc += ($item->mc * $item->qta);
        }
        return $mc;
    }

    private function getMCMese($mese)
    {
        return 0;
    }


    public function setKM($km) { $this->km = $km; }
    public function getKM() { return $this->km; }



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

    public function setGiorniDeposito($numero_giorni) { $this->giorni_deposito = $numero_giorni; }
    public function getGiorniDeposito() { return $this->giorni_deposito; }

    public function setNote($note)
    {
        $this->note = $note;
    }

    public function setFlagSopraluogo($flag)
    {
        $this->flag_sopraluogo = $flag;
    }

    public function setDataSopraluogo($data)
    {
        $this->data_sopraluogo = $data;
    }

    public function getNote() { return $this->note; }
    public function getFlagSopraluogo() { return $this->flag_sopraluogo; }

    public function setDataTrasloco($data) { $this->data_trasloco = $data; }
    public function getDataTrasloco() { return $this->data_trasloco; }

    /**
     * Salva il preventivo
     */
    public function save(Customer $customer = null) {
        $preventivo = new PreventivoBusiness();
        if (!$this->preventivo)
            $preventivo = $this->preventivo;

        if ($customer)
            $preventivo->setCliente($customer);

        $preventivo->setPartenza($this->indirizzo_partenza);
        $preventivo->setDestinazione($this->indirizzo_destinazione);
        $preventivo->setItems($this->lista_item);
        $preventivo->setServiziAccessoriPartenza($this->lista_servizi_partenza);
        $preventivo->setServiziAccessoriDestinazione($this->lista_servizi_destinazione);
        $preventivo->setImporto($this->prezzo_cliente_con_iva);
        $preventivo->setImponibile($this->prezzo_cliente_senza_iva);
        $preventivo->setIva($this->prezzo_cliente_con_iva - $this->prezzo_cliente_senza_iva);
        $preventivo->setStato($this->stato);
        $preventivo->setNote($this->note);
        $preventivo->setFlagSopraluogo($this->flag_sopraluogo);
        $preventivo->setDataSopraluogo($this->data_sopraluogo);
        $preventivo->setMC($this->mc);
        $preventivo->save();

        return $preventivo;
    }

    /**
     * Metodo da richiamare quando si modifica un item del preventivatore e si vuola anche aggiornare il preventivo stesso
     * @param $preventivo oggetto da aggiornare
     */
    public function updatePreventivo(PreventivoBusiness $preventivo = null)
    {
        if (!$preventivo)
            $preventivo = $this->preventivo;
        //$preventivo->setPartenza($this->indirizzo_partenza);
        //$preventivo->setDestinazione($this->indirizzo_destinazione);
        $preventivo->setItems($this->lista_item);
        $preventivo->setServiziAccessoriPartenza($this->lista_servizi_partenza);
        $preventivo->setServiziAccessoriDestinazione($this->lista_servizi_destinazione);
        $preventivo->setListaVociExtra($this->lista_voci_extra);
        $preventivo->setPartenza($this->indirizzo_partenza);
        $preventivo->setDestinazione($this->indirizzo_destinazione);
        $preventivo->setMC($this->mc);
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

    public function setNoteInterne($note)
    {
        $this->note_interne = $note;
    }

    public function getNoteInterne() { return $this->note_interne; }

    public function setReferencePreventivo(Preventivo $ref)
    {
        $this->preventivo = $ref;
    }


} 