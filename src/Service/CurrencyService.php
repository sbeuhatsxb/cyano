<?php

namespace App\Service;

use App\Repository\CurrencyRepository;
use App\Entity\Currency;

class CurrencyService
{
    /**
     * @var CurrencyRepository
     */
    protected $currencyRepository;

    /**
     * @param CurrencyRepository $currencyRepository
     * @param Currency $currency
     */
    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @param $code
     * @param $number
     */
    public function formatCurrency($code, $number)
    {
        $currentCurrency = $this->currencyRepository->findOneByCode($code);

        $displayedNumber = number_format(
            $number,
            2,
            $currentCurrency->getDecimalSeparator(),
            $currentCurrency->getThousandsSeparator()
        );

        if ($currentCurrency->getSymbolLocation() == false) {
            $displayedNumber = $currentCurrency->getSymbol().' '.$displayedNumber;
        } else {
            $displayedNumber = $displayedNumber.' '.$currentCurrency->getSymbol();
        }

        if (is_float($number) && $currentCurrency->getDecimalSeparator() == '') {
            $integer = floor($number);
            $decimal = ltrim(abs($number) - abs($integer), '0'); // .25
            $displayedNumber = rtrim($displayedNumber, $decimal);
        }

        return $displayedNumber;
    }
}
