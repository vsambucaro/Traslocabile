<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/30/14
 * Time: 9:00 AM
 */
require_once 'Bootstrap.php';
//xdebug_disable();

class TestPreventivi {

    public function run()
    {

        $preventivi = new Preventivi();
        $lista_preventivi =  $preventivi->getListaPreventivi(null, null, null, null);

        var_dump($lista_preventivi);

           // $prev  = $list_preventivi['preventivi'];
        foreach ($lista_preventivi as $preventivo)
        {
            echo "\nPreventivo ID: ".$preventivo->getId();
            $storia_assegnazione_trasportatori = $preventivo->getStoriaAssegnazioniTrasportatori();
            var_dump($storia_assegnazione_trasportatori);
        }
            //var_dump($prev);

        //var_dump($list_preventivi);
    }
}


$m = new TestPreventivi();
$m->run();
