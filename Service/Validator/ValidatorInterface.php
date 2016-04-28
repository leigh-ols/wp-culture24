<?php
/**
 * ValidatorInterface.php
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

/**
 * Interface ValidatorInterface
 *
 * @category
 * @package
 * @subpackage
 * @author     Leigh Bicknell <leigh@orangeleaf.com>
 * @license    Copyright Orangeleaf Systems Ltd 2013
 * @link       http://orangeleaf.com
 */
interface ValidatorInterface
{
    /**
     * Set a readable name for a specified field names.
     *
     * @param string $field
     * @param string $readable_name
     *
     * @return void
     */
    public function setFieldName($field, $readable_name);

    /**
     * Sanitize the input data.
     *
     * @param array $input
     * @param null  $fields
     * @param bool  $utf8_encode
     *
     * @return array
     */
    public function sanitize(array $input, array $fields = array(), $utf8_encode = true);

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
    public function validate(array $input, array $ruleset);

    /**
     * Process the validation errors and return an array of errors with field names as keys.
     *
     * @param $convert_to_string
     *
     * @return array | null (if empty)
     */
    public function getErrorsArray($convert_to_string = null);

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
    public function filter(array $input, array $filterset);

    public function validationRules(array $rules);

    public function filterRules(array $rules);
}

