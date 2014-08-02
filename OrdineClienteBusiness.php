<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/22/14
 * Time: 11:45 AM
 */

class OrdineClienteBusiness extends PreventivoBusiness {

  const TIPO_ORDINE=1;

    /**
     * costruisce l'oggetto ordine a partire dall'id_ordine
     * @param $id_ordine
     */
    public function __construct( $id_ordine)
    {
        $this->load($id_ordine);
    }

    public function getDataOrdine() { return $this->data_preventivo; }

} 