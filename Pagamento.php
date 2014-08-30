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

    public $numero_fattura;
    public $anno;

    public function __construct($importo, $data, $descrizione, $numero_fattura = null, $anno = null)
    {
        $this->importo = $importo;
        $this->data = $data;
        $this->descrizione = $descrizione;
        $this->numero_fattura = $numero_fattura;
        $this->anno = $anno;
    }

} 