<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 8/7/14
 * Time: 8:27 AM
 */

class CalcolatoreMobilieri {

    private $parametri_calcolo;

    public function setParametriCalcolo(ParametriPreventivoBusiness $parametri)
    {
        $this->parametri_calcolo = $parametri;

    }


    public function elabora()
    {
        $tmp = $this->parametri_calcolo->peso/$this->parametri_calcolo->mc_trasportati;
        $incremento_peso_volume = 1;
        if ($tmp>120)
            $incremento_peso_volume = (1+ $tmp/120);

        /*
        $km_sede_logistica = $this->calcolaKMSedeLogistica();
        $costo_trazione_sede_logistica = $this->getCostoTrazioneSedeLogistica($km_sede_logistica);
        $prezzo_trazione_sede_logistica = $costo_trazione_sede_logistica * $this->parametri_calcolo->mc_trasportati;
        */

        $prezzo_giacenza = 0;
        if ($this->parametri_calcolo->giorni_deposito > 10)
            $prezzo_giacenza = $this->parametri_calcolo->giorni_deposito * $this->parametri_calcolo->parametri[ParametriPreventivoBusinessMobilieri::giacenza] * $this->parametri_calcolo->mc_trasportati;

        /*
        $prezzo_scarico_carico_presso_deposito = $this->parametri_calcolo->parametri[ParametriPreventivoBusinessMobilieri::tariffa_scarico_ricarico_fino_sede_loggistica] *
            $this->parametri_calcolo->mc_trasportati * $incremento_peso_volume;
*       /
        /*
        $prezzo_trazione_sede_cliente = 0;
        $km_sede_cliente = $this->calcolaKMSedeCliente();
        if ($this->km_sede_cliente<30)
            $prezzo_trazione_sede_cliente = $this->parametri_calcolo->parametri[ParametriPreventivoBusinessMobilieri::trazione_sede_logistica_cliente_finale_entro_30km] * $this->parametri_calcolo->mc_trasportati;
        else
            $prezzo_trazione_sede_cliente = $this->parametri_calcolo->parametri[ParametriPreventivoBusinessMobilieri::trazione_sede_logistica_cliente_finale_entro_da_30km_a_200km] * $this->parametri_calcolo->mc_trasportati;

        */

        $prezzo_trazione = 0;

        //calcola KM
        $calcolatoreDistanza = new CalcolatoreDistanza();

        $info = $calcolatoreDistanza->getDrivingInformationV2($this->parametri_calcolo->indirizzo_partenza->toGoogleAddress(),
            $this->parametri_calcolo->indirizzo_destinazione->toGoogleAddress());

        $km = $info['distance'];

        //calcola costo trazione
        $prezzo_trazione = $this->_getCostoTrazione($this->parametri_calcolo->mc_trasportati, $km);


        $prezzo_salita_al_piano = $this->parametri_calcolo->parametri[ParametriPreventivoBusinessMobilieri::tariffa_salita_al_piano] * $incremento_peso_volume * $this->parametri_calcolo->piani_da_salire * $this->parametri_calcolo->mc_trasportati;

        $prezzo_scarico_presso_cliente = $this->parametri_calcolo->parametri[ParametriPreventivoBusinessMobilieri::tariffa_scarico_sede_cliente]
            * $incremento_peso_volume * $this->parametri_calcolo->mc_trasportati;

        $prezzo_montaggio = 0;
        if ($this->parametri_calcolo->montaggio)
            if ($this->parametri_calcolo->montaggio_in_locali_preggio)
                $prezzo_montaggio = $this->parametri_calcolo->parametri[ParametriPreventivoBusinessMobilieri::tariffa_montaggio_sede_cliente] * 1.2 * $this->parametri_calcolo->mc_trasportati;
            else
                $prezzo_montaggio = $this->parametri_calcolo->parametri[ParametriPreventivoBusinessMobilieri::tariffa_montaggio_sede_cliente] * $incremento_peso_volume * $this->parametri_calcolo->mc_trasportati;

        $prezzo_contrassegno = 0;
        if ($this->parametri_calcolo->pagamento_contrassegno)
            $prezzo_contrassegno = $this->parametri_calcolo->parametri[ParametriPreventivoBusinessMobilieri::pagamento_assegni];


        $totale = $prezzo_trazione + $prezzo_salita_al_piano +
            $prezzo_scarico_presso_cliente + $prezzo_montaggio + $prezzo_contrassegno;

        $sconto =  ($totale * $this->parametri_calcolo->sconto);

        $totale_scontato = $totale - $sconto;
        $tariffa_finale = $totale_scontato * (1 + $this->parametri_calcolo->margine_traslocabile);



        $prezzo_cliente_con_iva = $tariffa_finale * (1 + Parametri::getIVA());

        $tariffa_trasportatore = $prezzo_trazione;
        $tariffa_traslocatore_partenza = 0;
        $tariffa_traslocatore_destinazione = $prezzo_salita_al_piano + $prezzo_scarico_presso_cliente + $prezzo_montaggio;
        $tariffa_deposito = $prezzo_giacenza;

        return array(
            'prezzo_cliente_senza_iva'=>$tariffa_finale,
            'prezzo_cliente_con_iva'=>$prezzo_cliente_con_iva,
            'mc'=>$this->parametri_calcolo->mc_trasportati,
            'tariffa_trasportatore' =>$tariffa_trasportatore,
            'tariffa_traslocatore_partenza' => $tariffa_traslocatore_partenza,
            'tariffa_traslocatore_destinazione' => $tariffa_traslocatore_destinazione,
            'tariffa_depositario' => $tariffa_deposito
        );



    }


    private function _getCostoTrazione($mc, $km) {
        return $mc * TrazioneIstantaneo::getCostoMC($mc, $km); //TODO verificare se effettivamente la tabella è uguale. Presumo di no
    }

} 