<?php
App::import('Components', 'AbTestComponent');

/**
 * AB Test Helper
 *
 * For using A/B test component on views.
 *
 * @copyright   ULURU.CO.,LTD.
 * @link        https://github.com/uluru
 * @package     app
 * @subpackage  Helper
 * @since       2012/11/01
 * @author      TAMURA Yoshiya <a@fude-bako.com>
 */
class AbTestHelper extends AppHelper
{
    private $abTestComponent;

    /**
     * beforeRender
     *
     * @access public
     * @return void
     */
    public function beforeRender ()
    {
        $view = ClassRegistry::getObject('view');
        $this->abTestComponent = $view->getVar("AbTestComponent");
    }

    /**
     * Start A/B test (Wrapper methodo of AbTestComponent).
     *
     * @access public
     * @param   string $key  Abtest Type
     * @return  mixed        switched case
     */
    public function start ($key)
    {
        return $this->abTestComponent->start($key);
    }

    /**
     * Get Analytics's JS code for custom value
     * (Wrapper methodo of AbTestComponent).
     *
     * @access public
     * @return string $result
     */
    public function getAnalyticsCustomVar ()
    {
        return $this->abTestComponent->getAnalyticsCustomVar();
    }
}

