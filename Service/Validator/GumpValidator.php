<?php
/**
 * GumpValidator.php
 *
 * PHP Version 5.4
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */

namespace c24\Service\Validator;

use \GUMP as RealValidator;

/**
 * Class GumpValidator
 * This is a wrapper class for the GUMP Validator: https://github.com/Wixel/GUMP
 *
 * It implements ValidatorInterface, so if we ever decide to use a different
 * validator say "SuperValidate" all we need to do is create a SuperValidator
 * wrapper/adapter class that also impelemnts ValidatorInterface and we will know
 * 100% that it will plug directly into our existing code, without needing to make
 * any changes to said code.
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
class GumpValidator implements ValidatorInterface
{
    protected $validator;

    /**
     * __construct
     *
     * @param Validator $validator
     *
     * @return void
     * @access public
     */
    public function __construct(RealValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Set a readable name for a specified field names.
     *
     * @param string $field
     * @param string $readable_name
     *
     * @return void
     */
    public function setFieldName($field, $readable_name)
    {
        return $this->validator->set_field_name($field, $readable_name);
    }

    /**
     * Sanitize the input data.
     *
     * @param array $input
     * @param null  $fields
     * @param bool  $utf8_encode
     *
     * @return array
     */
    public function sanitize(array $input, array $fields = array(), $utf8_encode = true)
    {
        return $this->validator->sanitize($input, $fields, $utf8_encode);
    }

    /**
     * Perform data validation against the provided ruleset.
     *
     * @param mixed $input
     * @param array $ruleset
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function validate(array $input, array $ruleset)
    {
        return $this->validator->validator($input, $ruleset);
    }

    /**
     * Process the validation errors and return an array of errors with field names as keys.
     *
     * @param $convert_to_string
     *
     * @return array | null (if empty)
     */
    public function getErrorsArray($convert_to_string = null)
    {
        return $this->validator->get_errors_array($convert_to_string);
    }

    /**
     * Filter the input data according to the specified filter set.
     *
     * @param mixed $input
     * @param array $filterset
     *
     * @throws Exception
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function filter(array $input, array $filterset)
    {
        return $this->validator->filter($input, $filterset);
    }

    public function validationRules(array $rules)
    {
        return $this->validator->validation_rules($rules);
    }

    public function filterRules(array $rules)
    {
        return $this->validator->filter_rules($rules);
    }

    /**
     * Process the validation errors and return human readable error messages.
     *
     * @param bool   $convert_to_string = false
     * @param string $field_class
     * @param string $error_class
     *
     * @return array
     * @return string
     */
    public function getReadableErrors($convert_to_string = false, $field_class = 'gump-field', $error_class = 'gump-error-message')
    {
        return $this->validator->get_readable_errors($convert_to_string, $field_class, $error_class);
    }

    /**
     * run
     *
     * @param mixed $data
     * @param mixed $check_fields
     *
     * @return void
     * @throws [ExceptionClass] [Description]
     * @access
     */
    public function run(array $data, $check_fields = false)
    {
        return $this->validator->run($data, $check_fields);
    }
}

