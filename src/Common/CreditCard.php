<?php
/**
 * Credit Card class
 */

namespace Omnipay\Common;

use DateTime;
use DateTimeZone;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Credit Card class
 *
 * This class defines and abstracts all of the credit card types used
 * throughout the Omnipay system.
 *
 * Example:
 *
 * <code>
 *   // Define credit card parameters, which should look like this
 *   $parameters = [
 *       'firstName' => 'Bobby',
 *       'lastName' => 'Tables',
 *       'number' => '4444333322221111',
 *       'cvv' => '123',
 *       'expiryMonth' => '12',
 *       'expiryYear' => '2017',
 *       'email' => 'testcard@gmail.com',
 *   ];
 *
 *   // Create a credit card object
 *   $card = new CreditCard($parameters);
 * </code>
 *
 * The full list of card attributes that may be set via the parameter to
 * *new* is as follows:
 *
 * * title
 * * firstName
 * * lastName
 * * name
 * * company
 * * address1
 * * address2
 * * city
 * * postcode
 * * state
 * * country
 * * phone
 * * phoneExtension
 * * fax
 * * number
 * * expiryMonth
 * * expiryYear
 * * startMonth
 * * startYear
 * * cvv
 * * tracks
 * * issueNumber
 * * billingTitle
 * * billingName
 * * billingFirstName
 * * billingLastName
 * * billingCompany
 * * billingAddress1
 * * billingAddress2
 * * billingCity
 * * billingPostcode
 * * billingState
 * * billingCountry
 * * billingPhone
 * * billingFax
 * * shippingTitle
 * * shippingName
 * * shippingFirstName
 * * shippingLastName
 * * shippingCompany
 * * shippingAddress1
 * * shippingAddress2
 * * shippingCity
 * * shippingPostcode
 * * shippingState
 * * shippingCountry
 * * shippingPhone
 * * shippingFax
 * * email
 * * birthday
 * * gender
 *
 * If any unknown parameters are passed in, they will be ignored.  No error is thrown.
 */
class CreditCard
{
    use ParametersTrait;

    const BRAND_VISA = 'visa';
    const BRAND_VISAELECTRON = 'visaelectron';
    const BRAND_MASTERCARD = 'mastercard';
    const BRAND_DISCOVER = 'discover';
    const BRAND_AMEX = 'amex';
    const BRAND_DINERS_CLUB = 'diners_club';
    const BRAND_JCB = 'jcb';
    const BRAND_SWITCH = 'switch';
    const BRAND_SOLO = 'solo';
    //const BRAND_DANKORT = 'dankort';
    //const BRAND_FORBRUGSFORENINGEN = 'forbrugsforeningen';
    const BRAND_MAESTRO = 'maestro';
    const BRAND_LASER = 'laser';
    const BRAND_HIPERCARD = 'hipercard';
    const BRAND_ENROUTE = 'enroute';
    const BRAND_UNIONPAY = 'union';
    const BRAND_AURA = 'aura';
    const BRAND_BANESCARD = 'banescard';
    const BRAND_CABAL = 'cabal';
    const BRAND_ELO = 'elo';
    const BRAND_GOODCARD = 'goodcard';

