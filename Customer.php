<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/22/14
 * Time: 5:34 PM
 */

class Customer {

    public $id_cliente;
    public $email;
    public $ragione_sociale;
    public $indirizzo;
    public $citta;
    public $cap;
    public $provincia;
    public $codice_fiscale;
    public $piva;
    public $tipologia_cliente;

    const CLIENTE_BUSINESS = 1;
    const CLIENTE_CONSUMER = 0;


    public function __construct( $id_cliente, $email = null )
    {
        $this->id_cliente = $id_cliente;
        $this->email = $email;
    }

    public function setIdCliente($id_cliente)
    {
        $this->id_cliente = $id_cliente;
    }

} 