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
        $filter = array();
        $filter[ERP::FILTRO_PERIODO_DAL] = '2014-01-01 00:00:00';
        $filter[ERP::FILTRO_PERIODO_AL] = '2014-12-31 00:00:00';
        $res = $erp->getFatturato($filter);
        print_r($res);

        echo "\nFine\n";
    }
}

$m = new TestERP();
$m->test();