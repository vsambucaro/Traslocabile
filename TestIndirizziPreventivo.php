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

class TestPreventivo {

    public function run()
    {
        $preventivo = new Preventivo();
        //$preventivo->load(72);
        $preventivo->loadDettaglio(116);
        $preventivatore = $preventivo->getPreventivatore();

        $indirizzoPartenza = new Indirizzo('Via Fulvio Testi 10','POZZALLO','MILANO','20063');
        $indirizzoDestinazione = new Indirizzo('Viale Africa','Roma','ROMA','10100');
        $preventivatore->setIndirizzoPartenza($indirizzoPartenza);
        $preventivatore->setIndirizzoDestinazione($indirizzoDestinazione);

        $preventivatore->updatePreventivo($preventivo);
        $preventivo->save();


        echo "\nD: ".$preventivo->getIndirizzoPartenza()->toGoogleAddress();
        echo "\nD: ".$preventivo->getIndirizzoDestinazione()->toGoogleAddress();
    }

}

$t = new TestPreventivo();
$t->run();