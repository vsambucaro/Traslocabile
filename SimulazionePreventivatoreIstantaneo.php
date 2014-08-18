<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 11:46 AM
 */

require_once 'Bootstrap.php';
require_once 'PreventivatoreIstantaneo.php';

class SimulazionePreventivatoreIstantaneo {

    public function load()
    {
        $preventivo = new Preventivo();
        //306 è il preventivo di test

        if (!$preventivo->loadDettaglio(322)) die ("Preventivo non esiste");

        
        $preventivatore = $preventivo->getPreventivatore();
        $result = $preventivatore->elabora(true);


        echo "\nPrezzo cliente: ".round($result['prezzo_cliente_con_iva'],2);
        $tabellaArredi = array();
        $listaArredi = $preventivo->getListaArredi();
        foreach($listaArredi as $arredo) {
            $ambiente 	= $arredo->getCampo(Arredo::AMBIENTE);

            $tempArredo = array(
                "ambiente" 	=> $arredo->getCampo(Arredo::AMBIENTE),
                "arredo" 	=> $arredo->getCampo(Arredo::ARREDO),
                "variante"	=> $arredo->getCampo(Arredo::VARIANTE),
                "mc"		=> $arredo->getMC(),
                "qta"		=> $arredo->getQta()
            );

            echo "\nArredo: ".$tempArredo['arredo'].", variante: ".$tempArredo['variante'].", mc: ".$tempArredo['mc'];
            $tabellaArredi[$ambiente][] = $tempArredo;
        }

        $preventivatore->updatePreventivo($preventivo);
        $preventivo->save();


    }
    public function run()
    {
        $preventivatore = new PreventivatoreIstantaneo();

        $preventivatore->addArredoById(259, array(Arredo::METRI_LINEARI=>300) ); //CUCINA MOBILE CUCINA
        $preventivatore->addArredoById(258); //camera letto matrimoniale



        //Calcola KM
        $calcolatoreDistanza = new CalcolatoreDistanza();
        $info = $calcolatoreDistanza->getDrivingInformationV2('Via Garibaldi 7, Bergamo', 'Via Mazzini 45, Bergamo');
        echo "\nGOOGLE: ".$info['distance'] . ' - ' . $info['time'];
        //$preventivatore->setKM($info['distance']);

        $indirizzoPartenza = new Indirizzo('Via Garibaldi 7','Bermamo','','');
        $indirizzoDestinazione = new Indirizzo('Via Mazzini 45','Bergamo','','');
        $preventivatore->setIndirizzoPartenza($indirizzoPartenza);
        $preventivatore->setIndirizzoDestinazione($indirizzoDestinazione);

        $preventivatore->elabora();

        //ottiene i risultati

        $mc = $preventivatore->getMC();
        $prezzo_traslocatore = $preventivatore->getPrezzoTraslocatore();
        $prezzo_cliente_senza_iva = $preventivatore->getPrezzoClienteSenzaIva();
        $prezzo_cliente_con_iva = $preventivatore->getPrezzoClienteConIva();


        $customer = new Customer(1, 'test@gmai.com');
        $preventivatore->save($customer);


        //visualizza i risultati
        echo "\nMC: ".$mc;
        echo "\nprezzo_traslocatore: ".$prezzo_traslocatore;
        echo "\nprezzo_cliente_senza_iva: ".$prezzo_cliente_senza_iva;
        echo "\nprezzo_cliente_con_iva: ".$prezzo_cliente_con_iva;
    }

}

$t = new SimulazionePreventivatoreIstantaneo();
$t->run();