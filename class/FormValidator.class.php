<?php

class FieldValidationError extends Exception {}

class FormValidator {
	/* A tool to validate the parameters of a request (GET/POST) against a set
	 * of rules.
	 *
	 **/
	private $errors;
	private $sanitized;
	public static $validator;
  public static $field_validators;
  private $format;

	public function __construct($format) {
		$this->errors = array();
		$this->sanitized = array();
    $this->format = $format;
	}

	public function validate($request) {
		/** Validate the given request
		 *  value dict against the validator rules.
		 *
		 * @param $request a dict like $_REQUEST, $_GET or $_POST
		 * @returns true if valid, false else.
		 */
		$this->errors = array();
		$sanitized = array();
		foreach($this->format as $fieldname => $validators) {
			$err = false;
			$sanitized_f = false;
			foreach($validators as $validator) {
				if ($validator == 'required') {
					if (! isset($request[$fieldname])) {
						$err = 'n\'est pas renseigné';
						break;
					} else {
						$sanitized_f = $request[$fieldname];
					}
				} else {
					if (isset($request[$fieldname]) and $request[$fieldname]) {
						$val = $request[$fieldname];
						try {
							$sanitized_f = $this->validate_field($validator, $val);
						} catch (FieldValidationError $e) {
							$err = $e->getMessage();
							break;
						}
					}
				}
			}
			if ($err) {
				$this->errors[$fieldname] = $err;
			} else {
				$sanitized[$fieldname] = $sanitized_f;
			}
		}
		$this->sanitized = $sanitized;

		return ($err == false);
	}

	public function validate_field($validator, $content) {
		/** Returns sanitized value if ok, throws a FieldValidationError otherwise
		 */
		if (isset(self::$field_validators[$validator])) {
			$f = self::$field_validators[$validator];
			return $f($content);
		} else {
			throw new FieldValidationError("'$validator' validator does not exist");
		}
	}

	public function errors() {
		/** An associative array form-key => error
		 */
		return $this->errors;
	}

	public function sane_values() {
		return $this->sanitized;
	}

	public static function register($name, $function) {
		self::$field_validators[$name] = $function;
	}
}
FormValidator::$field_validators = array();


FormValidator::register(
  'numeric',
  function ($v) {
	  $sanitized = floatval($v);
	  if (($sanitized === false) || (!is_numeric($v))) {
	  	throw new FieldValidationError('n\'est pas une valeur numérique');
	  } else {
		  return $sanitized;
	  }
  }
);

FormValidator::register(
  'positive',
  function ($v) {
	  if ($v < 0) {
		  throw new FieldValidationError('est négatif');
	  } else {
		  return floatval($v);
	  }
  }
);
// Intended to validate checkbox which takes NULL for unchecked
FormValidator::register(
  'boolean',
  function ($v) {
	  if ($v == NULL) {
		  return false;
	  } else {
		  return true;
	  }
  }
);

?>
