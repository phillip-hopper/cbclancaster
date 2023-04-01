<?php


namespace Nextend\SmartSlider3\Platform\Joomla;


use JAccessExceptionNotallowed;
use JFactory;
use Joomla\CMS\Factory;
use JPluginHelper;
use JText;
use JUri;
use Nextend\Framework\PageFlow;
use Nextend\Framework\Request\Request;
use Nextend\SmartSlider3\Application\ApplicationSmartSlider3;
use Nextend\SmartSlider3\Install\Install;
use Nextend\SmartSlider3\Install\Tables;
use Nextend\SmartSlider3\Platform\SmartSlider3Platform;
use Nextend\SmartSlider3\Settings;
use Nextend\SmartSlider3\SmartSlider3Info;
use plgSystemSmartSlider3;

class AdministratorComponent {

    public function __construct() {

        $this->checkAcl();

        if (!Request::$GET->getInt('keepalive')) {

            /**
             * Required for the license activation to work.
             */
            Factory::getApplication()
                   ->setHeader('cross-origin-opener-policy', 'unsafe-none', true);

            $this->loadSystemPlugins();

            if (Settings::get('n2_ss3_version') != SmartSlider3Info::$completeVersion) {

                Install::install();
            } else if (Request::$REQUEST->getInt('repairss3')) {
                Install::install();

                Tables::repair();
                header('LOCATION: ' . SmartSlider3Platform::getAdminUrl());
                exit;
            }

            $applicationType = ApplicationSmartSlider3::getInstance()
                                                      ->getApplicationTypeAdmin();

            $isAjax = Request::$GET->getInt('nextendajax');

            $applicationType->processRequest('sliders', 'gettingstarted', $isAjax);

            ?>
            <script>
                _N2.r('$', function () {
                    var $ = _N2.$;
                    var __keepAlive = function () {
                        $.get('<?php echo esc_url(JURI::current() . '?option=com_smartslider3&keepalive=1');?>', function () {
                            setTimeout(__keepAlive, 300000);
                        });
                    };
                    setTimeout(__keepAlive, 300000);
                });
            </script>
            <?php
            PageFlow::markApplicationEnd();
        }
    }

    protected function checkAcl() {

        if (!JFactory::getUser()
                     ->authorise('core.manage', 'com_smartslider3')) {
            throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    protected function loadSystemPlugins() {

        if (!class_exists('plgSystemSmartSlider3')) {

            $plugin = JPluginHelper::getPlugin('system', 'smartslider3');
            new plgSystemSmartSlider3(JoomlaShim::getDispatcher(), (array)($plugin));
        }
    }
}