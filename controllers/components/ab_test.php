<?php
/*
                  #   #   #     #   #   ####    #   #
                  #   #   #     #   #   #   #   #   #
                  #   #   #     #   #   ####    #   #
                  #   #   #     #   #   #   #   #   #
                   ###    ####   ###    #   #    ###

             Copyright 2012 Uluru, Inc. All Rights Reserved.
*/

/**
 * AB Test Component
 *
 * @package     app
 * @subpackage  Component
 * @since       2012/11/01
 * @author      HASHIDA Kazuhide <k_hashida@uluru.jp>
 */
class AbTestComponent extends Component
{
    /**
     * Components
     *
     * @access public
     * @var    array
     */
    public $components = array('Session');

    /**
     * Keep user-configured A/B tests
     *
     * @access private
     * @var    array
     */
    private $tests = array();

    /**
     * Constructor
     *
     * @param  ComponentCollection $collection
     * @return void
     */
    public function __construct()
    {
        $abTestSetting = Configure::read('AbTestSetting');
        if ($this->isAbTestSettingProper($abTestSetting)) {
            $this->tests = $abTestSetting;
        }
    }

    /**
     * Start A/B test.
     *
     * @param   string $key  Abtest Type
     * @return  mixed        switched case
     */
    public function startTest($key)
    {
        if (empty($key)) {
            trigger_error("Given undefined AbTestSetting key.");
            return false;
        }
        $testCase = $this->getAbtestSession($key);
        if ($testCase == false) {
            $testCase = $this->setAbtestSession($key);
        }
        return $testCase;
    }

    /**
     * Get Analytics's JS code for custom value
     *
     * @access public
     * @return string $result
     */
    public function getAnalyticsCustomVar()
    {
        $result = "";
        $abTests = $this->getAbtestSessionAll();
        if (!empty($abTests)) {
            foreach ($abTests as $key => $value) {
                $test = $this->tests[$key];
                $result .= "_gaq.push(['_setCustomVar',".$test['customValueIndex'].",'$key','$value',2]);".PHP_EOL;
            }
        }
        return $result;
    }

    /**
     * Set abtest session.
     *
     * @access private
     * @param   string $key   sesseion key
     * @return  string        set session value
     */
    private function setAbtestSession($key)
    {
        if (empty($key)) {
            return false;
        }
        $values = $this->tests[$key]['values'];
        $sessionValue = (mt_rand(0, 1)) ? $values[0] : $values[1];
        if (!isset($this->Session)) {
            $this->Session = new SessionComponent;
        }
        $this->Session->write('AbTest.'.$key, $sessionValue);
        return $sessionValue;
    }

    /**
     * Get abtest session.
     *
     * @access private
     * @param  string $key   sesseion key
     * @return string value of session
     */
    private function getAbtestSession($key)
    {
        if (empty($key)) {
            return false;
        }
        if (!isset($this->Session)) {
            $this->Session = new SessionComponent();
        }
        return $this->Session->read('AbTest.'.$key);
    }

    /**
     * Get abtest session all.
     *
     * @access private
     * @return string value of session
     */
    private function getAbtestSessionAll()
    {
        if (!isset($this->Session)) {
            $this->Session = new SessionComponent();
        }
        return $this->Session->read('AbTest');
    }

    /**
     * Check Configured "AbTestSetting" is proper or not
     *
     * @param  array   $abTestSetting
     * @return boolean
     */
    private function isAbTestSettingProper($abTestSetting)
    {
        // Check below 5 items
        if (count($abTestSetting) > 5) {
            trigger_error("There are more than 5 items in AbTestSetting configure.");
            return false;
        }
        // Check unique customValueIndex
        $idx = array();
        foreach ($abTestSetting as $value) {
            $idx[] = $value['customValueIndex'];
        }
        sort($idx);
        foreach ($idx as $key => $val) {
            if ($key + 1 != $val) {
                trigger_error("There is ununique 'customValueIndex'.");
                return false;
            }
        }
        return true;
    }
}

