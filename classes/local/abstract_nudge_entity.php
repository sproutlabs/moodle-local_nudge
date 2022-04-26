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

// phpcs:disable moodle.Commenting

/*
 * @package     local_nudge\local
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\local;

use coding_exception;
use UnexpectedValueException;
use stdClass;

/**
 * This class is the base entity representation.
 *
 * @package     local_nudge\local
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
     * @var int|null ID of an {@see \core\entity\user} that created this entity.
     */
    public $createdby = null;

    /**
     * @var int|null Timestamp representing the time this entity was created.
     */
    public $timecreated = null;

    /**
     * @var int|null ID of an {@see \core\entity\user} that last modified this entity.
     */
    public $lastmodifiedby = null;

    /**
     * @var int|null Timestamp representing the last time this entity was modified.
     */
    public $lastmodified = null;

    /**
     * Constructs an instance of this record from an array or stdClass.
     *
     * Will attempt to set to set properties on this object using array keys of typecasted $data as follows:
     *      1. If there is a method named set_{fieldname} it will be delegated the handling of loading this property.
     *      2. If the property exists with an identical name it will set directly.
     *      3. If neither of the above work: {@throws UnexpectedValueException}.
     *
     * If you pass nothing the contructor will return immediately giving an instance with nulled fields (casted).
     *
     * @param stdClass|array|null $data The data to wrap with a nudge entity/instance.
     * @throws coding_exception Passed data that cannot be casted to an array.
     */
    public function __construct($data = null) {
        if ($data == null) {

            $this->id = (int) $this->id;
            $this->createdby = (int) $this->createdby;
            $this->timecreated = (int) $this->timecreated;
            $this->lastmodifiedby = (int) $this->lastmodifiedby;
            $this->lastmodified = (int) $this->lastmodified;

            $this->cast_fields();

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

        $this->id = (int) $this->id;
        $this->createdby = (int) $this->createdby;
        $this->timecreated = (int) $this->timecreated;
        $this->lastmodifiedby = (int) $this->lastmodifiedby;
        $this->lastmodified = (int) $this->lastmodified;

        $this->cast_fields();
    }

    /**
     * Fluent setter.
     *
     * @return $this
     */
    public function set_field(string $fieldname, mixed $fieldvalue) {
        $this->{$fieldname} = $fieldvalue;
    }

    /**
     * Casts the fields populated by {@see static::__construct()} to some sane defaults.
     *
     * @return void
     */
    abstract protected function cast_fields(): void;
}
