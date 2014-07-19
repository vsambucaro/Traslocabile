<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/11/14
 * Time: 9:37 PM
 */

class Pagamento
{
    private $id;
    public $importo;
    public $descrizione;
    public $data;

    public function __construct($importo, $data, $descrizione)
    {
        $this->importo = $importo;
        $this->data = $data;
        $this->descrizione = $descrizione;
    }

} 