<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This class is the base entity representation.
 *
 * @package     local_nudge\local
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\local;

// VSCODE's current pluginset doesn't support typehinted global so we have to type hint them in the local scope.
// phpcs:disable moodle.Commenting.InlineComment.TypeHintingMatch
// phpcs:disable moodle.Commenting.InlineComment.DocBlock

use coding_exception;
use UnexpectedValueException;
use stdClass;

/**
 * @package     local_nudge\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 */
abstract class abstract_nudge_entity {

    /**
     * @access public PHP 7.1 support.
     * @var array<string, mixed> Field defaults.
     */
    const DEFAULTS = [];

    /**
     * @access public
     */
    const SINGULAR_NAME = 'Unknown';

    /**
     * @access public
     */
    const PLURAL_NAME = 'Unknown';

    /**
     * @var int|null Primary key for {@see static}.
     */
    public $id = null;

    /**
     * Constructs an instance of this record from an array or stdClass (preferably returned from the a {@see $DB} recordset).
     *
     * Will attempt to set to set properties on this object using array keys of typecasted $data as follows:
     *      1. If there is a method named set_{fieldname} it will be delegated the handling of loading this property.
     *      2. If the property exists with an identical name it will set directly.
     *      3. If neither of the above work: {@throws UnexpectedValueException}.
     *
     * If you pass null the contructor will return immediately giving an instance with nulled fields.
     *
     * @param stdClass|array|null $data The data to wrap with a nudge entity/instance.
     * @throws coding_exception Passed data that cannot be casted to an array.
     */
    public function __construct($data = null) {
        if ($data == null) {
            return;
        }
        if (\is_object($data)) {
            $data = (array)$data;
        }
        if (!\is_array($data)) {
            throw new coding_exception(\sprintf(
                'You must provide valid data to %s to wrap a instance of %s',
                __METHOD__,
                __CLASS__
            ));
        }

        foreach ($data as $key => $value) {
            $setter = "set_{$key}";
            if (\method_exists($this, $setter)) {
                $this->$setter($key);
                continue;
            }

            if (\property_exists($this, $key)) {
                $this->$key = $value;
                continue;
            }

            throw new UnexpectedValueException(\sprintf(
                '%s\'s %s method was passed a property/field that doesn\'t exist on %s. Property name was: %s',
                __CLASS__,
                __METHOD__,
                __CLASS__,
                $key
            ));
        }

        $this->cast_fields();
    }


    /**
     * Gets a field on this {@see static} limited to a certain number of characters.
     *
     * @param int $maxlength The maximum ammount of characters before terminating with:
     * @param string $endlimiter A few characters to indicate continuation of field.
     * @return string|null
     */
    public function get_field_trimmed($fieldname, $maxlength = 10, $endlimiter = '...') {
        if (!\property_exists($this, $fieldname)) {
            throw new coding_exception(\sprintf(
                'Property %s doesn\'t exist on class %s',
                $fieldname,
                static::class
            ));
        }

        $fieldinstance = $this->$fieldname;

        if (!\is_string($fieldinstance)) {
            throw new coding_exception(\sprintf(
                'Property %s on class %s isn\'t cast to a string',
                $fieldname,
                static::class
            ));
        }

        if (\strlen($fieldinstance) > $maxlength) {
            $trimmedinstance = \substr($fieldinstance, 0, $maxlength);
            $fieldinstance = "{$trimmedinstance}{$endlimiter}";
        }

        return $fieldinstance;
    }

    /**
     * Casts the fields populated by {@see self::__construct()} to some sane defaults.
     *
     * @return void
     */
    protected function cast_fields() {
        // TODO.
    }
}
