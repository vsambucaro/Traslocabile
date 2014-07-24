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

    private $lista_servizi_partenza = array();
    private $lista_servizi_destinazione = array();
    protected $indirizzo_partenza;
    protected $indirizzo_destinazione;
    private $lista_voci_extra = array();


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
            $item->mc = $item->altezza * $item->lunghezza * $item->profondità;
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
        $mc_mese = $this->getMCMese(date('Y'));

        $mc_attuali = $this->getMC();
        $costo_scarico_ricarico_hub = TariffeClienteBusiness::getCostoScaricoRicaricoHub($mc_mese, $this->tipo_algoritmo);
        $costo_trazione = TariffeClienteBusiness::getCostoTrazione($mc_mese, $this->km, $this->tipo_algoritmo);
        $costo_scarico = TariffeClienteBusiness::getCostoScarico($mc_mese, $this->tipo_algoritmo);
        $costo_salita_piano = TariffeClienteBusiness::getCostoSalitaPiano($mc_mese, $this->tipo_algoritmo);
        $costo_montaggio = TariffeClienteBusiness::getCostoMontaggio($mc_mese, $this->tipo_algoritmo);

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

            $costo_servizi = $costo_montaggio_totale + $costo_salita_piano_totale + $costo_scarico_totale +
                $costo_trazione_totale + $costo_scarico_ricarico_hub_totale;

            //Aggiungi le aggravanti
            $costo_servizi_accessori_partenza = $this->_getCostoServiziAccessoriPartenza($costo_servizi);
            $costo_servizi_accessori_destinazione = $this->_getCostoServiziAccessoriDestinazione($costo_servizi);

            $costo_complessivo = $costo_servizi + $costo_servizi_accessori_partenza + $costo_servizi_accessori_destinazione
                + $valore_voci_extra;
            $prezzo_cliente = $costo_complessivo * (1- $this->sconto/100);
            return $prezzo_cliente;
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


} 