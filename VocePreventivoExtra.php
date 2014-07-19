<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/7/14
 * Time: 10:32 PM
 */

class VocePreventivoExtra {

    const POSITIVO='POSITIVO';
    const NEGATIVO='NEGATIVO';

    private $segno;
    private $descrizione;
    private $valore;

    public function __construct( $segno, $descrizione, $valore)
    {
        $this->segno = $segno;
        $this->descrizione = $descrizione;
        $this->valore = $valore;
    }

    public function setDescrizione($descrizione)
    {
        $this->descrizione = $descrizione;
    }

    public function setValore($valore)
    {
        $this->valore = $valore;
    }

    public function getDescrizione() { return $this->descrizione; }
    public function getSegno() { return $this->segno; }
    public function getValore() { return $this->valore; }


} 