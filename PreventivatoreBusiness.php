<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/23/14
 * Time: 9:58 PM
 */


class PreventivatoreBusiness {

    const TIPO_ALGORITMO_MOBILIERI = 0;
    const TIPO_ALGORITMO_CUCINIERI = 1;
    const TIPO_ALGORITMO_NON_STANDARD = 2;
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


    public function elabora()
    {
        if ($this->tipo_algoritmo == PreventivatoreBusiness::TIPO_ALGORITMO_MOBILIERI)
        {
            $parametri = new ParametriPreventivoBusinessMobilieri();
            $parametri->partenza = $this->indirizzo_partenza;
            $parametri->destinazione = $this->indirizzo_destinazione;
            $parametri->mc_trasportati = $this->getMC();
            $parametri->piani_da_salire = $this->getPianiDaSalire();
            $parametri->peso = $this->getPeso();
            $parametri->giorni_deposito = $this->getGiorniDeposito();
            $parametri->montaggio = $this->getMontaggio();
            $parametri->montaggio_in_locali_preggio = $this->getMontaggioInLocaliDiPreggio();
            $parametri->pagamento_contrassegno = $this->getPagamentoContrassegno();
            $parametri->margine_traslocabile = Parametri::getMargine();
            $parametri->lista_voci_extra = $this->getListaVociExtra();
            //TODO SERVIZI ACCESSORI DOVE LI METTIAMO ?
            $calcolatore = new CalcolatoreMobilieri();
            $calcolatore->setParametriCalcolo($parametri);
            //TODO CAPIRE CHE VALORI ESPORTARE
        }
        return 0;

    }

    public function getMC()
    {
        $mc = 0;
        foreach ($this->lista_item as $item) {
            $mc += ($item->mc * $item->qta);
        }
        return $mc;
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


    public function setKM($km) { $this->km = $km; }
    public function getKM() { return $this->km; }

    private function _getCostoServiziAccessoriPartenza($totale)
    {
        $valore_percentuale = 0;
        $valore_assoluto = 0;
        foreach ($this->lista_servizi_partenza as $servizio)
        {
            if (intval($servizio->getCampo(Servizio::PERCENTUALE))>0) {
                $percentuale = (intval($servizio->getCampo(Servizio::PERCENTUALE))) *
                    (1+intval($servizio->getCampo(Servizio::MARGINE))/100);

                $valore_percentuale += $totale * (1 + $percentuale/100);
            }
            else
            {
                $valore_assoluto += doubleval( ($servizio->getCampo(Servizio::VALORE_ASSOLUTO) *
                    ( 1 + intval($servizio->getCampo(Servizio::MARGINE))/100) ) );
            }
        }


        return array('valore_percentuale'=>$valore_percentuale ,
            'valore_assoluto'=>$valore_assoluto);
    }

    private function _getCostoServiziAccessoriDestinazione($totale)
    {
        $valore_percentuale = 0;
        $valore_assoluto = 0;
        foreach ($this->lista_servizi_destinazione as $servizio)
        {
            if (intval($servizio->getCampo(Servizio::PERCENTUALE))>0) {
                $percentuale = (intval($servizio->getCampo(Servizio::PERCENTUALE))) *
                    (1+intval($servizio->getCampo(Servizio::MARGINE))/100);

                $valore_percentuale += $totale * (1 + $percentuale/100);
            }
            else
            {
                $valore_assoluto += doubleval( ($servizio->getCampo(Servizio::VALORE_ASSOLUTO) *
                    ( 1 + intval($servizio->getCampo(Servizio::MARGINE))/100) ) );
            }
        }


        return array('valore_percentuale'=>$valore_percentuale ,
            'valore_assoluto'=>$valore_assoluto);
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