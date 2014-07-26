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

class TestPreventivoDettaglio {

    public function run()
    {
        $preventivo = new Preventivo();
        //$preventivo->load(72);
        if (!$preventivo->loadDettaglio(116)) die ("Preventivo non esiste");

        var_dump($preventivo);
        //var_dump($preventivo->getCliente());
        //var_dump($preventivo->getIndirizzoDestinazione());
        //var_dump($preventivo->getIndirizzoPartenza());
        //var_dump($preventivo->getListaArredi());
        //var_dump($preventivo->getListaServiziIstantaneo());

        //$preventivo->setIdTraslocatorePartenza( 716 );
        //$preventivo->setIdTrasportatore( 711 );
        //$preventivo->save();

        echo "\nStato: ".$preventivo->getStato();

        $preventivatore = $preventivo->getPreventivatore();

        //var_dump($preventivatore);

        $lista_arredi = $preventivo->getListaArredi();
        if ($lista_arredi)
        foreach ($lista_arredi as $arredo)
            echo "\nArredo: ".$arredo->getCampo(Arredo::ID).", paramtro_B:".$arredo->getParteVariabile(Arredo::METRI_LINEARI).", qta=".$arredo->getQta();


        return;
        $preventivatore->elabora();

        $mc = $preventivatore->getMC();

        echo "\nMC:";
        var_dump($mc);

        echo "\nUpdate\n";
        //UPDATE item 0 dell'array
        $arredo_tmp = $lista_arredi[0];
        $arredo_tmp->setParametroB(ArredoDettagliato::MONTATO_PIENO);
        $arredo_tmp->setCampo(Arredo::DIM_A,100);
        $arredo_tmp->setQta(2);

        //var_dump($arredo_tmp);

        //Aggiungo un nuovo arredo
        $preventivatore->addArredoById(264,array(Arredo::NUMERO_ANTE=>2),3, Arredo::MONTATO_VUOTO);
        $preventivatore->updatePreventivo($preventivo);
        $preventivo->save();

        //Rielabora
        $preventivatore->elabora();
        $mc = $preventivatore->getMC();

        echo "\nMC:";
        var_dump($mc);

        //Rimuovo un arredo
        $preventivatore->removeArredoByRow(2);
        $preventivatore->updatePreventivo($preventivo);
        $preventivo->save();

        //Rielabora
        $preventivatore->elabora();
        $mc = $preventivatore->getMC();

        echo "\nMC:";
        var_dump($mc);

        //Aggiungo una voce extra
        $voce_extra = new VocePreventivoExtra(VocePreventivoExtra::POSITIVO, "EXTRA COSTO PER LA POSIZIONE", 100);
        $preventivatore->addVocePreventivoExtra($voce_extra);


        //Aggiungo servizio accessori aggravante partenza con valori standard da db
        $preventivatore->addServizioById(5, Servizio::SERVIZIO_PARTENZA);

        //Aggiungo servizio accessore aggravante destinazione con valori custom
        $servizio = new ServizioAccessoreAggravante(6);
        $servizio->setMargine(40);
        $preventivatore->addServizioByItem($servizio, Servizio::SERVIZIO_DESTINAZIONE);

        //Aggiorno tutto e salvo
        $preventivatore->updatePreventivo($preventivo);
        $preventivo->save();

        //Rielabora
        $preventivatore->elabora();
        $mc = $preventivatore->getMC();

        echo "\nMC:";
        var_dump($mc);

    }

}

$t = new TestPreventivoDettaglio();
$t->run();