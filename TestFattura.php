<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/21/14
 * Time: 10:28 PM
 */

require_once "Bootstrap.php";

class TestFattura {

    public function creaFattura()
    {
        $cliente = new Customer(100, 'test@gmail.com');
        $cliente->id_cliente = 100;
        $cliente->cap=97016;
        $cliente->citta = "CARRAPIPI";
        $cliente->codice_fiscale="SMB";
        $cliente->indirizzo = "VIA TOTI 10";
        $cliente->provincia = "RG";
        $cliente->ragione_sociale = "RAG SOCIALE TEST";
        $cliente->piva="12345";

        $fatture = new Fatture();
        $progressivo = $fatture->createFattura(117, $cliente);
        if ($progressivo)
            echo "\nCreata fattura: ".$progressivo;

        echo "\nFine\n";
    }
}

$m = new TestFattura();
$m->creaFattura();