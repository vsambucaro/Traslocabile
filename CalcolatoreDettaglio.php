<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 8/14/14
 * Time: 3:28 PM
 */

class CalcolatoreDettaglio {

    public $mc = 0;
    public $km = 0;
    public $lista_servizi = null;
    public $lista_arredi = null;
    public $lista_servizi_partenza = null;
    public $lista_servizi_destinazione = null;
    public $giorni_deposito = 0;
    public $lista_voci_extra = null;

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

    private function _getMC()
    {
        $mc = 0;
        foreach ($this->lista_arredi as $arredo)
        {
            $mc+=$arredo->getMC();
        }
        //TODO arrotondamento
        return $mc;
    }

    private function _getCostoServizi($mc)
    {
        $costo = 0;
        if ($this->lista_servizi)
            foreach ($this->lista_servizi as $servizio)
            {
                $costo += $mc * $servizio->getCampo(Servizio::COSTO);
            }
        return $costo;
    }

    private function _getCostoTrazione($mc, $km) {
       $tmp = $mc * TrazioneIstantaneo::getCostoMC($mc, $km);

        //$res = $tmp/(1-0.2);
        return $tmp;
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

                $valore_percentuale += $totale * ( $percentuale/100);
                //echo "\nServizio: ".$percentuale.", valore: ".$valore_percentuale;
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

                $valore_percentuale += $totale * ( $percentuale/100);
                //echo "\nServizio: ".$percentuale;
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
    public function elabora()
    {

        $mc_smontaggio = $this->_getMCSmontaggio();
        $mc_no_smontaggio = $this->_getMCNoSmontaggio();
        $mc_da_trasportare = ($mc_smontaggio + $mc_no_smontaggio) * (1+ Parametri::getAggiustamentoMezzi());
        $mc_da_rimontare = $mc_smontaggio;
        $mc_scarico_salita = $mc_smontaggio + $mc_no_smontaggio;

        $this->mc_smontaggio = $mc_smontaggio;
       // echo "\nMC SMONTAGGIO: ".$mc_smontaggio;
        $this->mc_no_smontaggio = $mc_no_smontaggio;
       // echo "\nMC NO SMONTAGGIO: ".$mc_no_smontaggio;
        $this->mc_da_trasportare = $mc_da_trasportare;
      //  echo "\nMC DA TRASPORTARE: ".$mc_da_trasportare;
        $this->mc_da_rimontare = $mc_da_rimontare;
       // echo "\nMC DA RIMONTARE: ".$mc_da_rimontare;
        $this->mc_scarico_salita_piano = $mc_scarico_salita;
       // echo "\nMC SCARICO SALITA AL PIANO: ".$mc_scarico_salita;

        //calcola costo servizi
        $costo_servizio_smontaggio_imballo_carico = $this->_getCostoServizioSmontaggioImballoCarico($mc_smontaggio);
       // echo "\nCostoServizioSmontaggioImballoCarico: ".$costo_servizio_smontaggio_imballo_carico;
        $costo_servizio_imballo_carico = $this->_getCostoServizioImballoCarico($mc_no_smontaggio);
       // echo "\nCostoServizioImballoCarico: ".$costo_servizio_imballo_carico;
        $costo_trazione = $this->_getCostoTrazione($mc_da_trasportare, $this->km);
       // echo "\nCostoTrazione: ".$costo_trazione." km: ".$this->km;
        $deposito = $this->_getCostoServizioDeposito($this->mc, $this->giorni_deposito);
       // echo "\nDeposito: ".$deposito;
        $costo_servizio_scarico = $this->_getCostoServizioScarico($mc_scarico_salita);
       // echo "\nCostoServiziScarico: ".$costo_servizio_scarico;
        $costo_servizio_salita = $this->_getCostoServizioSalita($mc_scarico_salita);
       // echo "\nCostoServiziSalita: ".$costo_servizio_salita;
        $costo_servizio_montaggio = $this->_getCostoServizioMontaggio($mc_da_rimontare);
       // echo "\nCostoServiziMontaggio: ".$costo_servizio_montaggio;

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

        $tariffa_trasportatore = $costo_trazione;
        $tariffa_traslocatore_partenza = $costo_servizio_smontaggio_imballo_carico +
            $costo_servizio_imballo_carico ;

        $tariffa_traslocatore_destinazione = $costo_servizio_salita + $costo_servizio_montaggio + $costo_servizio_scarico;

        $tariffa_deposito = $deposito;

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
            'prezzo_cliente_con_iva'=>$prezzo_cliente_con_iva,
            'tariffa_trasportatore' =>$tariffa_trasportatore,
            'tariffa_traslocatore_partenza' => $tariffa_traslocatore_partenza,
            'tariffa_traslocatore_destinazione' => $tariffa_traslocatore_destinazione,
            'tariffa_depositario' => $tariffa_deposito,
            'KM'=>$this->km
        );

        return $result;

    }
} 