<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/11/14
 * Time: 10:10 PM
 */
require_once 'Bootstrap.php';


class TestOrdine
{
    private $log;


    public function __construct()
    {
        $this->log = new KLogger('traslocabile.txt',KLogger::DEBUG);
    }
    public function run()
    {
        $this->log->LogDebug("Inizio");
        $preventivo = new Preventivo();
        $ret = $preventivo->loadDettaglio(111);

        if (!$ret) die("Preventivo non presente");
        $ordine = $preventivo->changeToOrdine(); //cambia lo stato da preventivo a ordine;

        echo "Object is Ordine: ".($ordine instanceof OrdineCliente) ? "OK":"NOK";

        //Ottiene il saldo
        $saldo = $ordine->getSaldoCliente();
        echo "\nSaldo: ".$saldo;

        //effettuo pagamento
        $ordine->addPagamentoCliente(new Pagamento(100,date('Y-m-d'), "Anticipo 20%"));

        //Ottiene il saldo
        $saldo = $ordine->getSaldoCliente();
        echo "\nSaldo: ".$saldo;

        //effettuo pagamento
        $ordine->addPagamentoCliente(new Pagamento(208,date('Y-m-d'), "Saldo 80%"));

        //Ottiene il saldo
        $saldo = $ordine->getSaldoCliente();
        echo "\nSaldo: ".$saldo;

        $lista_pagamenti = $ordine->getListaPagamentiCliente();
        foreach ($lista_pagamenti as $pagamento)
            echo "\nData: ".$pagamento->data.", importo: ".$pagamento->importo.", descrizione: ".$pagamento->descrizione;

        $this->log->LogDebug("Fine");
    }
}


$test = new TestOrdine();
$test->run();