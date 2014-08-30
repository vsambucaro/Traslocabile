<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/21/14
 * Time: 10:28 PM
 */

require_once "Bootstrap.php";

class TestFattura {

    public function creaFatturaASaldoConsumer()
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
        $cliente->tipologia_cliente = Customer::CLIENTE_CONSUMER;

        $fatture = new FattureClienti();
        $progressivo = $fatture->creaNuovaFattura(array(new OrdineCliente(117)) , $cliente, null, 60, 20.4749761836);
        if ($progressivo)
            echo "\nCreata fattura: ".$progressivo;
        //Aggiunge il pagamento dell'accounto
        $fattura = new FatturaCliente($progressivo, 2014);
        $pagamento = new Pagamento(80.4749761836,"2014-08-27","Saldo Fattura per Ordine n. 117", $progressivo, 2014);
        $fattura->addPagamento($pagamento);
        $fattura->setFatturaSaldata();

        //Aggiorna anche lo stato ordine
        $ordine = new OrdineCliente(117);
        $ordine->setStatoFatturazioneCompleto();

        echo "\nFine\n";
    }

    public function creaFatturaAccontoConsumer()
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
        $cliente->tipologia_cliente = Customer::CLIENTE_CONSUMER;

        $fatture = new FattureClienti();
        $progressivo = $fatture->creaNuovaFattura(array(new OrdineCliente(117)) , $cliente, null, 20, 4);
        if ($progressivo)
            echo "\nCreata fattura: ".$progressivo;

        //Aggiunge il pagamento dell'accounto
        $fattura = new FatturaCliente($progressivo, 2014);
        $pagamento = new Pagamento(24,"2014-08-27","Acconto su Ordine n. 117", $progressivo, 2014);
        $fattura->addPagamento($pagamento);
        $fattura->setFatturaSaldata();

        echo "\nFine\n";
    }


    public function creaFatturaBusiness()
    {
        $cliente = new Customer(1, 'test@gmail.com');
        $cliente->id_cliente = 1;
        $cliente->cap=97016;
        $cliente->citta = "CARRAPIPI";
        $cliente->codice_fiscale="SMB";
        $cliente->indirizzo = "VIA TOTI 10";
        $cliente->provincia = "RG";
        $cliente->ragione_sociale = "RAG SOCIALE TEST";
        $cliente->piva="12345";
        $cliente->tipologia_cliente = Customer::CLIENTE_BUSINESS;

        $fatture = new FattureClienti();
        $progressivo = $fatture->creaNuovaFattura(array(new OrdineClienteBusiness(161), new OrdineClienteBusiness(162), new OrdineClienteBusiness(163)) , $cliente);
        if ($progressivo)
            echo "\nCreata fattura: ".$progressivo;

        echo "\nFine\n";
    }

    public function testPagamentoFatturaBusiness()
    {
        $fattura = new FatturaCliente( "000016", 2014 );
        $pagamento = new Pagamento(2216.7634326132,"2014-08-27","PROVA");
        $fattura->addPagamento($pagamento);
        print_r($fattura);
    }

    public function getListaPagamentiBusiness()
    {
        $fattura = new FatturaCliente( "000016", 2014 );
        $lista = $fattura->getListaPagamenti();
        foreach ($lista as $pagamento)
            print_r($pagamento);
    }
}

$m = new TestFattura();
//$m->creaFatturaAccontoConsumer();
$m->creaFatturaASaldoConsumer();
//$m->creaFatturaConsumer();
//$m->creaFatturaBusiness();
//$m->testPagamentoFatturaBusiness();
//$m->getListaPagamentiBusiness();