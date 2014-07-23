<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/11/14
 * Time: 10:10 PM
 */
require_once 'Bootstrap.php';


class TestOrdineFornitore
{
    private $log;


    public function __construct()
    {
        $this->log = new KLogger('traslocabile.txt',KLogger::DEBUG);
    }
    public function run()
    {
        $this->log->LogDebug("Inizio");
        $ordineFornitore = new OrdineFornitore();
        $ordineFornitore->load( 84, 98 );
        $saldo = $ordineFornitore->getSaldoFornitore(98);
        var_dump($saldo);
        $this->log->LogDebug("Fine");
    }
}


$test = new TestOrdineFornitore();
$test->run();