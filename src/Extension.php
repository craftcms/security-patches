<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license MIT
 */

namespace craft\sp;

use Craft;
use craft\controllers\AssetsController;
use craft\models\UpdateRelease;
use craft\web\Application as WebApplication;
use yii\base\ActionEvent;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Controller;
use yii\base\Event;
use yii\web\BadRequestHttpException;
use craft\events\UpdateReleaseEvent;

/**
 * Security Patches Extension
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 */
class Extension implements BootstrapInterface
{
    private $handledVersions = [
        'craftcms/cms' => [
            '3.9.15',
            '4.14.15',
            '5.6.17',
        ],
    ];

    public function bootstrap($app)
    {
        // CVE-2024-56145
        Event::on(Application::class, WebApplication::EVENT_INIT, function() {
            if (
                !Craft::$app->getRequest()->getIsConsoleRequest() &&
                !empty($_SERVER['argv'])
            ) {
                throw new BadRequestHttpException('argv not accepted on web requests.');
            }
        });

        // GHSA-f3gw-9ww9-jmc3
        Event::on(AssetsController::class, Controller::EVENT_BEFORE_ACTION, function(ActionEvent $event) {
            if ($event->action->id === 'generate-transform') {
                $handle = Craft::$app->request->getBodyParam('handle');
                if ($handle && !is_string($handle)) {
                    throw new BadRequestHttpException('Invalid transform handle.');
                }
            }
        });

        if (
            class_exists(UpdateRelease::class) &&
            defined(sprintf('%s::EVENT_IS_CRITICAL', UpdateRelease::class))
        ) {
            Event::on(UpdateRelease::class, UpdateRelease::EVENT_IS_CRITICAL, function(UpdateReleaseEvent $event) {
                /** @var UpdateRelease $release */
                $release = $event->sender;
                if (
                    isset($this->handledVersions[$event->update->packageName]) &&
                    in_array($release->version, $this->handledVersions[$event->update->packageName])
                ) {
                    $event->handled = true;
                }
            });
        }
    }
}
