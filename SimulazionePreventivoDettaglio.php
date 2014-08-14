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

    public function run()
    {
        $preventivo = new Preventivo();
        //$preventivo->load(72);
        if (!$preventivo->loadDettaglio(306)) die ("Preventivo non esiste");


        echo "\nStato: ".$preventivo->getStato();

        $preventivatore = $preventivo->getPreventivatore();
        $preventivatore->setKM(35);

        //var_dump($preventivatore);

        $lista_arredi = $preventivo->getListaArredi();
        if ($lista_arredi)
        foreach ($lista_arredi as $arredo)
            echo "\nArredo: ".$arredo->getCampo(Arredo::ID).", paramtro_B:".$arredo->getParteVariabile(Arredo::METRI_LINEARI).", qta=".$arredo->getQta();



        $elaborazione = $preventivatore->elabora();

        echo "\nELABORAZIONE:\n";
        echo "\nKM: ".$preventivatore->getKM()."\n";
        var_dump($elaborazione);

        $mc = $preventivatore->getMC();

        echo "\nMC:\n";
        var_dump($mc);


    }

}

$t = new SimulazionePreventivoDettaglio();
$t->run();