<?php

namespace CornellCustomDev\LaravelStarterKit\Contact;

use Exception;
use Giggsey\Locale\Locale;
use Illuminate\Support\Facades\Log;
use libphonenumber\PhoneNumberUtil;
use Propaganistas\LaravelPhone\PhoneNumber as LaravelPhoneNumber;

class PhoneNumber
{
    private ?LaravelPhoneNumber $phoneNumber;

    private string $sourceNumber;

    private ?string $sourceCountryCallingCode;

    private bool $isValid = false;

    public function __construct(string $number, ?string $countryCallingCode = null)
    {
        $this->sourceNumber = $number;
        $this->sourceCountryCallingCode = $countryCallingCode ?: null;
        try {
            $this->phoneNumber = $this->parseNumber($number, $countryCallingCode);
        } catch (Exception $exception) {
            Log::info('PhoneNumber '.$number.' '.$exception->getMessage());
        }
    }

    public static function make(string $number, ?string $countryCallingCode = null): ?self
    {
        return new self($number, $countryCallingCode);
    }

    public function getCountry(): ?string
    {
        if (is_null($this->phoneNumber)) {
            return null;
        }

        return $this->phoneNumber->getCountry();
    }

    public function isValid(): bool
    {
        if (! $this->isValid) {
            return false;
        }

        return $this->phoneNumber?->isValid() ?? false;
    }

    public static function getCountryListWithIntlCode()
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $phoneCountries = collect(Locale::getAllCountriesForLocale('en'))
            ->sort()
            ->mapWithKeys(function ($country, $code) use ($phoneNumberUtil) {
                // "US" => "United States (1)",
                return [$code => "$country ({$phoneNumberUtil->getCountryCodeForRegion($code)})"];
            });

        return $phoneCountries;
    }

    public function getSourceNumber(): string
    {
        return $this->sourceNumber;
    }

    public function getCallingCode(): ?int
    {
        if (! $this->isValid()) {
            return $this->sourceCountryCallingCode;
        }
        $regionCode = $this->phoneNumber->getCountry();
        $phoneUtil = PhoneNumberUtil::getInstance();

        return $phoneUtil->getCountryCodeForRegion($regionCode);
    }

    public function getNumberWithoutCallingCode(): string
    {
        if (! $this->isValid()) {
            return $this->sourceNumber;
        }

        $callingCode = $this->getCallingCode();
        $numberWithoutCode = str_replace("+$callingCode", '', $this->phoneNumber->formatE164());

        return $numberWithoutCode;
    }

    private function parseNumber(string $number, ?string $countryCallingCode = null): ?LaravelPhoneNumber
    {
        // If we don't have a calling code, just use the number directly
        if (empty($countryCallingCode)) {
            $phoneNumber = new LaravelPhoneNumber($number, 'US');
            if ($phoneNumber->isValid()) {
                $this->isValid = true;

                return $phoneNumber;
            }
            $phoneNumber = new LaravelPhoneNumber('+'.ltrim($number, '+'), 'US');
            if ($phoneNumber->isValid()) {
                $this->isValid = true;

                return $phoneNumber;
            }

            return null;
        }

        // Assume good data first...
        $phoneNumber = new LaravelPhoneNumber('+'.ltrim($countryCallingCode, '+').' '.$number, 'US');
        if ($phoneNumber->isValid()) {
            $this->isValid = true;

            return $phoneNumber;
        }

        // Didn't get parsable data, so try converting the country code
        $countryCallingCode = ltrim($countryCallingCode, '0+');
        $phoneNumber = new LaravelPhoneNumber('+'.$countryCallingCode.' '.$number, 'US');
        if ($phoneNumber->isValid()) {
            $this->isValid = true;

            return $phoneNumber;
        }

        return null;
    }

    public static function getCountryCallingCode(string $regionCode): ?int
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $countryCode = $phoneUtil->getCountryCodeForRegion($regionCode);

        return $countryCode == 0 ? null : $countryCode;
    }

    public function getNumber(): string
    {
        if (! $this->isValid()) {
            return trim($this->sourceCountryCallingCode.' '.$this->sourceNumber);
        }

        return $this->phoneNumber->formatE164();
    }

    public function getNumberFormattedForUS(): string
    {
        if (! $this->isValid()) {
            return $this->getNumber();
        }
        if ($this->getCountry() == 'US') {
            return $this->phoneNumber->formatNational();
        }

        return $this->phoneNumber->formatInternational();
    }

    public function getNumberFormattedForTel(): string
    {
        if (! $this->isValid()) {
            return $this->getNumber();
        }

        return $this->phoneNumber->formatForMobileDialingInCountry('US');
    }

    public static function formatNumberForUS(string $number): string
    {
        $phoneNumber = new self($number);

        return $phoneNumber->getNumberFormattedForUS();
    }

    public static function formatNumberForTel(string $number): string
    {
        $phoneNumber = new self($number);

        return $phoneNumber->getNumberFormattedForTel();
    }
}
