<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 10:32 AM
 */


class ArredoIstantaneo extends Arredo
{

    public function __construct( $id_arredo )
    {
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM arredi WHERE id='$id_arredo'";
        $res = mysql_query($sql);
        $found = 0;
        while ($row=mysql_fetch_object($res)) {
            $this->record[$this::ID] = $row->id;
            $this->record[$this::AMBIENTE] = $row->ambiente;
            $this->record[$this::ARREDO] = $row->arredo;
            $this->record[$this::VARIANTE] = $row->variante;
            $this->record[$this::DIMENSIONI_DA_RICHIEDERE] = strtoupper($row->dimensioni_da_richiedere);
            $this->record[$this::DIM_A] = $row->dim_A;
            $this->record[$this::DIM_L] = $row->dim_L;
            $this->record[$this::DIM_P] = $row->dim_P;
            $found = true;

        }
        DBUtils::closeConnection($con);

        if (!$found) {
            throw new Exception('id_arredo non trovato');
        }
    }

    /**
     * @return int Calcola e ritorna i metri cubi
     */
    public function getMC()
    {
        $mc = 0;
        $parte_variabile = $this->getParteVariabile($this->record[$this::DIMENSIONI_DA_RICHIEDERE]);
        //echo "\nCampo Variabile: ".$this->record[$this::DIMENSIONI_DA_RICHIEDERE];
        //echo "\nParte Variable: ".$parte_variabile;

        if ( !$parte_variabile )
        {
            $mc = ($this->record[$this::DIM_A]/100) * ($this->record[$this::DIM_L]/100) * ($this->record[$this::DIM_P]/100);

        }
        else
        {
            switch ($this->record[$this::DIMENSIONI_DA_RICHIEDERE])
            {
                case $this::METRI_LINEARI :
                    $mc = ($this->record[$this::DIM_A]/100) * ($this->record[$this::DIM_L] * $parte_variabile)/100 *  ($this->record[$this::DIM_P]/100);
                    break;
                case $this::LARGHEZZA :
                    $mc = ($this->record[$this::DIM_A]/100) * ($this->record[$this::DIM_L] * $parte_variabile)/100 *  ($this->record[$this::DIM_P]/100);
                    break;
                case $this::NUMERO_ANTE :
                    $mc = ($this->record[$this::DIM_A]/100) * ($this->record[$this::DIM_L] * $parte_variabile)/100 *  ($this->record[$this::DIM_P]/100);
                    break;
                case $this::NUMERO_POSTI :
                    $mc = ($this->record[$this::DIM_A]/100) * ($this->record[$this::DIM_L] * $parte_variabile)/100 *  ($this->record[$this::DIM_P]/100);
                    break;
                default:
                    $mc = ($this->record[$this::DIM_A]/100) * ($this->record[$this::DIM_L]/100) * ($this->record[$this::DIM_P]/100);
                    break;
            }
        }

        return $mc * $this->getQta();
    }

} 