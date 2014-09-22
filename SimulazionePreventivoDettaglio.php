<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 11:46 AM
 */
//ini_set ( "log_errors", 1 );
//ini_set ( "error_log", "/logs/error.log" );
//ini_set ( "display_errors", 1 );

require_once 'Bootstrap.php';
require_once 'Preventivo.php';

class SimulazionePreventivoDettaglio {

    public function testAnna()
    {
        $preventivo = new Preventivo();

        //$preventivo->load(72);
        if (!$preventivo->loadDettaglio(965)) die ("Preventivo non esiste");


        $preventivatore = $preventivo->getPreventivatore();
        //$preventivatore->setKM(55);
        $result = $preventivatore->elabora(false);
        print_r($result);

    }

    public function run()
    {
        $preventivo = new Preventivo();

        //$preventivo->load(72);
        if (!$preventivo->loadDettaglio(965)) die ("Preventivo non esiste");

        $preventivo->setLocalizzazionePartenza(1,1,1);
        $preventivo->setLocalizzazioneDestinazione(2,2,2);

        $preventivatore = $preventivo->getPreventivatore();
        $result = $preventivatore->elabora(false);


        echo "\nPrezzo cliente: ".round($result['prezzo_cliente_con_iva'],2);
        $tabellaArredi = array();
        $listaArredi = $preventivo->getListaArredi();
        foreach($listaArredi as $arredo) {
            $ambiente 	= $arredo->getCampo(Arredo::AMBIENTE);

            $tempArredo = array(
                'id'=>$arredo->getCampo(Arredo::ID),
                "ambiente" 	=> $arredo->getCampo(Arredo::AMBIENTE),
                "arredo" 	=> $arredo->getCampo(Arredo::ARREDO),
                "variante"	=> $arredo->getCampo(Arredo::VARIANTE),
                "mc"		=> $arredo->getMC(),
                "qta"		=> $arredo->getQta()
            );

            echo "\nID:" .$tempArredo['id'].", Arredo: ".$tempArredo['arredo'].", variante: ".$tempArredo['variante'].", mc: ".$tempArredo['mc'];
            $tabellaArredi[$ambiente][] = $tempArredo;
        }

        echo "\nLista Servizi: ";
        $servizi = $preventivo->getListaServiziIstantaneo();
        //print_r($servizi);
        if ($servizi)
            foreach ($servizi as $servizio)
                echo  "\nServizio: ".$servizio->getCampo(ServizioIstantaneo::SERVIZIO);

        //$preventivatore->updatePreventivo($preventivo);
        //$preventivo->save();

        echo "\nRESULT:\n";
        print_r($result);

        //Aggiorno tutto e salvo
        $preventivatore->updatePreventivo($preventivo);
        $preventivo->note_partenza = "NOTA PARTENZA TEST";
        $preventivo->note_destinazione = "NOTA DESTINAZIONE TEST";
        $preventivo->save();
        $km = $preventivatore->getKM();
        echo "\nKM: $km";




    }


}

$t = new SimulazionePreventivoDettaglio();
$t->testAnna();