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
$flag_servizio_montaggio = 0, $flag_servizio_smontaggio = 0, $flag_servizio_imballaggio = 0)
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
    private function _getMCSmontaggio()
    {
        $mc = 0;
        foreach ($this->lista_arredi as $arredo)
        {
            $tmp = $arredo->getMC();
            if ($arredo->getParametroB() == Arredo::SMONTATO_PIENO)
            {
                    $tmp = $tmp * $arredo->getCampo(Arredo::SMONTATO_PIENO);


            }
            if ($arredo->getParametroB() == Arredo::SMONTATO_VUOTO)
            {
                $tmp = $tmp * $arredo->getCampo(Arredo::SMONTATO_VUOTO);


            }
            $mc+= $tmp;

        }

        return $mc;
    }

    /*
    * Calcola e ritorna i mc
    */
    private function _getMCNoSmontaggio()
    {
        $mc = 0;
        foreach ($this->lista_arredi as $arredo)
        {
            $tmp = $arredo->getMC();
            if ($arredo->getParametroB() == Arredo::MONTATO_PIENO)
            {
                $tmp = $tmp * $arredo->getCampo(Arredo::MONTATO_PIENO);
                $mc+= $tmp;

            }

            if ($arredo->getParametroB() == Arredo::MONTATO_VUOTO)
            {
                $tmp = $tmp * $arredo->getCampo(Arredo::MONTATO_VUOTO);
                $mc+= $tmp;

            }



        }

        return $mc;
    }


    /*
    * Calcola e ritorna i mc
    */
    private function _getMCScaricoSalita()
    {
        $mc = 0;
        foreach ($this->lista_arredi as $arredo)
        {
            $mc+=$arredo->getMCScaricoSalita();
        }
        //TODO arrotondamento
        return $mc;
    }

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

    /*
     * Calcola il costo del servizio di smontaggio/imballo/carico
     */
    private function _getCostoServizioSmontaggioImballoCarico($mc ,  $tipo_costo = PreventivatoreDettagliato::COSTO_CLIENTE )
    {
        $tariffa = ParametriServizio::getParametro(ParametriServizio::TARIFFA_SMONTAGGIO_IMBALLO_CARICO);
        if ($tipo_costo == PreventivatoreDettagliato::COSTO_CLIENTE)
            $costo = $mc * $tariffa['prezzo'];
        else
            $costo = $mc * $tariffa['tariffa_operatore'];
        return $costo;
    }


    /*
     * Calcola il costo del servizio di imballo/carico
     */
    private function _getCostoServizioImballoCarico($mc, $tipo_costo = PreventivatoreDettagliato::COSTO_CLIENTE )
    {
        $tariffa = ParametriServizio::getParametro(ParametriServizio::TARIFFA_IMBALLO_CARICO);
        if ($tipo_costo == PreventivatoreDettagliato::COSTO_CLIENTE)
            $costo = $mc * $tariffa['prezzo'];
        else
            $costo = $mc * $tariffa['tariffa_operatore'];
        return $costo;
    }

    /*
     * Calcola il costo del servizio di imballo/carico
    */
    private function _getCostoServizioDeposito($mc, $giorni , $tipo_costo = PreventivatoreDettagliato::COSTO_CLIENTE)
    {
        $tariffa = ParametriServizio::getParametro(ParametriServizio::TARIFFA_DEPOSITO);
        if ($tipo_costo == PreventivatoreDettagliato::COSTO_CLIENTE)
            $costo = $mc * $tariffa['prezzo'] * $giorni;
        else
            $costo =$mc * $tariffa['tariffa_operatore'] * $giorni;

        return $costo;
    }

    /*
     * Calcola il costo del servizio di imballo/carico
    */
    private function _getCostoServizioScarico($mc, $tipo_costo = PreventivatoreDettagliato::COSTO_CLIENTE)
    {
        $tariffa = ParametriServizio::getParametro(ParametriServizio::TARIFFA_SCARICO);
        if ($tipo_costo == PreventivatoreDettagliato::COSTO_CLIENTE)
            $costo = $mc * $tariffa['prezzo'];
        else
            $costo = $mc * $tariffa['tariffa_operatore'];
        return $costo;
    }

    /*
    * Calcola il costo del servizio di imballo/carico
    */
    private function _getCostoServizioSalita($mc, $tipo_costo = PreventivatoreDettagliato::COSTO_CLIENTE)
    {
        $tariffa = ParametriServizio::getParametro(ParametriServizio::TARIFFA_SALITA_AL_PIANO);
        if ($tipo_costo == PreventivatoreDettagliato::COSTO_CLIENTE)
            $costo = $mc * $tariffa['prezzo'];
        else
            $costo = $mc * $tariffa['tariffa_operatore'];
        return $costo;
    }

    /*
    * Calcola il costo del servizio di imballo/carico
    */
    private function _getCostoServizioMontaggio($mc, $tipo_costo = PreventivatoreDettagliato::COSTO_CLIENTE)
    {
        $tariffa = ParametriServizio::getParametro(ParametriServizio::TARIFFA_MONTAGGIO);
        if ($tipo_costo == PreventivatoreDettagliato::COSTO_CLIENTE)
            $costo = $mc * $tariffa['prezzo'];
        else
            $costo = $mc * $tariffa['tariffa_operatore'];
        return $costo;
    }

    private function _getCostoTrazione($mc, $km) {
        $tmp = $mc * TrazioneIstantaneo::getCostoMC($mc, $km) ;
        //echo "\nTMP: ".$tmp;
        $calc = $tmp/(1- 0.2);
        //echo "\n CALC: ".$calc;
        return $calc;
    }

    public function getDettaglioMC()
    {
        //calcola mc
        $mc_smontaggio = $this->_getMCSmontaggio();
        $mc_no_smontaggio = $this->_getMCNoSmontaggio();
        $mc_da_trasportare = ($mc_smontaggio + $mc_no_smontaggio) * (1+ Parametri::getAggiustamentoMezzi());
        $mc_da_rimontare = $mc_smontaggio;
        $mc_scarico_salita_piano = $mc_smontaggio + $mc_no_smontaggio;

        return array('mc_smontaggio'=>$mc_smontaggio,
        'mc_no_smontaggio'=>$mc_no_smontaggio,
        'mc_da_trasportare'=>$mc_da_trasportare,
        'mc_da_rimontare'=>$mc_da_rimontare,
        'mc_scarico_salita_piano'=>$mc_scarico_salita_piano);

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

    public function elabora($modo_istantaneo = false)
    {
        if ($modo_istantaneo)
        {
            return $this->elaboraInstanteno();
        }

        //calcola mc
        $mc_smontaggio = $this->_getMCSmontaggio();
        $mc_no_smontaggio = $this->_getMCNoSmontaggio();
        $mc_da_trasportare = ($mc_smontaggio + $mc_no_smontaggio) * (1+ Parametri::getAggiustamentoMezzi());
        $mc_da_rimontare = $mc_smontaggio;
        $mc_scarico_salita = $mc_smontaggio + $mc_no_smontaggio;

        $this->mc_smontaggio = $mc_smontaggio;
        $this->mc_no_smontaggio = $mc_no_smontaggio;
        $this->mc_da_trasportare = $mc_da_trasportare;
        $this->mc_da_rimontare = $mc_da_rimontare;
        $this->mc_scarico_salita_piano = $mc_scarico_salita;


        //calcola costo servizi
        $costo_servizio_smontaggio_imballo_carico = $this->_getCostoServizioSmontaggioImballoCarico($mc_smontaggio);
        $costo_servizio_imballo_carico = $this->_getCostoServizioImballoCarico($mc_no_smontaggio);
        $costo_trazione = $this->_getCostoTrazione($mc_da_trasportare, $this->km);
        $deposito = $this->_getCostoServizioDeposito($this->mc, $this->giorni_deposito);
        $costo_servizio_scarico = $this->_getCostoServizioScarico($mc_scarico_salita);
        $costo_servizio_salita = $this->_getCostoServizioSalita($mc_scarico_salita);
        $costo_servizio_montaggio = $this->_getCostoServizioMontaggio($mc_da_rimontare);

        $costo_servizi = $costo_servizio_smontaggio_imballo_carico + $costo_servizio_imballo_carico +
                        $costo_trazione + $deposito + $costo_servizio_scarico +
                         $costo_servizio_salita + $costo_servizio_montaggio;

        //Aggiungi le aggravanti
        $costo_servizi_accessori_partenza = $this->_getCostoServiziAccessoriPartenza($costo_servizi);
        $costo_servizi_accessori_destinazione = $this->_getCostoServiziAccessoriDestinazione($costo_servizi);

        $valore_voci_extra  = 0;
        foreach ($this->lista_voci_extra as $voce) {
            if ($voce->getSegno() == VocePreventivoExtra::POSITIVO)
                $valore_voci_extra += $voce->getValore();
            else
                $valore_voci_extra -= $voce->getValore();
        }

        $prezzo_cliente_senza_iva = $costo_servizi + $costo_servizi_accessori_partenza['valore_percentuale'] +
            $costo_servizi_accessori_partenza['valore_assoluto'] +
            $costo_servizi_accessori_destinazione['valore_percentuale'] +
            $costo_servizi_accessori_destinazione['valore_assoluto'] +
            $valore_voci_extra;


        $prezzo_cliente_con_iva = $prezzo_cliente_senza_iva * (1 + Parametri::getIVA());

        //TODO capire dove esportare questi valori


        $result = array('costo_servizio_smontaggio_imballo_carico'=>$costo_servizio_smontaggio_imballo_carico,
            'costo_servizio_imballo_carico'=>$costo_servizio_imballo_carico,
            'costo_trazione'=>$costo_trazione,
            'deposito'=>$deposito,
            'costo_servizio_scarico'=>$costo_servizio_scarico,
            'costo_servizio_salita'=>$costo_servizio_salita,
            'costo_servizio_montaggio'=>$costo_servizio_montaggio,
            'costo_servizi_accessori_partenza'=>$costo_servizi_accessori_partenza,
            'costo_servizi_accessori_destinazione'=>$costo_servizi_accessori_destinazione,
            'prezzo_cliente_senza_iva'=>$prezzo_cliente_senza_iva,
            'prezzo_cliente_con_iva'=>$prezzo_cliente_con_iva
        );




        return $result;

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