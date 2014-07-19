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

    public function __construct( $id_cliente, $email )
    {
        $this->id_cliente = $id_cliente;
        $this->email = $email;
    }

    public function setIdCliente($id_cliente)
    {
        $this->id_cliente = $id_cliente;
    }

} 