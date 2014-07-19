<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/22/14
 * Time: 5:34 PM
 */

class Indirizzo {

    public $indirizzo;
    public $citta;
    public $provincia;
    public $cap;

    public function __construct( $indirizzo, $citta, $provincia, $cap )
    {
        $this->indirizzo = $indirizzo;
        $this->citta = $citta;
        $this->provincia = $provincia;
        $this->cap = $cap;
    }

    public function toGoogleAddress()
    {

        $ret = "";
        if ($this->indirizzo) {
            $ret = $this->indirizzo.",";

        }
        if ($this->cap) {
            $ret .= $this->cap.",";

        }
        if ($this->citta)
        {
            $ret .= $this->citta.",";
        }

        if ($this->provincia)
        {
            $ret .= $this->provincia.",";
        }

        $ret = substr($ret,0, strlen($ret)-1);

        return $ret;

    }
} 