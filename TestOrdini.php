<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 7:37 PM
 */

require_once 'Bootstrap.php';

class TestOrdini {


    public function run()
    {
        $ordini = new Ordini();
        $lista = $ordini->getListaOrdini(null,89);
        foreach ($lista as $ordine) {
            echo "\nOrdine: ".$ordine->getId();
        }
    }
}

$m = new TestOrdini();
$m->run();