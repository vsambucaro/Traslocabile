<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 8/7/14
 * Time: 8:27 AM
 */

class CalcolatoreCucinieri {

    private $parametri_calcolo;

    public function setParametriCalcolo(ParametriPreventivoBusinessCucinieri $parametri)
    {
        $this->parametri_calcolo = $parametri;

    }

    private function _getCostoServiziAccessoriPartenza($totale)
    {
        $valore_percentuale = 0;
        $valore_assoluto = 0;
        foreach ($this->parametri_calcolo->lista_servizi_partenza as $servizio)
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
        foreach ($this->parametri_calcolo->lista_servizi_destinazione as $servizio)
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
        $mc_mese = $this->parametri_calcolo->mc_mese;

        $mc_attuali = $this->parametri_calcolo->mc_trasportati;


        //calcola KM
        $calcolatoreDistanza = new CalcolatoreDistanza();
        $info = $calcolatoreDistanza->getDrivingInformationV2($this->parametri_calcolo->indirizzo_partenza->toGoogleAddress(),
            $this->parametri_calcolo->indirizzo_destinazione->toGoogleAddress());
        $km =  $info['distance'];

        $costo_scarico_ricarico_hub = TariffeCucinieri::getCostoScaricoRicaricoHub($mc_mese, $mc_attuali);
        $costo_trazione = TariffeCucinieri::getCostoTrazione($mc_mese,  $km, $mc_attuali);
        $costo_scarico = TariffeCucinieri::getCostoScarico($mc_mese, $mc_attuali);
        $costo_salita_piano = TariffeCucinieri::getCostoSalitaPiano($mc_mese, $mc_attuali);
        $costo_montaggio = TariffeCucinieri::getCostoMontaggio($mc_mese, $mc_attuali);

        $valore_voci_extra  = 0;
        foreach ($this->parametri_calcolo->lista_voci_extra as $voce) {
            if ($voce->getSegno() == VocePreventivoExtra::POSITIVO)
                $valore_voci_extra += $voce->getValore();
            else
                $valore_voci_extra -= $voce->getValore();
        }

            $costo_montaggio_totale = $costo_montaggio * $mc_attuali;
            $costo_salita_piano_totale = $costo_salita_piano * $mc_attuali;
            $costo_scarico_totale = $costo_scarico * $mc_attuali;
            $costo_trazione_totale = $costo_trazione * $mc_attuali;
            $costo_scarico_ricarico_hub_totale = $costo_scarico_ricarico_hub * $mc_attuali;
            $deposito = $this->_getCostoServizioDeposito($mc_attuali, $this->parametri_calcolo->giorni_deposito);

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

            $prezzo_cliente = $costo_complessivo * (1- $this->parametri_calcolo->sconto/100);

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
                'prezzo_cliente_con_iva'=>$prezzo_cliente_con_iva);


            $this->prezzo_cliente_con_iva = $prezzo_cliente_con_iva;
            $this->prezzo_cliente_senza_iva = $prezzo_cliente;
            return $result;



    }


    private function _getCostoTrazione($mc, $km) {
        return $mc * TrazioneIstantaneo::getCostoMC($mc, $km); //TODO verificare se effettivamente la tabella è uguale. Presumo di no
    }

} 