<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 7:37 PM
 */

require_once 'Bootstrap.php';

class TestArredi {


    public function run()
    {
        $arredi = new Arredi();
        $lista = $arredi->getArredi(Arredi::TIPO_ARREDI_PREVENTIVO_ISTANTANEO , 'CUCINA');
        foreach ($lista as $arredo) {
            echo "\nArredo: ".$arredo->toString();
        }
    }
}

$m = new TestArredi();
$m->run();