    /**
     * All known/supported card brands, and a regular expression to match them.
     *
     * The order of the card brands is important, as some of the regular expressions overlap.
     *
     * Note: The fact that a particular card brand has been added to this array does not imply
     * that a selected gateway will support the card.
     *
     * @link https://github.com/Shopify/active_merchant/blob/master/lib/active_merchant/billing/credit_card_methods.rb
     * @var array
     */
    const REGEX_MASTERCARD = '/^(603136|603689|608619|606200|603326|605919|608783|607998|603690|604891|603600|603134|608718|603680|608710|604998)|(5[1-5][0-9]{14}|2221[0-9]{12}|222[2-9][0-9]{12}|22[3-9][0-9]{13}|2[3-6][0-9]{14}|27[01][0-9]{13}|2720[0-9]{12})$/';
    protected $supported_cards = array(
        self::BRAND_GOODCARD => '/^(606387|605680|605674|603574)[0-9]{10,12}/',
        self::BRAND_ELO => '/^(401178|401179|431274|438935|451416|457393|457631|457632|504175|627780|636297|636368|(506699|5067[0-6]\d|50677[0-8])|(50900\d|5090[1-9]\d|509[1-9]\d{2})|65003[1-3]|(65003[5-9]|65004\d|65005[0-1])|(65040[5-9]|6504[1-3]\d)|(65048[5-9]|65049\d|6505[0-2]\d|65053[0-8])|(65054[1-9]|6505[5-8]\d|65059[0-8])|(65070\d|65071[0-8])|65072[0-7]|(65090[1-9]|65091\d|650920)|(65165[2-9]|6516[6-7]\d)|(65500\d|65501\d)|(65502[1-9]|6550[3-4]\d|65505[0-8]))[0-9]{10,12}/',
        self::BRAND_CABAL => '/^(604324|604330|604337|604203|604338)[0-9]{10,12}/',
        self::BRAND_BANESCARD => '/^(603182)[0-9]{10,12}/',
        self::BRAND_AURA => '/^50[0-9]{14,17}$/',
        self::BRAND_UNIONPAY => '/^(62[0-9]{14,17})$/',
        self::BRAND_LASER => '/^(6304|6706|6709|6771)[0-9]{12,15}$/',
        self::BRAND_VISAELECTRON => '/^(4026|417500|4405|4508|4844|4913|4917)\d+$/',
        self::BRAND_MAESTRO => '/^(5018|5020|5038|5893|6304|6759|6761|6762|6763)[0-9]{8,15}$/',
        self::BRAND_SWITCH => '/^(4903|4905|4911|4936|6333|6759)[0-9]{12}|(4903|4905|4911|4936|6333|6759)[0-9]{14}|(4903|4905|4911|4936|6333|6759)[0-9]{15}|564182[0-9]{10}|564182[0-9]{12}|564182[0-9]{13}|633110[0-9]{10}|633110[0-9]{12}|633110[0-9]{13}$/',
        self::BRAND_SOLO => '/^(6334|6767)[0-9]{12}|(6334|6767)[0-9]{14}|(6334|6767)[0-9]{15}$/',
        self::BRAND_ENROUTE => '/^(2014|2149)[0-9]{11}/',
        self::BRAND_JCB => '/^(?:2131|1800|35\d{3})\d{11}$/',
        self::BRAND_DISCOVER => '/^6(?:011|5[0-9]{2}|4[4-9][0-9]{1}|(22(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[01][0-9]|92[0-5]$)[0-9]{10}$))[0-9]{12}$/',
        self::BRAND_HIPERCARD => '/^(38[0-9]{17}|60[0-9]{14})$/',
        self::BRAND_AMEX => '/^3[47][0-9]{13}$/',
        self::BRAND_DINERS_CLUB => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
        self::BRAND_MASTERCARD => self::REGEX_MASTERCARD,
        self::BRAND_VISA => '/^4[0-9]{12}(?:[0-9]{3})?$/', //'/^4\d{12}(\d{3})?$/'

        //self::BRAND_DANKORT => '/^5019\d{12}$/',
        //self::BRAND_FORBRUGSFORENINGEN => '/^600722\d{10}$/',
    );

    /**
     * Create a new CreditCard object using the specified parameters
     *
     * @param array $parameters An array of parameters to set on the new object
     */
    public function __construct($parameters = null)
    {
        $this->initialize($parameters);
    }

    /**
     * All known/supported card brands, and a regular expression to match them.
     *
     * Note: The fact that this class knows about a particular card brand does not imply
     * that your gateway supports it.
     *
     * @return array
     */
    public function getSupportedBrands()
    {
        return $this->supported_cards;
    }

    /**
     * Set a custom supported card brand with a regular expression to match it.
     *
     * Note: The fact that a particular card is known does not imply that your
     * gateway supports it.
     *
     * Set $add_to_front to true if the key should be added to the front of the array
     *
     * @param  string  $name The name of the new supported brand.
     * @param  string  $expression The regular expression to check if a card is supported.
     * @return boolean success
     */
    public function addSupportedBrand($name, $expression)
    {
        $known_brands = array_keys($this->supported_cards);

        if (in_array($name, $known_brands)) {
            return false;
        }

        $this->supported_cards[$name] = $expression;
        return true;
    }

