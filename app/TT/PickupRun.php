<?php

namespace App\TT;

use App\TT\Items\Item;
use JetBrains\PhpStorm\ArrayShape;

class PickupRun
{
    #[ArrayShape(['scrap_emerald' => "float", 'scrap_ore' => "float|int", 'refined_flint' => "float|int", 'refined_sand' => "float|int"])]
    public static function quarry(int $truckCompacity): array
    {
        $rubbleWeight = 150;
        $pickupCount  = floor($truckCompacity / $rubbleWeight);

        // 10 gravel = 4 Flint and 6 Sand
        $gravel                 = $pickupCount * 12;
        $processableGravelCount = floor($gravel / 10);

        return [[
                    'scrap_emerald' => (int)$pickupCount,
                    'scrap_ore'     => (int)(4 * $pickupCount),
                    'refined_flint' => (int)(4 * $processableGravelCount),
                    'refined_sand'  => (int)(6 * $processableGravelCount)
                ]];
    }

    public static function logging(int $truckCompacity, string $craftingMaterialName): array
    {
        $logWeight   = 60;
        $pickupCount = floor($truckCompacity / $logWeight);

        if ($craftingMaterialName == 'refined_planks') {
            return [[
                'tcargodust'     => (int)(2 * $pickupCount),
                'refined_planks' => (int)($pickupCount)
            ]];
        }
        return [
            [
                'tcargodust' => (int)(10 * $pickupCount),
            ],
            [
                'tcargodust'     => (int)(2 * $pickupCount),
                'refined_planks' => (int)($pickupCount)
            ]
        ];

    }

    public static function trash(int $truckCompacity): array
    {
        $trashWeight = 90;
        $pickupCount = floor($truckCompacity / $trashWeight);

        return [
            [
                'scrap_aluminum' => (int)(4 * $pickupCount),
                'scrap_plastic'  => (int)(8 * $pickupCount),
                'scrap_tin'      => (int)(4 * $pickupCount),
            ]
        ];
    }

    public static function electronics(int $truckCompacity): array
    {
        $electronicsWeight = 130;
        $pickupCount       = floor($truckCompacity / $electronicsWeight);

        return [
            [
                'scrap_copper'  => (int)(8 * $pickupCount),
                'scrap_gold'    => (int)($pickupCount),
                'scrap_plastic' => (int)(12 * $pickupCount),
            ]
        ];
    }

    public static function toxicWaste(int $truckCompacity): array
    {
        $wasteWeight = 110;
        $pickupCount = floor($truckCompacity / $wasteWeight);

        return [
            [
                'scrap_acid' => (int)(4 * $pickupCount),
                'scrap_lead' => (int)(2 * $pickupCount),
                'scrap_mercury' => (int)(2 * $pickupCount),
            ]
        ];
    }

    public static function crudeOil(int $truckCompacity): array
    {
        $oilWeight = 150;
        $pickupCount = floor($truckCompacity / $oilWeight);

        return [
            [
                'petrochem_diesel' => (int)($pickupCount),
                'petrochem_kerosene' => (int)($pickupCount),
                'petrochem_petrol' => (int)($pickupCount * 2),
            ]
        ];
    }

    public static function rawGas(int $truckCompacity): array
    {
        $gasWeight = 150;
        $pickupCount = floor($truckCompacity / $gasWeight);

        return [
            [
                'military_chemicals' => (int)($pickupCount * 2),
                'petrochem_propane' => (int)($pickupCount * 2),
                'petrochem_waste' => (int)($pickupCount),
            ]
        ];
    }
}
