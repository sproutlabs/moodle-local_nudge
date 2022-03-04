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
 * @package     local_nudge\local
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\local;

use UnexpectedValueException;
use coding_exception;

defined('MOODLE_INTERNAL') || die();

class nudge_notification_content
{
    /**
     * @var array<string, mixed> Field defaults.
     */
    const DEFAULTS = [
        'nudgenotificationid' => 0,
        'lang' => 'en',
        'subject' => 'You just got a nudge, Your educator would like to reminder you of some learning.',
        'body' => <<<HTML
        <p>Hi [user_fullname], You are receiving have some unfinished learning from course: [course_fullname].</p>
        <p>You can return to this course to complete it here: [course_link].</p>
        <p>Hope this helps! If you have any questions you can reach out @ [educator_email]</p>
        HTML
    ];

    /**
     * @var int|null Primary key for {@see self}.
     */
    public $id = null;


    /**
     * @var int|null Foreign key for has_one to {@see nudge_notification}.
     */
    public $nudgenotificationid = null;

    /**
     * @var string|null Lang code representing the language this content is intended for.
     */
    public $lang = null;

    /**
     * @var string|null The subject for this notification event.
     */
    public $subject = null;

    /**
     * @var string|null The body of the email to send
     */
    public $body = null;

    /**
     * @param stdClass|array|null $data The data to wrap with a nudge entity/instance.
     * @throws UnexpectedValueException Passed a field that doesn't exist.
     * @throws coding_exception
     */
    public function __construct($data = null)
    {
        if ($data == null) return;
        if (\is_object($data)) $data = (array)$data;
        if (!\is_array($data)) {
            throw new coding_exception(\sprintf('You must provide valid data to %s to wrap a instance of %s', __METHOD__, __CLASS__));
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
     * Returns a limited title for this {@see nudge_notification_content} based off of it's {@see self::$subject}
     *
     * @param int $max_length
     * @return string|null String trimmed to fit its max limit ended with a trailing ellipses.
     */
    public function get_subject_trimmed($max_length = 10)
    {
        $title = $this->subject;

        if (\strlen($title) > $max_length) {
            $trimmed_title = \substr($title, 0, $max_length);
            $title = "{$trimmed_title}...";
        }

        return $title;
    }

    /**
     * Casts the fields populated by {@see self::__construct()} to some sane defaults.
     * 
     * @return void
     */
    private function cast_fields()
    {
        // TODO.
    }
}