    /**
     * Initialize the object with parameters.
     *
     * If any unknown parameters passed, they will be ignored.
     *
     * @param array $parameters An associative array of parameters
     * @return $this
     */
    public function initialize(array $parameters = null)
    {
        $this->parameters = new ParameterBag;

        Helper::initialize($this, $parameters);

        return $this;
    }

    /**
     * Set the credit card year.
     *
     * The input value is normalised to a 4 digit number.
     *
     * @param string $key Parameter key, e.g. 'expiryYear'
     * @param mixed $value Parameter value
     * @return $this
     */
    protected function setYearParameter($key, $value)
    {
        // normalize year to four digits
        if (null === $value || '' === $value) {
            $value = null;
        } else {
            $value = (int) gmdate('Y', gmmktime(0, 0, 0, 1, 1, (int) $value));
        }

        return $this->setParameter($key, $value);
    }

    /**
     * Validate this credit card. If the card is invalid, InvalidCreditCardException is thrown.
     *
     * This method is called internally by gateways to avoid wasting time with an API call
     * when the credit card is clearly invalid.
     *
     * Generally if you want to validate the credit card yourself with custom error
     * messages, you should use your framework's validation library, not this method.
     *
     * @return void
     * @throws Exception\InvalidRequestException
     * @throws InvalidCreditCardException
     */
    public function validate()
    {
        $requiredParameters = array(
            'number' => 'credit card number',
            'expiryMonth' => 'expiration month',
            'expiryYear' => 'expiration year'
        );

        foreach ($requiredParameters as $key => $val) {
            if (!$this->getParameter($key)) {
                throw new InvalidCreditCardException("The $val is required");
            }
        }

        if ($this->getExpiryDate('Ym') < gmdate('Ym')) {
            throw new InvalidCreditCardException('Card has expired');
        }

        if (!Helper::validateLuhn($this->getNumber())) {
            throw new InvalidCreditCardException('Card number is invalid');
        }

        if (!is_null($this->getNumber()) && !preg_match('/^\d{12,19}$/i', $this->getNumber())) {
            throw new InvalidCreditCardException('Card number should have 12 to 19 digits');
        }
    }
    /**
     * Get Card Title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getBillingTitle();
    }

    /**
     * Set Card Title.
     *
     * @param string $value Parameter value
     * @return $this
     */
    public function setTitle($value)
    {
        $this->setBillingTitle($value);
        $this->setShippingTitle($value);

        return $this;
    }

    /**
     * Get Card First Name.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->getBillingFirstName();
    }

    /**
     * Set Card First Name (Billing and Shipping).
     *
     * @param string $value Parameter value
     * @return $this
     */
    public function setFirstName($value)
    {
        $this->setBillingFirstName($value);
        $this->setShippingFirstName($value);

        return $this;
    }

    /**
     * Get Card Last Name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->getBillingLastName();
    }

    /**
     * Set Card Last Name (Billing and Shipping).
     *
     * @param string $value Parameter value
     * @return $this
     */
    public function setLastName($value)
    {
        $this->setBillingLastName($value);
        $this->setShippingLastName($value);

        return $this;
    }

    /**
     * Get Card Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getBillingName();
    }

    /**
     * Set Card Name (Billing and Shipping).
     *
     * @param string $value Parameter value
     * @return $this
     */
    public function setName($value)
    {
        $this->setBillingName($value);
        $this->setShippingName($value);

        return $this;
    }

    /**
     * Get Card Number.
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->getParameter('number');
    }

    /**
     * Set Card Number
     *
     * Non-numeric characters are stripped out of the card number, so
     * it's safe to pass in strings such as "4444-3333 2222 1111" etc.
     *
     * @param string $value Parameter value
     * @return $this
     */
    public function setNumber($value)
    {
        // strip non-numeric characters
        return $this->setParameter('number', preg_replace('/\D/', '', $value));
    }

