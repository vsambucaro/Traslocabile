<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/22/14
 * Time: 5:34 PM
 */

class Fornitore {

    public $id_fornitore;
    public $email;
    public $ragione_sociale;
    public $indirizzo;
    public $citta;
    public $cap;
    public $provincia;
    public $codice_fiscale;
    public $piva;




    public function __construct( $id_fornitore, $email = null )
    {
        $this->id_fornitore = $id_fornitore;
        $this->email = $email;
    }

    public function setIdFornitore($id_fornitore)
    {
        $this->id_fornitore = $id_fornitore;
    }

    //TODO metodo per getORdini e getFatture
} 