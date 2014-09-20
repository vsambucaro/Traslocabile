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
        $preventivatore = new PreventivatoreBusiness(null, 'CUCINIERI');

        $preventivatore->setAlgoritmo(PreventivatoreBusiness::TIPO_ALGORITMO_STANDARD);

        $preventivatore->addItem(new ItemPreventivatoreBusiness("test", null, 1,2,3,1));
        $preventivatore->addItem(new ItemPreventivatoreBusiness("prova", 10,null, null, null, 1));


        //$indirizzoPartenza = new Indirizzo('Via Flavio Gioia 8','Cernusco Sul Naviglio','MI','20063');
        //$indirizzoDestinazione = new Indirizzo('','Roma','','');

        $indirizzoPartenza = new Indirizzo('', '',  '' , '');
        $indirizzoDestinazione = new Indirizzo('','','','');

        $preventivatore->setIndirizzoPartenza($indirizzoPartenza);
        $preventivatore->setIndirizzoDestinazione($indirizzoDestinazione);
        $preventivatore->setPianiDaSalire(3);
        $preventivatore->setMontaggio(true);
        $preventivatore->setMontaggioInLocaliDiPreggio(false);
        $preventivatore->setPagamentoContrassegno(false);

        $res = $preventivatore->elabora();
        print_r($res);


        $customer = new Customer(1, 'test@gmail.com');
        $preventivo = $preventivatore->save($customer);

        //Destinatario
        $destinatario = new DestinatarioPreventivoBusiness();
        $destinatario->ragione_sociale="Pippo Rossi";
        $destinatario->cap= "20063";
        $destinatario->città = "Milano";
        $destinatario->provincia ="MI";
        $destinatario->indirizzo="VIA POPPA 1";
        $preventivo->setDestinatarioPreventivoBusiness($destinatario);
        $preventivo->note_partenza = "NOTA 1";
        $preventivo->note_destinazione = "NOTA 2";

        $preventivo->save();

        var_dump($preventivo);
       // $preventivo->setIdTraslocatorePartenza( 716 );
       // $preventivo->setIdTrasportatore( 711 );
       // $preventivo->setIdTraslocatoreDestinazione(716);

       // $preventivo->confirmTrasportatore();
       // $preventivo->confirmTraslocatorePartenza();
       // $preventivo->save();
        //$ordine = $preventivo->changeToOrdine(); //cambia lo stato da preventivo a ordine;
        echo "\nFINE";

    }

    public function loadPreventivo()
    {
        $id_preventivo = 5;
        $preventivo = new PreventivoBusiness();
        $preventivo->loadDettaglio($id_preventivo);
        $preventivatore = $preventivo->getPreventivatore();
        $result = $preventivatore->elabora();
        echo "\nPrezzo cliente: ".round($result['prezzo_cliente_con_iva'],2);

        var_dump($result);


        $listaItems = $preventivo->getItems();
        foreach($listaItems as $item) {

            $tempItem = array(
                'id'=>$item->id,
                "descrizione" 	=> $item->descrizione,
                "lunghezza" 	=> $item->lunghezza,
                "altezza"	=> $item->altezza,
                "profondita" => $item->profondita,
                "qta"		=> $item->qta,
                "mc"		=> $item->mc
            );

            echo "\nID:" .$tempItem['id'].", descrizione: ".$tempItem['descrizione'].
                ", lunghezza: ".$tempItem['lunghezza'].", mc: ".$tempItem['mc'].
                ", altezza: ".$tempItem['altezza'].", profondità: ".$tempItem['profondita'].
                ", qta: ".$tempItem['qta'];

        }

        echo "\nLista Servizi: ";
        $servizi = $preventivo->getListaServiziIstantaneo();
        //print_r($servizi);
        if ($servizi)
            foreach ($servizi as $servizio)
                echo  "\nServizio: ".$servizio->getCampo(ServizioIstantaneo::SERVIZIO);

    }

    public function testUpdate()
    {
        $id_preventivo = 5;
        $preventivo = new PreventivoBusiness();
        $preventivo->loadDettaglio($id_preventivo);
        $preventivatore = $preventivo->getPreventivatore();

        //Aggiungo una voce extra
        $voce_extra = new VocePreventivoExtra(VocePreventivoExtra::POSITIVO, "EXTRA COSTO PER LA POSIZIONE", 100);
        $preventivatore->addVocePreventivoExtra($voce_extra);
        $result = $preventivatore->elabora();

        //Aggiorno tutto e salvo
        $preventivatore->updatePreventivo($preventivo);
        $preventivo->save();



        echo "\nPrezzo cliente: ".round($result['prezzo_cliente_con_iva'],2);

        var_dump($result);


    }
}

$m = new TestPreventivatoreBusiness();
$m->run();
//$m->loadPreventivo();
//$m->testUpdate();