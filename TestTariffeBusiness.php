<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 22/09/14
 * Time: 22:57
 */
require_once 'Bootstrap.php';

class TestTariffeBusiness
{

    public function test()
    {
        $obj = new TariffeBusiness(null,'CUCINIERI');
        $lista = $obj->getTabella("SCARICO");

        print_r($lista);

    }
}

$t = new TestTariffeBusiness();
$t->test();