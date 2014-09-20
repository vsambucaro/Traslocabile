<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 9/20/14
 * Time: 9:22 AM
 */

class DestinatarioPreventivoBusiness {

    public $id_preventivo;
    public $ragione_sociale;
    public $cap;
    public $città;
    public $indirizzo;
    public $provincia;
    public $telefono;

    public function __construct()
    {
        $this->log = new KLogger('traslocabile.log',KLogger::DEBUG);

    }

    public function load($id_preventivo)
    {
        $con = DBUtils::getConnection();
        $sql ="SELECT * FROM destinataio_preventivo_business WHERE id_preventivo=$id_preventivo";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
        {
            $this->id_preventivo = $row->id_preventivo;
            $this->ragione_sociale = $row->ragione_sociale;
            $this->cap = $row->cap;
            $this->città = $row->città;
            $this->indirizzo = $row->indirizzo;
            $this->provincia = $row->provincia;
            $this->telefono = $row->telefono;
        }

        DBUtils::closeConnection($con);
    }

    public function save()
    {
        $id_preventivo = $this->id_preventivo;
        $ragione_sociale = $this->ragione_sociale;
        $cap = $this->cap;
        $città = $this->città;
        $provincia = $this->provincia;
        $indirizzo = $this->indirizzo;
        $telefono = $this->telefono;

        $SQL="INSERT INTO destinataio_preventivo_business (id_preventivo, ragione_sociale, cap, città, indirizzo, provincia, telefono)
        VALUES ('$id_preventivo', '$ragione_sociale', '$cap','$città', '$indirizzo', '$provincia','$telefono')
        ON DUPLICATE KEY
        UPDATE ragione_sociale=$ragione_sociale, cap=$cap, città=$città, provincia=$provincia,
        indirizzo = $indirizzo, telefono=$telefono";

        $con =DBUtils::getConnection();
        $res = mysql_query($SQL);
        if (!$res)
        {
            $this->log->LogError("Query fallita: ".$SQL);
        }
        DBUtils::closeConnection($con);

    }

} 