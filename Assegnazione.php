<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/30/14
 * Time: 8:42 AM
 */

abstract class Assegnazione {

    public $id;
    public $descrizione;
    public $stato;
    public $data;

    abstract public function load($id);

}