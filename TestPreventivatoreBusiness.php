<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/24/14
 * Time: 10:46 PM
 */
require_once "Bootstrap.php";

class TestPreventivatoreBusiness {

    public function run()
    {
        $preventivatore = new PreventivatoreBusiness();

        $preventivatore->addItem(new ItemPreventivatoreBusiness("test", null, 1,2,3,1));
        $preventivatore->addItem(new ItemPreventivatoreBusiness("prova", 10,null, null, null, 1));


        $indirizzoPartenza = new Indirizzo('Via Flavio Gioia 8','Cernusco Sul Naviglio','MI','20063');
        $indirizzoDestinazione = new Indirizzo('','Roma','','');
        $preventivatore->setIndirizzoPartenza($indirizzoPartenza);
        $preventivatore->setIndirizzoDestinazione($indirizzoDestinazione);
        $res = $preventivatore->elabora();



        $customer = new Customer(1, 'test@gmai.com');
        $preventivo = $preventivatore->save($customer);
        print_r($preventivo);

        $preventivo->setIdTraslocatorePartenza( 716 );
        $preventivo->setIdTrasportatore( 711 );
        $preventivo->setIdTraslocatoreDestinazione(716);

        $preventivo->confirmTrasportatore();
        $preventivo->confirmTraslocatorePartenza();
        $preventivo->save();
        $ordine = $preventivo->changeToOrdine(); //cambia lo stato da preventivo a ordine;


    }
}

$m = new TestPreventivatoreBusiness();
$m->run();