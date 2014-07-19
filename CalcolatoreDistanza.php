<?php
/**
 * Created by PhpStorm.
 * User: vsambucaro
 * Date: 6/21/14
 * Time: 4:49 PM
 */

class CalcolatoreDistanza {

    public function getDrivingInformationV2($start, $finish)
    {
        $start = urlencode($start);
        $finish = urlencode($finish);

        //$url = "http://maps.google.com/maps/nav?q=from:$start%20to:$finish";
        $url = "http://maps.googleapis.com/maps/api/distancematrix/json?origins=$start&destinations=$finish&mode=driving&language=en-EN&sensor=false";
        $jsonResult = file_get_contents($url);

        $jsonToObject = json_decode(utf8_encode(str_replace("\u0026nbsp;", " ", $jsonResult)));

        if ($jsonToObject->status == "OK") {
            //Good Address
            return array(
                'distance' => str_replace(' ', '', str_replace('km', '', $jsonToObject->rows[0]->elements[0]->distance->text)),
                'time' => $jsonToObject->rows[0]->elements[0]->duration->text,
            );
        } else {
            //Bad Address
            throw new Exception('Could not resolve URL');
        }
    }
} 