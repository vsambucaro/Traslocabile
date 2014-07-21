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
        $preventivo->loadDettaglio(115);

        //var_dump($preventivo->getCliente());
        //var_dump($preventivo->getIndirizzoDestinazione());
        //var_dump($preventivo->getListaArredi());
        //var_dump($preventivo->getListaServiziIstantaneo());

        //$preventivo->setIdTraslocatorePartenza( 716 );
        //$preventivo->setIdTrasportatore( 711 );
        //$preventivo->save();

        //$preventivo->setFlagSopraluogo(1);
        //$preventivo->save();
        echo "\nStato: ".$preventivo->getStato();
        $preventivo->setDataSopraluogo("2014-08-01");
        echo "\nDataSopralugo: ".$preventivo->getDataSopraluogo();

        //echo "\nFlag: ".$preventivo->getFlagSopraluogo();

        //$preventivatore = $preventivo->getPreventivatore();

        //var_dump($preventivatore);
/*
        $preventivo->setStato("PROVA NUOVO STATO");
        $preventivo->setDataSopralluogo("2014-07-10 10:00:00");
        $preventivo->setDataTrasloco("2014-07-12 07:00:00");
        $preventivo->setIdTrasportatore(55);
        $preventivo->setIdTraslocatoreDestinazione(100);
        $preventivo->setIdTraslocatorePartenza(101);
        $preventivo->setIdCliente(22);
        $preventivo->save();

        $preventivo->removeIdTraslocatorePartenza('incompatibile con il giorno richiesto');
        $preventivo->setIdTraslocatorePartenza(102);
        $preventivo->save();
        
        echo "\nStoria Assegnazioni Trasportatori:\n";

        $storia = $preventivo->getStoriaAssegnazioniTrasportatori();

        foreach ($storia as $record)
        {
            echo "\nData: ".$record->data.", id_trasportatore:".$record->id_trasportatore." , stato= ".$record->stato." , descrizione=".$record->descrizione;

        }

        echo "\nStoria Assegnazioni Traslocatori Partenza:\n";
        $storia = $preventivo->getStoriaAssegnazioniTraslocatoriPartenza();

        foreach ($storia as $record)
        {
            echo "\nData: ".$record->data.", id_traslocatore:".$record->id_traslocatore." , stato= ".$record->stato." , descrizione=".$record->descrizione;

        }

        echo "\nStoria Assegnazioni Traslocatori Destinazione:\n";
        $storia = $preventivo->getStoriaAssegnazioniTraslocatoriDestinazione();

        foreach ($storia as $record)
        {
            echo "\nData: ".$record->data.", id_traslocatore:".$record->id_traslocatore." , stato= ".$record->stato." , descrizione=".$record->descrizione;

        }

        //Arriva conferma da parte di tutti gli operatori
        $preventivo->confirmTrasportatore();
        $preventivo->confirmTraslocatorePartenza();
        $preventivo->confirmTraslocatoreDestinazione();

*/


       echo "\nStato Accettazione: ". ($preventivo->getStatoAccettazioneOperatori() ? 'ACCETTATO':'NON ACCETTATO');

        echo "\nD: ".$preventivo->getGiorniDeposito();
    }

}

$t = new TestPreventivo();
$t->run();