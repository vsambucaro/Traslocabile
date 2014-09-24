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

    public function testUpdate()
    {
        $obj = new TariffeBusiness(null,'CUCINIERI');
        $lista = $obj->getTabella("SCARICO");
        foreach ($lista as $record)
        {
            if ($record->id==20)
            {
                $record->costo = 15; //da 12 passa a 15;
            }
        }

        $obj->updateTabella($lista);
    }
}

$t = new TestTariffeBusiness();
$t->testUpdate();