    /**
     * Get the last 4 digits of the card number.
     *
     * @return string
     */
    public function getNumberLastFour()
    {
        return substr($this->getNumber(), -4, 4) ?: null;
    }

    /**
     * Returns a masked credit card number with only the last 4 chars visible
     *
     * @param string $mask Character to use in place of numbers
     * @return string
     */
    public function getNumberMasked($mask = 'X')
    {
        $maskLength = strlen($this->getNumber()) - 4;

        return str_repeat($mask, $maskLength) . $this->getNumberLastFour();
    }

    /**
     * Credit Card Brand
     *
     * Iterates through known/supported card brands to determine the brand of this card
     *
     * @return string
     */
    public function getBrand()
    {
        foreach ($this->getSupportedBrands() as $brand => $val) {
            if (preg_match($val, $this->getNumber())) {
                return $brand;
            }
        }
    }

    /**
     * Get the card expiry month.
     *
     * @return int
     */
    public function getExpiryMonth()
    {
        return $this->getParameter('expiryMonth');
    }

    /**
     * Sets the card expiry month.
     *
     * @param string $value
     * @return $this
     */
    public function setExpiryMonth($value)
    {
        return $this->setParameter('expiryMonth', (int) $value);
    }

    /**
     * Get the card expiry year.
     *
     * @return int
     */
    public function getExpiryYear()
    {
        return $this->getParameter('expiryYear');
    }

    /**
     * Sets the card expiry year.
     *
     * @param string $value
     * @return $this
     */
    public function setExpiryYear($value)
    {
        return $this->setYearParameter('expiryYear', $value);
    }

    /**
     * Get the card expiry date, using the specified date format string.
     *
     * @param string $format
     *
     * @return string
     */
    public function getExpiryDate($format)
    {
        return gmdate($format, gmmktime(0, 0, 0, $this->getExpiryMonth(), 1, $this->getExpiryYear()));
    }

    /**
     * Get the card start month.
     *
     * @return string
     */
    public function getStartMonth()
    {
        return $this->getParameter('startMonth');
    }

    /**
     * Sets the card start month.
     *
     * @param string $value
     * @return $this
     */
    public function setStartMonth($value)
    {
        return $this->setParameter('startMonth', (int) $value);
    }

    /**
     * Get the card start year.
     *
     * @return int
     */
    public function getStartYear()
    {
        return $this->getParameter('startYear');
    }

    /**
     * Sets the card start year.
     *
     * @param string $value
     * @return $this
     */
    public function setStartYear($value)
    {
        return $this->setYearParameter('startYear', $value);
    }

    /**
     * Get the card start date, using the specified date format string
     *
     * @param string $format
     *
     * @return string
     */
    public function getStartDate($format)
    {
        return gmdate($format, gmmktime(0, 0, 0, $this->getStartMonth(), 1, $this->getStartYear()));
    }

    /**
     * Get the card CVV.
     *
     * @return string
     */
    public function getCvv()
    {
        return $this->getParameter('cvv');
    }

    /**
     * Sets the card CVV.
     *
     * @param string $value
     * @return $this
     */
    public function setCvv($value)
    {
        return $this->setParameter('cvv', $value);
    }

    /**
     * Get raw data for all tracks on the credit card magnetic strip.
     *
     * @return string
     */
    public function getTracks()
    {
        return $this->getParameter('tracks');
    }

    /**
     * Get raw data for track 1 on the credit card magnetic strip.
     *
     * @return string|null
     */
    public function getTrack1()
    {
        return $this->getTrackByPattern('/\%B\d{1,19}\^.{2,26}\^\d{4}\d*\?/');
    }

    /**
     * Get raw data for track 2 on the credit card magnetic strip.
     *
     * @return string|null
     */
    public function getTrack2()
    {
        return $this->getTrackByPattern('/;\d{1,19}=\d{4}\d*\?/');
    }

