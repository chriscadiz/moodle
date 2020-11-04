<?php

/**
 * Adhoc task handling course grade resets.
 *
 * @package    core_course
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core\task;

class course_grade_reset extends adhoc_task {

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG;

        require_once("$CFG->dirroot/course/lib.php");
        require_once("$CFG->libdir/gradelib.php");

        $courseid = $this->get_custom_data()->courseid;
        grade_course_reset($courseid);
    }
}