<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/authlib.php');

class auth_plugin_token extends auth_plugin_db {

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password) {
        // normla logins not allowed! ( NORMLA !? )
        return true; // already logged in in pre hook
    }

    function loginpage_hook() {

        global $frm;  // can be used to override submitted login form
        global $user; // can be used to replace authenticate_user_login()
        global $DB;

        $extusername = core_text::convert($_GET['username'], 'utf-8', $this->config->extencoding);
        $extpassword = core_text::convert($_GET['token'], 'utf-8', $this->config->extencoding);

        $frm = new stdClass();
        $frm->username = $extusername;
        $frm->password = $extpassword;

        $authdb = $this->db_init();

        $yesterday = date('Y-m-d', strtotime('-1 DAY'));
        $rs = $authdb->Execute("SELECT t.token
                                FROM user_lms_tokens t INNER JOIN users u ON t.user_id = u.id 
                                WHERE u.email = '".$this->ext_addslashes($extusername)."' 
                                AND t.created_at > '" . $yesterday . "'");
        if (!$rs) {
            $authdb->Close();
            debugging(get_string('auth_dbcantconnect','auth_db'));
            return false;
        }

        if ($rs->EOF) {
            $authdb->Close();
            return false;
        }

        $fields = array_change_key_case($rs->fields, CASE_LOWER);
        $fromdb = $fields['token'];
        $rs->Close();
        $authdb->Close();

        if ($extpassword == $fromdb) {
            $user = $DB->get_record('user', array('username' => $extusername));
        }
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * Webserice auth doesn't use password fields, it uses only tokens.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return false;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return false;
    }

    /**
     * Confirm the new user as registered. This should normally not be used,
     * but it may be necessary if the user auth_method is changed to manual
     * before the user is confirmed.
     */
    function user_confirm($username, $confirmsecret = null) {
        return AUTH_CONFIRM_ERROR;
    }

}
