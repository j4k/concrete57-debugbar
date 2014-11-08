<?php

namespace Concrete\Package\Concrete5Debug;

require 'vendor/autoload.php';

use DebugBar\DebugBar;
use DebugBar\StandardDebugBar;
use DebugBar\Bridge\DoctrineCollector;
use Core;
use Package;
use Database;
use View;
use Events;
use Illuminate\Support\ServiceProvider;
use Doctrine;

class Controller extends Package {

   protected $pkgHandle = 'concrete5_debug';
   protected $pkgName = 'Concrete Debugbar';
   protected $pkgDescription = 'Concrete5 Debugging information';
   protected $appVersionRequired = '5.7';
   protected $pkgVersion = '0.0.1';

   public function getPackageDescription()
   {
        return t("Concrete Debug");
   }
   public function getPackageName()
   {
        return t("Concrete Debug");
   }

   public function __construct()
   {

   }

    public function install()
    {
        parent::install();
    }

    public function upgrade()
    {
        parent::upgrade();
    }

    public function on_start()
    {
        Core::instance('DebugBar', $bar = new StandardDebugBar());

        $debugStack = new \Doctrine\DBAL\Logging\DebugStack();

        // Cache javascript renderer object.
        $renderer = $bar->getJavascriptRenderer('/packages/concrete5_debug/vendor/maximebf/debugbar/src/DebugBar/Resources/');

        Database::connection()->getConfiguration()->setSQLLogger($debugStack);

        $bar->addCollector(new DoctrineCollector($debugStack));

        // enqueuing on_start means this gets added to everything, even php rendered javascript files, which we want to avoid
        if( ! strpos($_SERVER['REQUEST_URI'], 'i18n_js') > 0 ){
            View::getInstance()->addHeaderItem($renderer->renderHead());
        }

        Events::addListener('on_before_render', function() use ($renderer) {
            View::getInstance()->addFooterItem($renderer->render());
        });
    }

}

?>