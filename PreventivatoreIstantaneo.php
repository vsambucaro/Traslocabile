<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 10:57 AM
 */


class PreventivatoreIstantaneo extends Preventivatore
{

     public function addArredoById($id_arredo, $parte_variabile=null, $qta=1, $parametro_b=null, $dim_A = null, $dim_P = null, $dim_L = null)
     {
         //crea l'oggetto Arredo
         $arredo = new ArredoIstantaneo($id_arredo);

         //aggiunge le parti varibili per il calcolo dei mc
         if ($parte_variabile)
         {
             foreach ($parte_variabile as $key=>$value)
             {
                 //echo "\nKey: ".$key.", value: ".$value."\n";
                 $arredo->setParteVariabile(strtoupper($key), $value);
             }
         }

         //aggiunge l'oggetto alla lista

         $arredo->setQta($qta);
         $this->lista_arredi[] = $arredo;

         return $arredo->getMC();

     }

     public function addServizioById($id_servizio, $tipologia=null)
     {
        $servizio = new ServizioIstantaneo($id_servizio);
         $this->lista_servizi[] = $servizio;
     }

    /*
     * Calcola e ritorna i mc
     */
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
        foreach ($this->lista_servizi as $servizio)
        {
            $costo += $mc * $servizio->getCampo(Servizio::COSTO);
        }
        return $costo;
    }

    private function _getCostoTrazione($mc, $km) {
        return $mc * TrazioneIstantaneo::getCostoMC($mc, $km);
    }

    public function elabora()
    {

        //calcola mc
        $this->mc = $this->_getMC();

        //calcola costo servizi
        $this->costo_servizi = $this->_getCostoServizi($this->mc);

        //calcola KM
        $calcolatoreDistanza = new CalcolatoreDistanza();
        $info = $calcolatoreDistanza->getDrivingInformationV2($this->indirizzo_partenza->toGoogleAddress(),
            $this->indirizzo_destinazione->toGoogleAddress());


        $this->setKM($info['distance']);

        //calcola costo trazione
        $this->costo_trazione = $this->_getCostoTrazione($this->mc, $this->km);

        $prezzo_traslocatore = $this->costo_servizi + $this->costo_trazione;
        $prezzo_cliente_senza_iva = $prezzo_traslocatore * (1 + Parametri::getMargine());
        $prezzo_cliente_con_iva = $prezzo_cliente_senza_iva * (1 + Parametri::getIVA());

        $this->prezzo_traslocatore = $prezzo_traslocatore;
        $this->prezzo_cliente_senza_iva = $prezzo_cliente_senza_iva;
        $this->prezzo_cliente_con_iva = $prezzo_cliente_con_iva;
    }

    /**
     * Salva il preventivo
     */
    public function save(Customer $customer = null) {
        $preventivo = new Preventivo();
        if ($customer)
            $preventivo->setCliente($customer);
        $preventivo->setPartenza($this->indirizzo_partenza);
        $preventivo->setDestinazione($this->indirizzo_destinazione);
        $preventivo->setArredi($this->lista_arredi);
        $preventivo->setServiziIstantaneo($this->lista_servizi);
        $preventivo->setServiziAccessoriPartenza($this->lista_servizi_partenza);
        $preventivo->setImporto($this->prezzo_cliente_con_iva);
        $preventivo->setStato($this->stato);
        $preventivo->setStato($this->stato);
        $preventivo->setNote($this->note);
        $preventivo->setFlagSopraluogo($this->flag_sopraluogo);
        $preventivo->setDataTrasloco($this->data_trasloco);

        $preventivo->save();

        return $preventivo;
    }

    public function setCustomer($id_preventivo, Customer $customer)
    {
        $preventivo = new Preventivo();
        $preventivo->load($id_preventivo);
        $preventivo->setCliente($customer);
        $preventivo->save();

        return $preventivo;
    }

    public function getMC() { return $this->mc; }


    public function addServizioAccessoreById($id_servizio, $tipo)
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
    public function removeServizioAccessoById($id_servizio, $tipo)
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

    public function addServizioAccessoByItem(ServizioAccessoreAggravante $servizio, $tipo)
    {

        if ($tipo == Servizio::SERVIZIO_PARTENZA)
            $this->lista_servizi_partenza[] = $servizio;
        else
            $this->lista_servizi_destinazione[] = $servizio;
    }

} 