    /**
     * Get raw data for a track  on the credit card magnetic strip based on the pattern for track 1 or 2.
     *
     * @param $pattern
     * @return string|null
     */
    protected function getTrackByPattern($pattern)
    {
        if ($tracks = $this->getTracks()) {
            if (preg_match($pattern, $tracks, $matches) === 1) {
                return $matches[0];
            }
        }
    }

    /**
     * Sets raw data from all tracks on the credit card magnetic strip. Used by gateways that support card-present
     * transactions.
     *
     * @param $value
     * @return $this
     */
    public function setTracks($value)
    {
        return $this->setParameter('tracks', $value);
    }

    /**
     * Get the card issue number.
     *
     * @return string
     */
    public function getIssueNumber()
    {
        return $this->getParameter('issueNumber');
    }

    /**
     * Sets the card issue number.
     *
     * @param string $value
     * @return $this
     */
    public function setIssueNumber($value)
    {
        return $this->setParameter('issueNumber', $value);
    }

    /**
     * Get the card billing title.
     *
     * @return string
     */
    public function getBillingTitle()
    {
        return $this->getParameter('billingTitle');
    }

    /**
     * Sets the card billing title.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingTitle($value)
    {
        return $this->setParameter('billingTitle', $value);
    }

    /**
     * Get the card billing name.
     *
     * @return string
     */
    public function getBillingName()
    {
        return trim($this->getBillingFirstName() . ' ' . $this->getBillingLastName());
    }

    /**
     * Split the full name in the first and last name.
     *
     * @param $fullName
     * @return array with first and lastname
     */
    protected function listFirstLastName($fullName)
    {
        $names = explode(' ', $fullName, 2);

        return [$names[0], isset($names[1]) ? $names[1] : null];
    }

    /**
     * Sets the card billing name.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingName($value)
    {
        $names = $this->listFirstLastName($value);

        $this->setBillingFirstName($names[0]);
        $this->setBillingLastName($names[1]);

        return $this;
    }

    /**
     * Get the first part of the card billing name.
     *
     * @return string
     */
    public function getBillingFirstName()
    {
        return $this->getParameter('billingFirstName');
    }

    /**
     * Sets the first part of the card billing name.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingFirstName($value)
    {
        return $this->setParameter('billingFirstName', $value);
    }

    /**
     * Get the last part of the card billing name.
     *
     * @return string
     */
    public function getBillingLastName()
    {
        return $this->getParameter('billingLastName');
    }

    /**
     * Sets the last part of the card billing name.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingLastName($value)
    {
        return $this->setParameter('billingLastName', $value);
    }

    /**
     * Get the billing company name.
     *
     * @return string
     */
    public function getBillingCompany()
    {
        return $this->getParameter('billingCompany');
    }

    /**
     * Sets the billing company name.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingCompany($value)
    {
        return $this->setParameter('billingCompany', $value);
    }

    /**
     * Get the billing address, line 1.
     *
     * @return string
     */
    public function getBillingAddress1()
    {
        return $this->getParameter('billingAddress1');
    }

    /**
     * Sets the billing address, line 1.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingAddress1($value)
    {
        return $this->setParameter('billingAddress1', $value);
    }

    /**
     * Get the billing address, line 2.
     *
     * @return string
     */
    public function getBillingAddress2()
    {
        return $this->getParameter('billingAddress2');
    }

    /**
     * Sets the billing address, line 2.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingAddress2($value)
    {
        return $this->setParameter('billingAddress2', $value);
    }

    /**
     * Get the billing city.
     *
     * @return string
     */
    public function getBillingCity()
    {
        return $this->getParameter('billingCity');
    }

    /**
     * Sets billing city.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingCity($value)
    {
        return $this->setParameter('billingCity', $value);
    }

    /**
     * Get the billing postcode.
     *
     * @return string
     */
    public function getBillingPostcode()
    {
        return $this->getParameter('billingPostcode');
    }

    /**
     * Sets the billing postcode.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingPostcode($value)
    {
        return $this->setParameter('billingPostcode', $value);
    }

    /**
     * Get the billing state.
     *
     * @return string
     */
    public function getBillingState()
    {
        return $this->getParameter('billingState');
    }

