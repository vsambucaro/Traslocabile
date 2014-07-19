<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 11:46 AM
 */

require_once 'Bootstrap.php';
require_once 'PreventivatoreIstantaneo.php';

class TestPreventivatoreIstantaneo {

    public function run()
    {
        $preventivatore = new PreventivatoreIstantaneo();

        $preventivatore->addArredoById(259, array(Arredo::METRI_LINEARI=>500) ); //CUCINA MOBILE CUCINA
        $preventivatore->addArredoById(259, array(Arredo::METRI_LINEARI=>200) ); //CUCINA MOBILE CUCINA
        $preventivatore->addArredoById(267); //camera letto matrimoniale
        $preventivatore->addServizioById(1); //montaggio
        $preventivatore->addServizioById(2); //smontaggio

        //Calcola KM
        $calcolatoreDistanza = new CalcolatoreDistanza();
        $info = $calcolatoreDistanza->getDrivingInformationV2('Via Flavio Gioia 8, 20063 Cernusco Sul Naviglio, Milano', 'Roma');
        echo "\nGOOGLE: ".$info['distance'] . ' - ' . $info['time'];
        //$preventivatore->setKM($info['distance']);

        $indirizzoPartenza = new Indirizzo('Via Flavio Gioia 8','Cernusco Sul Naviglio','MI','20063');
        $indirizzoDestinazione = new Indirizzo('','Roma','','');
        $preventivatore->setIndirizzoPartenza($indirizzoPartenza);
        $preventivatore->setIndirizzoDestinazione($indirizzoDestinazione);
        $preventivatore->elabora();

        //ottiene i risultati
        $mc = $preventivatore->getMC();
        $costo_servizi = $preventivatore->getCostoServizi();
        $costo_trazione = $preventivatore->getCostoTrazione();
        $prezzo_traslocatore = $preventivatore->getPrezzoTraslocatore();
        $prezzo_cliente_senza_iva = $preventivatore->getPrezzoClienteSenzaIva();
        $prezzo_cliente_con_iva = $preventivatore->getPrezzoClienteConIva();


        $customer = new Customer(1, 'test@gmai.com');
        $preventivatore->save($customer);


        //visualizza i risultati
        echo "\nMC: ".$mc;
        echo "\ncosto_servizi: ".$costo_servizi;
        echo "\ncosto_trazione: ".$costo_trazione;
        echo "\nprezzo_traslocatore: ".$prezzo_traslocatore;
        echo "\nprezzo_cliente_senza_iva: ".$prezzo_cliente_senza_iva;
        echo "\nprezzo_cliente_con_iva: ".$prezzo_cliente_con_iva;
    }

}

$t = new TestPreventivatoreIstantaneo();
$t->run();