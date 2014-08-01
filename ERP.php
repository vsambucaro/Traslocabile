<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 7/12/14
 * Time: 5:10 PM
 */

class ERP
{
    const FILTRO_PERIODO_DAL="DAL";
    const FILTRO_PERIODO_AL="AL";
    private $log;

    public function __construct()
    {
        $this->log = new KLogger('traslocabile.log',KLogger::DEBUG);

    }
    /**
     * Ritorna la lista delle fatture da ricevere dai vari fornitori
     */
    public function getListaFattureDaRicevere($filtro_fornitori = null)
    {
        $lista = array();
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM ordini_fornitori WHERE saldato = 0";
        $res = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
        {
            if ($filtro_fornitori)
                if (!in_array($row->id_fornitore, $filtro_fornitori))
                    continue;

            $lista[] = array('id_fornitore'=>$row->id_fornitore,
            'id_ordine'=>$row->id_ordine,
            'importo'=>$row->importo,
            'data_ordine'=>$row->data_ordine);
        }

        DBUtils::closeConnection($con);
        return $lista;
    }

    /**
     * Lista delle fatture da emettere verso i clienti
     */
    public function getListaOrdiniDaFatturare($filtro_cliente = null, $periodo = null, $filtro_tipologia_cliente = null)
    {
        $lista = array();
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM preventivi WHERE id_preventivo NOT IN (SELECT id_ordine FROM ordini_fatture_attive) AND stato = 'Completato' AND tipo=".OrdineCliente::TIPO_ORDINE;

        if ($periodo)
            if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo) && (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo)) )
            {
                $sql .=" AND data_trasloco BETWEEN '".$periodo[ERP::FILTRO_PERIODO_DAL]."' AND '".$periodo[ERP::FILTRO_PERIODO_AL]."'";
            }
            else
            {
                if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo))
                {
                    $sql .= " AND data_trasloco>='".$periodo[ERP::FILTRO_PERIODO_DAL]."'";
                }
                if (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo))
                {
                    $sql .= " AND data_trasloco<='".$periodo[ERP::FILTRO_PERIODO_AL]."'";
                }
            }

        if ($filtro_tipologia_cliente)
            $sql .= "AND tipologia_cliente=".$filtro_tipologia_cliente;

        $res = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
        {
            if ($filtro_cliente)
                if (!in_array($row->id_cliente, $filtro_cliente))
                    continue;



            if (array_key_exists($row->id_cliente, $lista))
            {
                $tmp = $lista[$row->id_cliente];

                $lista[$row->id_cliente] = array_merge($tmp,  array(array(
                    'id_ordine'=>$row->id_preventivo,
                    'importo'=>$row->importo,
                    'imponibile'=>$row->imponibile,
                    'iva'=>$row->iva,
                    'tipologia_cliente' =>$row->tipologia_cliente,
                    'data_ordine' => $row->data,
                    'data_completamento_lavori' => $row->data_trasloco)  ));

            }
            else
            {
                $lista[$row->id_cliente] = array( array(
                    'id_ordine'=>$row->id_preventivo,
                    'importo'=>$row->importo,
                    'imponibile'=>$row->imponibile,
                    'iva'=>$row->iva,
                    'tipologia_cliente' =>$row->tipologia_cliente,
                    'data_ordine' => $row->data,
                    'data_completamento_lavori' => $row->data_trasloco) );
            }

        }

        DBUtils::closeConnection($con);
        return $lista;
    }

    /**
     * Ritorna il fatturato ovvero la lista di fatture emesse
     * @param $periodo array con il periodo
     */
    public function getFatturato($periodo = null, $filtro_id_cliente = null, $filtro_tipologia_cliente = null, $numero_fattura = null)
    {
        $sql = "SELECT * FROM fatture_attive fa WHERE TRUE";


        if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo) && (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo)) )
        {
            $sql .="  AND fa.data BETWEEN '".$periodo[ERP::FILTRO_PERIODO_DAL]."' AND '".$periodo[ERP::FILTRO_PERIODO_AL]."'";
        }
        else
        {
            if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo))
            {
                $sql .= "  AND fa.data>='".$periodo[ERP::FILTRO_PERIODO_DAL]."'";
            }
            if (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo))
            {
                $sql .= " AND fa.data<='".$periodo[ERP::FILTRO_PERIODO_AL]."'";
            }
        }

        if ($filtro_tipologia_cliente)
            $sql .= "AND fa.tipologia_cliente=".$filtro_tipologia_cliente;

        if ($filtro_id_cliente)
            $sql .= "AND fa.id_cliente=".$filtro_id_cliente;

        if ($numero_fattura)
            $sql .= "AND fa.numero_fattura=".$numero_fattura;

        $con = DBUtils::getConnection();
        $res = mysql_query($sql);
        $this->log->LogDebug("Query Fatturato: ".$sql);

        $fatturato  = array();

        while ($row = mysql_fetch_object($res))
        {
            if (array_key_exists($row->id_cliente, $fatturato))
            {
                $tmp = $fatturato[$row->id_cliente];
                $fatturato[$row->id_cliente] = array_merge($tmp,  array(new FatturaCliente($row->numero_fattura, $row->anno) ));

            }
            else
            {
                $fatturato[$row->id_cliente] = array( new FatturaCliente($row->numero_fattura, $row->anno) );
            }
        }


        DBUtils::closeConnection($con);

        return $fatturato;

    }

    public function getCosti($periodo = null)
    {
        $sql = "SELECT p.id_preventivo, o.importo FROM preventivi p , ordini_fornitori o WHERE p.tipo=".OrdineCliente::TIPO_ORDINE;

        if ($periodo)
        {
            if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo) && (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo)) )
            {
                $sql .=" AND p.data BETWEEN '".$periodo[ERP::FILTRO_PERIODO_DAL]."' AND '".$periodo[ERP::FILTRO_PERIODO_AL]."'";
            }
            else
            {
                if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo))
                {
                    $sql .= " AND p.data>='".$periodo[ERP::FILTRO_PERIODO_DAL]."'";
                }
                if (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo))
                {
                    $sql .= " AND p.data<='".$periodo[ERP::FILTRO_PERIODO_AL]."'";
                }
            }
        }

        $sql .= " AND o.id_ordine = p.id_preventivo";



        $this->log->LogDebug("ERP.getCosti->SQL: ".$sql);


        $con = DBUtils::getConnection();
        $res = mysql_query($sql);

        $costi  = 0;

        while ($row = mysql_fetch_object($res))
            $costi += $row->importo;

        DBUtils::closeConnection($con);

        return $costi;

    }

    /**
     * Ritorna la lista di tutti i pagamenti ricevuti
     * @param $periodo
     */
    public function getEntrate($periodo)
    {
        $sql = "SELECT * FROM pagamenti_clienti ";

        $first = true;

        if ($periodo)
        {

            if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo) && (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo)) )
            {
                if (!$first)
                {
                    $sql .= " AND ";
                    $first = false;
                }
                else
                {
                    $sql .= " WHERE ";
                }
                $sql .=" data BETWEEN '".$periodo[ERP::FILTRO_PERIODO_DAL]."' AND '".$periodo[ERP::FILTRO_PERIODO_AL]."'";
            }
            else
            {
                if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo))
                {
                    if (!$first)
                    {
                        $sql .= " AND ";
                        $first = false;
                    }
                    else
                    {
                        $sql .= " WHERE ";
                    }

                    $sql .= " data>='".$periodo[ERP::FILTRO_PERIODO_DAL]."'";
                }
                if (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo))
                {
                    if (!$first)
                    {
                        $sql .= " AND ";
                        $first = false;
                    }
                    else
                    {
                        $sql .= " WHERE ";
                    }


                    $sql .= " data<='".$periodo[ERP::FILTRO_PERIODO_AL]."'";
                }
            }
        }

        $this->log->LogDebug("ERP.getEntrate->SQL: ".$sql);
        $con = DBUtils::getConnection();
        $res = mysql_query($sql);

        $entrate  = 0;

        while ($row = mysql_fetch_object($res))
            $entrate += $row->importo;

        DBUtils::closeConnection($con);

        return $entrate;
    }

    /**
     * Ritorna la lista di tutti i pgamenti effettuati
     * @param $periodo
     */
    public function getUscite($periodo = null)
    {
        $sql = "SELECT * FROM pagamenti_fornitori ";

        $first = true;

        if ($periodo)
        {

            if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo) && (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo)) )
            {
                if (!$first)
                {
                    $sql .= " AND ";
                    $first = false;
                }
                else
                {
                    $sql .= " WHERE ";
                }
                $sql .=" data BETWEEN '".$periodo[ERP::FILTRO_PERIODO_DAL]."' AND '".$periodo[ERP::FILTRO_PERIODO_AL]."'";
            }
            else
            {
                if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo))
                {
                    if (!$first)
                    {
                        $sql .= " AND ";
                        $first = false;
                    }
                    else
                    {
                        $sql .= " WHERE ";
                    }

                    $sql .= " data>='".$periodo[ERP::FILTRO_PERIODO_DAL]."'";
                }
                if (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo))
                {
                    if (!$first)
                    {
                        $sql .= " AND ";
                        $first = false;
                    }
                    else
                    {
                        $sql .= " WHERE ";
                    }


                    $sql .= " data<='".$periodo[ERP::FILTRO_PERIODO_AL]."'";
                }
            }
        }

        $this->log->LogDebug("ERP.getEntrate->SQL: ".$sql);
        $con = DBUtils::getConnection();
        $res = mysql_query($sql);

        $uscite  = 0;

        while ($row = mysql_fetch_object($res))
            $uscite += $row->importo;

        DBUtils::closeConnection($con);

        return $uscite;
    }



    public function getListaOrdiniFornitori($periodo = null)
    {
        $lista = array();
        $con = DBUtils::getConnection();
        $sql = "SELECT * FROM ordini_fornitori";

        $first = true;

        if ($periodo)
        {

            if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo) && (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo)) )
            {
                if ($first)
                {
                    $sql .= " WHERE ";
                }
                $sql .=" data_ordine BETWEEN '".$periodo[ERP::FILTRO_PERIODO_DAL]."' AND '".$periodo[ERP::FILTRO_PERIODO_AL]."'";
            }
            else
            {
                if (array_key_exists(ERP::FILTRO_PERIODO_DAL, $periodo))
                {
                    if (!$first)
                    {
                        $sql .= " AND ";
                        $first = false;
                    }
                    else
                    {
                        $sql .= " WHERE ";
                    }

                    $sql .= " data_ordine>='".$periodo[ERP::FILTRO_PERIODO_DAL]."'";
                }
                if (array_key_exists(ERP::FILTRO_PERIODO_AL, $periodo))
                {
                    if (!$first)
                    {
                        $sql .= " AND ";
                        $first = false;
                    }
                    else
                    {
                        $sql .= " WHERE ";
                    }


                    $sql .= " data_ordine<='".$periodo[ERP::FILTRO_PERIODO_AL]."'";
                }
            }
        }

        $res = mysql_query($sql);
        while ($row = mysql_fetch_object($res))
        {

            $lista[] = array('id_fornitore'=>$row->id_fornitore,
                'id_ordine'=>$row->id_ordine,
                'importo'=>$row->importo,
                'data_ordine'=>$row->data_ordine);
        }

        DBUtils::closeConnection($con);
        return $lista;
    }

} 