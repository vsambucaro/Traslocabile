<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 8/14/14
 * Time: 4:21 PM
 */
require_once "Bootstrap.php";

class TestFatture {

    public function test()
    {
        $erp = new ERP();
        $res = $erp->getFattureFornitori(null,98,null,null);

        //print_r($res);

    }

    public function creaFattura()
    {
        $lista_ordini = array(
            OrdineFornitore::load(200,98),
            OrdineFornitore::load(201,98),
        );
        $manager = new FattureFornitori();
        $fornitore = new Fornitore(98,"test@cp.com");
        $fornitore->ragione_sociale="PINCO PALLA SRL";
        $manager->registraNuovaFattura($lista_ordini, $fornitore ,"2014-08-14",'X00001','2014');

    }

    public function getFatturaByOrdine()
    {

        $ordine = OrdineFornitore::load(200,98);
        $result = $ordine->getNumeroFattura();
        if ($result)
            echo "\nNumero FAttura: ".$result['numero_fattura']." - ".$result['anno'];
        else
            echo "\nOrdine ancora non fatturato";

    }

}


$m = new TestFatture();
//$m->test();
//$m->creaFattura();
$m->getFatturaByOrdine();