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
            '4.18.9',
            '5.6.17',
            '5.11.2',
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

        // GHSA-qcvp-ggx3-97r6
        Event::on(Application::class, WebApplication::EVENT_INIT, function() {
            $request = Craft::$app->getRequest();
            if (
                !$request->getIsConsoleRequest() &&
                (
                    $this->containsSearchConfig($request->getQueryParams()) ||
                    $this->containsSearchConfig($request->getBodyParams())
                )
            ) {
                throw new BadRequestHttpException('Search query configs not accepted on web requests.');
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

    /**
     * Returns whether the given request params contain a nested array with `query` and `attribute` keys,
     * which element queries would treat as a search query config.
     *
     * @param array $params
     * @return bool
     */
    private function containsSearchConfig(array $params): bool
    {
        foreach ($params as $value) {
            if (
                is_array($value) &&
                (isset($value['query'], $value['attribute']) || $this->containsSearchConfig($value))
            ) {
                return true;
            }
        }

        return false;
    }
}
