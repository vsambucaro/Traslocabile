<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/22/14
 * Time: 11:45 AM
 */

class OrdineClienteBusiness extends PreventivoBusiness {

  const TIPO_ORDINE=1;
    private $id_ordine;

    /**
     * costruisce l'oggetto ordine a partire dall'id_ordine
     * @param $id_ordine
     */
    public function __construct( $id_ordine)
    {
        $this->id_ordine = $id_ordine;
        $this->load($id_ordine);
    }

    public function getDataOrdine() { return $this->data_preventivo; }

    public function getNumeroFattura()
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT numero_fattura, anno FROM ordini_fatture_attive WHERE id_ordine=".$this->id_ordine;
        $res = mysql_query($sql);
        $numero_fattura = null;
        $anno = null;
        while ($row = mysql_fetch_object($res))
        {
            $numero_fattura = $row->numero_fattura;
            $anno = $row->anno;
        }

        DBUtils::closeConnection($con);

        if ($numero_fattura && $anno)
            return array('numero_fattura'=>$numero_fattura, 'anno'=>$anno);
        else
            return null;
    }

} 