    /**
     * Sets the billing state.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingState($value)
    {
        return $this->setParameter('billingState', $value);
    }

    /**
     * Get the billing country name.
     *
     * @return string
     */
    public function getBillingCountry()
    {
        return $this->getParameter('billingCountry');
    }

    /**
     * Sets the billing country name.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingCountry($value)
    {
        return $this->setParameter('billingCountry', $value);
    }

    /**
     * Get the billing phone number.
     *
     * @return string
     */
    public function getBillingPhone()
    {
        return $this->getParameter('billingPhone');
    }

    /**
     * Sets the billing phone number.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingPhone($value)
    {
        return $this->setParameter('billingPhone', $value);
    }

    /**
     * Get the billing phone number extension.
     *
     * @return string
     */
    public function getBillingPhoneExtension()
    {
        return $this->getParameter('billingPhoneExtension');
    }

    /**
     * Sets the billing phone number extension.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingPhoneExtension($value)
    {
        return $this->setParameter('billingPhoneExtension', $value);
    }

    /**
     * Get the billing fax number.
     *
     * @return string
     */
    public function getBillingFax()
    {
        return $this->getParameter('billingFax');
    }

    /**
     * Sets the billing fax number.
     *
     * @param string $value
     * @return $this
     */
    public function setBillingFax($value)
    {
        return $this->setParameter('billingFax', $value);
    }

    /**
     * Get the title of the card shipping name.
     *
     * @return string
     */
    public function getShippingTitle()
    {
        return $this->getParameter('shippingTitle');
    }

    /**
     * Sets the title of the card shipping name.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingTitle($value)
    {
        return $this->setParameter('shippingTitle', $value);
    }

    /**
     * Get the card shipping name.
     *
     * @return string
     */
    public function getShippingName()
    {
        return trim($this->getShippingFirstName() . ' ' . $this->getShippingLastName());
    }

    /**
     * Sets the card shipping name.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingName($value)
    {
        $names = $this->listFirstLastName($value);

        $this->setShippingFirstName($names[0]);
        $this->setShippingLastName($names[1]);

        return $this;
    }

    /**
     * Get the first part of the card shipping name.
     *
     * @return string
     */
    public function getShippingFirstName()
    {
        return $this->getParameter('shippingFirstName');
    }

    /**
     * Sets the first part of the card shipping name.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingFirstName($value)
    {
        return $this->setParameter('shippingFirstName', $value);
    }

    /**
     * Get the last part of the card shipping name.
     *
     * @return string
     */
    public function getShippingLastName()
    {
        return $this->getParameter('shippingLastName');
    }

    /**
     * Sets the last part of the card shipping name.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingLastName($value)
    {
        return $this->setParameter('shippingLastName', $value);
    }

    /**
     * Get the shipping company name.
     *
     * @return string
     */
    public function getShippingCompany()
    {
        return $this->getParameter('shippingCompany');
    }

    /**
     * Sets the shipping company name.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingCompany($value)
    {
        return $this->setParameter('shippingCompany', $value);
    }

    /**
     * Get the shipping address, line 1.
     *
     * @return string
     */
    public function getShippingAddress1()
    {
        return $this->getParameter('shippingAddress1');
    }

    /**
     * Sets the shipping address, line 1.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingAddress1($value)
    {
        return $this->setParameter('shippingAddress1', $value);
    }

    /**
     * Get the shipping address, line 2.
     *
     * @return string
     */
    public function getShippingAddress2()
    {
        return $this->getParameter('shippingAddress2');
    }

    /**
     * Sets the shipping address, line 2.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingAddress2($value)
    {
        return $this->setParameter('shippingAddress2', $value);
    }

    /**
     * Get the shipping city.
     *
     * @return string
     */
    public function getShippingCity()
    {
        return $this->getParameter('shippingCity');
    }

    /**
     * Sets the shipping city.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingCity($value)
    {
        return $this->setParameter('shippingCity', $value);
    }

    /**
     * Get the shipping postcode.
     *
     * @return string
     */
    public function getShippingPostcode()
    {
        return $this->getParameter('shippingPostcode');
    }

