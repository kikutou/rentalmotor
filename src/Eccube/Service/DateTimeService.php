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

namespace Eccube\Service;

use Eccube\Application;

/**
 * @deprecated since 3.0.0, to be removed in 3.1
 */
class DateTimeService
{
    /** @var \Eccube\Application */
    public $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param \Eccube\Entity\Product|null $Product
     * @return array
     */
    public function getRentalDate(\Eccube\Entity\Product $Product = null)
    {
        $now = new \DateTime();
        $rental = array();

        $deadline = (clone $now)->setDate((int)$now->format('Y'), (int)$now->format('m'), 1)->modify('+3 month');
        $date = (clone $now)->setDate((int)$now->format('Y'), (int)$now->format('m'), 1);
        $group = array();
        while ($date < $deadline) {
            $month = $date->format('n');
            $week = $date->format('w');
            if (!array_key_exists($month, $rental)) {
                $last_month = (clone $date)->modify('-1 day')->format('n');;
                $this->fillRentalDate($last_month, $group, $rental);

                $rental[$month] = array();
                $group = array();

                for ($w = 0; (string)$w < $week; $w++) {
                    $group[] = array('date' => '', 'day' => '', 'active' => false);
                }
            }

            $active = true;
            if (
                $date <= $now
                || $week === '0' || $week === '6'
                || (!is_null($Product) && $date < $Product->getStartDate())
            ) {
                $active = false;
            }
            $group[] = array('date' => $date->format('Y-m-d'), 'day' => $date->format('j'), 'active' => $active);

            if (count($group) === 7) {
                $rental[$month][] = $group;
                $group = array();
            }

            $date->modify('+1 day');
        }

        $last_month = (clone $date)->modify('-1 day')->format('n');;
        $this->fillRentalDate($last_month, $group, $rental);

        return $rental;
    }

    /**
     * @param string $last_month
     * @param array $group
     * @param array $rental
     */
    private function fillRentalDate($last_month, $group, &$rental)
    {
        if (!empty($group)) {
            while (count($group) < 7) {
                $group[] = array('date' => '', 'day' => '', 'active' => false);
            }

            $rental[$last_month][] = $group;

            while (count($rental[$last_month]) < 5) {
                $group = array();
                while (count($group) < 7) {
                    $group[] = array('date' => '', 'day' => '', 'active' => false);
                }

                $rental[$last_month][] = $group;
            }
        }
    }
}
