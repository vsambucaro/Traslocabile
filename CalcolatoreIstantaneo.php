<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 8/14/14
 * Time: 3:28 PM
 */

class CalcolatoreIstantaneo {

    public $mc = 0;
    public $km = 0;
    public $lista_servizi = null;
    public $lista_arredi = null;

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
       $tmp = $mc * TrazioneIstantaneo::getCostoMC($mc, $km);
        $res = $tmp/(1-0.2);
        return $res;
    }

    public function elabora()
    {

        //calcola mc
        $this->mc = $this->_getMC();
        $tmp_mc = $this->mc;
        //echo "\nKM: ".$this->km;
        //echo "\nMC : ".$this->mc;
        $this->mc = $this->mc * (1 + Parametri::getAggiustamentoMC());
        //echo "\nMC corretti: ".$this->mc." parametro : ".Parametri::getAggiustamentoMC();

        //calcola costo servizi
        $costo_servizi = $this->_getCostoServizi($this->mc);
        //echo "\nCostoServizi: ".$costo_servizi;
        //calcola costo trazione
        $costo_trazione = $this->_getCostoTrazione($this->mc, $this->km);
        //echo "\nCostoTrazione: ".$costo_trazione;

        $prezzo_traslocatore = $costo_servizi + $costo_trazione;
        //echo "\nprezzo traslocatore: ".$prezzo_traslocatore;

        $prezzo_cliente_senza_iva = $prezzo_traslocatore * (1 + Parametri::getMargine());
        $prezzo_cliente_con_iva = $prezzo_cliente_senza_iva * (1 + Parametri::getIVA());


        return array('prezzo_traslocatore'=>$prezzo_traslocatore,
            'prezzo_cliente_senza_iva'=>$prezzo_cliente_senza_iva,
            'prezzo_cliente_con_iva'=>$prezzo_cliente_con_iva,
            'mc'=>$tmp_mc
    );


    }
} 