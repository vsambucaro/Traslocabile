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
        //print_r($res);

        foreach ($res as $id_cliente => $fatture)
        {

            echo "\nID_CLIENTE: ".$id_cliente;

            foreach ($fatture as $fattura)
            {
                echo "\nNumero Fattura: ".$fattura->getNumeroFattura()."/".$fattura->getAnno()."\tImporto: ".$fattura->getImporto()
                ."\tImponibile: ".$fattura->getImponibile()."\tIVA:".$fattura->getIva()."\tData Fattura: ".$fattura->getDataFattura();

                echo "\nLista Ordini in fattura: ";
                $lista_ordini = $fattura->getListaOrdini();
                foreach ($lista_ordini as $ordine)
                {
                    echo "\nData Ordine: ".$ordine->getDataOrdine()."\tImporto: ".$ordine->getImporto();
                    if ($ordine instanceof OrdineCliente)
                    {
                        $lista_pagamenti = $ordine->getListaPagamentiCliente();
                        foreach ($lista_pagamenti as $pagamento)
                            echo "\nPagamento: ".$pagamento->importo;
                    }

                }

                echo "\n========================================";

            }
        }
        //$res = $erp->getListaOrdiniDaFatturare(array(89));
        //print_r($res);

        echo "\nFine\n";
    }

    public function listaOrdiniDaFatturare()
    {
        $erp = new ERP();
        $res = $erp->getListaOrdiniDaFatturare();

        foreach ($res as $id_cliente => $items)
        {
            echo "\nID_CLIENTE: ".$id_cliente;
            foreach ($items as $item)
            {
              echo  "\nNum.Ordine: ".$item['id_ordine']."\tImporto: ".$item['importo']."\tImponibile:".$item['imponibile']
                ."\tIva:".$item['iva']."\tTipoCliente: ".$item['tipologia_cliente']."\tDataOrdine:".$item['data_ordine']."\tDataFineLavori:".$item['data_completamento_lavori'];

            }
            echo "\n========================================";
        }
    }

    public function testListaOrdiniFornitoriNonFatturati()
    {
        $erp = new ERP();
        $res = $erp->getListaOrdiniFornitoriNonFatturati();
        print_r($res);
    }
}

$m = new TestERP();
$m->testListaOrdiniFornitoriNonFatturati();
//$m->listaOrdiniDaFatturare();