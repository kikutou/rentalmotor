<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Controller;

use Eccube\Application;

class TopController extends AbstractController
{

    public function index(Application $app)
    {
        return $app->render('index.twig');
    }

    public function api()
    {
        $insJson = $this->getHTTPS("https://www.instagram.com/justin/media/");
        $insStr = json_decode($insJson);
        $insImageArr = array();
        $insTextArr = array();
        $inslinkArr = array();
        for($i=0; $i<12; $i++){
            $insImageArr[$i] =  $insStr->{'items'}[$i]->images->low_resolution->url;
            $insTextArr[$i] = $insStr->{'items'}[$i]->caption->text;
            $inslinkArr[$i] = $insStr->{'items'}[$i]->link;
            $outputArr[$i] = $insImageArr[$i].",".$insTextArr[$i].",".$inslinkArr[$i];
        }
        //$outputArr = array_merge($insImageArr, $insTextArr, $inslinkArr);
        echo json_encode($outputArr);
        exit();
        //return $app->render('../api.twig');
    }

    private function getHTTPS($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
