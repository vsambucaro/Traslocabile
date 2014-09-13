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
        $preventivatore->setAlgoritmo(PreventivatoreBusiness::TIPO_ALGORITMO_MOBILIERI);

        $preventivatore->addItem(new ItemPreventivatoreBusiness("test", null, 1,2,3,1));
        $preventivatore->addItem(new ItemPreventivatoreBusiness("prova", 10,null, null, null, 1));


        $indirizzoPartenza = new Indirizzo('Via Flavio Gioia 8','Cernusco Sul Naviglio','MI','20063');
        $indirizzoDestinazione = new Indirizzo('','Roma','','');
        $preventivatore->setIndirizzoPartenza($indirizzoPartenza);
        $preventivatore->setIndirizzoDestinazione($indirizzoDestinazione);
        $preventivatore->setPianiDaSalire(3);
        $preventivatore->setMontaggio(true);
        $preventivatore->setMontaggioInLocaliDiPreggio(false);
        $preventivatore->setPagamentoContrassegno(false);

        $res = $preventivatore->elabora();
        print_r($res);


        $customer = new Customer(1, 'test@gmai.com');
        $preventivo = $preventivatore->save($customer);


       // $preventivo->setIdTraslocatorePartenza( 716 );
       // $preventivo->setIdTrasportatore( 711 );
       // $preventivo->setIdTraslocatoreDestinazione(716);

       // $preventivo->confirmTrasportatore();
       // $preventivo->confirmTraslocatorePartenza();
       // $preventivo->save();
        //$ordine = $preventivo->changeToOrdine(); //cambia lo stato da preventivo a ordine;
        echo "\nFINE";

    }
}

$m = new TestPreventivatoreBusiness();
$m->run();