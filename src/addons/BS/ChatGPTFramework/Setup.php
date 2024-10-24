<?php

namespace BS\ChatGPTFramework;

use XF\AddOn\AbstractSetup;

class Setup extends AbstractSetup
{
    private const OLD_ID = 'BS/ChatGPTBots';

    public function checkRequirements(&$errors = [], &$warnings = [])
    {
        $installedAddons = $this->app->addOnManager()->getInstalledAddOns();
        if (isset($installedAddons[self::OLD_ID])) {
            $errors[] = $this->oldAddOnUninstallError();
        }
    }

    protected function oldAddOnUninstallError(): string
    {
        return <<<ERROR
Please uninstall the old version of this add-on before installing this version.
Note: Don't forget to update your API key in the options ([021] ChatGPT Framework) after installation.
ERROR;

    }

    public function install(array $stepParams = [])
    {
    }

    public function upgrade(array $stepParams = [])
    {
    }

    public function uninstall(array $stepParams = [])
    {
    }
}
