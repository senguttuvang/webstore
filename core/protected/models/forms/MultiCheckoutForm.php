<?php

// Extends CheckoutForm to handle different validation scenarios

class MultiCheckoutForm extends CheckoutForm
{
	const USA = 224;
	const CANADA = 39;
	const GREATBRITAIN = 223;
	const AUSTRALIA = 13;

	/**
	 * @var The string to translate when a credit card type is not enabled.
	 */
	const DISABLED_CARD_TYPE = '{card type} card type is not supported.';

	/**
	 * @var the last successfully validated scenario
	 */
	public $passedScenario;

	/*
	 * Validation rules
	 */
	public function rules()
	{
		return array(

			array(
				'contactEmail, contactEmailConfirm,
				recipientName, contactFirstName, contactLastName, contactCompany, contactPhone,
				pickupPerson, pickupFirstName, pickupLastName, pickupPersonEmail, pickupPersonPhone,
				intShippingAddress, intBillingAddress, billingSameAsShipping,
				shippingFirstName, shippingLastName,
				shippingAddress1, shippingAddress2, shippingCity, shippingState, shippingPostal, shippingCountry, shippingCountryCode,
				billingAddress1, billingAddress2, billingCity, billingState, billingPostal, billingCountry, billingCountryCode,
				shippingProvider, shippingPriority,
				paymentProvider,
				cardNumber, cardNumberLast4, cardExpiry, cardExpiryMonth, cardExpiryYear, cardType, cardCVV, cardNameOnCard,
				acceptTerms,
				objAddresses,
				shippingLabel, billingLabel, shippingResidential, billingResidential,
				promoCode, orderNotes, receiveNewsletter,
				createPassword, createPassword_repeat,
				passedScenario', 'safe'),

			array('contactEmail', 'required', 'on' => 'Login, StorePickup, Shipping, ShippingOptions, Payment, PaymentSim, PaymentStorePickup, PaymentStorePickupCC, Confirmation, ConfirmationSim, ConfirmationStorePickup, ConfirmationStorePickupCC'),

			array('shippingFirstName, shippingLastName, contactPhone',
				'required', 'on' => 'StorePickup, Shipping, ShippingOptions, Payment, PaymentSim, PaymentStorePickup, PaymentStorePickupCC, Confirmation, ConfirmationSim, ConfirmationStorePickup, ConfirmationStorePickupCC'),

			array('shippingAddress1, shippingCity, shippingCountry',
				'validateShippingBlock', 'on' => 'CalculateShipping, Shipping, ShippingOptions, Payment, PaymentSim, Confirmation, ConfirmationSim'),

			array('billingAddress1, billingCity, billingCountry',
				'validateBillingBlock','on' => 'Payment, PaymentSim, PaymentStorePickupCC, PaymentStorePickupSimCC, Confirmation, ConfirmationSim'),

			array('shippingState', 'StateValidator', 'on' => 'CalculateShipping, Shipping, ShippingOptions, Payment, PaymentSim, Confirmation, ConfirmationSim'),

			array('shippingPostal',
				'validatePostal','on' => 'CalculateShipping, Shipping, ShippingOptions, Payment, PaymentSim, Confirmation, ConfirmationSim'),

			array('shippingProvider, shippingPriority', 'required', 'on' => 'ShippingOptions, Payment, PaymentSim, Confirmation, ConfirmationSim'),

			array('billingState', 'StateValidator', 'on' => 'Payment, PaymentSim, PaymentStorePickupCC, PaymentStorePickupSimCC, Confirmation, ConfirmationSim'),

			array('billingPostal', 'validatePostal', 'on' => 'Payment, PaymentSim, PaymentStorePickupCC, PaymentStorePickupSimCC, Confirmation, ConfirmationSim'),

			array('paymentProvider','required', 'on' => 'Payment, PaymentSim, PaymentStorePickup, PaymentStorePickupCC, PaymentStorePickupSimCC, Confirmation, ConfirmationSim, ConfirmationStorePickup, ConfirmationStorePickupCC'),

			// Credit card validation.
			array(
				'cardNumber, cardCVV, cardExpiryMonth, cardExpiryYear, cardNameOnCard',
				'required',
				'on' => 'Payment, PaymentStorePickupCC, Confirmation'
			),
			array(
				'cardType',
				'validateCardType',
				'on' => 'Payment, PaymentStorePickupCC, Confirmation'
			),
			array(
				'cardNumber',
				'validateCardNumber',
				'on' => 'Payment, PaymentStorePickupCC, Confirmation'
			),
			array(
				'cardCVV',
				'validateCardCVV',
				'on' => 'Payment, PaymentStorePickupCC, Confirmation'
			),
			array(
				'cardExpiryDate',
				'validateCardExpiryDate',
				'on' => 'Payment, PaymentStorePickupCC, Confirmation'
			),

			array('contactPhone', 'length', 'min' => 7, 'max' => 32),

			// email has to be a valid email address
			array('contactEmail', 'email'),
			array('contactEmail_repeat', 'safe'),
			array('contactEmail_repeat', 'validateEmailRepeat', 'on' => 'Login'),

			array('acceptTerms','required', 'requiredValue' => 1,
				'message' => Yii::t('global', 'You must accept Terms and Conditions'),
				'on' => 'Confirmation, ConfirmationSim, ConfirmationStorePickup'),

			array('createPassword', 'length', 'max' => 255),
			array('createPassword', 'compare', 'on' => 'formSubmitCreatingAccount'),
			array('createPassword_repeat', 'safe'),

		);

	}

	/**
	 * Returns the ECC validator based on the attributes in this form.
	 *
	 * @return null|ECCValidator An instance of ECCValidator.
	 */
	protected function getCardValidator()
	{
		if (empty($this->paymentProvider))
		{
			Yii::log(
				'Unable to get card validator: paymentProvider is empty',
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
			return null;
		}

		$objPaymentModule = Modules::model()->findByPk($this->paymentProvider);

		if (Yii::app()->getComponent($objPaymentModule->module)->uses_credit_card === false)
		{
			Yii::log(
				'Unable to get card validator: paypment provider does not use credit card',
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
			return null;
		}

		if (empty($this->cardType))
		{
			Yii::log(
				'Unable to get card validator: cardType is empty.',
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
			return null;
		}

		$objCreditCard = CreditCard::model()->findByAttributes(
			array('label' => $this->cardType)
		);

		if ($objCreditCard === null)
		{
			Yii::log(
				sprintf('Unable to get card validator: no such card type found: %s', $this->cardType),
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
			return null;
		}

		Yii::import('ext.validators.ECCValidator');
		$validatorFormat = 'ECCValidator::' . $objCreditCard->validfunc;
		if (defined($validatorFormat) === false)
		{
			Yii::log(
				sprintf('Unable to get card validator: no validator such validator: ', $validatorFormat),
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
			return null;
		}

		$cc = new ECCValidator();
		$cc->format = array(constant($validatorFormat));
		return $cc;
	}

	/**
	 * Check the credit card type.
	 * @param $attribute The attribute name.
	 * @param $params Additional paremeters defined in the rules.
	 * @return void
	 */
	public function validateCardType($attribute, $params)
	{
		if (empty($this->cardType) === true)
		{
			// If the card type isn't sent, we allow it and rely on the payment
			// processor to decline if invalid.
			Yii::log(
				'Unable to validate card type - card type is empty.',
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
			return;
		}

		$arrEnabledCreditCardLabel = array_map(
			function ($creditCard) {
				return $creditCard->label;
			},
			CreditCard::model()->enabled()->findAll()
		);

		if (in_array($this->cardType, $arrEnabledCreditCardLabel) === false)
		{
			$this->addError(
				$attribute,
				Yii::t(
					'checkout',
					static::DISABLED_CARD_TYPE,
					array('{card type}' => $this->cardType)
				)
			);
		}
	}

	/**
	 * Check the credit card number.
	 * @param $attribute The attribute name.
	 * @param $params Additional paremeters defined in the rules.
	 * @return void
	 */
	public function validateCardNumber($attribute, $params)
	{
		$validator = static::getCardValidator();
		if ($validator === null)
		{
			$this->addError(
				$attribute,
				Yii::t(
					'checkout',
					'Unsupported card type.'
				)
			);
			return;
		}

		if($validator->validateNumber($this->cardNumber) === false)
		{
			$this->addError(
				$attribute,
				Yii::t(
					'checkout',
					'Invalid Card Number or Type mismatch.'
				)
			);
		} else {
			Yii::log(
				sprintf('Validated cardNumber as %s', $this->cardType),
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
		}
	}

	/**
	 * Check the credit card CVV.
	 * @param $attribute The attribute name.
	 * @param $params Additional paremeters defined in the rules.
	 * @return void
	 */
	public function validateCardCVV($attribute, $params)
	{
		$validator = static::getCardValidator();
		if ($validator === null)
		{
			Yii::log(
				'Unable to validate card CVV.',
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
			return;
		}

		if ($validator->validateCVV($this->cardCVV) === false)
		{
			$this->addError(
				$attribute,
				Yii::t(
					'checkout',
					'Invalid CVV or type mismatch.'
				)
			);
		} else {
			Yii::log(
				sprintf('Validated cardCVV as %s', $this->cardType),
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
		}
	}

	/**
	 * Check the credit card expiry date.
	 * @param $attribute The attribute name.
	 * @param $params Additional paremeters defined in the rules.
	 * @return void
	 */
	public function validateCardExpiryDate($attribute, $params)
	{
		$validator = static::getCardValidator();
		if ($validator === null)
		{
			Yii::log(
				'Unable to validate card expiry date.',
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
			return;
		}

		if ($validator->validateDate($this->cardExpiryMonth, $this->cardExpiryYear) === false)
		{
			$this->addError(
				$attribute,
				Yii::t(
					'checkout',
					'Invalid expiry date.'
				)
			);
		} else {
			Yii::log(
				sprintf('Validated cardExpiryDate as %s', $this->cardType),
				'info',
				'application.'.__CLASS__.'.'.__FUNCTION__
			);
		}
	}

	/**
	 * Return shipping address as one line string
	 *
	 * @return string
	 */
	public function getStrShippingAddress()
	{
		// ensure address is up to date
		if (empty($this->intShippingAddress) === false)
		{
			$this->fillAddressFields($this->intShippingAddress, 'shipping');
		}

		$str = '';

		$str .= $this->shippingAddress1 . ', ';
		$str .= $this->shippingAddress2 ? $this->shippingAddress2.' ': '';
		$str .= $this->shippingCity . ', ';
		$str .= $this->shippingState ? $this->shippingState . ', ' : '';
		$str .= Country::CodeById($this->shippingCountry) . ' ';
		$str .= $this->shippingPostal;

		return $str;
	}

	/**
	 * Return billing address as one line string
	 *
	 * @return string
	 */
	public function getStrBillingAddress()
	{
		if ($this->billingSameAsShipping == 1)
		{
			return $this->getStrShippingAddress();
		}

		$str = '';

		$str .= $this->billingAddress1 . ', ';
		$str .= $this->billingAddress2 ? $this->billingAddress2.' ': '';
		$str .= $this->billingCity . ', ';
		$str .= $this->billingState ? $this->billingState . ', ' : '';
		$str .= Country::CodeById($this->billingCountry) . ' ';

		$str .= $this->billingPostal;

		return $str;
	}

	/**
	 * Return shipping address as formatted html
	 *
	 * @return string
	 */

	public function getHtmlShippingAddress()
	{
		$str = '';
		$str .= $this->shippingAddress1 . '<br>';
		$str .= $this->shippingAddress2 ? $this->shippingAddress2 . '<br>' : ' ';
		$str .= $this->shippingCity. ', ';
		$str .= $this->shippingState ? $this->shippingState . ', ' : '';
		$str .= $this->shippingPostal ? $this->shippingPostal . '<br>' : '';

		// Only show the country if different from the store default.
		if ($this->shippingCountry != Yii::app()->params['DEFAULT_COUNTRY'])
		{
			$str .= Country::CountryById($this->shippingCountry);
		}

		return $str;
	}

	/**
	 * Return shipping address as formatted html
	 *
	 * @return string
	 */

	public function getHtmlBillingAddress()
	{
		if ($this->billingSameAsShipping == 1)
		{
			return $this->getHtmlShippingAddress();
		}

		$str = '';
		$str .= $this->billingAddress1 . '<br>';
		$str .= $this->billingAddress2 ? $this->billingAddress2 . '<br>' : '';
		$str .= $this->billingCity. ', ';
		$str .= $this->billingState ? $this->billingState . ', ' : '';
		$str .= $this->billingPostal ? $this->billingPostal . '<br>' : '';

		// Only show the country if different from the store default.
		if ($this->billingCountry != Yii::app()->params['DEFAULT_COUNTRY'])
		{
			$str .= Country::CountryById($this->billingCountry);
		}

		return $str;
	}


	/**
	 * Populate the address fields as required to pass validation
	 *
	 * @param $intAddressId
	 * @param string $str
	 * @return void
	 */

	public function fillAddressFields($intAddressId, $str = 'billing')
	{
		$objAddress = CustomerAddress::model()->findByPk($intAddressId);

		if (!$objAddress instanceof CustomerAddress)
		{
			Yii::log('No address found with id: '.$intAddressId, 'error', 'application.'.__CLASS__.'.'.__FUNCTION__);
			return;
		}

		switch ($str)
		{
			case 'billing':
				$state = State::CodeById($objAddress->state_id);
				if ($state === '')
				{
					$state = $this->billingState;
				}

				$this->billingAddress1 = $objAddress->address1;
				$this->billingAddress2 = $objAddress->address2;
				$this->billingCity = $objAddress->city;
				$this->billingState = $state;
				$this->billingPostal = $objAddress->postal;
				$this->billingCountry = $objAddress->country_id;
				break;

			case 'shipping':
				$this->shippingFirstName = $objAddress->first_name;
				$this->shippingLastName = $objAddress->last_name;
				$this->shippingCompany = $objAddress->company;
				$this->shippingAddress1 = $objAddress->address1;
				$this->shippingAddress2 = $objAddress->address2;
				$this->shippingCity = $objAddress->city;
				$this->shippingState = State::CodeById($objAddress->state_id);
				$this->shippingPostal = $objAddress->postal;
				$this->shippingCountry = $objAddress->country_id;
				$this->contactPhone = $objAddress->phone;
				$this->shippingResidential = $objAddress->residential;
				break;
		}
	}


	/**
	 * Clear specified address fields from form
	 *
	 * @param string $str
	 * @return void
	 */
	public function clearAddressFields($str = 'shipping')
	{
		switch ($str)
		{
			case 'shipping':
				$this->intShippingAddress = null;
				$this->billingSameAsShipping = null;
				$this->shippingFirstName = null;
				$this->shippingLastName = null;
				$this->shippingAddress1 = null;
				$this->shippingAddress2 = null;
				$this->shippingCity = null;
				$this->shippingState = null;
				$this->shippingCountry = null;
				$this->shippingPostal = null;
				$this->contactPhone = null;
				break;

			case 'billing':
				$this->intBillingAddress = null;
				$this->billingAddress1 = null;
				$this->billingAddress2 = null;
				$this->billingCity = null;
				$this->billingState = null;
				$this->billingCountry = null;
				$this->billingPostal = null;
				break;
		}
	}

	/**
	 * Load an instance of MultiCheckoutForm from the user's session. If
	 * there's not one in the session, create a new one.
	 * TODO: Should probably be renamed to getFromSessionOrNew().
	 * @return CheckoutForm A CheckoutForm object.
	 */
	public static function loadFromSessionOrNew()
	{
		$checkoutForm = self::loadFromSession();
		if ($checkoutForm === null)
		{
			return new MultiCheckoutForm();
		}

		return $checkoutForm;
	}

	/**
	 * Load an instance of MultiCheckoutForm from the user's session.
	 * TODO: Should probably be renamed to getFromSession().
	 * @return CheckoutForm A CheckoutForm object.
	 */
	public static function loadFromSession()
	{
		$checkoutFormAttributes = Yii::app()->session->get(self::$sessionKey);
		if ($checkoutFormAttributes === null)
		{
			return null;
		}

		$checkoutForm = new MultiCheckoutForm();
		$checkoutForm->attributes = $checkoutFormAttributes;
		return $checkoutForm;
	}

	/**
	 * Save the checkout form to the user's session.
	 * @param CheckoutForm $checkoutForm A partially completed checkoutForm.
	 * @return void
	 */
	public static function saveToSession($checkoutForm)
	{
		Yii::app()->session[self::$sessionKey] = $checkoutForm->attributes;
	}

	/**
	 * Returns the shipping country code from the session.
	 * @return string|null The shipping provider ID that the cart will ship using.
	 */
	public static function getShippingProviderFromSession()
	{
		$checkoutForm = self::loadFromSession();
		if ($checkoutForm === null)
		{
			return null;
		}

		return $checkoutForm->shippingProvider;
	}

	/**
	 * Returns the shipping country code from the session.
	 * @return string|null The shipping priority label that the cart will ship using.
	 */
	public static function getShippingPriorityFromSession()
	{
		$checkoutForm = self::loadFromSession();
		if ($checkoutForm === null)
		{
			return null;
		}

		return $checkoutForm->shippingPriority;
	}

	/**
	 * Returns whether the user has made a choice about their shipping provider.
	 * @return boolean True if the user has chosen a shipping provider, false
	 * otherwise.
	 */
	public static function hasSelectedShippingProvider()
	{
		$checkoutForm = self::loadFromSession();
		if ($checkoutForm === null)
		{
			return false;
		}

		return (
			$checkoutForm->shippingProvider !== null &&
			$checkoutForm->shippingPriority !== null
		);
	}

	/**
	 * Returns the shipping country code from the session.
	 * @return string|null The country code that the cart will ship to.
	 */
	public static function getShippingCountryCodeFromSession()
	{
		$checkoutForm = self::loadFromSession();
		if ($checkoutForm === null)
		{
			return null;
		}

		return $checkoutForm->shippingCountry;
	}

	/**
	 * Returns the shipping postal code from the session.
	 * @return string|null The postal code that the cart will ship to.
	 */
	public static function getShippingPostalFromSession()
	{
		$checkoutForm = self::loadFromSession();
		if ($checkoutForm === null)
		{
			return null;
		}

		return $checkoutForm->shippingPostal;
	}

	/**
	 * Returns the shipping city from the session.
	 * @return string|null The city that the cart will ship to.
	 */
	public static function getShippingCityFromSession()
	{
		$checkoutForm = self::loadFromSession();
		if ($checkoutForm === null)
		{
			return null;
		}

		return $checkoutForm->shippingCity;
	}

	/**
	 * Returns the shipping state from the session.
	 * @return string|null The state code that the cart will ship to.
	 */
	public static function getShippingStateFromSession()
	{
		$checkoutForm = self::loadFromSession();
		if ($checkoutForm === null)
		{
			return null;
		}

		return $checkoutForm->shippingState;
	}

	/**
	 * Save the provided cart scenario as the selected one in the session.
	 * @param array $cartScenario A cart scenario.
	 * @see Shipping::getCartScenarios.
	 * @return void
	 */
	public static function saveSelectedCartScenario($cartScenario)
	{
		$checkoutForm = self::loadFromSessionOrNew();
		$checkoutForm->shippingProvider = $cartScenario['providerId'];
		$checkoutForm->shippingPriority = $cartScenario['priorityLabel'];
		self::saveToSession($checkoutForm);
	}

	/**
	 * See if the passed id is a match for
	 * an address in objAddresses
	 *
	 * @param $intAddressId
	 * @return bool
	 */
	public function addressBelongsToUser($intAddressId)
	{
		if ($intAddressId === null)
		{
			return false;
		}

		foreach ($this->objAddresses as $address)
		{
			if ($intAddressId === $address->id)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns if the In Store Pickup is active.
	 *
	 * @return bool true if in-store pickup option is active
	 */
	public function isInStorePickupActive()
	{
		return Modules::isActive("storepickup", "shipping");
	}

	/**
	* This method checks to see if the customer's addresses should be displayed in the shipping option.
	*
	* @return bool true if the store pickup is not the only option available | false if store pickup should only
	* be displayed and not the addresses because it's the only option available in the admin by the store owner
	*/
	public function shouldDisplayShippingAddresses()
	{
		if (count(Modules::model()->shipping()->notStorePickup()->findAll()) > 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * This method checks if the customer has the In Store pickup option selected
	 *
	 * @return bool true if store pickup is selected | false otherwise
	 */
	public function isStorePickupSelected()
	{
		$shipping = Modules::model()->findByPk($this->shippingProvider);

		if (is_null($shipping))
		{
			return false;
		}

		if ($shipping->module === 'storepickup')
		{
			return true;
		}

		return false;
	}
}
