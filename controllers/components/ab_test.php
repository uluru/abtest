<?php
/**
 * AB Test Component
 *
 * @copyright   ULURU.CO.,LTD.
 * @link        https://github.com/uluru
 * @package     app
 * @subpackage  Component
 * @since       2012/11/01
 * @author      TAMURA Yoshiya <y_tamura@uluru.jp>
 */
class AbTestComponent extends Object
{
    /**
     * Keep  A/B test cases of AbTestConfig.
     *
     * @access private
     * @var    array
     */
    private $testCases = array();

    /**
     * Keep expires date of AbTestConfig.
     *
     * @access private
     * @var   integer
     */
    private $expires;

    /**
     * Max customIndexValue which could track on Google Analytics.
     * 5 by default (Premier Account can extend this slot).
     *
     * @var integer
     */
    private $maxCustomIndexValue;

    /**
     * Settings for the Component
     *
     * - expire: cookie expire time(second).
     *           default: 60 * 60 * 24 * 30 = 2592000
     */
    private $settings = array(
        'expires' => 2592000
    );

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $conf = array_merge(
            $this->settings,
            Configure::read('AbTestConfig')
        );

        $this->testCases = $conf['testCases'];
        $this->expires = $conf['expires'];
        $this->maxCustomIndexValue = array_key_exists('maxCustomIndexValue', $conf) ? $conf['maxCustomIndexValue'] : 5 ;
        // Check AbTestConfig is valid or not
        if (!$this->isValidConfig()) {
            return;
        }
    }

    /**
     * Initialize
     *
     * @access public
     * @param  Controller $controller
     * @return void
     */
    public function initialize (Controller $controller)
    {
        // Use Controller's CookieComponent instance.
        // Even if you initialize CookieComponent instance in this class,
        // it doesn't write 'set_cookie' (seems to conflict with Controller's one).
        $this->Cookie = $controller->Cookie;
    }

    /**
     * beforeRender
     *
     * @access public
     * @param  Controller $controller
     * @return void
     */
    public function beforeRender (Controller $controller)
    {
        // Set me(this instance) into viewVars
        // to make AbTestHelper use me.
        $controller->set('AbTestComponent', $this);
    }

    /**
     * Start A/B test.
     *
     * @access  public
     * @param   string $key  Abtest Type
     * @return  mixed        switched case
     */
    public function start($key)
    {
        // Check given A/B Test key is valid or not.
        if (!$this->isValidKey($key)) {
            return false;
        }
        // Get a split A/B value from cookie.
        $testCase = $this->readCookie($key);
        if (is_null($testCase)) {
            $testCase = $this->writeCookie($key);
        }
        // Write log, if more than maxCustomValueIndex Ab Test started on a session.
        $cookies = $this->readCookieAll();
        if (count($cookies) > $this->maxCustomIndexValue) {
            $this->log("Error:[AbTestPlugin] Google Analytics can trace only $this->maxCustomIndexValue custom values at one session. More than $this->maxCustomIndexValue custom values have set.");
        }
        return $testCase;
    }

    /**
     * Get Analytics's JS code for custom value.
     *
     * @access public
     * @return string $result
     */
    public function getAnalyticsCustomVar()
    {
        $result = "";
        $abTests = $this->readCookieAll();
        if (!empty($abTests)) {
            $keys = array();
            foreach ($abTests as $key => $value) {
                $result .= "_gaq.push(['_setCustomVar',".$value['index'].",'$key','".$value['value']."',2]);".PHP_EOL;
                $keys[] = $value['index'];
            }

            // Write log, if same customValueIndex has set in as session.
            if (count($abTests) != count(array_unique($keys))) {
                $this->log("[AbTestPlugin] Same customValueIndex has been set in a session. customValueIndex should be 1 to $this->maxCustomIndexValue by unique.");
            }
        }

        return $result;
    }

    /**
     * Get Universal analytics's JS code for custom value.
     *
     * @access public
     * @return string $result
     */
    public function getUniversalAnalyticsDimensionVar()
    {
        $result = "";
        $abTests = $this->readCookieAll();
        if (!empty($abTests)) {
            $keys = array();
            foreach ($abTests as $key => $value) {
                $result .= "ga('set','dimension" . $value['index'] . "','{$value['value']}');".PHP_EOL;
                $keys[] = $value['index'];
            }
        }

        return $result;
    }

    /**
     * Set abtest cookie.
     *
     * @access private
     * @param   string $key cookie key
     * @return  string      set cookie value
     */
    private function writeCookie($key)
    {
        $values = $this->testCases[$key]['values'];
        $cookieValue = (mt_rand(0, 1)) ? $values[0] : $values[1];
        $this->Cookie->write(
            'AbTest.'.$key,
            array(
                'value' => $cookieValue,
                'index' => $this->testCases[$key]['customValueIndex']
            ),
            true,
            $this->expires
        );
        return $cookieValue;
    }

    /**
     * Get abtest cookie.
     *
     * @access private
     * @param  string $key   cookie key
     * @return string value of cookie
     */
    private function readCookie($key)
    {
        return $this->Cookie->read('AbTest.'.$key.'.value');
    }

    /**
     * Get abtest cookie all.
     *
     * @access private
     * @return string value of cookie
     */
    private function readCookieAll()
    {
        return $this->Cookie->read('AbTest');
    }

    /**
     * Check AbTestConfig is valid or not.
     *
     * @access private
     * @return  boolean
     */
    private function isValidConfig ()
    {
        // Check 'testCases' exists in Configure or not.
        if (is_null($this->testCases)) {
            trigger_error("'testCases' has not been found in AbTestConfig.");
            return false;
        }
        // Check 'expires' exists in Configure or not.
        if (is_null($this->expires)) {
            trigger_error("'expires' has not been found in AbTestConfig.");
            return false;
        }
        // Check 'values' have 2 values.
        $values = array();
        foreach (Set::extract($this->testCases, '{s}.values.{n}') as $a) {
            if (count($a) != 2) {
                trigger_error("AbTestConfig 'values' must have 2 values.");
                return false;
            }
            $values[] = $a[0];
            $values[] = $a[1];
        }
        // Check 'values' are unique between all testcases.
        if ($values != array_unique($values)) {
            trigger_error("There is ununique value in 'values' of AbTestConfig.");
            return false;
        }
        // Check each customValueIndex are between 1 and $this->maxCustomIndexValue or not.
        $indexes = Set::extract($this->testCases, '{s}.customValueIndex');
        foreach ($indexes as $idx) {
            if ($idx > $this->maxCustomIndexValue || $idx < 1) {
                trigger_error("customValueIndex must be set between 1 and $this->maxCustomIndexValue, found '$idx'.");
                return false;
            }
        }
        return true;
    }

    /**
     * Check given A/B Test key is valid or not.
     *
     * @access private
     * @param   array   $key
     * @return  boolean
     */
    private function isValidKey ($key)
    {
        // Check $key has been given or not
        if (empty($key)) {
            trigger_error("AbTestConfig key has not been given.");
            return false;
        }
        // Check valid key has been given or not
        if (is_null($this->testCases[$key])) {
            trigger_error("'$key' is not found in AbTestConfig.");
            return false;
        }
        return true;
    }
}
