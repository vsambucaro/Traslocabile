<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 10:32 AM
 */


class ArredoDettagliato extends Arredo
{


    public function __construct( $id_arredo = null)
    {
        if (!$id_arredo) return;
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
            $this->record[$this::SMONTABILE] = $row->smontabile;
            $this->record[$this::CONTENITORE] = $row->contenitore;
            $this->record[$this::IMBALLABILE] = $row->imballabile;
            $this->record[$this::MONTATO_PIENO] = $row->montato_pieno;
            $this->record[$this::MONTATO_VUOTO] = $row->montato_vuoto;
            $this->record[$this::SMONTATO_PIENO] = $row->smontato_pieno;
            $this->record[$this::SMONTATO_VUOTO] = $row->smontato_vuoto;
            $this->record[$this::DESCRIZIONE] = $row->descrizione;
            $found = true;
        }
        DBUtils::closeConnection($con);

        if (!$found) {
            throw new Exception('id_arredo non trovato');
        }
    }

    public function toString()
    {
        $str = 'ID:'.$this->record[$this::ID].', '.
            'AMBIENTE:'.$this->record[$this::AMBIENTE].', '.
            'ARREDO:'.$this->record[$this::ARREDO].', '.
            'VARIANTE:'.$this->record[$this::VARIANTE].', '.
            'DIMENSIONI_DA_RICHIEDERE:'.$this->record[$this::DIMENSIONI_DA_RICHIEDERE].', '.
            'DIM_A:'.$this->record[$this::DIM_A].', '.
            'DIM_L:'.$this->record[$this::DIM_L].', '.
            'DIM_P:'.$this->record[$this::DIM_P].', '.
            'DESCRIZIONE:'.$this->record[$this::DESCRIZIONE].', '.
            'SMONTABILE:'.$this->record[$this::SMONTABILE].', '.
            'CONTENITORE:'.$this->record[$this::CONTENITORE].', '.
            'IMBALLABILE:'.$this->record[$this::IMBALLABILE].', '.
            'MONTATO_PIENO:'.$this->record[$this::MONTATO_PIENO].', '.
            'MONTATO_VUOTO:'.$this->record[$this::MONTATO_VUOTO].', '.
            'SMONTATO_PIENO:'.$this->record[$this::SMONTATO_PIENO].', '.
            'SMONTATO_VUOTO:'.$this->record[$this::SMONTATO_VUOTO];

        return $str;
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