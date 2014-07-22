<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/21/14
 * Time: 10:28 PM
 */

require_once "Bootstrap.php";

class TestERP {

    public function test()
    {

        $erp = new ERP();
        $costi = $erp->getCosti();
        print_r($costi);

        echo "\nFine\n";
    }
}

$m = new TestERP();
$m->test();