<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/23/14
 * Time: 10:03 PM
 */

class ItemPreventivatoreBusiness {

    public $descrizione;
    public $mc;
    public $larghezza;
    public $altezza;
    public $profondita;
    public $qta;
    public $id;

    public function __construct($descrizione, $mc = null, $larghezza = null, $altezza = null, $profondita = null, $qta = 1)
    {
        $this->descrizione = $descrizione;
        $this->larghezza = $larghezza;
        $this->altezza = $altezza;
        $this->profondita = $profondita;
        $this->qta = $qta;
        $this->mc = $mc;

    }
} 