<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/23/14
 * Time: 11:24 PM
 */

require_once "Bootstrap.php";

class TestTariffeClientiBusiness {

    public function test()
    {
        $costo = TariffeSnaidero::getCostoMontaggio(200, 500, 1);
        echo "\nCosto scaglione da usare: ".$costo;
    }
}

$m = new TestTariffeClientiBusiness();
$m->test();