    /**
     * Sets the shipping postcode.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingPostcode($value)
    {
        return $this->setParameter('shippingPostcode', $value);
    }

    /**
     * Get the shipping state.
     *
     * @return string
     */
    public function getShippingState()
    {
        return $this->getParameter('shippingState');
    }

    /**
     * Sets the shipping state.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingState($value)
    {
        return $this->setParameter('shippingState', $value);
    }

    /**
     * Get the shipping country.
     *
     * @return string
     */
    public function getShippingCountry()
    {
        return $this->getParameter('shippingCountry');
    }

    /**
     * Sets the shipping country.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingCountry($value)
    {
        return $this->setParameter('shippingCountry', $value);
    }

    /**
     * Get the shipping phone number.
     *
     * @return string
     */
    public function getShippingPhone()
    {
        return $this->getParameter('shippingPhone');
    }

    /**
     * Sets the shipping phone number.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingPhone($value)
    {
        return $this->setParameter('shippingPhone', $value);
    }

    /**
     * Get the shipping phone number extension.
     *
     * @return string
     */
    public function getShippingPhoneExtension()
    {
        return $this->getParameter('shippingPhoneExtension');
    }

    /**
     * Sets the shipping phone number extension.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingPhoneExtension($value)
    {
        return $this->setParameter('shippingPhoneExtension', $value);
    }

    /**
     * Get the shipping fax number.
     *
     * @return string
     */
    public function getShippingFax()
    {
        return $this->getParameter('shippingFax');
    }

    /**
     * Sets the shipping fax number.
     *
     * @param string $value
     * @return $this
     */
    public function setShippingFax($value)
    {
        return $this->setParameter('shippingFax', $value);
    }

    /**
     * Get the billing address, line 1.
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->getParameter('billingAddress1');
    }

    /**
     * Sets the billing and shipping address, line 1.
     *
     * @param string $value
     * @return $this
     */
    public function setAddress1($value)
    {
        $this->setParameter('billingAddress1', $value);
        $this->setParameter('shippingAddress1', $value);

        return $this;
    }

    /**
     * Get the billing address, line 2.
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->getParameter('billingAddress2');
    }

    /**
     * Sets the billing and shipping address, line 2.
     *
     * @param string $value
     * @return $this
     */
    public function setAddress2($value)
    {
        $this->setParameter('billingAddress2', $value);
        $this->setParameter('shippingAddress2', $value);

        return $this;
    }

    /**
     * Get the billing city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->getParameter('billingCity');
    }

    /**
     * Sets the billing and shipping city.
     *
     * @param string $value
     * @return $this
     */
    public function setCity($value)
    {
        $this->setParameter('billingCity', $value);
        $this->setParameter('shippingCity', $value);

        return $this;
    }

    /**
     * Get the billing postcode.
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getParameter('billingPostcode');
    }

    /**
     * Sets the billing and shipping postcode.
     *
     * @param string $value
     * @return $this
     */
    public function setPostcode($value)
    {
        $this->setParameter('billingPostcode', $value);
        $this->setParameter('shippingPostcode', $value);

        return $this;
    }

    /**
     * Get the billing state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->getParameter('billingState');
    }

    /**
     * Sets the billing and shipping state.
     *
     * @param string $value
     * @return $this
     */
    public function setState($value)
    {
        $this->setParameter('billingState', $value);
        $this->setParameter('shippingState', $value);

        return $this;
    }

    /**
     * Get the billing country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->getParameter('billingCountry');
    }

    /**
     * Sets the billing and shipping country.
     *
     * @param string $value
     * @return $this
     */
    public function setCountry($value)
    {
        $this->setParameter('billingCountry', $value);
        $this->setParameter('shippingCountry', $value);

        return $this;
    }

    /**
     * Get the billing phone number.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->getParameter('billingPhone');
    }

    /**
     * Sets the billing and shipping phone number.
     *
     * @param string $value
     * @return $this
     */
    public function setPhone($value)
    {
        $this->setParameter('billingPhone', $value);
        $this->setParameter('shippingPhone', $value);

        return $this;
    }

    /**
     * Get the billing phone number extension.
     *
     * @return string
     */
    public function getPhoneExtension()
    {
        return $this->getParameter('billingPhoneExtension');
    }

    /**
     * Sets the billing and shipping phone number extension.
     *
     * @param string $value
     * @return $this
     */
    public function setPhoneExtension($value)
    {
        $this->setParameter('billingPhoneExtension', $value);
        $this->setParameter('shippingPhoneExtension', $value);

        return $this;
    }

    /**
     * Get the billing fax number..
     *
     * @return string
     */
    public function getFax()
    {
        return $this->getParameter('billingFax');
    }

    /**
     * Sets the billing and shipping fax number.
     *
     * @param string $value
     * @return $this
     */
    public function setFax($value)
    {
        $this->setParameter('billingFax', $value);
        $this->setParameter('shippingFax', $value);

        return $this;
    }

    /**
     * Get the card billing company name.
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->getParameter('billingCompany');
    }

    /**
     * Sets the billing and shipping company name.
     *
     * @param string $value
     * @return $this
     */
    public function setCompany($value)
    {
        $this->setParameter('billingCompany', $value);
        $this->setParameter('shippingCompany', $value);

        return $this;
    }

    /**
     * Get the cardholder's email address.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getParameter('email');
    }

    /**
     * Sets the cardholder's email address.
     *
     * @param string $value
     * @return $this
     */
    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    /**
     * Get the cardholder's birthday.
     *
     * @return string
     */
    public function getBirthday($format = 'Y-m-d')
    {
        $value = $this->getParameter('birthday');

        return $value ? $value->format($format) : null;
    }

    /**
     * Sets the cardholder's birthday.
     *
     * @param string $value
     * @return $this
     */
    public function setBirthday($value)
    {
        if ($value) {
            $value = new DateTime($value, new DateTimeZone('UTC'));
        } else {
            $value = null;
        }

        return $this->setParameter('birthday', $value);
    }

    /**
     * Get the cardholder's gender.
     *
     * @return string
     */
    public function getGender()
    {
        return $this->getParameter('gender');
    }

    /**
     * Sets the cardholder's gender.
     *
     * @param string $value
     * @return $this
     */
    public function setGender($value)
    {
        return $this->setParameter('gender', $value);
    }

    public function getNumberToken()
    {
        return $this->getParameter('number_token');
    }

    public function setNumberToken($value)
    {
        // strip non-numeric characters
        return $this->setParameter('number_token', $value);
    }

    public function getShippingNumber()
    {
        return $this->getParameter('shippingNumber');
    }

    public function setShippingNumber($value)
    {
        // strip non-numeric characters
        return $this->setParameter('shippingNumber', $value);
    }

    public function getShippingDistrict()
    {
        return $this->getParameter('shippingDistrict');
    }

    public function setShippingDistrict($value)
    {
        // strip non-numeric characters
        return $this->setParameter('shippingDistrict', $value);
    }

    public function getShippingAmount()
    {
        return $this->getParameter('shippingAmount');
    }

    public function setShippingAmount($value)
    {
        // strip non-numeric characters
        return $this->setParameter('shippingAmount', $value);
    }

    public function getBillingNumber()
    {
        return $this->getParameter('billingNumber');
    }

    public function setBillingNumber($value)
    {
        // strip non-numeric characters
        return $this->setParameter('billingNumber', $value);
    }

    public function getBillingDistrict()
    {
        return $this->getParameter('billingDistrict');
    }

    public function setBillingDistrict($value)
    {
        // strip non-numeric characters
        return $this->setParameter('billingDistrict', $value);
    }

    public function getHolderDocumentNumber()
    {
        return $this->getParameter('holder_document_number');
    }

    public function setHolderDocumentNumber($value)
    {
        // strip non-numeric characters
        return $this->setParameter('holder_document_number', $value);
    }

    public function getAreaCode()
    {
        $phone = $this->getPhone();
        return substr($phone, 0, 2);
    }
}
